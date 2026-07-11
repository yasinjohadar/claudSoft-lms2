<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PhoneOtpService;
use App\Events\Registered;
use App\Events\N8nWebhookEvent;
use App\Services\Gamification\ReferralService;
use App\Support\InternationalPhoneDigits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterOtpController extends Controller
{
    public function __construct(
        private PhoneOtpService $otpService
    ) {}

    public function verifyAndRegister(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|min:4|max:8',
            'phone' => 'required|string',
        ]);

        $pending = session('pending_registration');
        if (! is_array($pending)) {
            return redirect()->route('register')->with('error', 'انتهت جلسة التسجيل.');
        }

        try {
            $this->otpService->verify(
                (string) $request->input('phone'),
                OtpPurpose::Register,
                (string) $request->input('code')
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $phoneDigits = InternationalPhoneDigits::fromCountryAndLocal(
            (string) ($pending['country_code'] ?? ''),
            (string) ($pending['phone'] ?? '')
        );

        if ($phoneDigits !== null && User::fullPhoneDigitsTaken($phoneDigits)) {
            return redirect()->route('register')->withErrors([
                'phone' => 'رقم الهاتف مستخدم بالفعل لحساب آخر.',
            ]);
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'],
            'country_code' => $pending['country_code'] ?? null,
            'phone' => $pending['phone'] ?? null,
            'phone_verified_at' => now(),
        ]);

        app(ReferralService::class)->attachReferrer($user, session('referral_code'));
        session()->forget(['pending_registration', 'referral_code']);

        event(new Registered($user));
        event(new N8nWebhookEvent('user.registered', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'registered_at' => now()->toIso8601String(),
        ]));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
