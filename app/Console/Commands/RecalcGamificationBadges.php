<?php

namespace App\Console\Commands;

use App\Models\AssignmentSubmission;
use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\DailyStreak;
use App\Models\ModuleCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\AchievementService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalcGamificationBadges extends Command
{
    protected $signature = 'gamification:recalc-badges
                            {--user= : معرّف مستخدم واحد فقط}
                            {--dry-run : عرض النتائج دون حفظ}
                            {--diagnose : عرض أرقام الدروس/الكورسات للمستخدم الأول (لتشخيص السبب)}';
    protected $description = 'إعادة احتساب إحصائيات gamification والتحقق من الشارات لجميع الطلاب المحققين للشروط';

    public function __construct(
        protected BadgeService $badgeService,
        protected AchievementService $achievementService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $diagnose = $this->option('diagnose');

        if ($dryRun) {
            $this->warn('وضع المعاينة (dry-run): لن يتم حفظ أي تغييرات.');
        }

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }
        $users = $query->get();
        $total = $users->count();

        if ($total === 0) {
            $this->warn('لم يتم العثور على مستخدمين.');
            return Command::SUCCESS;
        }

        $activeBadgesCount = Badge::where('is_active', true)->where('is_visible', true)->count();
        if ($activeBadgesCount === 0) {
            $this->warn('تحذير: لا توجد شارات نشطة ومعروضة (is_active=1, is_visible=1). تأكد من تشغيل BadgeSeeder أو إضافة شارات من لوحة التحكم.');
        } else {
            $this->info("عدد الشارات النشطة والمعروضة: {$activeBadgesCount}");
        }

        $this->info("معالجة {$total} مستخدم...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $statsUpdated = 0;
        $badgesAwarded = 0;
        $achievementsCompleted = 0;
        $verboseShown = false;

        foreach ($users as $user) {
            $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

            // عدد الوحدات المكتملة من module_completions (جميع الأنواع: lesson, video, quiz, resource, ...)
            // لضمان ظهور الشارات حتى لو كانت الوحدات مسجلة بأنواع أخرى في course_modules
            $lessonsCount = ModuleCompletion::query()
                ->where('student_id', $user->id)
                ->where('completion_status', 'completed')
                ->whereHas('module') // أي وحدة مرتبطة بـ course_modules
                ->count();

            // عدد الكورسات المكتملة من course_enrollments (نسبة إكمال 100%)
            $coursesCount = CourseEnrollment::query()
                ->where('student_id', $user->id)
                ->where('completion_percentage', '>=', 100)
                ->count();

            $quizzesCount = QuizAttempt::query()
                ->where('student_id', $user->id)
                ->where('is_completed', true)
                ->count();

            $perfectScoresCount = QuizAttempt::query()
                ->where('student_id', $user->id)
                ->where('is_completed', true)
                ->where('percentage_score', '>=', 100)
                ->count();

            $assignmentsCount = AssignmentSubmission::query()
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'graded', 'returned'])
                ->count();

            $streakCounts = $this->recalculateStreakCounts($user->id);

            if ($diagnose && !$verboseShown) {
                $rawCompletions = ModuleCompletion::query()
                    ->where('student_id', $user->id)
                    ->where('completion_status', 'completed')
                    ->count();
                $this->newLine();
                $this->line("  [تشخيص] المستخدم #{$user->id} ({$user->name}): وحدات={$lessonsCount}, خام={$rawCompletions}, كورسات={$coursesCount}, اختبارات={$quizzesCount}, سلسلة={$streakCounts['current_streak']}");
                $verboseShown = true;
            }

            $updates = [];
            $this->queueStatUpdate($updates, 'lessons_completed', (int) ($stats->lessons_completed ?? 0), $lessonsCount);
            $this->queueStatUpdate($updates, 'courses_completed', (int) ($stats->courses_completed ?? 0), $coursesCount);
            $this->queueStatUpdate($updates, 'quizzes_completed', (int) ($stats->quizzes_completed ?? 0), $quizzesCount);
            $this->queueStatUpdate($updates, 'perfect_scores', (int) ($stats->perfect_scores ?? 0), $perfectScoresCount);
            $this->queueStatUpdate($updates, 'assignments_submitted', (int) ($stats->assignments_submitted ?? 0), $assignmentsCount);
            $this->queueStatUpdate($updates, 'current_streak', (int) ($stats->current_streak ?? 0), $streakCounts['current_streak']);
            $this->queueStatUpdate($updates, 'longest_streak', (int) ($stats->longest_streak ?? 0), $streakCounts['longest_streak']);

            if (!$dryRun && count($updates) > 0) {
                $stats->update($updates);
                $statsUpdated++;
            }

            $user->unsetRelation('stats');

            $awarded = [];
            $completed = [];

            if (!$dryRun) {
                $awarded = $this->badgeService->checkAllBadgesWithCascade($user);
                $completed = $this->achievementService->checkAllAchievements($user);
                $badgesAwarded += count($awarded);
                $achievementsCompleted += count($completed);
            } elseif (count($updates) > 0) {
                $this->newLine();
                $this->line("  المستخدم #{$user->id} ({$user->name}): تحديثات معلقة = " . json_encode($updates, JSON_UNESCAPED_UNICODE));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ انتهى التنفيذ.');
        $this->table(
            ['البيان', 'العدد'],
            [
                ['مستخدمين تمت معالجتهم', $total],
                ['سجلات إحصائيات محدثة', $dryRun ? '—' : $statsUpdated],
                ['شارات مُمنحة في هذه الجولة', $dryRun ? '—' : $badgesAwarded],
                ['إنجازات مكتملة في هذه الجولة', $dryRun ? '—' : $achievementsCompleted],
            ]
        );

        if ($dryRun) {
            $this->warn('لم يتم الحفظ (dry-run). شغّل الأمر بدون --dry-run لتطبيق التغييرات.');
        }

        return Command::SUCCESS;
    }

    protected function queueStatUpdate(array &$updates, string $field, int $current, int $calculated): void
    {
        if ($current !== $calculated) {
            $updates[$field] = $calculated;
        }
    }

    /**
     * إعادة حساب السلسلة الحالية وأطول سلسلة من سجلات daily_streaks
     */
    protected function recalculateStreakCounts(int $userId): array
    {
        $dates = DailyStreak::query()
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->values();

        if ($dates->isEmpty()) {
            return ['current_streak' => 0, 'longest_streak' => 0];
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $currentStreak = 0;
        $firstDate = $dates->first();

        if ($firstDate->equalTo($today) || $firstDate->equalTo($yesterday)) {
            $expected = $firstDate->copy();
            foreach ($dates as $date) {
                if (!$date->equalTo($expected)) {
                    break;
                }
                $currentStreak++;
                $expected->subDay();
            }
        }

        $longestStreak = 0;
        $run = 0;
        $previous = null;

        foreach ($dates->sort()->values() as $date) {
            if ($previous && $date->equalTo($previous->copy()->addDay())) {
                $run++;
            } else {
                $run = 1;
            }

            $longestStreak = max($longestStreak, $run);
            $previous = $date;
        }

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
        ];
    }
}
