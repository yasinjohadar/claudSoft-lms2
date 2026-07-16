<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammingChallengeTarget extends Model
{
    protected $fillable = [
        'programming_challenge_id',
        'course_id',
        'group_id',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'group_id' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallenge::class, 'programming_challenge_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function isWholeCourse(): bool
    {
        return $this->group_id === null;
    }
}
