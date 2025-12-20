<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WebhookToken extends Model
{
    protected $fillable = [
        'name',
        'source',
        'token',
        'allowed_ips',
        'form_types',
        'description',
        'is_active',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'form_types' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Encrypt token before saving
     */
    public function setTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['token'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt token when retrieving
     */
    public function getTokenAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Get active token for a source
     */
    public static function getActiveToken($source = 'wpforms')
    {
        return static::where('source', $source)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get sources list
     */
    public static function getSources(): array
    {
        return [
            'wpforms' => 'WPForms',
            'n8n' => 'n8n',
            'other' => 'أخرى',
        ];
    }
}
