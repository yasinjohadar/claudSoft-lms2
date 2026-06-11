<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use App\Events\N8nWebhookEvent;
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

        return view('auth.register');
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
