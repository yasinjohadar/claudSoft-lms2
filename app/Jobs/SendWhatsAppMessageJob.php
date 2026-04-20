<?php

namespace App\Jobs;

use App\Exceptions\WhatsAppApiException;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\WhatsAppRecipientNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public WhatsAppMessage $message,
        public array $messageData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppSettingsService $settingsService): void
    {
        try {
            $contact = $this->message->contact;
            // Get provider settings
            $settings = $settingsService->getSettings();
            $provider = $settings['whatsapp_provider'] ?? 'meta';
            $config = $settingsService->getProviderConfig();
            $to = WhatsAppRecipientNormalizer::normalize($provider, $contact->wa_id);

            // Create provider instance
            $providerInstance = WhatsAppProviderFactory::create($provider, $config);

            $messageType = $this->messageData['type'] ?? $this->message->type;
            
            if ($messageType === 'template') {
                $response = $providerInstance->sendTemplate(
                    $to,
                    $this->messageData['template_name'] ?? $this->message->body,
                    $this->messageData['language'] ?? 'ar',
                    $this->messageData['components'] ?? []
                );
            } elseif ($messageType === 'document') {
                $response = $providerInstance->sendDocument(
                    $to,
                    $this->messageData['document_url'] ?? '',
                    $this->messageData['filename'] ?? 'document.pdf',
                    $this->messageData['caption'] ?? null
                );
            } else {
                $response = $providerInstance->sendText(
                    $to,
                    $this->messageData['text'] ?? $this->message->body ?? '',
                    $this->messageData['preview_url'] ?? false
                );
            }

            // Update message with meta_message_id and status
            $this->message->update([
                'meta_message_id' => $response->metaMessageId,
                'status' => WhatsAppMessage::STATUS_SENT,
                'payload' => array_merge($this->message->payload ?? [], [
                    'response' => $response->rawResponse,
                ]),
            ]);

            Log::channel('whatsapp')->info('WhatsApp message sent via job', [
                'message_id' => $this->message->id,
                'meta_message_id' => $response->metaMessageId,
                'to' => $to,
            ]);
        } catch (WhatsAppApiException $e) {
            // Update message with error
            $this->message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'details' => $e->getDetails(),
                ],
            ]);

            Log::channel('whatsapp')->error('Failed to send WhatsApp message', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
                'details' => $e->getDetails(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            $safeMessage = $this->toSingleLineError($e->getMessage());

            // Update message with error
            $this->message->update([
                'status' => WhatsAppMessage::STATUS_FAILED,
                'error' => [
                    'message' => $safeMessage,
                ],
            ]);

            Log::channel('whatsapp')->error('Exception sending WhatsApp message', [
                'message_id' => $this->message->id,
                'error' => $safeMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function toSingleLineError(string $message): string
    {
        $parts = preg_split('/\R+/', trim($message));
        return (string) ($parts[0] ?? 'حدث خطأ غير متوقع أثناء الإرسال.');
    }
}
