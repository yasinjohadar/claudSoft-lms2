<?php

namespace App\Services\Video;

use App\Models\BunnyStreamLibrary;
use App\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStreamPlaybackService
{
    public function isEmbedTokenAuthEnabled(): bool
    {
        $flag = config('services.bunny_stream.embed_token_enabled');

        if ($this->isExplicitFalse($flag)) {
            return false;
        }

        if ($this->isExplicitTrue($flag)) {
            return true;
        }

        return BunnyStreamLibrary::query()->where('is_active', true)->exists()
            || $this->hasLegacyTokenSecurityKey();
    }

    /**
     * @deprecated Legacy single-key fallback; prefer per-library keys in DB.
     */
    public function hasTokenSecurityKey(): bool
    {
        return $this->hasLegacyTokenSecurityKey()
            || BunnyStreamLibrary::query()->where('is_active', true)->exists();
    }

    private function hasLegacyTokenSecurityKey(): bool
    {
        $key = config('services.bunny_stream.token_security_key');

        return is_string($key) && $key !== '';
    }

    public function signEmbedUrlForVideo(Video $video, ?int $ttl = null): ?string
    {
        $ids = $video->parseBunnyStreamIds();
        if (! $ids) {
            return null;
        }

        $library = $video->resolveBunnyStreamLibrary();
        if ($library) {
            return $this->signEmbedUrl($library, $ids['video_id'], $ttl);
        }

        if ($this->isEmbedTokenAuthEnabled() && $this->hasLegacyTokenSecurityKey()) {
            return $this->signEmbedUrlWithLegacyKey($ids['library_id'], $ids['video_id'], $ttl);
        }

        if ($this->isEmbedTokenAuthEnabled()) {
            Log::error('Bunny Stream video has no registered library for signing', [
                'video_id' => $video->id,
                'library_id' => $ids['library_id'],
            ]);
        }

        return $this->buildEmbedUrl($ids['library_id'], $ids['video_id'], [
            'responsive' => 'true',
            'preload' => 'false',
        ]);
    }

    /**
     * Build a signed Bunny Stream embed iframe URL for a library + video.
     *
     * @see https://docs.bunny.net/docs/stream-embed-token-authentication
     */
    public function signEmbedUrl(BunnyStreamLibrary $library, string $videoId, ?int $ttl = null): ?string
    {
        $libraryId = trim((string) $library->library_id);
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

        if (! $library->is_active || ! $library->hasTokenSecurityKey()) {
            Log::error('Bunny Stream library missing token security key or inactive', [
                'library_id' => $libraryId,
                'library_record_id' => $library->id,
            ]);

            return null;
        }

        $ttl = $ttl ?? (int) config('services.bunny_stream.embed_token_ttl', 7200);
        $expires = time() + max(1, $ttl);

        $query['token'] = $this->generateEmbedToken($library->token_security_key, $videoId, $expires);
        $query['expires'] = $expires;

        return $this->buildEmbedUrl($libraryId, $videoId, $query);
    }

    /**
     * Legacy signing path for backwards compatibility during migration.
     */
    public function signEmbedUrlWithLegacyKey(string $libraryId, string $videoId, ?int $ttl = null): ?string
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

        if (! $this->hasLegacyTokenSecurityKey()) {
            return null;
        }

        $ttl = $ttl ?? (int) config('services.bunny_stream.embed_token_ttl', 7200);
        $expires = time() + max(1, $ttl);
        $securityKey = (string) config('services.bunny_stream.token_security_key');

        $query['token'] = $this->generateEmbedToken($securityKey, $videoId, $expires);
        $query['expires'] = $expires;

        return $this->buildEmbedUrl($libraryId, $videoId, $query);
    }

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

    public function resolveCdnHostname(?string $libraryId, ?string $videoId = null, ?BunnyStreamLibrary $library = null): ?string
    {
        $libraryRecord = $library;
        if (! $libraryRecord && $libraryId) {
            $libraryRecord = BunnyStreamLibrary::query()
                ->where('library_id', $libraryId)
                ->where('is_active', true)
                ->first();
        }

        $configured = config('services.bunny_stream.cdn_hostname');
        if (is_string($configured) && $configured !== '') {
            return $this->normalizeHostname($configured);
        }

        if (! $libraryId) {
            return null;
        }

        $apiKey = $libraryRecord?->api_key;
        if (! is_string($apiKey) || $apiKey === '') {
            $apiKey = config('services.bunny_stream.api_key');
        }

        if (! is_string($apiKey) || $apiKey === '') {
            return null;
        }

        $cacheKey = 'bunny_stream_cdn_host:'.$libraryId;

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
