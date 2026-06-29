<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\EnforcesDeviceAccess;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceAccessService;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use App\Support\LocalDevLoginGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocalDevLoginController extends Controller
{
    use EnforcesDeviceAccess;

    public function __construct(
        protected DeviceTrackingService $deviceTrackingService,
        protected SessionTrackingService $sessionTrackingService,
        protected DeviceAccessService $deviceAccessService,
    ) {}

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

        if (! $user) {
            return back()->with('error', 'لم يُعثَر على مستخدم بدور «'.($role === 'admin' ? 'أدمن' : 'طالب').'».');
        }

        Auth::login($user, remember: true);

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'الحساب غير مفعّل.');
        }

        $deviceDenied = $this->finalizeAuthenticatedLogin(
            $user,
            $request,
            $this->deviceAccessService,
            $this->deviceTrackingService,
            $this->sessionTrackingService,
            'role',
        );

        if ($deviceDenied) {
            return $deviceDenied;
        }

        $request->session()->regenerate();

        return $this->loginRedirectFor($user);
    }
}
