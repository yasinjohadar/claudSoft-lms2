<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ModuleCompletion;
use App\Models\CourseEnrollment;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\AchievementService;
use Illuminate\Console\Command;

class RecalcGamificationBadges extends Command
{
    protected $signature = 'gamification:recalc-badges
                            {--user= : معرّف مستخدم واحد فقط}
                            {--dry-run : عرض النتائج دون حفظ}
                            {--diagnose : عرض أرقام الدروس/الكورسات للمستخدم الأول (لتشخيص السبب)}';
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

            if ($diagnose && !$verboseShown) {
                $rawCompletions = ModuleCompletion::query()
                    ->where('student_id', $user->id)
                    ->where('completion_status', 'completed')
                    ->count();
                $this->newLine();
                $this->line("  [تشخيص] المستخدم #{$user->id} ({$user->name}): وحدات مكتملة (محسوبة) = {$lessonsCount}, إكمالات خام = {$rawCompletions}, كورسات مكتملة = {$coursesCount}");
                $verboseShown = true;
            }

            $updates = [];
            if ((int) ($stats->lessons_completed ?? 0) != $lessonsCount) {
                $updates['lessons_completed'] = $lessonsCount;
            }
            if ((int) ($stats->courses_completed ?? 0) != $coursesCount) {
                $updates['courses_completed'] = $coursesCount;
            }
            if (!$dryRun && count($updates) > 0) {
                $stats->update($updates);
                $statsUpdated++;
            }

            // إعادة تحميل علاقة الإحصائيات حتى checkAllBadges يرى القيم المحدثة
            $user->unsetRelation('stats');

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
                ['سجلات إحصائيات محدثة (دروس/كورسات)', $dryRun ? '—' : $statsUpdated],
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
