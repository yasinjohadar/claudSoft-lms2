<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected DeviceTrackingService $deviceTrackingService;
    protected SessionTrackingService $sessionTrackingService;

    public function __construct(
        DeviceTrackingService $deviceTrackingService,
        SessionTrackingService $sessionTrackingService
    ) {
        $this->deviceTrackingService = $deviceTrackingService;
        $this->sessionTrackingService = $sessionTrackingService;
    }
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

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
            ]);
        }

        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_device_type' => $this->detectDevice($request),
            ]);
        } catch (\Exception $e) {
            // Ignore if columns don't exist
        }

        try {
            $this->deviceTrackingService->trackDeviceOnLogin($user, $request);
            $this->sessionTrackingService->startSession($user, $request);
        } catch (\Exception $e) {
            \Log::error('Failed to track device/session on login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $request->session()->regenerate();

        $fallback = route('frontend.home', absolute: false);
        if ($user->hasRole('admin')) {
            $fallback = route('admin.dashboard', absolute: false);
        } elseif ($user->hasRole('student')) {
            $fallback = route('student.dashboard', absolute: false);
        }

        return redirect()->intended($fallback);
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
        // End current session tracking
        try {
            $this->sessionTrackingService->endSession(null, $request);
        } catch (\Exception $e) {
            \Log::error('Failed to end session on logout', [
                'error' => $e->getMessage(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
