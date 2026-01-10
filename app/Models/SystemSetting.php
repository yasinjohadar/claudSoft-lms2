<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Scope a query to filter by key
     */
    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to filter by group
     */
    public function scopeOfGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Get the value attribute with proper casting based on type
     */
    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        return match($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($value === '1' || $value === 'true'),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper casting based on type
     */
    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['value'] = null;
            return;
        }

        $this->attributes['value'] = match($this->type ?? 'string') {
            'integer' => (string) $value,
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Create or update a system setting
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string|null $description
     * @return static
     */
    public static function set(string $key, $value, string $type, string $group, ?string $description = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key, 'group' => $group],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        return $setting;
    }

    /**
     * Get a setting value by key and group
     *
     * @param string $key
     * @param string $group
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, string $group, $default = null)
    {
        $setting = static::where('key', $key)
            ->where('group', $group)
            ->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }
}

