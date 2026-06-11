<?php

namespace App\Services\Gamification;

use App\Models\Achievement;
use App\Models\Gamification\Achievement as GamificationAchievement;
use App\Models\User;
use App\Support\Gamification\AchievementCriteriaMapper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AchievementRecalculationService
{
    public function __construct(
        protected UserGamificationStatSyncService $statSyncService,
        protected AchievementService $achievementService
    ) {}

    /**
     * @return array{stats_synced: int, achievements_completed: int, initialized: int}
     */
    public function recalculateForUser(User $user): array
    {
        $sync = $this->statSyncService->syncUserStats($user);
        $initialized = $this->achievementService->initializeAllAchievements($user);
        $completed = $this->achievementService->checkAllAchievements($user);

        return [
            'stats_synced' => $sync['updated'] ? 1 : 0,
            'initialized' => $initialized,
            'achievements_completed' => count($completed),
        ];
    }

    /**
     * @return array{students: int, stats_synced: int, achievements_completed: int, initialized: int}
     */
    public function recalculateForAllActiveStudents(): array
    {
        $students = 0;
        $statsSynced = 0;
        $achievementsCompleted = 0;
        $initialized = 0;

        Role::findOrCreate('student', 'web');

        User::role('student')
            ->where('is_active', true)
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$students, &$statsSynced, &$achievementsCompleted, &$initialized) {
                foreach ($users as $user) {
                    $fullUser = User::find($user->id);
                    if (!$fullUser) {
                        continue;
                    }

                    $result = $this->recalculateForUser($fullUser);
                    $students++;
                    $statsSynced += $result['stats_synced'];
                    $initialized += $result['initialized'];
                    $achievementsCompleted += $result['achievements_completed'];
                }
            });

        return [
            'students' => $students,
            'stats_synced' => $statsSynced,
            'initialized' => $initialized,
            'achievements_completed' => $achievementsCompleted,
        ];
    }

    /**
     * ترحيل سجلات gamification_achievements إلى achievements إن وُجدت.
     */
    public function migrateGamificationAchievements(): int
    {
        $migrated = 0;

        GamificationAchievement::query()->each(function (GamificationAchievement $row) use (&$migrated) {
            $mapped = AchievementCriteriaMapper::formToAchievementData(
                $row->requirement_type,
                $row->requirement_value
            );

            if (!$mapped) {
                return;
            }

            $slug = Str::slug($row->name).'-gm-'.$row->id;

            $exists = Achievement::withTrashed()
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                return;
            }

            Achievement::create([
                'name' => $row->name,
                'slug' => $slug,
                'description' => $row->description,
                'icon' => $row->icon ?? '🏆',
                'tier' => $row->tier ?? 'bronze',
                'type' => 'general',
                'target_value' => $mapped['target_value'],
                'criteria' => $mapped['criteria'],
                'points_reward' => $row->points_reward ?? 0,
                'is_active' => (bool) $row->is_active,
                'sort_order' => 1000 + $row->id,
            ]);

            $migrated++;
        });

        if ($migrated > 0) {
            Log::info('Migrated gamification achievements to legacy table', ['count' => $migrated]);
        }

        return $migrated;
    }
}
