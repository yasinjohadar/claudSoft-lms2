<?php

namespace App\Services\Notifications;

use App\Models\SystemSetting;

class NotificationHubSettings
{
    public function channelEnabled(string $channel): bool
    {
        $fromDb = SystemSetting::get("channel_{$channel}_enabled", 'notification_hub', null);
        if ($fromDb !== null) {
            return (bool) $fromDb;
        }

        return (bool) config("notification_hub.channels.{$channel}", false);
    }

    public function eventEnabled(string $eventKey): bool
    {
        $global = SystemSetting::get('events_enabled_default', 'notification_hub', true);
        $fromDb = SystemSetting::get("event_{$eventKey}_enabled", 'notification_hub', null);

        if ($fromDb !== null) {
            return (bool) $fromDb;
        }

        return (bool) $global;
    }
}
