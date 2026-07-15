<?php

namespace App\Notifications;

use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUntrustedDeviceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public UserDevice $device
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $user = $this->device->user;
        $userName = $user?->name_ar ?: ($user?->name ?? 'مستخدم');
        $browser = trim(($this->device->browser ?? '') . ' ' . ($this->device->platform ?? ''));

        return [
            'type' => 'new_untrusted_device',
            'title' => 'جهاز جديد بانتظار الموافقة',
            'message' => sprintf(
                'حاول %s تسجيل الدخول من جهاز جديد (%s).',
                $userName,
                $browser !== '' ? $browser : 'غير محدد'
            ),
            'device_id' => $this->device->id,
            'user_id' => $this->device->user_id,
            'action_url' => route('admin.user-devices.show', $this->device->id),
            'icon' => 'device',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
