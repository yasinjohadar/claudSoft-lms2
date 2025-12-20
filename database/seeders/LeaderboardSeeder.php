<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leaderboard;

class LeaderboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaderboards = [
            // ========================================
            // Global Leaderboard (دائم)
            // ========================================
            [
                'name' => 'لوحة المتصدرين العامة',
                'slug' => 'global',
                'description' => 'ترتيب جميع الطلاب حسب إجمالي النقاط المكتسبة',
                'type' => 'global',
                'icon' => '🌟',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 100,
                'rewards' => [
                    1 => ['points' => 5000, 'gems' => 500],
                    2 => ['points' => 3000, 'gems' => 300],
                    3 => ['points' => 2000, 'gems' => 200],
                    'top_10' => ['points' => 500, 'gems' => 50],
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
            ],

            // ========================================
            // Weekly Leaderboard
            // ========================================
            [
                'name' => 'لوحة المتصدرين الأسبوعية',
                'slug' => 'weekly',
                'description' => 'ترتيب الطلاب حسب النقاط المكتسبة هذا الأسبوع',
                'type' => 'weekly',
                'icon' => '📅',
                'period_type' => 'weekly',
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
                'max_entries' => 50,
                'rewards' => [
                    1 => ['points' => 1000, 'gems' => 100],
                    2 => ['points' => 750, 'gems' => 75],
                    3 => ['points' => 500, 'gems' => 50],
                    'top_10' => ['points' => 250, 'gems' => 25],
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 2,
            ],

            // ========================================
            // Monthly Leaderboard
            // ========================================
            [
                'name' => 'لوحة المتصدرين الشهرية',
                'slug' => 'monthly',
                'description' => 'ترتيب الطلاب حسب النقاط المكتسبة هذا الشهر',
                'type' => 'monthly',
                'icon' => '📊',
                'period_type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'max_entries' => 50,
                'rewards' => [
                    1 => ['points' => 5000, 'gems' => 500],
                    2 => ['points' => 3500, 'gems' => 350],
                    3 => ['points' => 2500, 'gems' => 250],
                    'top_10' => ['points' => 1000, 'gems' => 100],
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 3,
            ],

            // ========================================
            // Courses Leaderboard
            // ========================================
            [
                'name' => 'لوحة منهي الكورسات',
                'slug' => 'courses',
                'description' => 'ترتيب الطلاب حسب عدد الكورسات المنجزة',
                'type' => 'courses',
                'icon' => '🎓',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 50,
                'rewards' => null,
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 4,
            ],

            // ========================================
            // Quizzes Leaderboard
            // ========================================
            [
                'name' => 'لوحة أبطال الاختبارات',
                'slug' => 'quizzes',
                'description' => 'ترتيب الطلاب حسب عدد الاختبارات المنجزة بنجاح',
                'type' => 'quizzes',
                'icon' => '🧠',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 50,
                'rewards' => null,
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 5,
            ],

            // ========================================
            // Streaks Leaderboard
            // ========================================
            [
                'name' => 'لوحة أطول السلاسل',
                'slug' => 'streaks',
                'description' => 'ترتيب الطلاب حسب أطول سلسلة نشاط يومي',
                'type' => 'streaks',
                'icon' => '🔥',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 50,
                'rewards' => null,
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 6,
            ],

            // ========================================
            // Badges Leaderboard
            // ========================================
            [
                'name' => 'لوحة جامعي الشارات',
                'slug' => 'badges',
                'description' => 'ترتيب الطلاب حسب عدد الشارات المكتسبة',
                'type' => 'badges',
                'icon' => '🏅',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 50,
                'rewards' => null,
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 7,
            ],

            // ========================================
            // Level Leaderboard
            // ========================================
            [
                'name' => 'لوحة الأعلى مستوى',
                'slug' => 'level',
                'description' => 'ترتيب الطلاب حسب المستوى والخبرة',
                'type' => 'level',
                'icon' => '⭐',
                'period_type' => 'all_time',
                'start_date' => null,
                'end_date' => null,
                'max_entries' => 50,
                'rewards' => null,
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($leaderboards as $leaderboard) {
            Leaderboard::updateOrCreate(
                ['slug' => $leaderboard['slug']],
                $leaderboard
            );
        }

        $this->command->info('✅ تم إنشاء ' . count($leaderboards) . ' لوحة متصدرين بنجاح!');
    }
}
