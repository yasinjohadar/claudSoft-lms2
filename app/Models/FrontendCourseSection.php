<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrontendCourseSection extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'is_active',
        'lessons_count',
        'duration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lessons_count' => 'integer',
        'duration' => 'decimal:2',
        'order' => 'integer',
    ];

    /**
     * Get the course that owns the section
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(FrontendCourse::class, 'course_id');
    }

    /**
     * Get all lessons for this section
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(FrontendCourseLesson::class, 'section_id')->orderBy('order');
    }

    /**
     * Get only active lessons
     */
    public function activeLessons(): HasMany
    {
        return $this->lessons()->where('is_active', true);
    }

    /**
     * Calculate total duration from lessons
     */
    public function calculateDuration(): float
    {
        return $this->lessons()->sum('duration') / 60; // Convert minutes to hours
    }

    /**
     * Update lessons count
     */
    public function updateLessonsCount(): void
    {
        $this->lessons_count = $this->lessons()->count();
        $this->save();
    }
}
