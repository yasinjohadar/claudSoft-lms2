<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Gamification\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API بروفايل الطالب الكامل — لعرض كل البيانات في تطبيق Flutter.
 */
class ProfileController extends Controller
{
    /**
     * بروفايل الطالب الكامل: بيانات الحساب، الإحصائيات، المستوى، الشارات، الإنجازات، ملخص التسجيلات.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing(['stats', 'nationality']);

        $stats = $user->stats;
        if (!$stats) {
            $stats = $user->stats()->create(['user_id' => $user->id]);
        }

        // اسم المستوى من جدول المستويات (إن وُجد)
        $levelName = null;
        try {
            $levelRow = Level::where('level', (int) $stats->current_level)->first();
            $levelName = $levelRow?->name;
        } catch (\Throwable $e) {
            // تجاهل إن لم يكن الجدول موجوداً
        }

        // الشارات المكتسبة مع تاريخ المنح
        $earnedBadges = $user->userBadges()
            ->with('badge')
            ->latest('awarded_at')
            ->get()
            ->map(function ($ub) {
                return [
                    'id' => $ub->badge_id,
                    'name' => $ub->badge?->name,
                    'slug' => $ub->badge?->slug,
                    'description' => $ub->badge?->description,
                    'icon' => $ub->badge?->icon,
                    'rarity' => $ub->badge?->rarity,
                    'points_value' => $ub->badge?->points_value,
                    'awarded_at' => $this->formatDateTime($ub->awarded_at),
                ];
            });

        // الإنجازات: مكتملة + قيد التقدم
        $userAchievements = $user->userAchievements()
            ->with('achievement')
            ->orderByDesc('completed_at')
            ->get();

        $achievementsCompleted = $userAchievements
            ->where('status', 'completed')
            ->map(fn ($ua) => $this->formatAchievement($ua))
            ->values();
        $achievementsInProgress = $userAchievements
            ->where('status', '!=', 'completed')
            ->map(fn ($ua) => $this->formatAchievement($ua))
            ->values();

        // ملخص التسجيلات في الكورسات (قد يكون null إذا لا توجد تسجيلات)
        $enrollmentsSummary = CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN enrollment_status = ? THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN completion_percentage >= 100 THEN 1 ELSE 0 END) as completed
            ', ['active'])
            ->first();

        $enrollmentsTotal = (int) (optional($enrollmentsSummary)->total ?? 0);
        $enrollmentsActive = (int) (optional($enrollmentsSummary)->active ?? 0);
        $enrollmentsCompleted = (int) (optional($enrollmentsSummary)->completed ?? 0);

        $profile = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'name_ar' => $user->name_ar ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'full_phone' => $user->full_phone,
                'avatar' => $user->avatar ? url($user->avatar) : null,
                'date_of_birth' => $this->formatDate($user->date_of_birth),
                'gender' => $user->gender,
                'address' => $user->address,
                'city' => $user->city,
                'nationality_id' => $user->nationality_id,
                'nationality_name' => $user->nationality?->name ?? null,
                'student_id' => $user->student_id,
                'is_profile_public' => (bool) $user->is_profile_public,
                'last_login_at' => $this->formatDateTime($user->last_login_at),
            ],
            'stats' => [
                'total_points' => (int) ($stats->total_points ?? 0),
                'available_points' => (int) ($stats->available_points ?? 0),
                'total_xp' => (int) ($stats->total_xp ?? 0),
                'current_level' => (int) ($stats->current_level ?? 1),
                'level_name' => $levelName,
                'xp_to_next_level' => (int) ($stats->xp_to_next_level ?? 0),
                'level_progress' => (float) ($stats->level_progress ?? 0),
                'total_badges' => (int) ($stats->total_badges ?? 0),
                'total_achievements' => (int) ($stats->total_achievements ?? 0),
                'current_streak' => (int) ($stats->current_streak ?? 0),
                'longest_streak' => (int) ($stats->longest_streak ?? 0),
                'courses_completed' => (int) ($stats->courses_completed ?? 0),
                'courses_enrolled' => (int) ($stats->courses_enrolled ?? 0),
                'lessons_completed' => (int) ($stats->lessons_completed ?? 0),
                'quizzes_completed' => (int) ($stats->quizzes_completed ?? 0),
                'perfect_scores' => (int) ($stats->perfect_scores ?? 0),
                'assignments_submitted' => (int) ($stats->assignments_submitted ?? 0),
                'average_quiz_score' => (float) ($stats->average_quiz_score ?? 0),
                'average_assignment_score' => (float) ($stats->average_assignment_score ?? 0),
                'global_rank' => $stats->global_rank ? (int) $stats->global_rank : null,
                'division' => $stats->division ?? null,
                'total_study_time' => (int) ($stats->total_study_time ?? 0),
                'last_activity_at' => $this->formatDateTime($stats->last_activity_at),
            ],
            'enrollments_summary' => [
                'total' => $enrollmentsTotal,
                'active' => $enrollmentsActive,
                'completed' => $enrollmentsCompleted,
            ],
            'badges' => $earnedBadges,
            'achievements_completed' => $achievementsCompleted,
            'achievements_in_progress' => $achievementsInProgress,
        ];

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    private function formatDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($value) ? $value : null;
        }
    }

    /** يرجع تاريخاً بصيغة ISO 8601 أو null دون استثناء */
    private function formatDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return is_string($value) ? $value : null;
        }
    }

    private function formatAchievement($userAchievement): array
    {
        $a = $userAchievement->achievement;
        return [
            'id' => $userAchievement->achievement_id,
            'name' => $a?->name,
            'slug' => $a?->slug,
            'description' => $a?->description,
            'icon' => $a?->icon,
            'tier' => $a?->tier,
            'status' => $userAchievement->status,
            'current_value' => (int) ($userAchievement->current_value ?? 0),
            'target_value' => (int) ($userAchievement->target_value ?? 0),
            'progress_percentage' => (float) ($userAchievement->progress_percentage ?? 0),
            'started_at' => $this->formatDateTime($userAchievement->started_at),
            'completed_at' => $this->formatDateTime($userAchievement->completed_at),
            'points_claimed' => (int) ($userAchievement->points_claimed ?? 0),
            'xp_claimed' => (int) ($userAchievement->xp_claimed ?? 0),
        ];
    }
}
