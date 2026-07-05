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

    public function clientFor(?EvolutionInstance $instance = null, ?string $instanceName = null): EvolutionApiClient
    {
        if ($instanceName !== null && $instance === null) {
            $instance = EvolutionInstance::where('instance_name', $instanceName)->first();
        }

        if ($instance instanceof EvolutionInstance && $instance->hasCustomCredentials()) {
            return EvolutionApiClient::fromConfig($instance->resolveApiConfig());
        }

        return $this->client();
    }

    public function clientForActiveInstance(): EvolutionApiClient
    {
        return $this->clientFor(null, $this->activeInstanceName());
    }

    public function refreshInstanceFromApi(EvolutionInstance $instance): EvolutionInstance
    {
        $client = $this->clientFor($instance);
        $state = $client->getConnectionState($instance->instance_name);
        $connection = strtolower((string) ($state['instance']['state'] ?? $state['state'] ?? 'close'));

        $instance->update([
            'connection_status' => $connection,
            'connected_at' => $connection === 'open' ? now() : $instance->connected_at,
            'disconnected_at' => $connection === 'open' ? null : now(),
        ]);

        return $instance->fresh();
    }

    /**
     * @return string[]
     */
    public function parseInstanceNamesList(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($line) => trim($line),
            $lines
        ))));
    }

    public function registerManualInstance(array $data): EvolutionInstance
    {
        $name = trim((string) ($data['instance_name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('اسم Instance مطلوب.');
        }

        $instance = EvolutionInstance::firstOrNew(['instance_name' => $name]);
        $instance->fill([
            'label' => trim((string) ($data['label'] ?? '')) ?: null,
            'is_manual' => true,
            'connection_status' => $instance->connection_status ?: 'pending',
        ]);

        $baseUrl = trim((string) ($data['evolution_base_url'] ?? ''));
        if ($baseUrl !== '') {
            $instance->evolution_base_url = rtrim($baseUrl, '/');
        }

        $apiKey = trim((string) ($data['evolution_api_key'] ?? ''));
        if ($apiKey !== '') {
            $instance->evolution_api_key = $apiKey;
        }

        $instance->save();

        if (! empty($data['verify']) && ($instance->hasCustomCredentials() || $this->hasGlobalCredentials())) {
            try {
                $this->refreshInstanceFromApi($instance);
            } catch (\Throwable) {
                // keep manual row even if verify fails
            }
        }

        if (! empty($data['set_as_default'])) {
            $this->assignDefaultInstance($instance->instance_name);
        }

        return $instance->fresh();
    }

    public function assignDefaultInstance(string $instanceName): void
    {
        $this->settingsService->updateSettings([
            'evolution_instance_name' => $instanceName,
        ]);

        EvolutionInstance::query()->update(['is_default' => false]);
        EvolutionInstance::where('instance_name', $instanceName)->update(['is_default' => true]);
    }

    public function hasGlobalCredentials(): bool
    {
        $settings = $this->getSettings();

        return ($settings['evolution_base_url'] ?? '') !== '' && ($settings['evolution_api_key'] ?? '') !== '';
    }

    public function provider(?array $override = null): EvolutionApiProvider
    {
        $settings = $override ?? $this->getSettings();

        return $this->providerForInstance($settings['evolution_instance_name'] ?? '', $settings);
    }

    public function providerForInstance(string $instanceName, ?array $settings = null): EvolutionApiProvider
    {
        $settings = $settings ?? $this->getSettings();

        return WhatsAppProviderFactory::create('evolution', [
            'base_url' => $settings['evolution_base_url'] ?? '',
            'api_key' => $settings['evolution_api_key'] ?? '',
            'instance_name' => $instanceName,
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
        $remoteNames = [];

        foreach ($list as $instance) {
            if (! is_array($instance) || empty($instance['name'])) {
                continue;
            }

            $name = (string) $instance['name'];
            $remoteNames[] = $name;

            try {
                $synced[] = EvolutionInstance::syncFromApiArray(
                    $instance,
                    $markConfiguredAsDefault && $name === $configuredName
                );
            } catch (\Throwable) {
                // Table may not exist until migration runs
            }
        }

        if ($remoteNames !== []) {
            EvolutionInstance::query()
                ->where('is_manual', false)
                ->whereNotIn('instance_name', $remoteNames)
                ->delete();
        }

        return $synced;
    }

    public function webhookBaseUrl(): string
    {
        $settings = $this->getSettings();
        $custom = trim((string) ($settings['evolution_webhook_base_url'] ?? ''));
        if ($custom !== '') {
            return rtrim($custom, '/');
        }

        return rtrim((string) config('app.url'), '/');
    }

    public function isLocalWebhookBaseUrl(?string $baseUrl = null): bool
    {
        $base = strtolower($baseUrl ?? $this->webhookBaseUrl());

        return str_contains($base, '127.0.0.1')
            || str_contains($base, 'localhost')
            || str_contains($base, '::1');
    }

    public function webhookUrl(?string $instanceName = null): string
    {
        $instance = $instanceName ?: $this->activeInstanceName();

        return $this->webhookBaseUrl() . '/api/webhooks/evolution/' . urlencode($instance);
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
