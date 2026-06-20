<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStreamPlaybackService
{
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
