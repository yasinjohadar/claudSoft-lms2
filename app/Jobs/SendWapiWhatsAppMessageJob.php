<?php

namespace App\Jobs;

use App\Enums\WapiMessageStatus;
use App\Enums\WapiMessageType;
use App\Models\WapiMessage;
use App\Models\WapiTemplateVariableLog;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendWapiWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 90];
    }

    public function __construct(public WapiMessage $wapiMessage) {}

    public function handle(WhatsAppService $service): void
    {
        $message = $this->wapiMessage->fresh();
        if (! $message) {
            return;
        }

        $content = $message->content ?? [];
        $attachmentRelative = $content['attachment_storage_path'] ?? null;
        $abs = null;
        if (is_string($attachmentRelative) && $attachmentRelative !== '') {
            $candidate = Storage::disk('local')->path($attachmentRelative);
            $abs = is_readable($candidate) ? $candidate : null;
        }

        try {
            $service->assertConfigured();

            $result = match ($message->type) {
                WapiMessageType::Message => $service->sendMessage(
                    (string) $message->phone,
                    (string) ($content['message'] ?? ''),
                    $abs,
                    (string) ($content['header'] ?? ''),
                    (string) ($content['footer'] ?? ''),
                    (string) ($content['buttons'] ?? ''),
                ),
                WapiMessageType::Template => $service->sendTemplate(
                    (string) $message->phone,
                    (string) $content['template_name'],
                    (string) $content['language'],
                    is_array($content['components'] ?? null) ? $content['components'] : [],
                    $abs,
                ),
                WapiMessageType::Campaign => $this->sendCampaignWithTrigger($service, $content),
            };

            $message->update([
                'status' => $result['status'],
                'response' => $result['log_payload'],
            ]);

            $this->logTemplateVariablesIfNeeded($message, $content, $result['status']);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('[Flaxxa WAPI] job failed', [
                'wapi_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);

            $message->update([
                'status' => WapiMessageStatus::Failed,
                'response' => [
                    'exception' => $e->getMessage(),
                ],
            ]);

            throw $e;
        } finally {
            if (is_string($attachmentRelative) && $attachmentRelative !== '') {
                Storage::disk('local')->delete($attachmentRelative);
            }
        }
    }

    protected function logTemplateVariablesIfNeeded(WapiMessage $message, array $content, WapiMessageStatus $status): void
    {
        if ($message->type !== WapiMessageType::Template) {
            return;
        }
        if (! in_array($status, [WapiMessageStatus::Sent, WapiMessageStatus::SentPendingConfirmation], true)) {
            return;
        }
        $tid = $content['wapi_template_id'] ?? null;
        if ($tid === null) {
            return;
        }

        WapiTemplateVariableLog::query()->create([
            'wapi_template_id' => (int) $tid,
            'variables' => (array) ($content['variables_log'] ?? []),
            'created_at' => now(),
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::channel('whatsapp')->error('[Flaxxa WAPI] job exhausted', [
            'wapi_message_id' => $this->wapiMessage->id,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array{response: \Illuminate\Http\Client\Response, status: WapiMessageStatus, log_payload: array<string, mixed>}
     */
    protected function sendCampaignWithTrigger(WhatsAppService $service, array $content): array
    {
        $createResult = $service->sendCampaign(
            (string) $content['name'],
            (string) $content['template_id'],
            (string) $content['group_id'],
            is_array($content['campaign_body'] ?? null) ? $content['campaign_body'] : [],
        );

        if ($createResult['status'] === WapiMessageStatus::Failed) {
            return $createResult;
        }

        $json = $createResult['log_payload']['json'] ?? [];
        $campaignId = $json['campaign_id'] ?? $json['id'] ?? data_get($json, 'data.id');

        if ($campaignId === null) {
            $createResult['log_payload']['trigger_skipped'] = 'no campaign_id in Create_Campaign response';

            return $createResult;
        }

        $triggerResult = $service->triggerCampaign(
            $campaignId,
            (string) $content['group_id'],
            is_array($content['campaign_body'] ?? null) ? $content['campaign_body'] : [],
        );

        $createResult['log_payload']['trigger'] = $triggerResult['log_payload'];

        if ($triggerResult['status'] === WapiMessageStatus::Failed) {
            return $triggerResult;
        }

        return $triggerResult;
    }
}
