<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'event_key',
        'database_enabled',
        'realtime_enabled',
        'fcm_enabled',
        'mail_enabled',
        'whatsapp_enabled',
        'whatsapp_wapi_enabled',
        'meta',
    ];

    protected $casts = [
        'database_enabled' => 'boolean',
        'realtime_enabled' => 'boolean',
        'fcm_enabled' => 'boolean',
        'mail_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'whatsapp_wapi_enabled' => 'boolean',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
