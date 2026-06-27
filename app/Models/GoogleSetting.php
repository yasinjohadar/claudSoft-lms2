<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class GoogleSetting extends Model
{
    protected $fillable = [
        'gtm_container_id',
        'gtm_enabled',
        'search_console_verification',
        'search_console_enabled',
        'ga4_property_id',
        'gsc_site_url',
        'service_account_json',
        'analytics_api_enabled',
        'analytics_cache_minutes',
        'last_analytics_sync_at',
    ];

    protected $casts = [
        'gtm_enabled' => 'boolean',
        'search_console_enabled' => 'boolean',
        'service_account_json' => 'encrypted',
        'analytics_api_enabled' => 'boolean',
        'analytics_cache_minutes' => 'integer',
        'last_analytics_sync_at' => 'datetime',
    ];

    public static function getSettings(): self
    {
        $ttl = (int) config('google_marketing.cache.settings_ttl', 300);

        try {
            if (! Schema::hasTable('google_settings')) {
                return new self(static::defaultAttributes());
            }

            return Cache::remember('google_settings_singleton', $ttl, function () {
                return static::query()->first() ?? static::query()->create(static::defaultAttributes());
            });
        } catch (\Throwable) {
            return new self(static::defaultAttributes());
        }
    }

    public static function clearCache(): void
    {
        Cache::forget('google_settings_singleton');
    }

    public static function defaultAttributes(): array
    {
        return [
            'gtm_container_id' => null,
            'gtm_enabled' => false,
            'search_console_verification' => null,
            'search_console_enabled' => false,
            'ga4_property_id' => null,
            'gsc_site_url' => null,
            'service_account_json' => null,
            'analytics_api_enabled' => false,
            'analytics_cache_minutes' => 60,
            'last_analytics_sync_at' => null,
        ];
    }

    public function isGtmActive(): bool
    {
        return $this->gtm_enabled
            && filled($this->gtm_container_id)
            && preg_match('/^GTM-[A-Z0-9]+$/', (string) $this->gtm_container_id);
    }

    public function isSearchConsoleActive(): bool
    {
        return $this->search_console_enabled && filled($this->search_console_verification);
    }

    public function isAnalyticsApiActive(): bool
    {
        return $this->analytics_api_enabled
            && filled($this->ga4_property_id)
            && filled($this->service_account_json);
    }

    public function isSearchConsoleApiActive(): bool
    {
        return $this->isAnalyticsApiActive() && filled($this->gsc_site_url);
    }

    public function getAnalyticsCacheMinutes(): int
    {
        return max(5, (int) ($this->analytics_cache_minutes ?: 60));
    }

    public function getGa4PropertyPath(): string
    {
        return 'properties/' . preg_replace('/\D/', '', (string) $this->ga4_property_id);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
