<?php

namespace App\Services\WhatsApp\AutoReply;

use App\Services\WhatsApp\Evolution\EvolutionApiClient;
use App\Services\WhatsApp\Evolution\EvolutionService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Log;

class WhatsAppAutoReplyPresenceService
{
    public function __construct(
        private WhatsAppSettingsService $settingsService,
        private EvolutionService $evolutionService,
    ) {}

    public function sendComposing(string $instanceName, string $recipient, int $delayMs): void
    {
        if ($instanceName === '' || $recipient === '') {
            return;
        }

        try {
            // بيانات اعتماد الـ instance نفسه لا العامة: الإرسال يمرّ عبر
            // providerConfigForInstance بينما كان هذا يستخدم المفتاح العام،
            // فيفشل مؤشّر «يكتب…» بـ 401 وحده رغم نجاح إرسال الرسالة.
            $config = $this->evolutionService->providerConfigForInstance($instanceName);
            $client = EvolutionApiClient::fromConfig($config);
            $client->sendChatPresence($instanceName, $recipient, 'composing', $delayMs);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->warning('AutoReply: typing presence failed', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
