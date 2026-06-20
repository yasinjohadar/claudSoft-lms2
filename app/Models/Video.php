<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'video_type',
        'video_url',
        'video_path',
        'embed_code',
        'thumbnail',
        'duration',
        'quality',
        'subtitles',
        'processing_status',
        'processing_error',
        'is_published',
        'is_visible',
        'available_from',
        'available_until',
        'allow_download',
        'allow_speed_control',
        'require_watch_complete',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'allow_download' => 'boolean',
        'allow_speed_control' => 'boolean',
        'require_watch_complete' => 'boolean',
        'quality' => 'array',
        'subtitles' => 'array',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    // Relationships

    /**
     * Get all of the module's course modules.
     */
    public function courseModules()
    {
        return $this->morphMany(CourseModule::class, 'modulable');
    }

    /**
     * Get the user who created the video.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the video.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    /**
     * Scope a query to only include published videos.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible videos.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available videos based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    /**
     * Scope a query to only include videos by processing status.
     */
    public function scopeByProcessingStatus($query, string $status)
    {
        return $query->where('processing_status', $status);
    }

    /**
     * Scope a query to only include completed videos.
     */
    public function scopeCompleted($query)
    {
        return $query->where('processing_status', 'completed');
    }

    /**
     * Scope a query to only include processing videos.
     */
    public function scopeProcessing($query)
    {
        return $query->where('processing_status', 'processing');
    }

    /**
     * Scope a query to only include failed videos.
     */
    public function scopeFailed($query)
    {
        return $query->where('processing_status', 'failed');
    }

    // Helper Methods

    /**
     * Check if the video is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if video processing is completed.
     */
    public function isProcessingCompleted(): bool
    {
        return $this->processing_status === 'completed';
    }

    /**
     * Check if video processing failed.
     */
    public function isProcessingFailed(): bool
    {
        return $this->processing_status === 'failed';
    }

    /**
     * Check if video is currently processing.
     */
    public function isProcessing(): bool
    {
        return $this->processing_status === 'processing';
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutes(): int
    {
        return $this->duration ? (int) ceil($this->duration / 60) : 0;
    }

    /**
     * Get formatted duration (HH:MM:SS).
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '00:00:00';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Check if video is external (YouTube, Vimeo, etc.).
     */
    public function isExternal(): bool
    {
        return in_array($this->video_type, ['youtube', 'vimeo', 'external']);
    }

    /**
     * Check if video is uploaded.
     */
    public function isUploaded(): bool
    {
        return $this->video_type === 'upload';
    }

    /**
     * Check if video has subtitles.
     */
    public function hasSubtitles(): bool
    {
        return !empty($this->subtitles);
    }

    /**
     * Get video embed URL for external videos.
     */
    public function getEmbedUrl(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        switch ($this->video_type) {
            case 'youtube':
                // Extract video ID from YouTube URL
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $this->video_url, $match);
                return isset($match[1]) ? "https://www.youtube.com/embed/{$match[1]}" : null;

            case 'vimeo':
                // Extract video ID from Vimeo URL
                preg_match('/vimeo\.com\/(\d+)/i', $this->video_url, $match);
                return isset($match[1]) ? "https://player.vimeo.com/video/{$match[1]}" : null;

            default:
                return $this->video_url;
        }
    }

    /**
     * Check if the video is hosted on Bunny Stream.
     */
    public function isBunnyStreamVideo(): bool
    {
        foreach ([$this->video_url, $this->embed_code, $this->thumbnail] as $value) {
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

    /**
     * Direct MP4/HLS URL for Bunny Stream (bypasses iframe player and RUM metrics).
     */
    public function getBunnyNativePlaybackUrl(string $quality = '720p'): ?string
    {
        if (! $this->isBunnyStreamVideo()) {
            return null;
        }

        $videoUrl = trim((string) ($this->video_url ?? ''));
        if ($videoUrl !== '' && $this->isBunnyDirectMediaUrl($videoUrl)) {
            return $videoUrl;
        }

        $ids = $this->parseBunnyStreamIds();
        $hostname = $this->resolveBunnyCdnHostname();

        if (! $ids || ! $hostname) {
            return null;
        }

        return "https://{$hostname}/{$ids['video_id']}/play_{$quality}.mp4";
    }

    /**
     * Bunny iframe embed URL (fallback when direct playback is unavailable).
     */
    public function getBunnyIframeSrc(): ?string
    {
        $ids = $this->parseBunnyStreamIds();
        if (! $ids) {
            return null;
        }

        $query = http_build_query([
            'responsive' => 'true',
            'preload' => 'false',
        ]);

        return "https://iframe.mediadelivery.net/embed/{$ids['library_id']}/{$ids['video_id']}?{$query}";
    }

    /**
     * @return array{library_id: string, video_id: string}|null
     */
    public function parseBunnyStreamIds(): ?array
    {
        foreach ([$this->video_url, $this->embed_code] as $source) {
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

    public function resolveBunnyCdnHostname(): ?string
    {
        foreach ([$this->video_url, $this->embed_code, $this->thumbnail] as $source) {
            $hostname = $this->extractBunnyCdnHostnameFromText($source);
            if ($hostname) {
                return $hostname;
            }
        }

        $ids = $this->parseBunnyStreamIds();
        if ($ids) {
            $hostname = app(\App\Services\Video\BunnyStreamPlaybackService::class)
                ->resolveCdnHostname($ids['library_id'], $ids['video_id']);

            if ($hostname) {
                return $hostname;
            }
        }

        return null;
    }

    private function extractBunnyCdnHostnameFromText(mixed $text): ?string
    {
        return app(\App\Services\Video\BunnyStreamPlaybackService::class)
            ->extractBunnyCdnHostnameFromText($text);
    }

    private function isBunnyDirectMediaUrl(string $url): bool
    {
        if (! str_contains($url, 'b-cdn.net')) {
            return false;
        }

        return (bool) preg_match('#/play_\d+p\.mp4(?:\?|$)#i', $url)
            || str_contains($url, '/playlist.m3u8')
            || str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?: ''), '.mp4');
    }

    /**
     * Get embed code for the video.
     * Returns saved embed_code if exists, otherwise generates from video_url for Bunny Stream.
     */
    public function getEmbedCode(): ?string
    {
        if ($this->isBunnyStreamVideo()) {
            return null;
        }

        if ($this->getBunnyNativePlaybackUrl()) {
            return null;
        }

        // If embed_code exists, return it
        if ($this->embed_code) {
            return $this->embed_code;
        }
        
        // If video_url is from Bunny Stream, generate embed code automatically
        if ($this->video_type === 'external' && $this->video_url) {
            if (str_contains($this->video_url, 'mediadelivery.net') || 
                str_contains($this->video_url, 'bunny.net') || 
                str_contains($this->video_url, 'b-cdn.net') ||
                str_contains($this->video_url, 'iframe.mediadelivery')) {
                
                return $this->generateBunnyEmbedCode($this->video_url);
            }
        }
        
        return null;
    }

    /**
     * Generate Bunny Stream embed code from video URL.
     */
    private function generateBunnyEmbedCode(string $videoUrl): ?string
    {
        // Try to extract video ID from various Bunny Stream URL formats
        // Format 1: https://iframe.mediadelivery.net/embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
        // Format 2: https://iframe.mediadelivery.net/play/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
        // Format 3: https://vz-xxxxx.b-cdn.net/xxxxx/xxxxx.mp4
        
        $parsedUrl = parse_url($videoUrl);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return null;
        }
        
        // Check if it's a mediadelivery.net URL
        if (str_contains($parsedUrl['host'], 'mediadelivery.net') && isset($parsedUrl['path'])) {
            // Extract library ID and video ID from path
            // Path format: /embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
            // or /play/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
            $pathParts = array_filter(explode('/', trim($parsedUrl['path'], '/')));
            $pathParts = array_values($pathParts); // Re-index array
            
            // Check if we have at least 3 parts (embed/play, libraryId, videoId)
            if (count($pathParts) >= 3) {
                $action = $pathParts[0]; // 'embed' or 'play'
                $libraryId = $pathParts[1];
                $videoId = $pathParts[2];
                
                // Build embed URL (always use /embed/ for iframe)
                $embedUrl = "https://iframe.mediadelivery.net/embed/{$libraryId}/{$videoId}";
                
                // Add query parameters if they exist
                $queryParams = [];
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                }
                
                // Ensure responsive is true, disable preload to reduce player overhead
                $queryParams['responsive'] = 'true';
                $queryParams['preload'] = 'false';
                
                if (!empty($queryParams)) {
                    $embedUrl .= '?' . http_build_query($queryParams);
                }
                
                // Generate full iframe embed code with responsive wrapper (same as Bunny Stream)
                $escapedUrl = htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8');
                $iframe = '<iframe src="' . $escapedUrl . '" loading="lazy" style="border:0;position:absolute;top:0;height:100%;width:100%;" allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" allowfullscreen="true"></iframe>';
                
                return '<div style="position:relative;padding-top:56.25%;">' . $iframe . '</div>';
            }
        }
        
        return null;
    }
}
