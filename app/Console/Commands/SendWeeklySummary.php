<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\Gamification\NotificationService;
use App\Services\Gamification\AnalyticsService;

class SendWeeklySummary extends Command
{
    protected $signature = 'gamification:weekly-summary';
    protected $description = 'إرسال الملخص الأسبوعي للطلاب';

    protected NotificationService $notificationService;
    protected AnalyticsService $analyticsService;

    public function __construct(
        NotificationService $notificationService,
        AnalyticsService $analyticsService
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->analyticsService = $analyticsService;
    }

    public function handle()
    {
        $this->info('📋 إرسال الملخص الأسبوعي...');

        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->with('stats')
            ->get();

        $sent = 0;
        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        foreach ($students as $student) {
            $stats = $student->stats;

            if (!$stats) {
                $bar->advance();
                continue;
            }

            // حساب إنجازات الأسبوع
            $weeklyPoints = $stats->weekly_points ?? 0;
            $weeklyXP = $stats->weekly_xp ?? 0;

            $message = "هذا الأسبوع: كسبت {$weeklyPoints} نقطة و {$weeklyXP} XP. ";
            $message .= "سلسلتك الحالية: {$stats->current_streak} يوم. ";
            $message .= "استمر في التقدم!";

            $this->notificationService->send(
                $student,
                'weekly_summary',
                'ملخصك الأسبوعي 📋',
                $message,
                '📊',
                '/student/gamification/dashboard'
            );

            $sent++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ تم إرسال الملخص لـ {$sent} طالب!");

        return Command::SUCCESS;
    }
}
