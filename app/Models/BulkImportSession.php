<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkImportSession extends Model
{
    protected $fillable = [
        'uploaded_by',
        'file_name',
        'file_path',
        'total_rows',
        'new_users',
        'updated_users',
        'skipped_rows',
        'failed_rows',
        'enrollments_created',
        'group_members_added',
        'updated_users_details',
        'new_users_details',
        'errors',
        'mapping',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'updated_users_details' => 'array',
        'new_users_details' => 'array',
        'errors' => 'array',
        'mapping' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم الذي رفع الملف
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * التحقق من حالة الجلسة
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * تحديث حالة الجلسة
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }

    /**
     * إضافة مستخدم جديد للتفاصيل
     */
    public function addNewUser(array $userDetails): void
    {
        $details = $this->new_users_details ?? [];
        $details[] = $userDetails;

        $this->update([
            'new_users_details' => $details,
            'new_users' => count($details),
        ]);
    }

    /**
     * إضافة مستخدم محدَّث للتفاصيل
     */
    public function addUpdatedUser(array $userDetails): void
    {
        $details = $this->updated_users_details ?? [];
        $details[] = $userDetails;

        $this->update([
            'updated_users_details' => $details,
            'updated_users' => count($details),
        ]);
    }

    /**
     * إضافة خطأ
     */
    public function addError(int $row, string $email, string $message): void
    {
        $errors = $this->errors ?? [];
        $errors[] = [
            'row' => $row,
            'email' => $email,
            'message' => $message,
        ];

        $this->update([
            'errors' => $errors,
            'failed_rows' => count($errors),
        ]);
    }

    /**
     * زيادة عداد التسجيلات في الكورسات
     */
    public function incrementEnrollments(): void
    {
        $this->increment('enrollments_created');
    }

    /**
     * زيادة عداد الإضافات للمجموعات
     */
    public function incrementGroupMembers(): void
    {
        $this->increment('group_members_added');
    }

    /**
     * زيادة عداد الصفوف المتخطاة
     */
    public function incrementSkipped(): void
    {
        $this->increment('skipped_rows');
    }

    /**
     * حساب معدل النجاح
     */
    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        $successful = $this->new_users + $this->updated_users;
        return round(($successful / $this->total_rows) * 100, 2);
    }

    /**
     * حساب معدل الفشل
     */
    public function getFailureRate(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->failed_rows / $this->total_rows) * 100, 2);
    }
}
