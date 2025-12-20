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
}
