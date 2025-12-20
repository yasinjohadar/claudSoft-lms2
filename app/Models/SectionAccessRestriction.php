<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SectionAccessRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'restriction_type',
        'restriction_id',
        'access_type',
    ];

    // Relationships

    /**
     * Get the section that owns the restriction.
     */
    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    // Scopes

    /**
     * Scope a query to only include allow restrictions.
     */
    public function scopeAllow($query)
    {
        return $query->where('access_type', 'allow');
    }

    /**
     * Scope a query to only include deny restrictions.
     */
    public function scopeDeny($query)
    {
        return $query->where('access_type', 'deny');
    }

    /**
     * Scope a query to filter by restriction type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('restriction_type', $type);
    }

    // Helper Methods

    /**
     * Check if this is an allow restriction.
     */
    public function isAllow(): bool
    {
        return $this->access_type === 'allow';
    }

    /**
     * Check if this is a deny restriction.
     */
    public function isDeny(): bool
    {
        return $this->access_type === 'deny';
    }
}
