<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use App\Events\N8nWebhookEvent;
use App\Services\Auth\PhoneOtpService;
use App\Enums\OtpPurpose;
use App\Services\Gamification\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // التحقق من تفعيل التسجيل العام
        if (!SiteSetting::isPublicRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('error', 'التسجيل العام معطل حالياً. يرجى التواصل مع الإدارة أو استخدام حساب موجود.');
        }

        if ($request->filled('ref')) {
            session(['referral_code' => $request->query('ref')]);
        }

        $otpService = app(PhoneOtpService::class);

        return view('auth.register', [
            'otpRegisterAvailable' => $otpService->isAvailableFor(OtpPurpose::Register),
            'countryCodes' => config('country_codes.list', []),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // التحقق من تفعيل التسجيل العام
        if (!SiteSetting::isPublicRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('error', 'التسجيل العام معطل حالياً. يرجى التواصل مع الإدارة أو استخدام حساب موجود.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_code' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{6,14}$/'],
        ]);

        $otpService = app(PhoneOtpService::class);
        $phoneProvided = $request->filled('country_code') && $request->filled('phone');

        if ($phoneProvided && $otpService->isAvailableFor(OtpPurpose::Register)) {
            $fullPhone = $otpService->formatPhoneDisplay(
                (string) $request->input('country_code'),
                (string) $request->input('phone')
            );

            try {
                $otpService->send($fullPhone, OtpPurpose::Register, null, $request->ip());
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['phone' => $e->getMessage()]);
            }

            session([
                'pending_registration' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'country_code' => $request->input('country_code'),
                    'phone' => $request->input('phone'),
                ],
            ]);

            return redirect()->route('phone-otp.verify', [
                'purpose' => OtpPurpose::Register->value,
                'phone' => $otpService->normalizePhone($fullPhone),
            ])->with('status', 'تم إرسال رمز التحقق إلى واتساب. أكمل التسجيل.');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_code' => $request->input('country_code'),
            'phone' => $request->input('phone'),
        ]);

        $referralService = app(ReferralService::class);
        $referralService->attachReferrer(
            $user,
            $request->input('ref') ?? session('referral_code')
        );
        session()->forget('referral_code');

        event(new Registered($user));

        // Dispatch n8n webhook event
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
