<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use App\Support\LocalDevLoginGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocalDevLoginController extends Controller
{
    public function __construct(
        protected DeviceTrackingService $deviceTrackingService,
        protected SessionTrackingService $sessionTrackingService
    ) {
    }

    public function show(): View
    {
        abort_unless(LocalDevLoginGate::isAvailable(), 404);

        return view('auth.local-dev-login', [
            'adminEmail' => config('local-dev-login.admin_email', 'admin@admin.com'),
            'studentEmail' => config('local-dev-login.student_email', 'student@gmail.com'),
            'accessPath' => LocalDevLoginGate::path(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        abort_unless(LocalDevLoginGate::isAvailable(), 404);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,student'],
        ]);

        $role = $validated['role'];
        $email = $role === 'admin'
            ? config('local-dev-login.admin_email', 'admin@admin.com')
            : config('local-dev-login.student_email', 'student@gmail.com');

        $user = User::role($role)->where('email', $email)->first()
            ?? User::role($role)->first();

        if (!$user) {
            return back()->with('error', 'لم يُعثَر على مستخدم بدور «' . ($role === 'admin' ? 'أدمن' : 'طالب') . '».');
        }

        Auth::login($user, remember: true);

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'الحساب غير مفعّل.');
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
            \Log::error('Failed to track device/session on local dev login', [
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

    protected function detectDevice(Request $request): string
    {
        $ua = $request->header('User-Agent', '');

        if (stripos($ua, 'mobile') !== false) {
            return 'mobile';
        }

        if (stripos($ua, 'tablet') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }
}
