<?php

namespace App\Services\Gamification;

class PointEarningCatalog
{
    protected array $categoryLabels = [
        'learning' => 'التعلم والكورسات',
        'gamification' => 'التلعيب والإنجازات',
        'social' => 'النشاط الاجتماعي',
        'referral' => 'الإحالة',
        'streak' => 'السلسلة اليومية',
    ];

    protected array $sourceCategories = [
        'lesson_completion' => 'learning',
        'video_watch' => 'learning',
        'quiz_completion' => 'learning',
        'perfect_score' => 'learning',
        'assignment_submission' => 'learning',
        'course_completion' => 'learning',
        'course_share' => 'social',
        'daily_login' => 'streak',
        'comment_post' => 'social',
        'comment_like' => 'social',
        'referral' => 'referral',
        'streak_milestones' => 'streak',
    ];

    protected array $sourceIcons = [
        'lesson_completion' => 'ri-book-open-line',
        'video_watch' => 'ri-play-circle-line',
        'quiz_completion' => 'ri-question-answer-line',
        'perfect_score' => 'ri-star-line',
        'assignment_submission' => 'ri-file-upload-line',
        'course_completion' => 'ri-graduation-cap-line',
        'daily_login' => 'ri-login-circle-line',
        'comment_post' => 'ri-chat-3-line',
        'comment_like' => 'ri-thumb-up-line',
        'course_share' => 'ri-share-line',
        'referral' => 'ri-user-add-line',
        'streak_milestones' => 'ri-fire-line',
    ];

    public function getEarningMethods(): array
    {
        $pointsConfig = config('gamification.points', []);
        $methods = [];

        foreach ($pointsConfig as $key => $config) {
            if ($key === 'streak_milestones' || ! is_array($config)) {
                continue;
            }

            if (! isset($config['points'])) {
                continue;
            }

            $methods[] = [
                'key' => $key,
                'title' => $config['description'] ?? $key,
                'description' => $this->getMethodDescription($key, $config),
                'points' => (int) $config['points'],
                'xp' => (int) ($config['xp'] ?? 0),
                'icon' => $this->sourceIcons[$key] ?? 'ri-star-line',
                'category' => $this->sourceCategories[$key] ?? 'learning',
                'daily_limit' => $config['daily_limit'] ?? null,
                'extra' => $this->getMethodExtra($key, $config),
            ];
        }

        return $methods;
    }

    public function getEarningMethodsByCategory(): array
    {
        $grouped = [];

        foreach ($this->getEarningMethods() as $method) {
            $category = $method['category'];
            $grouped[$category]['label'] = $this->categoryLabels[$category] ?? $category;
            $grouped[$category]['methods'][] = $method;
        }

        return $grouped;
    }

    public function getStreakMultipliers(): array
    {
        return config('gamification.streak_multipliers', []);
    }

    public function getStreakMilestones(): array
    {
        $milestones = config('gamification.points.streak_milestones', []);
        $result = [];

        foreach ($milestones as $days => $config) {
            if (! is_array($config)) {
                continue;
            }

            $result[] = [
                'days' => (int) $days,
                'points' => (int) ($config['points'] ?? 0),
                'description' => $config['description'] ?? "{$days} يوم",
            ];
        }

        return $result;
    }

    public function getSourceLabel(string $source): string
    {
        $labels = [
            'lesson_completion' => 'إتمام درس',
            'video_watch' => 'مشاهدة فيديو',
            'quiz_completion' => 'إتمام اختبار',
            'perfect_score' => 'درجة كاملة',
            'assignment_submission' => 'تسليم واجب',
            'course_completion' => 'إتمام كورس',
            'daily_login' => 'تسجيل دخول يومي',
            'comment_post' => 'تعليق',
            'comment_like' => 'إعجاب',
            'course_share' => 'مشاركة كورس',
            'referral' => 'إحالة صديق',
            'streak_milestones' => 'سلسلة يومية',
            'badge_earned' => 'شارة جديدة',
            'achievement_completed' => 'إنجاز',
            'bonus' => 'مكافأة إدارية',
            'admin_adjustment' => 'تعديل إداري',
            'shop_purchase' => 'شراء من المتجر',
            'penalty' => 'خصم',
        ];

        if (isset($labels[$source])) {
            return $labels[$source];
        }

        $config = config("gamification.points.{$source}.description");

        return is_string($config) ? $config : str_replace('_', ' ', $source);
    }

    public function getSourceIcon(string $source): string
    {
        $extra = [
            'badge_earned' => 'ri-medal-line',
            'achievement_completed' => 'ri-trophy-line',
            'bonus' => 'ri-gift-line',
            'admin_adjustment' => 'ri-settings-3-line',
            'shop_purchase' => 'ri-store-2-line',
            'penalty' => 'ri-subtract-line',
        ];

        return $extra[$source] ?? $this->sourceIcons[$source] ?? 'ri-star-line';
    }

    public function getDistinctSourcesForFilter(): array
    {
        return collect($this->sourceIcons)
            ->keys()
            ->merge(['badge_earned', 'achievement_completed', 'bonus', 'admin_adjustment', 'shop_purchase'])
            ->unique()
            ->mapWithKeys(fn (string $source) => [$source => $this->getSourceLabel($source)])
            ->sort()
            ->all();
    }

    protected function getMethodDescription(string $key, array $config): string
    {
        return match ($key) {
            'lesson_completion' => 'أكمل أي درس في كورساتك المسجّلة',
            'video_watch' => 'شاهد '.($config['min_watch_percentage'] ?? 80).'% أو أكثر من الفيديو',
            'quiz_completion' => 'أكمل الاختبارات — الدرجة العالية تعطيك مضاعفاً إضافياً',
            'perfect_score' => 'مكافأة إضافية عند الحصول على 100% في اختبار',
            'assignment_submission' => 'سلّم واجباتك في الوقت المحدد',
            'course_completion' => 'أكمل كورساً بالكامل بنسبة 100%',
            'daily_login' => 'سجّل دخولك يومياً للحفاظ على سلسلتك',
            'comment_post' => 'علّق على أنشطة الأصدقاء في التلعيب',
            'comment_like' => 'احصل على إعجابات على نشاطاتك',
            'course_share' => 'شارك كورساً مع أصدقائك',
            'referral' => 'ادعُ صديقاً للتسجيل في الأكاديمية',
            default => $config['description'] ?? '',
        };
    }

    protected function getMethodExtra(string $key, array $config): ?string
    {
        if ($key === 'quiz_completion' && isset($config['score_multipliers'])) {
            return 'مضاعفات: ممتاز 90%+ (×1.5)، جيد 75%+ (×1.25)، رسوب أقل من 50% (×0.5)';
        }

        if ($key === 'comment_post' && isset($config['daily_limit'])) {
            return "حد أقصى {$config['daily_limit']} تعليقات يومياً";
        }

        return null;
    }
}
