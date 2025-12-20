<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'platform_version',
        'ip_address',
        'user_agent',
        'total_logins',
        'first_used_at',
        'last_used_at',
        'last_ip_address',
        'is_trusted',
        'is_blocked',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_logins' => 'integer',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_trusted' => 'boolean',
        'is_blocked' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include trusted devices.
     */
    public function scopeTrusted($query)
    {
        return $query->where('is_trusted', true);
    }

    /**
     * Scope a query to only include blocked devices.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope a query to only include active devices (not blocked).
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope a query to get devices by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Scope a query to get recently used devices.
     */
    public function scopeRecentlyUsed($query, int $days = 30)
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }

    /**
     * Increment the total logins count.
     */
    public function incrementLogins(): void
    {
        $this->increment('total_logins');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Update the last used information.
     */
    public function updateLastUsed(string $ipAddress): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_ip_address' => $ipAddress,
        ]);
    }

    /**
     * Mark the device as trusted.
     */
    public function trust(): bool
    {
        return $this->update(['is_trusted' => true]);
    }

    /**
     * Mark the device as untrusted.
     */
    public function untrust(): bool
    {
        return $this->update(['is_trusted' => false]);
    }

    /**
     * Block the device.
     */
    public function block(): bool
    {
        return $this->update(['is_blocked' => true]);
    }

    /**
     * Unblock the device.
     */
    public function unblock(): bool
    {
        return $this->update(['is_blocked' => false]);
    }
}
