<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSectionQuestion extends Model
{
    protected $table = 'course_section_questions';

    protected $fillable = [
        'course_section_id',
        'question_id',
        'question_order',
        'question_grade',
        'is_required',
        'settings',
    ];

    protected $casts = [
        'question_grade' => 'decimal:2',
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the course section.
     */
    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    /**
     * Get the question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
