<?php

namespace App\Services;

use App\Enums\DeviceAccessResult;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceAccessService
{
    public function __construct(
        protected DeviceTrackingService $deviceTrackingService,
        protected DeviceSecuritySettingsService $settingsService,
    ) {}

    public function validateLoginDevice(User $user, Request $request): DeviceAccessResult
    {
        if (! $this->settingsService->isEnforcementActiveForUser($user)) {
            return DeviceAccessResult::Allowed;
        }

        $fingerprint = $this->deviceTrackingService->generateDeviceFingerprint($request);
        $device = $this->deviceTrackingService->findDeviceForRequest($user, $request, $fingerprint);

        if ($device?->is_blocked) {
            $this->logDeniedAttempt($user, $request, DeviceAccessResult::Blocked);

            return DeviceAccessResult::Blocked;
        }

        if ($device?->is_trusted) {
            return DeviceAccessResult::Allowed;
        }

        $hasTrustedDevice = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('is_trusted', true)
            ->exists();

        if (! $device && ! $hasTrustedDevice && $this->settingsService->autoTrustFirstDevice()) {
            return DeviceAccessResult::AllowedFirstDevice;
        }

        if (! $device) {
            $this->deviceTrackingService->registerPendingDevice($user, $request);
            $this->logDeniedAttempt($user, $request, DeviceAccessResult::UntrustedNew);

            return DeviceAccessResult::UntrustedNew;
        }

        // Existing pending (untrusted) device — refresh last_used without creating duplicates.
        $this->logDeniedAttempt($user, $request, DeviceAccessResult::UntrustedExisting);

        return DeviceAccessResult::UntrustedExisting;
    }

    public function completeAllowedLogin(User $user, Request $request, DeviceAccessResult $result): UserDevice
    {
        $trustOnCreate = $result === DeviceAccessResult::AllowedFirstDevice;

        return $this->deviceTrackingService->trackDeviceOnLogin($user, $request, $trustOnCreate);
    }

    protected function logDeniedAttempt(User $user, Request $request, DeviceAccessResult $result): void
    {
        Log::info('Device login denied', [
            'user_id' => $user->id,
            'reason' => $result->value,
            'ip' => $request->ip(),
            'fingerprint' => $this->deviceTrackingService->generateDeviceFingerprint($request),
        ]);
    }
}
