<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\Gamification\ChallengeService;

class AssignDailyChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:assign-daily {--user_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تعيين التحديات اليومية للطلاب';

    protected ChallengeService $challengeService;

    /**
     * Create a new command instance.
     */
    public function __construct(ChallengeService $challengeService)
    {
        parent::__construct();
        $this->challengeService = $challengeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 تعيين التحديات اليومية...');

        $userId = $this->option('user_id');

        if ($userId) {
            // تعيين لمستخدم محدد
            $user = User::find($userId);
            if (!$user) {
                $this->error("المستخدم غير موجود!");
                return Command::FAILURE;
            }

            $assigned = $this->challengeService->assignDailyChallenges($user);
            $this->info("✅ تم تعيين " . count($assigned) . " تحدي للمستخدم {$user->name}");
        } else {
            // تعيين لجميع الطلاب النشطين
            $students = User::where('role', 'student')
                ->where('is_active', true)
                ->get();

            $totalAssigned = 0;
            $bar = $this->output->createProgressBar($students->count());
            $bar->start();

            foreach ($students as $student) {
                $assigned = $this->challengeService->assignDailyChallenges($student);
                $totalAssigned += count($assigned);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✅ تم تعيين {$totalAssigned} تحدي لـ " . $students->count() . " طالب!");
        }

        return Command::SUCCESS;
    }
}
