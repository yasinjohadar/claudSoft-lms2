<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;

class DeviceSecuritySettingsService
{
    public const GROUP = 'device_security';

    public function isGloballyEnabled(): bool
    {
        return (bool) SystemSetting::get('trusted_devices_only_enabled', self::GROUP, false);
    }

    public function autoTrustFirstDevice(): bool
    {
        return (bool) SystemSetting::get('auto_trust_first_device', self::GROUP, true);
    }

    public function isEnforcementActiveForUser(User $user): bool
    {
        $mode = $user->device_lock_mode ?? 'inherit';

        return match ($mode) {
            'enabled' => true,
            'disabled' => false,
            default => $this->isGloballyEnabled(),
        };
    }

    /**
     * @return array{trusted_devices_only_enabled: bool, auto_trust_first_device: bool}
     */
    public function all(): array
    {
        return [
            'trusted_devices_only_enabled' => $this->isGloballyEnabled(),
            'auto_trust_first_device' => $this->autoTrustFirstDevice(),
        ];
    }

    public function update(array $settings): void
    {
        SystemSetting::set(
            'trusted_devices_only_enabled',
            ! empty($settings['trusted_devices_only_enabled']),
            'boolean',
            self::GROUP,
            'السماح بتسجيل الدخول من الأجهزة الموثوقة فقط'
        );

        SystemSetting::set(
            'auto_trust_first_device',
            ! empty($settings['auto_trust_first_device']),
            'boolean',
            self::GROUP,
            'توثيق أول جهاز للمستخدم تلقائياً عند تفعيل السياسة'
        );
    }

    public function seedDefaults(): void
    {
        if (SystemSetting::where('key', 'trusted_devices_only_enabled')->where('group', self::GROUP)->doesntExist()) {
            SystemSetting::set('trusted_devices_only_enabled', false, 'boolean', self::GROUP, 'السماح بتسجيل الدخول من الأجهزة الموثوقة فقط');
        }

        if (SystemSetting::where('key', 'auto_trust_first_device')->where('group', self::GROUP)->doesntExist()) {
            SystemSetting::set('auto_trust_first_device', true, 'boolean', self::GROUP, 'توثيق أول جهاز للمستخدم تلقائياً');
        }
    }
}
