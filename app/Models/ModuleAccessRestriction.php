<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleAccessRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'restriction_type',
        'restriction_id',
        'access_type',
    ];

    // Relationships

    /**
     * Get the module that owns the restriction.
     */
    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
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
