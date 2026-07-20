<?php

namespace App\Services\Video;

use App\Models\BunnyStreamLibrary;
use App\Models\Video;
use App\Support\BunnyStreamUrlParser;

class BunnyStreamVideoLinker
{
    /**
     * Link Bunny videos to registered libraries by parsing existing URLs.
     *
     * @return array{linked: int, already_linked: int, unresolved: int, skipped: int}
     */
    public function linkAll(): array
    {
        $stats = [
            'linked' => 0,
            'already_linked' => 0,
            'unresolved' => 0,
            'skipped' => 0,
        ];

        Video::query()->chunkById(100, function ($videos) use (&$stats) {
            foreach ($videos as $video) {
                if (! $video->isBunnyStreamVideo()) {
                    $stats['skipped']++;

                    continue;
                }

                $ids = BunnyStreamUrlParser::parseIds($video->video_url, $video->embed_code);
                if (! $ids) {
                    $stats['unresolved']++;

                    continue;
                }

                $library = BunnyStreamLibrary::query()
                    ->where('library_id', $ids['library_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $library) {
                    $stats['unresolved']++;

                    continue;
                }

                if ((int) $video->bunny_stream_library_id === (int) $library->id) {
                    $stats['already_linked']++;

                    continue;
                }

                $video->forceFill(['bunny_stream_library_id' => $library->id])->saveQuietly();
                $stats['linked']++;
            }
        });

        return $stats;
    }
}
