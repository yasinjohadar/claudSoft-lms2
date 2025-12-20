<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\ChallengeService;

class CheckExpiredChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص وتحديث التحديات المنتهية';

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
        $this->info('🔍 فحص التحديات المنتهية...');

        $expiredCount = $this->challengeService->checkExpiredChallenges();

        if ($expiredCount > 0) {
            $this->info("✅ تم تحديث {$expiredCount} تحدي منتهي!");
        } else {
            $this->info('✓ لا توجد تحديات منتهية.');
        }

        return Command::SUCCESS;
    }
}
