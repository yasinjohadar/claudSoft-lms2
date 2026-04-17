<?php

namespace App\Services;

use App\Enums\WapiMessageStatus;
use App\Enums\WapiMessageType;
use App\Jobs\SendWapiWhatsAppMessageJob;
use App\Models\WapiMessage;
use App\Support\WapiPhoneNormalizer;
use App\Support\WapiTemplatePayloadBuilder;

class WapiOutboundDispatcher
{
    public function queueMessage(
        string $phone,
        string $message,
        ?string $attachmentStoragePath,
        string $header = '',
        string $footer = '',
        string $buttons = ''
    ): WapiMessage {
        $phone = WapiPhoneNormalizer::normalize($phone);

        $model = WapiMessage::query()->create([
            'phone' => $phone,
            'type' => WapiMessageType::Message,
            'content' => [
                'message' => $message,
                'header' => $header,
                'footer' => $footer,
                'buttons' => $buttons,
                'attachment_storage_path' => $attachmentStoragePath,
            ],
            'status' => WapiMessageStatus::Pending,
        ]);

        SendWapiWhatsAppMessageJob::dispatch($model);

        return $model;
    }

    /**
     * @param  array<int, array<string, mixed>>  $components  مصفوفة components بنمط Meta Cloud API
     * @param  array<string, mixed>  $variablesLog
     */
    public function queueTemplate(
        string $phone,
        string $templateName,
        string $language,
        array $components,
        ?string $attachmentStoragePath,
        ?int $wapiTemplateId,
        array $variablesLog,
        int $delaySeconds = 0
    ): WapiMessage {
        $phone = WapiPhoneNormalizer::normalize($phone);

        $model = WapiMessage::query()->create([
            'phone' => $phone,
            'type' => WapiMessageType::Template,
            'content' => [
                'template_name' => $templateName,
                'language' => $language,
                'components' => $components,
                'attachment_storage_path' => $attachmentStoragePath,
                'wapi_template_id' => $wapiTemplateId,
                'variables_log' => $variablesLog,
            ],
            'status' => WapiMessageStatus::Pending,
        ]);

        if ($delaySeconds > 0) {
            SendWapiWhatsAppMessageJob::dispatch($model)->delay(now()->addSeconds($delaySeconds));
        } else {
            SendWapiWhatsAppMessageJob::dispatch($model);
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $variablesLog
     */
    public function queueCampaign(
        string $name,
        string $templateId,
        string $groupId,
        array $headerVariables,
        array $bodyVariables,
        ?int $wapiTemplateId,
        array $variablesLog
    ): WapiMessage {
        $campaignBody = WapiTemplatePayloadBuilder::flaxxaComponentsFromVariables($headerVariables, $bodyVariables);

        $model = WapiMessage::query()->create([
            'phone' => 'campaign',
            'type' => WapiMessageType::Campaign,
            'content' => [
                'name' => $name,
                'template_id' => $templateId,
                'group_id' => $groupId,
                'campaign_body' => $campaignBody,
                'wapi_template_id' => $wapiTemplateId,
                'variables_log' => $variablesLog,
            ],
            'status' => WapiMessageStatus::Pending,
        ]);

        SendWapiWhatsAppMessageJob::dispatch($model);

        return $model;
    }
}
