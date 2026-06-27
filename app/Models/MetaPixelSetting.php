<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MetaPixelSetting extends Model
{
    protected $fillable = [
        'pixel_id',
        'enabled',
        'capi_access_token',
        'capi_enabled',
        'test_event_code',
        'track_page_view',
        'track_view_content',
        'track_search',
        'track_lead',
        'track_contact',
        'track_lead_started',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'capi_enabled' => 'boolean',
        'capi_access_token' => 'encrypted',
        'track_page_view' => 'boolean',
        'track_view_content' => 'boolean',
        'track_search' => 'boolean',
        'track_lead' => 'boolean',
        'track_contact' => 'boolean',
        'track_lead_started' => 'boolean',
    ];

    public static function getSettings(): self
    {
        try {
            if (! Schema::hasTable('meta_pixel_settings')) {
                return new self(static::defaultAttributes());
            }

            return static::query()->first() ?? static::query()->create(static::defaultAttributes());
        } catch (\Throwable) {
            return new self(static::defaultAttributes());
        }
    }

    public static function defaultAttributes(): array
    {
        return [
            'pixel_id' => null,
            'enabled' => false,
            'capi_access_token' => null,
            'capi_enabled' => false,
            'test_event_code' => null,
            'track_page_view' => true,
            'track_view_content' => true,
            'track_search' => true,
            'track_lead' => true,
            'track_contact' => true,
            'track_lead_started' => true,
        ];
    }

    public function isEventEnabled(string $eventName): bool
    {
        $definition = config("meta_pixel.events.{$eventName}");

        if (! $definition) {
            return true;
        }

        $key = $definition['setting_key'] ?? null;

        if (! $key) {
            return true;
        }

        return (bool) ($this->{$key} ?? true);
    }

    public function enabledEventsCount(): int
    {
        $count = 0;

        foreach (array_keys(config('meta_pixel.events', [])) as $name) {
            if ($this->isEventEnabled($name)) {
                $count++;
            }
        }

        return $count;
    }

    public function hasValidPixel(): bool
    {
        return $this->enabled && filled($this->pixel_id) && preg_match('/^\d+$/', (string) $this->pixel_id);
    }

    public function hasValidCapi(): bool
    {
        return $this->capi_enabled && filled($this->capi_access_token) && $this->hasValidPixel();
    }
}
