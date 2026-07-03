<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramBroadcast extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const TARGET_STUDENTS = 'students';

    public const TARGET_GROUP_CHAT = 'group_chat';

    public const TARGET_CHANNEL = 'channel';

    protected $fillable = [
        'message_template', 'send_type', 'target_type', 'course_id', 'group_id',
        'telegram_chat_id', 'telegram_chat_title', 'total_recipients',
        'sent_count', 'failed_count', 'status', 'meta', 'created_by',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'meta' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(TelegramBroadcastRecipient::class, 'broadcast_id');
    }
}
