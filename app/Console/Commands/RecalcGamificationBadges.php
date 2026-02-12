<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserStat;
use App\Models\ModuleCompletion;
use App\Models\CourseModule;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\AchievementService;
use Illuminate\Console\Command;

class RecalcGamificationBadges extends Command
{
    protected $signature = 'gamification:recalc-badges
                            {--user= : معرّف مستخدم واحد فقط}
                            {--dry-run : عرض النتائج دون حفظ}';
    protected $description = 'إعادة احتساب عداد الدروس المكتملة والتحقق من الشارات لجميع الطلاب المحققين للشروط';

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

        $this->info("معالجة {$total} مستخدم...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $statsUpdated = 0;
        $badgesAwarded = 0;
        $achievementsCompleted = 0;

        foreach ($users as $user) {
            $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

            // عدد الدروس المكتملة من module_completions (وحدات من نوع lesson فقط)
            $lessonsCount = ModuleCompletion::query()
                ->where('student_id', $user->id)
                ->where('completion_status', 'completed')
                ->whereHas('module', fn ($q) => $q->where('module_type', 'lesson'))
                ->count();

            $prevLessons = (int) ($stats->lessons_completed ?? 0);
            if (!$dryRun && $lessonsCount != $prevLessons) {
                $stats->update(['lessons_completed' => $lessonsCount]);
                $statsUpdated++;
            }

            // التحقق من الشارات والإنجازات
            $awarded = $this->badgeService->checkAllBadges($user);
            $completed = $this->achievementService->checkAllAchievements($user);

            if (!$dryRun) {
                $badgesAwarded += count($awarded);
                $achievementsCompleted += count($completed);
            } elseif (count($awarded) > 0 || count($completed) > 0) {
                $this->newLine();
                $this->line("  المستخدم #{$user->id} ({$user->name}): دروس مكتملة = {$lessonsCount}, شارات جديدة = " . count($awarded) . ", إنجازات = " . count($completed));
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
                ['سجلات إحصائيات محدثة (lessons_completed)', $dryRun ? '—' : $statsUpdated],
                ['شارات مُمنحة في هذه الجولة', $dryRun ? '—' : $badgesAwarded],
                ['إنجازات مكتملة في هذه الجولة', $dryRun ? '—' : $achievementsCompleted],
            ]
        );

        if ($dryRun) {
            $this->warn('لم يتم الحفظ (dry-run). شغّل الأمر بدون --dry-run لتطبيق التغييرات.');
        }

        return Command::SUCCESS;
    }
}
