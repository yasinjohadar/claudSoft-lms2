<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'name_ar',
        'email',
        'phone',
        'country_code',
        'full_phone',
        'nationality_id',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'notes',
        'additional_info',
        'special_requirements',
        'commitment_to_training',
        'has_sufficient_time',
        'has_computer',
        'computer_experience_level',
        'programming_experience',
        'computer_programming_background',
        'education_level',
        'education_major',
        'current_job',
        'interested_in_bootcamp',
        'email_sent',
        'whatsapp_sent',
        'email_sent_at',
        'whatsapp_sent_at',
        'whatsapp_error',
        'user_created',
        'user_id',
        'status',
        'processed_at',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_sent' => 'boolean',
        'whatsapp_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'whatsapp_sent_at' => 'datetime',
        'user_created' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Gender constants
     */
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDER_OTHER = 'other';

    // Relationships

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

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

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper Methods

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }
}
