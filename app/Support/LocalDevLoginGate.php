<?php

namespace App\Support;

use App\Models\SiteSetting;

class LocalDevLoginGate
{
    public static function isEnvironmentLocal(): bool
    {
        return app()->environment('local');
    }

    public static function isSettingEnabled(): bool
    {
        return SiteSetting::isLocalDevLoginEnabled();
    }

    public static function isAvailable(): bool
    {
        return static::isEnvironmentLocal() && static::isSettingEnabled();
    }

    public static function path(): string
    {
        $path = trim((string) config('local-dev-login.path', '_dev/platform-access'), '/');

        return $path !== '' ? $path : '_dev/platform-access';
    }

    public static function url(): string
    {
        return url(static::path());
    }
}
