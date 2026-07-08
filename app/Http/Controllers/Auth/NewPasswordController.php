<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PasswordCredentialDeliveryService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plainPassword = (string) $request->password;

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::query()->where('email', $request->email)->first();
            if ($user) {
                app(PasswordCredentialDeliveryService::class)->deliver(
                    $user,
                    $plainPassword,
                    PasswordCredentialDeliveryService::CONTEXT_FORGOT_MANUAL
                );
            }
        }

        // Translate status messages to Arabic
        // The status is already a translation key (e.g., 'passwords.reset', 'passwords.user')
        $messages = [
            'passwords.reset' => 'تم إعادة تعيين كلمة المرور بنجاح.',
            'passwords.user' => 'لا يمكننا العثور على مستخدم بهذا البريد الإلكتروني.',
            'passwords.token' => 'رابط إعادة تعيين كلمة المرور غير صحيح أو منتهي الصلاحية.',
            'passwords.throttled' => 'يرجى الانتظار قبل إعادة المحاولة.',
        ];

        $message = $messages[$status] ?? trans($status, [], 'ar');

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', $message)
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => $message]);
    }
}
