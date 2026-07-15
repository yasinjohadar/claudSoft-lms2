<?php

namespace App\Services;

use App\Models\CourseGroup;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    public function isSingleSessionGloballyEnabled(): bool
    {
        return (bool) SystemSetting::get('single_session_enabled', self::GROUP, false);
    }

    public function isSessionDeviceBindingGloballyEnabled(): bool
    {
        return (bool) SystemSetting::get('bind_session_to_device_enabled', self::GROUP, false);
    }

    public function isEnforcementActiveForUser(User $user): bool
    {
        $mode = $user->device_lock_mode ?? 'inherit';

        return match ($mode) {
            'enabled' => true,
            'disabled' => false,
            default => $this->isGloballyEnabled() || $this->belongsToRestrictedGroup($user),
        };
    }

    /**
     * Whether only one concurrent web session is allowed for this user.
     * Reuses the same per-user / restricted-group override model as trusted devices.
     */
    public function isSingleSessionActiveForUser(User $user): bool
    {
        $mode = $user->device_lock_mode ?? 'inherit';

        return match ($mode) {
            'enabled' => true,
            'disabled' => false,
            default => $this->isSingleSessionGloballyEnabled() || $this->belongsToRestrictedGroup($user),
        };
    }

    /**
     * Whether the web session must stay bound to the device that authenticated it.
     * Independent toggle from single-session; same per-user / group override model.
     */
    public function isSessionDeviceBindingActiveForUser(User $user): bool
    {
        $mode = $user->device_lock_mode ?? 'inherit';

        return match ($mode) {
            'enabled' => true,
            'disabled' => false,
            default => $this->isSessionDeviceBindingGloballyEnabled() || $this->belongsToRestrictedGroup($user),
        };
    }

    /**
     * Whether the user belongs to at least one group with device lock enabled.
     */
    public function belongsToRestrictedGroup(User $user): bool
    {
        if (! $user->exists) {
            return false;
        }

        return DB::table('course_group_members')
            ->join('course_groups', 'course_groups.id', '=', 'course_group_members.group_id')
            ->where('course_group_members.student_id', $user->id)
            ->where('course_groups.device_lock_enabled', true)
            ->whereNull('course_groups.deleted_at')
            ->exists();
    }

    /**
     * Replace the groups whose members must use trusted devices.
     *
     * @param  array<int, int|string>  $groupIds
     */
    public function syncRestrictedGroups(array $groupIds): void
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($groupIds): void {
            CourseGroup::query()->update(['device_lock_enabled' => false]);

            if ($groupIds !== []) {
                CourseGroup::query()
                    ->whereIn('id', $groupIds)
                    ->update(['device_lock_enabled' => true]);
            }
        });
    }

    /**
     * @return array{
     *     trusted_devices_only_enabled: bool,
     *     auto_trust_first_device: bool,
     *     single_session_enabled: bool,
     *     bind_session_to_device_enabled: bool
     * }
     */
    public function all(): array
    {
        return [
            'trusted_devices_only_enabled' => $this->isGloballyEnabled(),
            'auto_trust_first_device' => $this->autoTrustFirstDevice(),
            'single_session_enabled' => $this->isSingleSessionGloballyEnabled(),
            'bind_session_to_device_enabled' => $this->isSessionDeviceBindingGloballyEnabled(),
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

        SystemSetting::set(
            'single_session_enabled',
            ! empty($settings['single_session_enabled']),
            'boolean',
            self::GROUP,
            'جلسة واحدة نشطة فقط لكل مستخدم (إنهاء الجلسات الأخرى)'
        );

        SystemSetting::set(
            'bind_session_to_device_enabled',
            ! empty($settings['bind_session_to_device_enabled']),
            'boolean',
            self::GROUP,
            'ربط الجلسة بالجهاز الذي تم تسجيل الدخول منه'
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

        if (SystemSetting::where('key', 'single_session_enabled')->where('group', self::GROUP)->doesntExist()) {
            SystemSetting::set('single_session_enabled', false, 'boolean', self::GROUP, 'جلسة واحدة نشطة فقط لكل مستخدم');
        }

        if (SystemSetting::where('key', 'bind_session_to_device_enabled')->where('group', self::GROUP)->doesntExist()) {
            SystemSetting::set('bind_session_to_device_enabled', false, 'boolean', self::GROUP, 'ربط الجلسة بالجهاز الذي تم تسجيل الدخول منه');
        }
    }
}
