<?php

namespace App\Services\CourseGroup;

use App\Models\CourseGroup;
use App\Models\CourseGroupMembershipHistory;
use App\Models\User;
use Illuminate\Support\Arr;

class CourseGroupMembershipHistoryService
{
    /**
     * @param  array{reason?: ?string, source?: string, performed_by?: ?int, source_reference_id?: ?int}  $context
     */
    public function recordJoin(CourseGroup $group, User $student, string $role, array $context = []): CourseGroupMembershipHistory
    {
        $source = $context['source'] ?? CourseGroupMembershipHistory::SOURCE_SYSTEM;
        $performedBy = Arr::get($context, 'performed_by', auth()->id());

        return CourseGroupMembershipHistory::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'role' => $role,
            'joined_at' => now(),
            'join_reason' => $this->resolveReason(
                Arr::get($context, 'reason'),
                $source,
                'join'
            ),
            'joined_by' => $performedBy,
            'source' => $source,
            'source_reference_id' => Arr::get($context, 'source_reference_id'),
        ]);
    }

    /**
     * @param  array{reason?: ?string, source?: string, performed_by?: ?int, source_reference_id?: ?int}  $context
     */
    public function recordLeave(CourseGroup $group, User $student, array $context = []): ?CourseGroupMembershipHistory
    {
        $source = $context['source'] ?? CourseGroupMembershipHistory::SOURCE_SYSTEM;
        $performedBy = Arr::get($context, 'performed_by', auth()->id());

        $history = CourseGroupMembershipHistory::query()
            ->where('student_id', $student->id)
            ->where('group_id', $group->id)
            ->whereNull('left_at')
            ->latest('joined_at')
            ->first();

        if (! $history) {
            return null;
        }

        $history->update([
            'left_at' => now(),
            'leave_reason' => $this->resolveReason(
                Arr::get($context, 'reason'),
                $source,
                'leave'
            ),
            'removed_by' => $performedBy,
        ]);

        return $history->fresh();
    }

    public function defaultReason(string $source, string $action): string
    {
        if ($action === 'join') {
            return match ($source) {
                CourseGroupMembershipHistory::SOURCE_PROFILE => 'إضافة من بروفايل الطالب',
                CourseGroupMembershipHistory::SOURCE_GROUP_PAGE => 'إضافة يدوية من صفحة المجموعة',
                CourseGroupMembershipHistory::SOURCE_MEMBERSHIP_REQUEST => 'قبول طلب انضمام',
                CourseGroupMembershipHistory::SOURCE_BULK_ENROLL => 'تسجيل جماعي',
                CourseGroupMembershipHistory::SOURCE_BULK_IMPORT => 'استيراد جماعي',
                CourseGroupMembershipHistory::SOURCE_BACKFILL => 'سجل تلقائي — عضوية سابقة للنظام',
                default => 'انضمام للمجموعة',
            };
        }

        return match ($source) {
            CourseGroupMembershipHistory::SOURCE_PROFILE => 'إزالة من بروفايل الطالب',
            CourseGroupMembershipHistory::SOURCE_GROUP_PAGE => 'إزالة من صفحة المجموعة',
            CourseGroupMembershipHistory::SOURCE_BULK_REMOVE => 'إزالة جماعية',
            default => 'مغادرة المجموعة',
        };
    }

    private function resolveReason(?string $reason, string $source, string $action): string
    {
        $trimmed = trim((string) $reason);

        if ($trimmed !== '') {
            return $trimmed;
        }

        return $this->defaultReason($source, $action);
    }
}
