<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Gamification\BadgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAllBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:check-all 
                            {--user-id= : Check badges for specific user ID}
                            {--limit= : Limit number of users to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من جميع الشارات للمستخدمين الموجودين ومنحها تلقائياً';

    protected BadgeService $badgeService;

    /**
     * Create a new command instance.
     */
    public function __construct(BadgeService $badgeService)
    {
        parent::__construct();
        $this->badgeService = $badgeService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 بدء التحقق من الشارات...');

        $userId = $this->option('user-id');
        $limit = $this->option('limit');

        // الحصول على المستخدمين
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("❌ لم يتم العثور على مستخدم بالرقم: {$userId}");
                return Command::FAILURE;
            }
        } else {
            $query = User::query();
            if ($limit) {
                $query->limit((int)$limit);
            }
            $users = $query->get();
        }

        $totalUsers = $users->count();
        $this->info("📊 عدد المستخدمين: {$totalUsers}");

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $totalAwarded = 0;
        $usersWithBadges = 0;

        foreach ($users as $user) {
            try {
                // التحقق من جميع الشارات للمستخدم
                $awarded = $this->badgeService->checkAllBadges($user);

                if (count($awarded) > 0) {
                    $totalAwarded += count($awarded);
                    $usersWithBadges++;
                    
                    $this->newLine();
                    $this->line("✅ المستخدم #{$user->id} ({$user->name}): تم منح " . count($awarded) . " شارة");
                }

                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ خطأ في معالجة المستخدم #{$user->id}: " . $e->getMessage());
                Log::error("Failed to check badges for user", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // عرض النتائج
        $this->info("✅ اكتمل التحقق من الشارات!");
        $this->table(
            ['المقياس', 'القيمة'],
            [
                ['إجمالي المستخدمين', $totalUsers],
                ['المستخدمين الذين حصلوا على شارات', $usersWithBadges],
                ['إجمالي الشارات الممنوحة', $totalAwarded],
            ]
        );

        return Command::SUCCESS;
    }
}
