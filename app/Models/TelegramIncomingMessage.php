<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramIncomingMessage extends Model
{
    protected $fillable = [
        'user_id', 'chat_id', 'telegram_username', 'update_id', 'message_id',
        'text', 'payload', 'received_at', 'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
