<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    protected PointsService $pointsService;
    protected LevelService $levelService;
    protected StreakService $streakService;

    public function __construct(
        PointsService $pointsService,
        LevelService $levelService,
        StreakService $streakService
    ) {
        $this->pointsService = $pointsService;
        $this->levelService = $levelService;
        $this->streakService = $streakService;
    }

    /**
     * منح مكافأة كاملة (نقاط + XP) مع مراعاة المضاعفات
     */
    public function awardReward(
        User $user,
        int $basePoints,
        int $baseXP,
        string $source,
        ?string $description = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        array $metadata = []
    ): array {
        try {
            return DB::transaction(function () use (
                $user,
                $basePoints,
                $baseXP,
                $source,
                $description,
                $relatedType,
                $relatedId,
                $metadata
            ) {
                // تسجيل النشاط اليومي والحصول على المضاعف
                $streakData = $this->streakService->recordDailyActivity($user);
                $multiplier = $streakData['multiplier'] ?? 1.0;

                // منح النقاط مع المضاعف
                $pointsTransaction = $this->pointsService->awardPoints(
                    $user,
                    $basePoints,
                    $source,
                    $description,
                    $relatedType,
                    $relatedId,
                    $multiplier
                );

                // حساب XP النهائي مع المضاعف
                $finalXP = (int) ($baseXP * $multiplier);

                // منح XP
                $this->levelService->awardXP($user, $finalXP, $source, $description);

                // تحديث أرباح اليوم
                $finalPoints = (int) ($basePoints * $multiplier);
                $this->streakService->updateDailyEarnings($user, $finalPoints, $finalXP);

                Log::info("Gamification reward awarded", [
                    'user_id' => $user->id,
                    'source' => $source,
                    'base_points' => $basePoints,
                    'final_points' => $finalPoints,
                    'base_xp' => $baseXP,
                    'final_xp' => $finalXP,
                    'multiplier' => $multiplier,
                    'metadata' => $metadata,
                ]);

                return [
                    'success' => true,
                    'points_awarded' => $finalPoints,
                    'xp_awarded' => $finalXP,
                    'multiplier' => $multiplier,
                    'streak_data' => $streakData,
                    'transaction' => $pointsTransaction,
                ];
            });
        } catch (\Exception $e) {
            Log::error("Failed to award gamification reward", [
                'user_id' => $user->id,
                'source' => $source,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة إتمام درس
     */
    public function handleLessonCompletion(
        User $user,
        int $lessonId,
        array $metadata = []
    ): array {
        $config = config('gamification.points.lesson_completion', [
            'points' => 50,
            'xp' => 25,
        ]);

        return $this->awardReward(
            $user,
            $config['points'],
            $config['xp'],
            'lesson_completion',
            'إتمام درس',
            'App\Models\Lesson',
            $lessonId,
            $metadata
        );
    }

    /**
     * معالجة مشاهدة فيديو
     */
    public function handleVideoWatch(
        User $user,
        int $videoId,
        int $watchPercentage,
        array $metadata = []
    ): array {
        $config = config('gamification.points.video_watch', [
            'points' => 30,
            'xp' => 15,
        ]);

        // منح نقاط كاملة فقط إذا شاهد 80% أو أكثر
        if ($watchPercentage >= 80) {
            return $this->awardReward(
                $user,
                $config['points'],
                $config['xp'],
                'video_watch',
                "مشاهدة فيديو ({$watchPercentage}%)",
                'App\Models\Video',
                $videoId,
                array_merge($metadata, ['watch_percentage' => $watchPercentage])
            );
        }

        return ['success' => false, 'reason' => 'watch_percentage_too_low'];
    }

    /**
     * معالجة إتمام كويز
     */
    public function handleQuizCompletion(
        User $user,
        int $quizId,
        int $score,
        int $totalQuestions,
        array $metadata = []
    ): array {
        $config = config('gamification.points.quiz_completion', [
            'points' => 100,
            'xp' => 50,
        ]);

        $percentage = ($score / $totalQuestions) * 100;

        // مضاعف إضافي بناءً على النتيجة
        $scoreMultiplier = 1.0;
        if ($percentage >= 90) {
            $scoreMultiplier = 1.5; // +50% للدرجات الممتازة
        } elseif ($percentage >= 75) {
            $scoreMultiplier = 1.25; // +25% للدرجات الجيدة
        } elseif ($percentage < 50) {
            $scoreMultiplier = 0.5; // نصف النقاط للرسوب
        }

        $adjustedPoints = (int) ($config['points'] * $scoreMultiplier);
        $adjustedXP = (int) ($config['xp'] * $scoreMultiplier);

        $result = $this->awardReward(
            $user,
            $adjustedPoints,
            $adjustedXP,
            'quiz_completion',
            "إتمام اختبار (الدرجة: {$score}/{$totalQuestions})",
            'App\Models\Quiz',
            $quizId,
            array_merge($metadata, [
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => $percentage,
                'score_multiplier' => $scoreMultiplier,
            ])
        );

        // منح شارة خاصة للدرجة الكاملة
        if ($percentage == 100) {
            $this->handlePerfectScore($user, $quizId);
        }

        return $result;
    }

    /**
     * معالجة الحصول على درجة كاملة
     */
    protected function handlePerfectScore(User $user, int $quizId): void
    {
        $stats = $user->stats;
        $stats->increment('perfect_scores');

        // مكافأة إضافية للدرجة الكاملة
        $this->awardReward(
            $user,
            200,
            100,
            'perfect_score',
            'مكافأة الدرجة الكاملة!',
            'App\Models\Quiz',
            $quizId
        );

        Log::info("Perfect score bonus awarded", [
            'user_id' => $user->id,
            'quiz_id' => $quizId,
            'total_perfect_scores' => $stats->perfect_scores,
        ]);
    }

    /**
     * معالجة إتمام واجب
     */
    public function handleAssignmentSubmission(
        User $user,
        int $assignmentId,
        array $metadata = []
    ): array {
        $config = config('gamification.points.assignment_submission', [
            'points' => 150,
            'xp' => 75,
        ]);

        return $this->awardReward(
            $user,
            $config['points'],
            $config['xp'],
            'assignment_submission',
            'تسليم واجب',
            'App\Models\Assignment',
            $assignmentId,
            $metadata
        );
    }

    /**
     * معالجة إتمام كورس كامل
     */
    public function handleCourseCompletion(
        User $user,
        int $courseId,
        array $metadata = []
    ): array {
        $config = config('gamification.points.course_completion', [
            'points' => 1000,
            'xp' => 500,
        ]);

        $result = $this->awardReward(
            $user,
            $config['points'],
            $config['xp'],
            'course_completion',
            'إتمام كورس كامل!',
            'App\Models\Course',
            $courseId,
            $metadata
        );

        // تحديث إحصائيات الكورسات
        $stats = $user->stats;
        $stats->increment('courses_completed');

        return $result;
    }

    /**
     * معالجة تسجيل الدخول اليومي
     */
    public function handleDailyLogin(User $user): array
    {
        $streakData = $this->streakService->recordDailyActivity($user);

        // منح نقاط تسجيل الدخول اليومي
        if ($streakData['is_new_streak_day']) {
            $config = config('gamification.points.daily_login', [
                'points' => 10,
                'xp' => 5,
            ]);

            $this->awardReward(
                $user,
                $config['points'],
                $config['xp'],
                'daily_login',
                'تسجيل الدخول اليومي',
                null,
                null
            );
        }

        return $streakData;
    }

    /**
     * الحصول على ملخص كامل للمستخدم
     */
    public function getUserDashboard(User $user): array
    {
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'points' => [
                'total' => $stats->total_points,
                'available' => $stats->available_points,
                'spent' => $stats->spent_points,
            ],
            'level' => $this->levelService->getUserLevelInfo($user),
            'streak' => $this->streakService->getStreakInfo($user),
            'badges' => [
                'total' => $stats->total_badges,
                'recent' => $user->userBadges()->latest()->take(5)->get(),
            ],
            'achievements' => [
                'total' => $stats->total_achievements,
                'completed' => $user->userAchievements()
                    ->where('status', 'completed')
                    ->count(),
                'in_progress' => $user->userAchievements()
                    ->where('status', 'in_progress')
                    ->count(),
            ],
            'stats' => [
                'courses_completed' => $stats->courses_completed,
                'lessons_completed' => $stats->lessons_completed,
                'quizzes_completed' => $stats->quizzes_completed,
                'perfect_scores' => $stats->perfect_scores,
                'total_active_days' => $stats->total_active_days,
            ],
            'leaderboard' => [
                'global_rank' => $stats->global_rank,
                'monthly_rank' => $stats->monthly_rank,
                'weekly_rank' => $stats->weekly_rank,
            ],
        ];
    }

    /**
     * الحصول على تاريخ الأنشطة الأخيرة
     */
    public function getRecentActivity(User $user, int $limit = 20): array
    {
        $transactions = $this->pointsService->getPointsHistory($user, $limit);

        return [
            'transactions' => $transactions,
            'summary' => [
                'total_transactions' => $transactions->count(),
                'points_earned' => $transactions->where('points', '>', 0)->sum('points'),
                'points_spent' => abs($transactions->where('points', '<', 0)->sum('points')),
            ],
        ];
    }

    /**
     * التحقق من الإنجازات والشارات بعد كل نشاط
     */
    public function checkAndAwardAchievements(User $user): void
    {
        // سيتم تطبيقه في المرحلة القادمة (Achievement System)
        Log::info("Achievement check triggered", ['user_id' => $user->id]);
    }

    /**
     * إعادة حساب الإحصائيات للمستخدم
     */
    public function recalculateStats(User $user): bool
    {
        try {
            return DB::transaction(function () use ($user) {
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                // إعادة حساب النقاط
                $totalPoints = $user->pointsTransactions()
                    ->where('points', '>', 0)
                    ->sum('points');

                $spentPoints = abs($user->pointsTransactions()
                    ->where('points', '<', 0)
                    ->sum('points'));

                $availablePoints = $totalPoints - $spentPoints;

                // إعادة حساب الشارات
                $totalBadges = $user->userBadges()->count();

                // إعادة حساب الإنجازات
                $totalAchievements = $user->userAchievements()
                    ->where('status', 'completed')
                    ->count();

                // تحديث الإحصائيات
                $stats->update([
                    'total_points' => $totalPoints,
                    'available_points' => $availablePoints,
                    'spent_points' => $spentPoints,
                    'total_badges' => $totalBadges,
                    'total_achievements' => $totalAchievements,
                ]);

                Log::info("Stats recalculated", [
                    'user_id' => $user->id,
                    'total_points' => $totalPoints,
                    'total_badges' => $totalBadges,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to recalculate stats", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
