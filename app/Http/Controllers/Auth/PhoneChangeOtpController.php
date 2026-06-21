<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Services\Auth\PhoneOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneChangeOtpController extends Controller
{
    public function __construct(
        private PhoneOtpService $otpService
    ) {}

    public function send(Request $request): RedirectResponse
    {
        if (! $this->otpService->isAvailableFor(OtpPurpose::ChangePhone)) {
            return back()->withErrors(['phone' => 'تغيير الرقم عبر OTP غير متاح.']);
        }

        $request->validate([
            'country_code' => 'required|string',
            'phone' => 'required|string|regex:/^[0-9]{6,14}$/',
        ]);

        $fullPhone = $this->otpService->formatPhoneDisplay(
            (string) $request->input('country_code'),
            (string) $request->input('phone')
        );

        try {
            $this->otpService->send($fullPhone, OtpPurpose::ChangePhone, Auth::user(), $request->ip());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['phone' => $e->getMessage()]);
        }

        session([
            'pending_phone_change' => [
                'country_code' => $request->input('country_code'),
                'phone' => $request->input('phone'),
                'full_phone_digits' => $this->otpService->normalizePhone($fullPhone),
            ],
        ]);

        return redirect()->route('phone-otp.verify', [
            'purpose' => OtpPurpose::ChangePhone->value,
            'phone' => session('pending_phone_change.full_phone_digits'),
        ])->with('status', 'تم إرسال رمز التحقق إلى الرقم الجديد.');
    }

    public function applyVerifiedPhone(Request $request): RedirectResponse
    {
        $pending = session('pending_phone_change');
        if (! is_array($pending)) {
            return redirect()->back()->with('error', 'لا يوجد طلب تغيير رقم معلّق.');
        }

        $phoneDigits = (string) ($pending['full_phone_digits'] ?? '');

        if ($request->filled('code')) {
            try {
                $this->otpService->verify($phoneDigits, OtpPurpose::ChangePhone, (string) $request->input('code'));
                $this->otpService->markSessionVerified($phoneDigits, OtpPurpose::ChangePhone);
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['code' => $e->getMessage()]);
            }
        }

        if (! $this->otpService->hasRecentVerification($phoneDigits, OtpPurpose::ChangePhone)) {
            return redirect()->back()->with('error', 'يجب التحقق من الرقم الجديد أولاً.');
        }

        $user = Auth::user();
        $user->country_code = $pending['country_code'];
        $user->phone = $pending['phone'];
        $user->phone_verified_at = now();

        $profilePending = session('pending_profile_update');
        if (is_array($profilePending)) {
            $user->fill(collect($profilePending)->only([
                'name', 'name_ar', 'date_of_birth', 'gender', 'city', 'address', 'nationality_id',
            ])->toArray());
            if (array_key_exists('is_profile_public', $profilePending)) {
                $user->is_profile_public = (bool) $profilePending['is_profile_public'];
            }
        }

        $user->save();

        session()->forget(['pending_phone_change', 'pending_profile_update']);

        return redirect()->route('student.profile.index')->with('success', 'تم تحديث رقم الهاتف والملف الشخصي.');
    }
}
