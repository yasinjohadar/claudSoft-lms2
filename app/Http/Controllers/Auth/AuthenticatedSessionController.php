<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\Auth\Concerns\EnforcesDeviceAccess;
use App\Services\DeviceAccessService;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use EnforcesDeviceAccess;

    public function __construct(
        protected DeviceTrackingService $deviceTrackingService,
        protected SessionTrackingService $sessionTrackingService,
        protected DeviceAccessService $deviceAccessService,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'تم إلغاء تفعيل حسابك. يرجى التواصل مع الإدارة.',
            ]);
        }

        $deviceDenied = $this->finalizeAuthenticatedLogin(
            $user,
            $request,
            $this->deviceAccessService,
            $this->deviceTrackingService,
            $this->sessionTrackingService,
        );

        if ($deviceDenied) {
            return $deviceDenied;
        }

        $request->session()->regenerate();

        return $this->loginRedirectFor($user);
    }

    public function destroy(Request $request): RedirectResponse
    {
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
