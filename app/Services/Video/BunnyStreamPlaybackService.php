<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStreamPlaybackService
{
    /**
     * Whether Embed View Token Authentication should be applied to iframe URLs.
     * When env flag is unset, auto-enables if token_security_key is present.
     */
    public function isEmbedTokenAuthEnabled(): bool
    {
        $flag = config('services.bunny_stream.embed_token_enabled');

        if ($this->isExplicitFalse($flag)) {
            return false;
        }

        if ($this->isExplicitTrue($flag)) {
            return true;
        }

        return $this->hasTokenSecurityKey();
    }

    public function hasTokenSecurityKey(): bool
    {
        $key = config('services.bunny_stream.token_security_key');

        return is_string($key) && $key !== '';
    }

    /**
     * Build a Bunny Stream embed iframe URL, optionally signed with token + expires.
     *
     * @see https://docs.bunny.net/docs/stream-embed-token-authentication
     */
    public function signEmbedUrl(string $libraryId, string $videoId, ?int $ttl = null): ?string
    {
        $libraryId = trim($libraryId);
        $videoId = trim($videoId);

        if ($libraryId === '' || $videoId === '') {
            return null;
        }

        $query = [
            'responsive' => 'true',
            'preload' => 'false',
        ];

        if (! $this->isEmbedTokenAuthEnabled()) {
            return $this->buildEmbedUrl($libraryId, $videoId, $query);
        }

        if (! $this->hasTokenSecurityKey()) {
            Log::error('Bunny Stream embed token auth is enabled but BUNNY_STREAM_TOKEN_SECURITY_KEY is missing');

            return null;
        }

        $ttl = $ttl ?? (int) config('services.bunny_stream.embed_token_ttl', 7200);
        $expires = time() + max(1, $ttl);
        $securityKey = (string) config('services.bunny_stream.token_security_key');

        $query['token'] = hash('sha256', $securityKey.$videoId.$expires);
        $query['expires'] = $expires;

        return $this->buildEmbedUrl($libraryId, $videoId, $query);
    }

    /**
     * SHA256 hex token per Bunny Embed View Token docs (for tests / callers).
     */
    public function generateEmbedToken(string $securityKey, string $videoId, int $expires): string
    {
        return hash('sha256', $securityKey.$videoId.$expires);
    }

    /**
     * @param  array<string, scalar>  $query
     */
    private function buildEmbedUrl(string $libraryId, string $videoId, array $query): string
    {
        return 'https://iframe.mediadelivery.net/embed/'.$libraryId.'/'.$videoId.'?'.http_build_query($query);
    }

    private function isExplicitTrue(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true';
    }

    private function isExplicitFalse(mixed $value): bool
    {
        return $value === false || $value === 0 || $value === '0' || $value === 'false';
    }

    public function resolveCdnHostname(?string $libraryId, ?string $videoId = null): ?string
    {
        $configured = config('services.bunny_stream.cdn_hostname');
        if (is_string($configured) && $configured !== '') {
            return $this->normalizeHostname($configured);
        }

        if (! $libraryId) {
            return null;
        }

        $apiKey = config('services.bunny_stream.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return null;
        }

        $cacheKey = 'bunny_stream_cdn_host:' . $libraryId;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($libraryId, $videoId, $apiKey) {
            $hostname = $this->fetchLibraryCdnHostname($libraryId, $apiKey);

            if (! $hostname && $videoId) {
                $hostname = $this->fetchVideoCdnHostname($libraryId, $videoId, $apiKey);
            }

            return $hostname;
        });
    }

    private function fetchLibraryCdnHostname(string $libraryId, string $apiKey): ?string
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['AccessKey' => $apiKey])
                ->get("https://video.bunnycdn.com/library/{$libraryId}");

            if (! $response->successful()) {
                return null;
            }

            return $this->extractHostnameFromPayload($response->json());
        } catch (\Throwable $e) {
            Log::debug('Bunny Stream library lookup failed', [
                'library_id' => $libraryId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function fetchVideoCdnHostname(string $libraryId, string $videoId, string $apiKey): ?string
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['AccessKey' => $apiKey])
                ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}");

            if (! $response->successful()) {
                return null;
            }

            return $this->extractHostnameFromPayload($response->json());
        } catch (\Throwable $e) {
            Log::debug('Bunny Stream video lookup failed', [
                'library_id' => $libraryId,
                'video_id' => $videoId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function extractHostnameFromPayload(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['cdnHostname', 'hostname', 'HostName', 'hostName', 'pullZoneUrl', 'PullZoneUrl'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                $hostname = $this->normalizeHostname($payload[$key]);
                if ($hostname) {
                    return $hostname;
                }
            }
        }

        foreach (['thumbnailUrl', 'previewUrl', 'preview_url'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                $hostname = $this->extractBunnyCdnHostnameFromText($payload[$key]);
                if ($hostname) {
                    return $hostname;
                }
            }
        }

        return null;
    }

    public function extractBunnyCdnHostnameFromText(mixed $text): ?string
    {
        if (! is_string($text) || $text === '') {
            return null;
        }

        if (preg_match('#https?://([a-z0-9-]+\.b-cdn\.net)#i', $text, $matches)) {
            return strtolower($matches[1]);
        }

        if (preg_match('#\b([a-z0-9-]+\.b-cdn\.net)\b#i', $text, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    private function normalizeHostname(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, '://')) {
            $host = parse_url($value, PHP_URL_HOST);

            return is_string($host) && $host !== '' ? strtolower($host) : null;
        }

        return strtolower(rtrim($value, '/'));
    }
}
