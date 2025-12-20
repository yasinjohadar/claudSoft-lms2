<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'category',
        'rarity',
        'criteria',
        'points_value',
        'is_active',
        'is_visible',
        'is_hidden',
        'sort_order',
        'color_code',
        'awarded_count',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_hidden' => 'boolean',
        'points_value' => 'integer',
        'awarded_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all users who have this badge
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot([
                'awarded_at',
                'reason',
                'related_type',
                'related_id',
                'progress',
                'is_seen',
                'is_featured',
                'points_awarded',
                'metadata'
            ])
            ->withTimestamps();
    }

    /**
     * Get user badge records
     */
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get achievements that award this badge
     */
    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * Get challenges that award this badge
     */
    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }

    /**
     * Get experience levels that award this badge
     */
    public function experienceLevels()
    {
        return $this->hasMany(ExperienceLevel::class);
    }

    /**
     * Scope for active badges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible badges (not hidden)
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by rarity
     */
    public function scopeOfRarity($query, $rarity)
    {
        return $query->where('rarity', $rarity);
    }
}
