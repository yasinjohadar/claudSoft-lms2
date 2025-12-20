<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\ChallengeService;
use App\Services\Gamification\InventoryService;
use App\Services\Gamification\BoosterService;
use App\Services\Gamification\NotificationService;
use App\Models\User;

class GamificationDailyTasks extends Command
{
    protected $signature = 'gamification:daily-tasks';
    protected $description = 'تنفيذ المهام اليومية لنظام الـ Gamification';

    protected ChallengeService $challengeService;
    protected InventoryService $inventoryService;
    protected BoosterService $boosterService;
    protected NotificationService $notificationService;

    public function __construct(
        ChallengeService $challengeService,
        InventoryService $inventoryService,
        BoosterService $boosterService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->challengeService = $challengeService;
        $this->inventoryService = $inventoryService;
        $this->boosterService = $boosterService;
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info('🚀 بدء المهام اليومية...');

        // 1. فحص التحديات المنتهية
        $this->info('📋 فحص التحديات المنتهية...');
        $expiredChallenges = $this->challengeService->checkExpiredChallenges();
        $this->info("   ✓ تم تحديث {$expiredChallenges} تحدي منتهي");

        // 2. تعيين التحديات اليومية
        $this->info('🎯 تعيين التحديات اليومية...');
        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->get();

        $totalAssigned = 0;
        foreach ($students as $student) {
            $assigned = $this->challengeService->assignDailyChallenges($student);
            $totalAssigned += count($assigned);
        }
        $this->info("   ✓ تم تعيين {$totalAssigned} تحدي لـ " . $students->count() . " طالب");

        // 3. فحص العناصر المنتهية
        $this->info('📦 فحص العناصر المنتهية...');
        $expiredItems = $this->inventoryService->checkExpiredItems();
        $this->info("   ✓ تم تحديث {$expiredItems} عنصر منتهي");

        // 4. فحص المعززات المنتهية
        $this->info('⚡ فحص المعززات المنتهية...');
        $expiredBoosters = $this->boosterService->checkExpiredBoosters();
        $this->info("   ✓ تم تحديث {$expiredBoosters} معزز منتهي");

        // 5. حذف الإشعارات القديمة
        $this->info('🔔 حذف الإشعارات القديمة...');
        $deletedNotifications = $this->notificationService->deleteOldNotifications(30);
        $this->info("   ✓ تم حذف {$deletedNotifications} إشعار قديم");

        $this->info('✅ تمت المهام اليومية بنجاح!');

        return Command::SUCCESS;
    }
}
