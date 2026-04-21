<?php

namespace App\Services\Notifications;

/**
 * نصوص افتراضية عربية عندما لا يوجد قالب في notification_templates أو لا يُمرَّر title/body صريحان.
 */
class NotificationHubFallbackCopy
{
    /**
     * قوالب عنوان/نص قبل تمريرها على TemplateRenderer (تدعم {{placeholders}}).
     */
    public static function titleTemplate(string $eventKey, array $data): string
    {
        if ($eventKey === 'student.activity.tracked' && ! empty($data['activity_key'])) {
            $act = config('notification_hub.activity_fallbacks.'.$data['activity_key']);
            if (is_array($act) && ! empty($act['title'])) {
                return (string) $act['title'];
            }
        }

        $ev = config('notification_hub.event_fallbacks.'.$eventKey);
        if (is_array($ev) && ! empty($ev['title'])) {
            return (string) $ev['title'];
        }

        return self::genericTitleFromKey($eventKey);
    }

    public static function bodyTemplate(string $eventKey, array $data): string
    {
        if ($eventKey === 'student.activity.tracked' && ! empty($data['activity_key'])) {
            $act = config('notification_hub.activity_fallbacks.'.$data['activity_key']);
            if (is_array($act) && ! empty($act['body'])) {
                return (string) $act['body'];
            }
        }

        $ev = config('notification_hub.event_fallbacks.'.$eventKey);
        if (is_array($ev) && ! empty($ev['body'])) {
            return (string) $ev['body'];
        }

        return 'لديك تحديث جديد على المنصة.';
    }

    /**
     * آخر خيار: عدم عرض مفتاح تقني كعنوان.
     */
    public static function genericTitleFromKey(string $eventKey): string
    {
        $map = config('notification_hub.event_title_short', []);

        return is_string($map[$eventKey] ?? null) ? $map[$eventKey] : 'إشعار من المنصة';
    }
}
