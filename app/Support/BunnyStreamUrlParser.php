<?php

namespace App\Support;

class BunnyStreamUrlParser
{
    /**
     * @return array{library_id: string, video_id: string}|null
     */
    public static function parseIds(?string $videoUrl, ?string $embedCode = null): ?array
    {
        foreach ([$videoUrl, $embedCode] as $source) {
            if (! is_string($source) || $source === '') {
                continue;
            }

            if (preg_match('#mediadelivery\.net/(?:embed|play)/(\d+)/([a-f0-9-]+)#i', $source, $matches)) {
                return [
                    'library_id' => $matches[1],
                    'video_id' => $matches[2],
                ];
            }
        }

        return null;
    }

    public static function isBunnySource(?string ...$sources): bool
    {
        foreach ($sources as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            if (str_contains($value, 'mediadelivery.net')
                || str_contains($value, 'bunny.net')
                || str_contains($value, 'b-cdn.net')
                || str_contains($value, 'iframe.mediadelivery')) {
                return true;
            }
        }

        return false;
    }
}
