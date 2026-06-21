<?php

namespace App\Services\WhatsApp\Evolution;

use App\Models\EvolutionInstance;
use App\Services\WhatsApp\Providers\EvolutionApiProvider;
use App\Services\WhatsApp\WhatsAppProviderFactory;
use App\Services\WhatsApp\WhatsAppSettingsService;

class EvolutionService
{
    public function __construct(
        private WhatsAppSettingsService $settingsService
    ) {}

    public function getSettings(): array
    {
        $this->settingsService->initializeDefaults();

        return $this->settingsService->getSettings();
    }

    public function client(?array $override = null): EvolutionApiClient
    {
        $settings = $override ?? $this->getSettings();

        return EvolutionApiClient::fromConfig([
            'base_url' => $settings['evolution_base_url'] ?? '',
            'api_key' => $settings['evolution_api_key'] ?? '',
        ]);
    }

    public function provider(?array $override = null): EvolutionApiProvider
    {
        $settings = $override ?? $this->getSettings();

        return WhatsAppProviderFactory::create('evolution', [
            'base_url' => $settings['evolution_base_url'] ?? '',
            'api_key' => $settings['evolution_api_key'] ?? '',
            'instance_name' => $settings['evolution_instance_name'] ?? '',
        ]);
    }

    public function activeInstanceName(): string
    {
        $settings = $this->getSettings();
        if (! empty($settings['evolution_instance_name'])) {
            return $settings['evolution_instance_name'];
        }

        return EvolutionInstance::defaultInstance()?->instance_name ?? '';
    }

    public function syncInstances(bool $markConfiguredAsDefault = true): array
    {
        $settings = $this->getSettings();
        $configuredName = $settings['evolution_instance_name'] ?? '';
        $instances = $this->client()->fetchInstances();
        $list = is_array($instances) && isset($instances[0]) ? $instances : ($instances['value'] ?? $instances);

        if (! is_array($list)) {
            return [];
        }

        $synced = [];
        foreach ($list as $instance) {
            if (! is_array($instance) || empty($instance['name'])) {
                continue;
            }
            try {
                $synced[] = EvolutionInstance::syncFromApiArray(
                    $instance,
                    $markConfiguredAsDefault && $instance['name'] === $configuredName
                );
            } catch (\Throwable) {
                // Table may not exist until migration runs
            }
        }

        return $synced;
    }

    public function webhookUrl(?string $instanceName = null): string
    {
        $instance = $instanceName ?: $this->activeInstanceName();
        $base = rtrim((string) config('app.url'), '/');

        return $base . '/api/webhooks/evolution/' . urlencode($instance);
    }

    public function defaultWebhookEvents(): array
    {
        return [
            'MESSAGES_UPSERT',
            'MESSAGES_UPDATE',
            'CONNECTION_UPDATE',
            'SEND_MESSAGE',
            'QRCODE_UPDATED',
        ];
    }
}
