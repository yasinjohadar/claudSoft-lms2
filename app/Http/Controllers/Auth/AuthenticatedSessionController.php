<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
    //  */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     // التحقق من أن المستخدم نشط
    //     $user = Auth::user();
    //     if (!$user->is_active) {
    //         Auth::logout();
    //         $request->session()->invalidate();
    //         $request->session()->regenerateToken();

    //         return back()->withErrors([
    //             'email' => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
    //         ]);
    //     }

    //     $request->session()->regenerate();

    //     return redirect()->intended(route('dashboard', absolute: false));
    // }


    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // التحقق من تفعيل الحساب
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
            ]);
        }

        // حدّث بيانات آخر دخول (اختياري) - فقط إذا كانت الأعمدة موجودة
        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_device_type' => $this->detectDevice($request),
            ]);
        } catch (\Exception $e) {
            // Ignore if columns don't exist
        }

        $request->session()->regenerate();

        // التوجيه حسب الدور عبر spatie
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        // احتياط: لو لم يكن له أي دور
        return redirect()->route('home');
    }

    // مساعد بسيط لاكتشاف نوع الجهاز
    protected function detectDevice($request): string
    {
        $ua = $request->header('User-Agent', '');
        if (stripos($ua, 'mobile') !== false)
            return 'mobile';
        if (stripos($ua, 'tablet') !== false)
            return 'tablet';
        return 'desktop';
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
