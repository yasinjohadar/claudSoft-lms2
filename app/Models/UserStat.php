<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'available_points',
        'spent_points',
        'total_xp',
        'current_level',
        'xp_to_next_level',
        'level_progress',
        'total_badges',
        'total_achievements',
        'current_streak',
        'longest_streak',
        'last_login_date',
        'courses_completed',
        'courses_enrolled',
        'quizzes_completed',
        'assignments_submitted',
        'average_quiz_score',
        'average_assignment_score',
        'perfect_scores',
        'global_rank',
        'course_rank',
        'division',
        'comments_count',
        'discussions_count',
        'helpful_count',
        'total_study_time',
        'last_activity_at',
        'additional_stats',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'spent_points' => 'integer',
        'total_xp' => 'integer',
        'current_level' => 'integer',
        'xp_to_next_level' => 'integer',
        'level_progress' => 'decimal:2',
        'total_badges' => 'integer',
        'total_achievements' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_login_date' => 'date',
        'courses_completed' => 'integer',
        'courses_enrolled' => 'integer',
        'quizzes_completed' => 'integer',
        'assignments_submitted' => 'integer',
        'average_quiz_score' => 'decimal:2',
        'average_assignment_score' => 'decimal:2',
        'perfect_scores' => 'integer',
        'global_rank' => 'integer',
        'course_rank' => 'integer',
        'comments_count' => 'integer',
        'discussions_count' => 'integer',
        'helpful_count' => 'integer',
        'total_study_time' => 'integer',
        'last_activity_at' => 'datetime',
        'additional_stats' => 'array',
    ];

    /**
     * Get the user that owns the stats
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for users in a specific division
     */
    public function scopeInDivision($query, $division)
    {
        return $query->where('division', $division);
    }

    /**
     * Scope for users with active streaks
     */
    public function scopeActiveStreak($query)
    {
        return $query->where('current_streak', '>', 0);
    }

    /**
     * Scope for top ranked users
     */
    public function scopeTopRanked($query, $limit = 10)
    {
        return $query->whereNotNull('global_rank')
            ->orderBy('global_rank', 'asc')
            ->limit($limit);
    }
}
