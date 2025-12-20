<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;
use App\Models\Badge;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض الشارات للربط
        $firstLessonBadge = Badge::where('slug', 'first-lesson')->first();
        $activeLearnerBadge = Badge::where('slug', 'active-learner')->first();
        $firstCourseBadge = Badge::where('slug', 'first-course')->first();
        $perfectScoreBadge = Badge::where('slug', 'perfect-score')->first();
        $weekStreakBadge = Badge::where('slug', 'week-streak')->first();

        $achievements = [
            // ========================================
            // Bronze Tier Achievements (برونز)
            // ========================================
            [
                'name' => 'المتعلم المبتدئ',
                'slug' => 'beginner-learner',
                'description' => 'أكمل 5 دروس',
                'tier' => 'bronze',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 5,
                'criteria' => ['field' => 'lessons_completed'],
                'points_reward' => 50,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'المستكشف',
                'slug' => 'explorer',
                'description' => 'أكمل 3 اختبارات',
                'tier' => 'bronze',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 3,
                'criteria' => ['field' => 'quizzes_completed'],
                'points_reward' => 50,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'أول خطوة',
                'slug' => 'first-step',
                'description' => 'سجل دخول لمدة 3 أيام متتالية',
                'tier' => 'bronze',
                'badge_id' => null,
                'type' => 'social',
                'target_value' => 3,
                'criteria' => ['field' => 'longest_streak'],
                'points_reward' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],

            // ========================================
            // Silver Tier Achievements (فضي)
            // ========================================
            [
                'name' => 'المتعلم المتحمس',
                'slug' => 'enthusiastic-learner',
                'description' => 'أكمل 25 درساً',
                'tier' => 'silver',
                'badge_id' => $activeLearnerBadge?->id,
                'type' => 'general',
                'target_value' => 25,
                'criteria' => ['field' => 'lessons_completed'],
                'points_reward' => 150,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'ماهر الاختبارات',
                'slug' => 'quiz-master',
                'description' => 'أكمل 15 اختباراً',
                'tier' => 'silver',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 15,
                'criteria' => ['field' => 'quizzes_completed'],
                'points_reward' => 150,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'الطالب الملتزم',
                'slug' => 'committed-student',
                'description' => 'سجل دخول لمدة أسبوع متتالي',
                'tier' => 'silver',
                'badge_id' => $weekStreakBadge?->id,
                'type' => 'social',
                'target_value' => 7,
                'criteria' => ['field' => 'longest_streak'],
                'points_reward' => 120,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'صاحب الدرجات العالية',
                'slug' => 'high-scorer',
                'description' => 'احصل على 3 درجات كاملة',
                'tier' => 'silver',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 3,
                'criteria' => ['field' => 'perfect_scores'],
                'points_reward' => 200,
                'is_active' => true,
                'sort_order' => 13,
            ],

            // ========================================
            // Gold Tier Achievements (ذهبي)
            // ========================================
            [
                'name' => 'خبير التعلم',
                'slug' => 'learning-expert',
                'description' => 'أكمل 75 درساً',
                'tier' => 'gold',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 75,
                'criteria' => ['field' => 'lessons_completed'],
                'points_reward' => 400,
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'منهي الكورسات',
                'slug' => 'course-finisher',
                'description' => 'أكمل 3 كورسات كاملة',
                'tier' => 'gold',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 3,
                'criteria' => ['field' => 'courses_completed'],
                'points_reward' => 500,
                'is_active' => true,
                'sort_order' => 21,
            ],
            [
                'name' => 'المثابر',
                'slug' => 'persistent',
                'description' => 'سجل دخول لمدة 30 يوم متتالي',
                'tier' => 'gold',
                'badge_id' => null,
                'type' => 'social',
                'target_value' => 30,
                'criteria' => ['field' => 'longest_streak'],
                'points_reward' => 500,
                'is_active' => true,
                'sort_order' => 22,
            ],
            [
                'name' => 'عاشق الكمال',
                'slug' => 'perfectionist',
                'description' => 'احصل على 10 درجات كاملة',
                'tier' => 'gold',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 10,
                'criteria' => ['field' => 'perfect_scores'],
                'points_reward' => 600,
                'is_active' => true,
                'sort_order' => 23,
            ],

            // ========================================
            // Platinum Tier Achievements (بلاتيني)
            // ========================================
            [
                'name' => 'المعلم المتقدم',
                'slug' => 'advanced-learner',
                'description' => 'أكمل 200 درس',
                'tier' => 'platinum',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 200,
                'criteria' => ['field' => 'lessons_completed'],
                'points_reward' => 1000,
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'محترف الكورسات',
                'slug' => 'course-professional',
                'description' => 'أكمل 7 كورسات',
                'tier' => 'platinum',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 7,
                'criteria' => ['field' => 'courses_completed'],
                'points_reward' => 1200,
                'is_active' => true,
                'sort_order' => 31,
            ],
            [
                'name' => 'الطالب الدائم',
                'slug' => 'eternal-student',
                'description' => 'سجل دخول لمدة 90 يوم متتالي',
                'tier' => 'platinum',
                'badge_id' => null,
                'type' => 'social',
                'target_value' => 90,
                'criteria' => ['field' => 'longest_streak'],
                'points_reward' => 1500,
                'is_active' => true,
                'sort_order' => 32,
            ],
            [
                'name' => 'سيد الدرجات الكاملة',
                'slug' => 'perfect-score-master',
                'description' => 'احصل على 25 درجة كاملة',
                'tier' => 'platinum',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 25,
                'criteria' => ['field' => 'perfect_scores'],
                'points_reward' => 1500,
                'is_active' => true,
                'sort_order' => 33,
            ],

            // ========================================
            // Diamond Tier Achievements (ماسي)
            // ========================================
            [
                'name' => 'أسطورة الدروس',
                'slug' => 'lesson-legend',
                'description' => 'أكمل 500 درس',
                'tier' => 'diamond',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 500,
                'criteria' => ['field' => 'lessons_completed'],
                'points_reward' => 3000,
                'is_active' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'أسطورة الكورسات',
                'slug' => 'course-legend',
                'description' => 'أكمل 15 كورس',
                'tier' => 'diamond',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 15,
                'criteria' => ['field' => 'courses_completed'],
                'points_reward' => 3500,
                'is_active' => true,
                'sort_order' => 41,
            ],
            [
                'name' => 'السنة الكاملة',
                'slug' => 'full-year',
                'description' => 'سجل دخول لمدة 365 يوم متتالي',
                'tier' => 'diamond',
                'badge_id' => null,
                'type' => 'social',
                'target_value' => 365,
                'criteria' => ['field' => 'longest_streak'],
                'points_reward' => 5000,
                'is_active' => true,
                'sort_order' => 42,
            ],
            [
                'name' => 'إله الدرجات الكاملة',
                'slug' => 'perfect-score-god',
                'description' => 'احصل على 50 درجة كاملة',
                'tier' => 'diamond',
                'badge_id' => null,
                'type' => 'general',
                'target_value' => 50,
                'criteria' => ['field' => 'perfect_scores'],
                'points_reward' => 5000,
                'is_active' => true,
                'sort_order' => 43,
            ],
            [
                'name' => 'جامع الشارات الأسطوري',
                'slug' => 'legendary-badge-collector',
                'description' => 'اجمع 40 شارة',
                'tier' => 'diamond',
                'badge_id' => null,
                'type' => 'special',
                'target_value' => 40,
                'criteria' => ['field' => 'total_badges'],
                'points_reward' => 4000,
                'is_active' => true,
                'sort_order' => 44,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }

        $this->command->info('✅ تم إنشاء ' . count($achievements) . ' إنجاز بنجاح!');
    }
}
