<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkEmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_setting_id',
        'email_template_id',
        'content_mode',
        'subject',
        'body',
        'audience_type',
        'course_id',
        'group_id',
        'student_ids',
        'total_recipients',
        'sent_count',
        'failed_count',
        'skipped_count',
        'status',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'student_ids' => 'array',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'skipped_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const CONTENT_MODE_TEMPLATE = 'template';
    public const CONTENT_MODE_CUSTOM = 'custom';

    public const AUDIENCE_INDIVIDUAL = 'individual';
    public const AUDIENCE_SELECTED = 'selected';
    public const AUDIENCE_GROUP = 'group';
    public const AUDIENCE_COURSE = 'course';
    public const AUDIENCE_COURSE_GROUP = 'course_group';

    public function emailSetting(): BelongsTo
    {
        return $this->belongsTo(EmailSetting::class, 'email_setting_id');
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
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
        return $this->hasMany(BulkEmailRecipient::class, 'campaign_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function getProcessedCountAttribute(): int
    {
        return $this->sent_count + $this->failed_count + $this->skipped_count;
    }

    public function isFullyProcessed(): bool
    {
        return $this->processed_count >= $this->total_recipients;
    }
}
