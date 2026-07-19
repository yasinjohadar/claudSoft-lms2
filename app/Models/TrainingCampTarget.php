<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCampTarget extends Model
{
    protected $fillable = [
        'training_camp_id',
        'course_id',
        'group_id',
    ];

    protected $casts = [
        'training_camp_id' => 'integer',
        'course_id' => 'integer',
        'group_id' => 'integer',
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(TrainingCamp::class, 'training_camp_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }
}
