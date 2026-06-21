<?php

namespace App\Services\WhatsApp\Evolution;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiClient
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->apiKey = $apiKey;
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            $config['base_url'] ?? '',
            $config['api_key'] ?? ''
        );
    }

    public function getInformation(): array
    {
        return $this->request('GET', '/');
    }

    public function fetchInstances(?string $instanceName = null): array
    {
        $path = '/instance/fetchInstances';
        if ($instanceName) {
            $path .= '?instanceName=' . urlencode($instanceName);
        }

        return $this->request('GET', $path);
    }

    public function getConnectionState(string $instanceName): array
    {
        return $this->request('GET', "/instance/connectionState/{$instanceName}");
    }

    public function connectInstance(string $instanceName, ?string $phoneNumber = null): array
    {
        $path = "/instance/connect/{$instanceName}";
        if ($phoneNumber) {
            $path .= '?number=' . urlencode($phoneNumber);
        }

        return $this->request('GET', $path);
    }

    public function createInstance(array $params): array
    {
        return $this->request('POST', '/instance/create', $params);
    }

    public function restartInstance(string $instanceName): array
    {
        return $this->request('POST', "/instance/restart/{$instanceName}");
    }

    public function logoutInstance(string $instanceName): array
    {
        return $this->request('DELETE', "/instance/logout/{$instanceName}");
    }

    public function deleteInstance(string $instanceName): array
    {
        return $this->request('DELETE', "/instance/delete/{$instanceName}");
    }

    public function setPresence(string $instanceName, string $presence): array
    {
        return $this->request('POST', "/instance/setPresence/{$instanceName}", [
            'presence' => $presence,
        ]);
    }

    public function getWebhook(string $instanceName): array
    {
        return $this->request('GET', "/webhook/find/{$instanceName}");
    }

    public function setWebhook(string $instanceName, array $config): array
    {
        // Evolution API v2 expects: { "webhook": { enabled, url, events, ... } }
        $webhook = isset($config['webhook']) && is_array($config['webhook'])
            ? $config['webhook']
            : $config;

        return $this->request('POST', "/webhook/set/{$instanceName}", [
            'webhook' => $webhook,
        ]);
    }

    public function setSettings(string $instanceName, array $settings): array
    {
        $payload = isset($settings['settings']) && is_array($settings['settings'])
            ? $settings
            : ['settings' => $settings];

        return $this->request('POST', "/settings/set/{$instanceName}", $payload);
    }

    public function getSettings(string $instanceName): array
    {
        return $this->request('GET', "/settings/find/{$instanceName}");
    }

    public function sendText(string $instanceName, string $number, string $text, array $options = []): array
    {
        return $this->request('POST', "/message/sendText/{$instanceName}", array_merge([
            'number' => $number,
            'text' => $text,
        ], $options));
    }

    public function sendMedia(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendMedia/{$instanceName}", $payload);
    }

    public function sendWhatsAppAudio(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendWhatsAppAudio/{$instanceName}", $payload);
    }

    public function sendSticker(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendSticker/{$instanceName}", $payload);
    }

    public function sendLocation(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendLocation/{$instanceName}", $payload);
    }

    public function sendContact(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendContact/{$instanceName}", $payload);
    }

    public function sendReaction(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendReaction/{$instanceName}", $payload);
    }

    public function sendPoll(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendPoll/{$instanceName}", $payload);
    }

    public function sendList(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendList/{$instanceName}", $payload);
    }

    public function sendButtons(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendButtons/{$instanceName}", $payload);
    }

    public function sendStatus(string $instanceName, array $payload): array
    {
        return $this->request('POST', "/message/sendStatus/{$instanceName}", $payload);
    }

    public function fetchAllGroups(string $instanceName, bool $getParticipants = false): array
    {
        $path = "/group/fetchAllGroups/{$instanceName}?getParticipants=" . ($getParticipants ? 'true' : 'false');

        return $this->request('GET', $path);
    }

    public function findGroupByJid(string $instanceName, string $groupJid): array
    {
        return $this->request('GET', "/group/findGroupInfos/{$instanceName}?groupJid=" . urlencode($groupJid));
    }

    public function findGroupMembers(string $instanceName, string $groupJid): array
    {
        return $this->request('GET', "/group/participants/{$instanceName}?groupJid=" . urlencode($groupJid));
    }

    public function findChats(string $instanceName): array
    {
        return $this->request('POST', "/chat/findChats/{$instanceName}");
    }

    public function findContacts(string $instanceName): array
    {
        return $this->request('POST', "/chat/findContacts/{$instanceName}");
    }

    protected function request(string $method, string $path, array $data = []): array
    {
        if ($this->baseUrl === '' || $this->apiKey === '') {
            throw new \RuntimeException('Evolution API URL و API Key مطلوبان.');
        }

        $client = Http::timeout(30)
            ->withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);

        try {
            /** @var Response $response */
            $response = match (strtoupper($method)) {
                'GET' => $client->get($this->baseUrl . $path),
                'POST' => $client->post($this->baseUrl . $path, $data),
                'DELETE' => $client->delete($this->baseUrl . $path, $data),
                'PUT' => $client->put($this->baseUrl . $path, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };
        } catch (ConnectionException $e) {
            $host = parse_url($this->baseUrl, PHP_URL_HOST) ?: $this->baseUrl;

            Log::channel('whatsapp')->error('Evolution API connection failed', [
                'method' => $method,
                'path' => $path,
                'base_url' => $this->baseUrl,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'cURL connection failed for https://'.$host.' — '.$e->getMessage(),
                0,
                $e
            );
        }

        if ($response->successful()) {
            $json = $response->json();

            return is_array($json) ? $json : ['raw' => $response->body()];
        }

        $errorData = $response->json();
        $message = $this->extractErrorMessage($errorData, $response->body());

        Log::channel('whatsapp')->error('Evolution API error', [
            'method' => $method,
            'path' => $path,
            'status' => $response->status(),
            'error' => $errorData,
        ]);

        throw new \RuntimeException($message, $response->status());
    }

    protected function extractErrorMessage(mixed $errorData, string $fallbackBody): string
    {
        if (! is_array($errorData)) {
            return $fallbackBody !== '' ? $fallbackBody : 'Evolution API request failed';
        }

        $message = $errorData['message'] ?? $errorData['error'] ?? null;
        if (is_string($message) && $message !== '') {
            return $message;
        }

        if (is_array($message)) {
            return json_encode($message, JSON_UNESCAPED_UNICODE) ?: 'Evolution API request failed';
        }

        $nested = data_get($errorData, 'response.message');
        if (is_array($nested)) {
            return json_encode($nested, JSON_UNESCAPED_UNICODE) ?: 'Evolution API request failed';
        }

        if (is_string($nested) && $nested !== '') {
            return $nested;
        }

        return $fallbackBody !== '' ? $fallbackBody : 'Evolution API request failed';
    }
}
