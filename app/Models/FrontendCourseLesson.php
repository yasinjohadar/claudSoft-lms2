<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrontendCourseLesson extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'order',
        'type',
        'video_url',
        'duration',
        'is_active',
        'is_free',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'duration' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the section that owns the lesson
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(FrontendCourseSection::class, 'section_id');
    }

    /**
     * Get the course through section
     */
    public function course(): BelongsTo
    {
        return $this->section->course();
    }

    /**
     * Format duration for display (e.g., "1h 30m" or "45m")
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '-';
        }

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}ساعة {$minutes}د" : "{$hours}ساعة";
        }

        return "{$minutes}د";
    }

    /**
     * Get type label in Arabic
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'video' => 'فيديو',
            'text' => 'نص',
            'file' => 'ملف',
            'quiz' => 'اختبار',
            'live' => 'مباشر',
            default => $this->type,
        };
    }
}
