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
        'trusted_at',
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
        'trusted_at' => 'datetime',
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
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by device type.
     */
    public function scopeByDeviceType($query, string $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Devices awaiting admin trust approval.
     */
    public function scopePendingTrust($query)
    {
        return $query->where('is_trusted', false)->where('is_blocked', false);
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
     * Mark the device as trusted (and clear any block).
     */
    public function trust(): bool
    {
        return $this->update([
            'is_trusted' => true,
            'trusted_at' => now(),
            'is_blocked' => false,
        ]);
    }

    /**
     * Mark the device as untrusted.
     */
    public function untrust(): bool
    {
        return $this->update([
            'is_trusted' => false,
            'trusted_at' => null,
        ]);
    }

    /**
     * Block the device (and revoke trust).
     */
    public function block(): bool
    {
        return $this->update([
            'is_blocked' => true,
            'is_trusted' => false,
            'trusted_at' => null,
        ]);
    }

    /**
     * Unblock the device.
     */
    public function unblock(): bool
    {
        return $this->update(['is_blocked' => false]);
    }

    /**
     * Whether the device is awaiting admin approval.
     */
    public function isPendingTrust(): bool
    {
        return ! $this->is_trusted && ! $this->is_blocked;
    }

    // ========== Accessors ==========

    /**
     * Get device info as formatted string.
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = [];
        
        if ($this->device_name) {
            $parts[] = $this->device_name;
        }
        
        if ($this->device_type) {
            $deviceTypeNames = [
                'mobile' => 'جوال',
                'tablet' => 'تابلت',
                'desktop' => 'سطح مكتب',
            ];
            $parts[] = $deviceTypeNames[$this->device_type] ?? ucfirst($this->device_type);
        }
        
        if ($this->browser) {
            $browserInfo = $this->browser;
            if ($this->browser_version) {
                $browserInfo .= ' ' . $this->browser_version;
            }
            $parts[] = $browserInfo;
        }
        
        if ($this->platform) {
            $platformInfo = $this->platform;
            if ($this->platform_version) {
                $platformInfo .= ' ' . $this->platform_version;
            }
            $parts[] = $platformInfo;
        }

        return implode(' • ', $parts) ?: 'غير محدد';
    }

    /**
     * Get status badge information.
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_blocked) {
            return [
                'text' => 'محظور',
                'class' => 'badge bg-danger',
                'icon' => 'fa-ban',
            ];
        }
        
        if ($this->is_trusted) {
            return [
                'text' => 'موثوق',
                'class' => 'badge bg-success',
                'icon' => 'fa-shield-check',
            ];
        }

        return [
            'text' => 'بانتظار الموافقة',
            'class' => 'badge bg-warning',
            'icon' => 'fa-clock',
        ];
    }

    /**
     * Get time since last used in human readable format.
     */
    public function getLastUsedHumanAttribute(): string
    {
        if (!$this->last_used_at) {
            return 'لم يُستخدم';
        }
        
        return $this->last_used_at->diffForHumans();
    }

    /**
     * Get time since first used in human readable format.
     */
    public function getFirstUsedHumanAttribute(): string
    {
        if (!$this->first_used_at) {
            return 'غير محدد';
        }
        
        return $this->first_used_at->format('Y-m-d H:i');
    }

    /**
     * Get location information from meta.
     * If location is not in meta but IP exists, try to fetch it.
     */
    public function getLocationAttribute(): ?array
    {
        $location = $this->meta['location'] ?? null;
        
        // If no location but we have IP, try to fetch it (lazy loading)
        if (!$location && ($this->ip_address || $this->last_ip_address)) {
            try {
                $ipToUse = $this->last_ip_address ?? $this->ip_address;
                $geoService = app(\App\Services\GeoLocationService::class);
                $location = $geoService->getLocationFromIp($ipToUse);
                
                // Update meta if location was found
                if ($location) {
                    $meta = $this->meta ?? [];
                    $meta['location'] = $location;
                    $this->update(['meta' => $meta]);
                }
            } catch (\Exception $e) {
                // Silently fail
            }
        }
        
        return $location;
    }

    /**
     * Get formatted location string (City, Country).
     */
    public function getLocationFormattedAttribute(): string
    {
        $location = $this->location;
        
        if (!$location) {
            return '-';
        }

        $parts = [];
        
        if (!empty($location['city'])) {
            $parts[] = $location['city'];
        }
        
        if (!empty($location['country_name'])) {
            $parts[] = $location['country_name'];
        } elseif (!empty($location['country'])) {
            $parts[] = $location['country'];
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }

    /**
     * Get country name.
     */
    public function getCountryAttribute(): ?string
    {
        return $this->location['country_name'] ?? $this->location['country'] ?? null;
    }

    /**
     * Get city name.
     */
    public function getCityAttribute(): ?string
    {
        return $this->location['city'] ?? null;
    }
}
