<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PhoneOtpService;
use App\Services\Auth\PasswordResetDeliveryService;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PhoneLoginController extends Controller
{
    public function __construct(
        private PhoneOtpService $otpService,
        private DeviceTrackingService $deviceTrackingService,
        private SessionTrackingService $sessionTrackingService
    ) {}

    public function create(): View|RedirectResponse
    {
        if (! $this->otpService->isAvailableFor(OtpPurpose::Login)) {
            return redirect()->route('login')->with('error', 'تسجيل الدخول برمز OTP غير متاح حالياً.');
        }

        return view('auth.phone-login', [
            'countryCodes' => config('country_codes.list', []),
        ]);
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        if (! $this->otpService->isAvailableFor(OtpPurpose::Login)) {
            return back()->withErrors(['phone' => 'تسجيل الدخول برمز OTP غير متاح حالياً.']);
        }

        $request->validate([
            'country_code' => 'required|string',
            'phone' => 'required|string|regex:/^[0-9]{6,14}$/',
        ]);

        $fullPhone = $this->otpService->formatPhoneDisplay(
            (string) $request->input('country_code'),
            (string) $request->input('phone')
        );

        $user = app(PasswordResetDeliveryService::class)->findUserByPhone($fullPhone);
        if (! $user) {
            return back()->withInput()->withErrors(['phone' => 'لا يوجد حساب مرتبط بهذا الرقم.']);
        }

        if (! $user->is_active) {
            return back()->withInput()->withErrors(['phone' => 'تم إلغاء تفعيل هذا الحساب.']);
        }

        try {
            $this->otpService->send($fullPhone, OtpPurpose::Login, $user, $request->ip());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['phone' => $e->getMessage()]);
        }

        session([
            'phone_login_user_id' => $user->id,
            'phone_login_phone' => $this->otpService->normalizePhone($fullPhone),
        ]);

        return redirect()->route('phone-otp.verify', [
            'purpose' => OtpPurpose::Login->value,
            'phone' => session('phone_login_phone'),
        ])->with('status', 'تم إرسال رمز الدخول إلى واتساب.');
    }

    public function verifyAndLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|min:4|max:8',
        ]);

        $phone = (string) session('phone_login_phone');
        $userId = session('phone_login_user_id');

        if ($phone === '' || ! $userId) {
            return redirect()->route('phone-login')->with('error', 'انتهت جلسة الدخول. أعد المحاولة.');
        }

        try {
            $this->otpService->verify($phone, OtpPurpose::Login, (string) $request->input('code'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->is_active) {
            session()->forget(['phone_login_user_id', 'phone_login_phone']);

            return redirect()->route('phone-login')->with('error', 'تعذّر تسجيل الدخول.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        session()->forget(['phone_login_user_id', 'phone_login_phone']);

        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            $this->deviceTrackingService->trackDeviceOnLogin($user, $request);
            $this->sessionTrackingService->startSession($user, $request);
        } catch (\Throwable) {
        }

        $fallback = route('frontend.home', absolute: false);
        if ($user->hasRole('admin')) {
            $fallback = route('admin.dashboard', absolute: false);
        } elseif ($user->hasRole('student')) {
            $fallback = route('student.dashboard', absolute: false);
        }

        return redirect()->intended($fallback);
    }
}
