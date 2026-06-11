<?php

namespace Database\Seeders;

use App\Models\Leaderboard;
use Illuminate\Database\Seeder;

class LeaderboardSeeder extends Seeder
{
    public function run(): void
    {
        $leaderboards = [
            [
                'name' => 'لوحة المتصدرين العامة',
                'slug' => 'global',
                'description' => 'ترتيب جميع الطلاب حسب إجمالي النقاط المكتسبة',
                'type' => 'global',
                'metric' => 'total_points',
                'icon' => '🌟',
                'period' => 'all_time',
                'max_entries' => 100,
                'rewards' => [
                    1 => ['points' => 5000],
                    2 => ['points' => 3000],
                    3 => ['points' => 2000],
                    'top_10' => ['points' => 500],
                ],
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'لوحة المتصدرين الأسبوعية',
                'slug' => 'weekly',
                'description' => 'ترتيب الطلاب حسب النقاط المكتسبة هذا الأسبوع',
                'type' => 'weekly',
                'metric' => 'total_points',
                'icon' => '📅',
                'period' => 'weekly',
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
                'max_entries' => 50,
                'rewards' => [
                    1 => ['points' => 1000],
                    2 => ['points' => 750],
                    3 => ['points' => 500],
                    'top_10' => ['points' => 250],
                ],
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'لوحة المتصدرين الشهرية',
                'slug' => 'monthly',
                'description' => 'ترتيب الطلاب حسب النقاط المكتسبة هذا الشهر',
                'type' => 'monthly',
                'metric' => 'total_points',
                'icon' => '📊',
                'period' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'max_entries' => 50,
                'rewards' => [
                    1 => ['points' => 5000],
                    2 => ['points' => 3500],
                    3 => ['points' => 2500],
                    'top_10' => ['points' => 1000],
                ],
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'لوحة منهي الكورسات',
                'slug' => 'courses',
                'description' => 'ترتيب الطلاب حسب عدد الكورسات المنجزة',
                'type' => 'global',
                'metric' => 'courses_completed',
                'icon' => '🎓',
                'period' => 'all_time',
                'max_entries' => 50,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'لوحة أبطال الاختبارات',
                'slug' => 'quizzes',
                'description' => 'ترتيب الطلاب حسب عدد الاختبارات المنجزة',
                'type' => 'accuracy',
                'metric' => 'quizzes_completed',
                'icon' => '🧠',
                'period' => 'all_time',
                'max_entries' => 50,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'لوحة أطول السلاسل',
                'slug' => 'streaks',
                'description' => 'ترتيب الطلاب حسب أطول سلسلة نشاط يومي',
                'type' => 'streak',
                'metric' => 'longest_streak',
                'icon' => '🔥',
                'period' => 'all_time',
                'max_entries' => 50,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'لوحة جامعي الشارات',
                'slug' => 'badges',
                'description' => 'ترتيب الطلاب حسب عدد الشارات المكتسبة',
                'type' => 'social',
                'metric' => 'total_badges',
                'icon' => '🏅',
                'period' => 'all_time',
                'max_entries' => 50,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'لوحة الأعلى مستوى',
                'slug' => 'level',
                'description' => 'ترتيب الطلاب حسب المستوى والخبرة',
                'type' => 'global',
                'metric' => 'current_level',
                'icon' => '⭐',
                'period' => 'all_time',
                'max_entries' => 50,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($leaderboards as $leaderboard) {
            Leaderboard::updateOrCreate(
                ['slug' => $leaderboard['slug']],
                $leaderboard
            );
        }

        $this->command?->info('تم إنشاء '.count($leaderboards).' لوحة متصدرين بنجاح!');
    }
}
