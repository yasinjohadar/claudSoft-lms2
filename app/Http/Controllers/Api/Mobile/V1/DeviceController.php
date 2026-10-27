<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Models\NotificationDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * تسجيل توكن FCM لجهاز الطالب (تجهيز للإشعارات خارج التطبيق عند تفعيل Firebase).
 */
class DeviceController extends BaseMobileController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            // العمود في notification_device_tokens بطول 512.
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:android,ios,web'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        NotificationDeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'],
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ],
        );

        return $this->success(null, __('mobile.device_registered'));
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:512']]);

        NotificationDeviceToken::where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->update(['is_active' => false]);

        return $this->success(null);
    }
}
