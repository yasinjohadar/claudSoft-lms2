<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseReview extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'student_id',
        'rating',
        'title',
        'review',
        'status',
        'approved_by',
        'approved_at',
        'admin_feedback',
        'helpful_count',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
        'helpful_count' => 'integer',
        'is_featured' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }

    public function scopeTopRated($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    public function scopeMostHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    // Helper Methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject(?string $adminFeedback = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_feedback' => $adminFeedback,
        ]);
    }

    public function incrementHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function getStarsAttribute(): string
    {
        return str_repeat('⭐', $this->rating);
    }

    public static function getStatuses(): array
    {
        return [
            'pending' => ['name' => 'قيد المراجعة', 'color' => 'warning', 'icon' => 'ri-time-line'],
            'approved' => ['name' => 'معتمد', 'color' => 'success', 'icon' => 'ri-checkbox-circle-line'],
            'rejected' => ['name' => 'مرفوض', 'color' => 'danger', 'icon' => 'ri-close-circle-line'],
        ];
    }
}
