<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * الإشعارات الفورية (Reverb) لتطبيق الجوال.
 *
 * - config(): نفس القيم العامة التي تستخدمها صفحة الطالب في الويب (footer-scripts.blade.php)
 *   حتى لا يُكتب أي إعداد ثابتاً داخل التطبيق.
 * - auth(): تفويض القنوات الخاصة بتوكن Sanctum (مسار الويب /broadcasting/auth يعمل بالجلسة فقط).
 *   يعيد استخدام تعريفات routes/channels.php نفسها.
 */
class RealtimeController extends BaseMobileController
{
    public function config(Request $request): JsonResponse
    {
        $reverb = config('broadcasting.connections.reverb');
        $enabled = config('broadcasting.default') === 'reverb'
            && config('notification_hub.channels.realtime', true)
            && ! empty($reverb['key']);

        return $this->success([
            'enabled' => (bool) $enabled,
            'key' => $enabled ? $reverb['key'] : null,
            'host' => config('reverb.servers.reverb.hostname', '127.0.0.1'),
            'port' => (int) config('reverb.servers.reverb.port', 8080),
            'scheme' => config('reverb.servers.reverb.options.scheme', 'http'),
            'channel' => 'private-user.notifications.'.$request->user()->id,
            'event' => 'notification.created',
        ]);
    }

    public function auth(Request $request)
    {
        return Broadcast::auth($request);
    }
}
