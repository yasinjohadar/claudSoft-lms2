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
            $act = self::activityFallback((string) $data['activity_key']);
            if (is_array($act) && ! empty($act['title'])) {
                return (string) $act['title'];
            }
        }

        $ev = self::eventFallback($eventKey);
        if (is_array($ev) && ! empty($ev['title'])) {
            return (string) $ev['title'];
        }

        return self::genericTitleFromKey($eventKey);
    }

    public static function bodyTemplate(string $eventKey, array $data): string
    {
        if ($eventKey === 'student.activity.tracked' && ! empty($data['activity_key'])) {
            $act = self::activityFallback((string) $data['activity_key']);
            if (is_array($act) && ! empty($act['body'])) {
                return (string) $act['body'];
            }
        }

        $ev = self::eventFallback($eventKey);
        if (is_array($ev) && ! empty($ev['body'])) {
            return (string) $ev['body'];
        }

        return 'لديك تحديث جديد على المنصة.';
    }

    /**
     * Event keys are dot-namespaced (e.g. "student.lesson.completed"), so a plain
     * config('notification_hub.event_fallbacks.'.$eventKey) lookup can never reach them —
     * Laravel's dot-notation resolver treats every dot as a nesting level. Fetch the
     * whole map once and index into it with a literal array key instead.
     */
    private static function eventFallback(string $eventKey): ?array
    {
        $all = config('notification_hub.event_fallbacks', []);

        return is_array($all[$eventKey] ?? null) ? $all[$eventKey] : null;
    }

    private static function activityFallback(string $activityKey): ?array
    {
        $all = config('notification_hub.activity_fallbacks', []);

        return is_array($all[$activityKey] ?? null) ? $all[$activityKey] : null;
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
