<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\Auth\Concerns\EnforcesDeviceAccess;
use App\Services\DeviceAccessService;
use App\Services\DeviceTrackingService;
use App\Services\SessionDeviceBindingService;
use App\Services\SessionTrackingService;
use App\Services\SingleSessionService;
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
        protected SingleSessionService $singleSessionService,
        protected SessionDeviceBindingService $sessionDeviceBindingService,
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
        $this->singleSessionService->enforce($user, $request);
        $this->sessionDeviceBindingService->bind($user, $request);
        // Keep the stamp aligned with the session id that the client will actually
        // present on the next request (array driver / cookie hand-off edge cases).
        $request->session()->put('_single_session_stamp', $request->session()->getId());

        return $this->loginRedirectFor($user);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user() ?? Auth::user();

        try {
            $this->sessionTrackingService->endSession(null, $request);
        } catch (\Exception $e) {
            \Log::error('Failed to end session on logout', [
                'error' => $e->getMessage(),
            ]);
        }

        if ($user) {
            $activeSessionId = \App\Models\User::query()
                ->whereKey($user->id)
                ->value('active_session_id');
            $currentSessionId = $request->session()->getId();

            // Clear when this browser owns the active stamp, or when stamp was just
            // re-claimed below / not yet set. Never wipe another device's stamp.
            $ownsSession = $activeSessionId === null
                || $activeSessionId === ''
                || hash_equals((string) $activeSessionId, (string) $currentSessionId)
                || (
                    is_string($request->session()->get('_single_session_stamp'))
                    && hash_equals((string) $activeSessionId, (string) $request->session()->get('_single_session_stamp'))
                );

            if ($ownsSession) {
                $this->singleSessionService->clear($user);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
