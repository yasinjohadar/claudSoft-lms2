<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leaderboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'course_id',
        'period',
        'start_date',
        'end_date',
        'season_number',
        'season_name',
        'metric',
        'sort_direction',
        'max_entries',
        'min_score',
        'has_divisions',
        'division_thresholds',
        'is_active',
        'is_visible',
        'last_updated_at',
        'sort_order',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'season_number' => 'integer',
        'max_entries' => 'integer',
        'min_score' => 'integer',
        'has_divisions' => 'boolean',
        'division_thresholds' => 'array',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'last_updated_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Get the course this leaderboard belongs to
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get all entries for this leaderboard
     */
    public function entries()
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    /**
     * Get top entries
     */
    public function topEntries($limit = 10)
    {
        return $this->hasMany(LeaderboardEntry::class)
            ->orderBy('rank', 'asc')
            ->limit($limit);
    }

    /**
     * Scope for active leaderboards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible leaderboards
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by period
     */
    public function scopeOfPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope for current season
     */
    public function scopeCurrentSeason($query)
    {
        return $query->whereNotNull('season_number')
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for course leaderboards
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope for global leaderboards
     */
    public function scopeGlobal($query)
    {
        return $query->where('type', 'global');
    }
}
