<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseGroupMembershipHistory extends Model
{
    use HasFactory;

    public const SOURCE_PROFILE = 'profile';

    public const SOURCE_GROUP_PAGE = 'group_page';

    public const SOURCE_MEMBERSHIP_REQUEST = 'membership_request';

    public const SOURCE_BULK_ENROLL = 'bulk_enroll';

    public const SOURCE_BULK_IMPORT = 'bulk_import';

    public const SOURCE_BULK_REMOVE = 'bulk_remove';

    public const SOURCE_BACKFILL = 'backfill';

    public const SOURCE_SYSTEM = 'system';

    protected $fillable = [
        'student_id',
        'group_id',
        'role',
        'joined_at',
        'left_at',
        'join_reason',
        'leave_reason',
        'joined_by',
        'removed_by',
        'source',
        'source_reference_id',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    public function joinedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'joined_by');
    }

    public function removedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }

    public function isActive(): bool
    {
        return $this->left_at === null;
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_PROFILE => 'بروفايل الطالب',
            self::SOURCE_GROUP_PAGE => 'صفحة المجموعة',
            self::SOURCE_MEMBERSHIP_REQUEST => 'طلب انضمام',
            self::SOURCE_BULK_ENROLL => 'تسجيل جماعي',
            self::SOURCE_BULK_IMPORT => 'استيراد جماعي',
            self::SOURCE_BULK_REMOVE => 'إزالة جماعية',
            self::SOURCE_BACKFILL => 'سجل تلقائي',
            default => 'النظام',
        };
    }
}
