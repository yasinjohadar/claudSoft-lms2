<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAccount extends Model
{
    protected $fillable = [
        'label', 'phone_number', 'session_status', 'is_default', 'metadata', 'connected_at',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
        'connected_at' => 'datetime',
    ];

    public function isConnected(): bool
    {
        return $this->session_status === 'connected';
    }

    public static function defaultAccount(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->where('session_status', 'connected')->first();
    }
}
