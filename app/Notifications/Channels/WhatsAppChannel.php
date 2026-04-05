<?php

namespace App\Notifications\Channels;

use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function __construct(
        private SendWhatsAppMessage $sender,
        private WhatsAppSettingsService $settingsService,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = $notifiable->whatsapp_number ?? null;
        if (empty($to)) {
            return;
        }

        $settings = $this->settingsService->getSettings();
        if (empty($settings['whatsapp_enabled'])) {
            return;
        }

        $text = $notification->toWhatsApp($notifiable);
        if ($text === null || $text === '') {
            return;
        }

        try {
            $this->sender->sendText($to, $text, true);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('WhatsAppChannel: failed to queue study report message', [
                'notifiable_id' => $notifiable->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
