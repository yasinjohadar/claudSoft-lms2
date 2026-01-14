<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id','session_uuid','session_name','session_description',
        'started_at','ended_at','duration_seconds','ip_address','user_agent',
        'device_type','browser','browser_version','platform','platform_version',
        'screen_resolution','connection_type','bandwidth_mbps','status','meta',
        'login_log_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'duration_seconds' => 'integer',
        'meta'       => 'array',
    ];

    /**
     * العلاقة مع User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع SessionActivities
     */
    public function activities()
    {
        return $this->hasMany(SessionActivity::class, 'user_session_id')->latest('occurred_at');
    }

    // ========== Scopes ==========

    /**
     * Scope a query to only include active sessions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include disconnected sessions.
     */
    public function scopeDisconnected(Builder $query): Builder
    {
        return $query->where('status', 'disconnected');
    }

    /**
     * Scope a query to only include timeout sessions.
     */
    public function scopeTimeout(Builder $query): Builder
    {
        return $query->where('status', 'timeout');
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by device type.
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by connection type.
     */
    public function scopeByConnectionType(Builder $query, string $connectionType): Builder
    {
        return $query->where('connection_type', $connectionType);
    }

    // ========== Accessors ==========

    /**
     * Get formatted duration (hours:minutes:seconds).
     */
    public function getDurationFormattedAttribute(): string
    {
        $seconds = $this->duration_seconds ?? 0;
        
        if ($seconds == 0 && $this->started_at && !$this->ended_at) {
            // Calculate from timestamps if duration_seconds is not set
            $seconds = now()->diffInSeconds($this->started_at);
        } elseif ($seconds == 0 && $this->started_at && $this->ended_at) {
            $seconds = $this->ended_at->diffInSeconds($this->started_at);
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            return sprintf('%d:%02d', $minutes, $secs);
        }
    }

    /**
     * Get device info as formatted string.
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = [];
        
        if ($this->device_type) {
            $parts[] = ucfirst($this->device_type);
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
     * Check if session is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->ended_at === null;
    }

    /**
     * Get activities count.
     */
    public function getActivitiesCountAttribute(): int
    {
        return $this->activities()->count();
    }

    /**
     * Get location information from meta.
     */
    public function getLocationAttribute(): ?array
    {
        return $this->meta['location'] ?? null;
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

    // ========== Helper Methods ==========

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ended_at === null;
    }

    /**
     * Get calculated duration in seconds.
     */
    public function getCalculatedDuration(): int
    {
        if ($this->duration_seconds) {
            return $this->duration_seconds;
        }

        if ($this->started_at) {
            $endTime = $this->ended_at ?? now();
            return $endTime->diffInSeconds($this->started_at);
        }

        return 0;
    }
}
