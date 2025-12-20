<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gamification\Level;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level' => 1, 'name' => 'مبتدئ', 'xp_required' => 0, 'points_reward' => 0, 'gems_reward' => 0],
            ['level' => 2, 'name' => 'متعلم', 'xp_required' => 100, 'points_reward' => 10, 'gems_reward' => 1],
            ['level' => 3, 'name' => 'طالب', 'xp_required' => 250, 'points_reward' => 15, 'gems_reward' => 1],
            ['level' => 4, 'name' => 'دارس', 'xp_required' => 500, 'points_reward' => 20, 'gems_reward' => 2],
            ['level' => 5, 'name' => 'مجتهد', 'xp_required' => 800, 'points_reward' => 25, 'gems_reward' => 2],
            ['level' => 6, 'name' => 'متفوق', 'xp_required' => 1200, 'points_reward' => 30, 'gems_reward' => 3],
            ['level' => 7, 'name' => 'نابغ', 'xp_required' => 1700, 'points_reward' => 35, 'gems_reward' => 3],
            ['level' => 8, 'name' => 'عالم', 'xp_required' => 2300, 'points_reward' => 40, 'gems_reward' => 4],
            ['level' => 9, 'name' => 'خبير', 'xp_required' => 3000, 'points_reward' => 50, 'gems_reward' => 5],
            ['level' => 10, 'name' => 'أستاذ', 'xp_required' => 3800, 'points_reward' => 60, 'gems_reward' => 6],
            ['level' => 11, 'name' => 'محترف', 'xp_required' => 4700, 'points_reward' => 70, 'gems_reward' => 7],
            ['level' => 12, 'name' => 'متميز', 'xp_required' => 5700, 'points_reward' => 80, 'gems_reward' => 8],
            ['level' => 13, 'name' => 'رائد', 'xp_required' => 6800, 'points_reward' => 90, 'gems_reward' => 9],
            ['level' => 14, 'name' => 'قائد', 'xp_required' => 8000, 'points_reward' => 100, 'gems_reward' => 10],
            ['level' => 15, 'name' => 'بطل', 'xp_required' => 9500, 'points_reward' => 120, 'gems_reward' => 12],
            ['level' => 16, 'name' => 'أسطورة', 'xp_required' => 11000, 'points_reward' => 140, 'gems_reward' => 14],
            ['level' => 17, 'name' => 'عبقري', 'xp_required' => 13000, 'points_reward' => 160, 'gems_reward' => 16],
            ['level' => 18, 'name' => 'نجم', 'xp_required' => 15500, 'points_reward' => 180, 'gems_reward' => 18],
            ['level' => 19, 'name' => 'فائق', 'xp_required' => 18000, 'points_reward' => 200, 'gems_reward' => 20],
            ['level' => 20, 'name' => 'ماسي', 'xp_required' => 21000, 'points_reward' => 250, 'gems_reward' => 25],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['level' => $level['level']],
                $level
            );
        }
    }
}
