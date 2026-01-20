<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseGroupVisibilityRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'required_group_id',
    ];

    /**
     * Get the group that has this visibility requirement.
     */
    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    /**
     * Get the required group (students must be members of this group to see the main group).
     */
    public function requiredGroup()
    {
        return $this->belongsTo(CourseGroup::class, 'required_group_id');
    }
}
