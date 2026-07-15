<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class SessionDeviceBindingService
{
    public const SESSION_KEY = 'bound_user_device_id';

    public function __construct(
        protected DeviceSecuritySettingsService $settingsService,
        protected DeviceTrackingService $deviceTrackingService,
    ) {}

    /**
     * Store the current request's device id on the session after login.
     */
    public function bind(User $user, Request $request, ?UserDevice $device = null): void
    {
        if (! $this->settingsService->isSessionDeviceBindingActiveForUser($user)) {
            return;
        }

        $device ??= $this->deviceTrackingService->findDeviceForRequest($user, $request);

        if (! $device || (int) $device->user_id !== (int) $user->id) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, (int) $device->id);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Validate that the current request still matches the device bound to this session.
     *
     * @return bool true when the request may continue
     */
    public function validate(User $user, Request $request): bool
    {
        if (! $this->settingsService->isSessionDeviceBindingActiveForUser($user)) {
            return true;
        }

        $boundId = $request->session()->get(self::SESSION_KEY);

        if (! $boundId) {
            // Legacy / first request after enabling: claim only when we can identify the device.
            $device = $this->deviceTrackingService->findDeviceForRequest($user, $request);
            if (! $device) {
                return false;
            }

            $request->session()->put(self::SESSION_KEY, (int) $device->id);

            return ! $device->is_blocked;
        }

        $boundDevice = UserDevice::query()
            ->whereKey((int) $boundId)
            ->where('user_id', $user->id)
            ->first();

        if (! $boundDevice) {
            return false;
        }

        if ($boundDevice->is_blocked) {
            return false;
        }

        $current = $this->deviceTrackingService->findDeviceForRequest($user, $request);

        if (! $current) {
            return false;
        }

        return (int) $current->id === (int) $boundDevice->id;
    }
}
