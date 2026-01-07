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
     * Get embed code for the video.
     * Returns saved embed_code if exists, otherwise generates from video_url for Bunny Stream.
     */
    public function getEmbedCode(): ?string
    {
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
        
        // Check if it's an iframe URL
        if (str_contains($parsedUrl['host'], 'mediadelivery.net') && isset($parsedUrl['path'])) {
            // Extract library ID and video ID from path
            // Path format: /embed/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
            // or /play/488464/79b92b75-405c-4ce7-bc99-c1eb3af092c9
            $pathParts = explode('/', trim($parsedUrl['path'], '/'));
            
            if (count($pathParts) >= 3 && ($pathParts[0] === 'embed' || $pathParts[0] === 'play')) {
                $libraryId = $pathParts[1];
                $videoId = $pathParts[2];
                
                // Build embed URL with responsive parameter
                $embedUrl = "https://iframe.mediadelivery.net/embed/{$libraryId}/{$videoId}";
                
                // Add query parameters if they exist
                $queryParams = [];
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                }
                
                // Ensure responsive is true
                $queryParams['responsive'] = 'true';
                
                if (!empty($queryParams)) {
                    $embedUrl .= '?' . http_build_query($queryParams);
                }
                
                // Generate full iframe embed code
                return '<iframe src="' . htmlspecialchars($embedUrl) . '" loading="lazy" allowfullscreen></iframe>';
            }
        }
        
        return null;
    }
}
