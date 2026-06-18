<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkEmailRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'email',
        'status',
        'error_message',
        'rendered_subject',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BulkEmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
