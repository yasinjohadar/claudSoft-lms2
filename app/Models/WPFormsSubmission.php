<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WPFormsSubmission extends Model
{
    protected $table = 'wpforms_submissions';

    protected $fillable = [
        'form_id',
        'entry_id',
        'submission_type',
        'form_data',
        'status',
        'user_id',
        'course_id',
        'processing_notes',
        'processed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'form_data' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('submission_type', $type);
    }

    // Helper Methods
    public function markAsProcessed(?int $userId = null, ?int $courseId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'processed',
            'user_id' => $userId,
            'course_id' => $courseId,
            'processing_notes' => $notes,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'processing_notes' => $error,
            'processed_at' => now(),
        ]);
    }

    public function getFormField(string $fieldName)
    {
        return $this->form_data[$fieldName] ?? null;
    }

    public static function getSubmissionTypes(): array
    {
        return [
            'enrollment' => 'تسجيل في كورس',
            'contact' => 'رسالة تواصل',
            'review' => 'مراجعة كورس',
            'payment' => 'دفع رسوم',
            'application' => 'طلب التحاق',
            'survey' => 'استبيان',
        ];
    }
}
