<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BadgeDistributionReportService
{
    public function __construct(
        protected GamificationStudentScopeService $studentScopeService,
        protected BadgeService $badgeService
    ) {}

    public function buildScopeStats(int $courseId = 0, int $groupId = 0): array
    {
        $totalStudents = $this->studentScopeService->countStudentsInScope($courseId, $groupId);
        $activeBadges = Badge::query()->where('is_active', true)->count();
        $studentSubquery = $this->studentScopeService->scopedStudentIdsSubquery($courseId, $groupId);

        $awardsQuery = UserBadge::query();
        $this->applyStudentSubqueryToUserBadges($awardsQuery, $studentSubquery);

        $totalAwards = (clone $awardsQuery)->count();

        $studentsWithBadges = 0;
        $avgBadgesPerStudent = 0.0;

        if ($totalStudents > 0) {
            $studentsWithBadges = (clone $awardsQuery)
                ->distinct('user_id')
                ->count('user_id');

            $avgBadgesPerStudent = round($totalAwards / $totalStudents, 2);
        }

        $studentsWithBadgeRate = $totalStudents > 0
            ? round(($studentsWithBadges / $totalStudents) * 100, 2)
            : 0.0;

        return [
            'total_students' => $totalStudents,
            'active_badges' => $activeBadges,
            'total_awards' => $totalAwards,
            'avg_badges_per_student' => $avgBadgesPerStudent,
            'students_with_badges' => $studentsWithBadges,
            'students_with_badge_rate' => $studentsWithBadgeRate,
        ];
    }

    public function paginateBadgeDistribution(
        int $courseId = 0,
        int $groupId = 0,
        ?string $rarity = null,
        ?string $search = null,
        int $perPage = 30
    ): LengthAwarePaginator {
        $totalStudents = $this->studentScopeService->countStudentsInScope($courseId, $groupId);
        $studentSubquery = $this->studentScopeService->scopedStudentIdsSubquery($courseId, $groupId);

        $query = Badge::query()
            ->where('is_active', true)
            ->withCount(['userBadges as earners_count' => function (Builder $badgeQuery) use ($studentSubquery) {
                $this->applyStudentSubqueryToUserBadges($badgeQuery, $studentSubquery);
            }]);

        if ($rarity) {
            $query->where('rarity', $rarity);
        }

        if ($search) {
            $term = trim($search);
            $query->where(function (Builder $badgeQuery) use ($term) {
                $badgeQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $badges = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);

        $badges->getCollection()->transform(function (Badge $badge) use ($totalStudents) {
            $badge->award_rate = $totalStudents > 0
                ? round(($badge->earners_count / $totalStudents) * 100, 2)
                : 0.0;

            return $badge;
        });

        return $badges;
    }

    public function paginateStudentsReport(
        int $courseId = 0,
        int $groupId = 0,
        ?string $search = null,
        int $perPage = 30
    ): LengthAwarePaginator {
        $activeBadgesTotal = Badge::query()->where('is_active', true)->count();
        $query = $this->studentScopeService->studentsInScopeQuery($courseId, $groupId)
            ->withCount(['userBadges as earned_count' => function (Builder $userBadgeQuery) {
                $userBadgeQuery->whereHas('badge', fn (Builder $badgeQuery) => $badgeQuery->where('is_active', true));
            }]);

        if ($search) {
            $term = trim($search);
            $query->where(function (Builder $studentQuery) use ($term) {
                $studentQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $students = $query->orderBy('name')->paginate($perPage);

        $students->getCollection()->transform(function (User $student) use ($activeBadgesTotal) {
            $student->active_badges_total = $activeBadgesTotal;
            $student->completion_rate = $activeBadgesTotal > 0
                ? round(($student->earned_count / $activeBadgesTotal) * 100, 2)
                : 0.0;

            return $student;
        });

        return $students;
    }

    public function buildStudentDetail(User $student, int $courseId = 0, int $groupId = 0): array
    {
        $this->ensureStudentInScope($student, $courseId, $groupId);

        $earned = UserBadge::query()
            ->where('user_id', $student->id)
            ->with(['badge' => fn ($query) => $query->where('is_active', true)])
            ->whereHas('badge', fn (Builder $query) => $query->where('is_active', true))
            ->latest('awarded_at')
            ->get()
            ->filter(fn (UserBadge $userBadge) => $userBadge->badge !== null)
            ->values();

        $earnedBadgeIds = $earned->pluck('badge_id')->all();

        $inProgress = Badge::query()
            ->where('is_active', true)
            ->whereNotIn('id', $earnedBadgeIds)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (Badge $badge) use ($student) {
                $progress = $this->badgeService->getBadgeProgress($student, $badge);

                return [
                    'badge' => $badge,
                    'progress' => $progress,
                ];
            })
            ->filter(fn (array $row) => ($row['progress']['progress'] ?? 0) > 0)
            ->sortByDesc(fn (array $row) => $row['progress']['progress'] ?? 0)
            ->values();

        $activeBadgesTotal = Badge::query()->where('is_active', true)->count();
        $overallProgressValues = $inProgress->pluck('progress.progress')->filter(fn ($value) => $value > 0);

        return [
            'student' => $student,
            'earned' => $earned,
            'in_progress' => $inProgress,
            'earned_count' => $earned->count(),
            'active_badges_total' => $activeBadgesTotal,
            'completion_rate' => $activeBadgesTotal > 0
                ? round(($earned->count() / $activeBadgesTotal) * 100, 2)
                : 0.0,
            'overall_progress' => $overallProgressValues->isNotEmpty()
                ? round($overallProgressValues->avg(), 2)
                : 0.0,
        ];
    }

    protected function ensureStudentInScope(User $student, int $courseId, int $groupId): void
    {
        $exists = $this->studentScopeService
            ->studentsInScopeQuery($courseId, $groupId)
            ->where('id', $student->id)
            ->exists();

        if (! $exists) {
            abort(404);
        }
    }

    protected function applyStudentSubqueryToUserBadges(Builder $query, \Illuminate\Database\Query\Builder $studentSubquery): void
    {
        $query->whereIn('user_id', $studentSubquery);
    }
}
