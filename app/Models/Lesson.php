<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'content',
        'objectives',
        'attachments',
        'is_published',
        'is_visible',
        'available_from',
        'available_until',
        'allow_comments',
        'reading_time',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'allow_comments' => 'boolean',
        'attachments' => 'array',
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
     * Get the primary course module for this lesson.
     */
    public function module()
    {
        return $this->morphOne(CourseModule::class, 'modulable');
    }

    /**
     * Get the user who created the lesson.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the lesson.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    /**
     * Scope a query to only include published lessons.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible lessons.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available lessons based on dates.
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

    // Helper Methods

    /**
     * Check if the lesson is available.
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
     * Get estimated reading time in minutes.
     */
    public function getEstimatedReadingTime(): int
    {
        if ($this->reading_time) {
            return $this->reading_time;
        }

        // Calculate based on content (average reading speed: 200 words per minute)
        if ($this->content) {
            $wordCount = str_word_count(strip_tags($this->content));
            return (int) ceil($wordCount / 200);
        }

        return 0;
    }

    /**
     * Check if lesson has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachments count.
     */
    public function getAttachmentsCount(): int
    {
        return $this->attachments ? count($this->attachments) : 0;
    }
}
