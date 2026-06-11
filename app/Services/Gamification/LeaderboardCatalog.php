<?php

namespace App\Services\Gamification;

use App\Models\Leaderboard;

class LeaderboardCatalog
{
    public const METRICS = [
        'total_points' => 'إجمالي النقاط',
        'total_xp' => 'إجمالي XP',
        'courses_completed' => 'كورسات مكتملة',
        'quizzes_completed' => 'اختبارات مكتملة',
        'longest_streak' => 'أطول سلسلة',
        'current_streak' => 'السلسلة الحالية',
        'total_badges' => 'الشارات',
        'current_level' => 'المستوى',
    ];

    public const TYPES = [
        'global' => 'عام',
        'course' => 'كورس',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'speed' => 'سرعة',
        'accuracy' => 'دقة',
        'streak' => 'سلسلة',
        'social' => 'اجتماعي',
    ];

    public const PERIODS = [
        'all_time' => 'كل الأوقات',
        'daily' => 'يومي',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'yearly' => 'سنوي',
        'season' => 'موسم',
    ];

    public const DIVISIONS = [
        'bronze' => ['label' => 'برونزي', 'color' => 'warning', 'icon' => 'ri-shield-star-line'],
        'silver' => ['label' => 'فضي', 'color' => 'secondary', 'icon' => 'ri-medal-line'],
        'gold' => ['label' => 'ذهبي', 'color' => 'warning', 'icon' => 'ri-vip-crown-line'],
        'platinum' => ['label' => 'بلاتيني', 'color' => 'info', 'icon' => 'ri-vip-crown-fill'],
        'diamond' => ['label' => 'ماسي', 'color' => 'primary', 'icon' => 'ri-vip-diamond-fill'],
    ];

    public function getMetricOptions(): array
    {
        return self::METRICS;
    }

    public function getTypeOptions(): array
    {
        return self::TYPES;
    }

    public function getPeriodOptions(): array
    {
        return self::PERIODS;
    }

    public function getTypeLabel(string $type): string
    {
        return self::TYPES[$type] ?? $type;
    }

    public function getPeriodLabel(string $period): string
    {
        return self::PERIODS[$period] ?? $period;
    }

    public function getMetricLabel(string $metric): string
    {
        return self::METRICS[$metric] ?? $metric;
    }

    public function getDivisionLabel(string $division): string
    {
        return self::DIVISIONS[$division]['label'] ?? $division;
    }

    public function getDivisionColor(string $division): string
    {
        return self::DIVISIONS[$division]['color'] ?? 'secondary';
    }

    public function getDivisionIcon(string $division): string
    {
        return self::DIVISIONS[$division]['icon'] ?? 'ri-award-line';
    }

    public function defaultMetricForType(string $type): string
    {
        return match ($type) {
            'streak' => 'longest_streak',
            'course' => 'courses_completed',
            'speed', 'accuracy' => 'quizzes_completed',
            'social' => 'total_badges',
            'weekly', 'monthly' => 'total_points',
            default => 'total_points',
        };
    }

    public function defaultPeriodForType(string $type): string
    {
        return match ($type) {
            'weekly' => 'weekly',
            'monthly' => 'monthly',
            default => 'all_time',
        };
    }

    public function resolveMetric(Leaderboard $leaderboard): string
    {
        $metric = $leaderboard->metric;

        if ($metric && isset(self::METRICS[$metric])) {
            return $metric;
        }

        return $this->defaultMetricForType($leaderboard->type);
    }

    public function usesTransactionPeriod(Leaderboard $leaderboard): bool
    {
        return in_array($leaderboard->period, ['daily', 'weekly', 'monthly'], true)
            && $this->resolveMetric($leaderboard) === 'total_points';
    }
}
