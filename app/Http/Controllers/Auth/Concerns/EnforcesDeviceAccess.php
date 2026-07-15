<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Enums\DeviceAccessResult;
use App\Models\User;
use App\Services\DeviceAccessService;
use App\Services\DeviceTrackingService;
use App\Services\SessionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait EnforcesDeviceAccess
{
    protected function finalizeAuthenticatedLogin(
        User $user,
        Request $request,
        DeviceAccessService $deviceAccessService,
        DeviceTrackingService $deviceTrackingService,
        SessionTrackingService $sessionTrackingService,
        string $errorField = 'email',
    ): ?RedirectResponse {
        $accessResult = $deviceAccessService->validateLoginDevice($user, $request);

        if (! $accessResult->isAllowed()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                $errorField => $accessResult->userMessage(),
            ]);
        }

        try {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_device_type' => $this->detectLoginDeviceType($request),
            ]);
        } catch (\Exception) {
            // Columns may be absent in lightweight schemas (e.g. feature tests).
            $user->syncOriginal();
        }

        try {
            $deviceAccessService->completeAllowedLogin($user, $request, $accessResult);
            $sessionTrackingService->startSession($user, $request);
        } catch (\Exception $e) {
            \Log::error('Failed to track device/session on login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    protected function detectLoginDeviceType(Request $request): string
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

    protected function loginRedirectFor(User $user): RedirectResponse
    {
        $fallback = route('frontend.home', absolute: false);

        if ($user->hasRole('admin')) {
            $fallback = route('admin.dashboard', absolute: false);
        } elseif ($user->hasRole('student')) {
            $fallback = route('student.dashboard', absolute: false);
        }

        return redirect()->intended($fallback);
    }
}
