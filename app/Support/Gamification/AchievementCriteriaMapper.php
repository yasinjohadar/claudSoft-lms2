<?php

namespace App\Support\Gamification;

class AchievementCriteriaMapper
{
    public const FORM_TO_FIELD = [
        'lessons_completed' => 'lessons_completed',
        'quizzes_passed' => 'quizzes_completed',
        'points_earned' => 'total_points',
        'badges_earned' => 'total_badges',
        'streak_days' => 'current_streak',
        'courses_completed' => 'courses_completed',
    ];

    public const FIELD_LABELS = [
        'lessons_completed' => 'دروس مكتملة',
        'quizzes_completed' => 'اختبارات مكتملة',
        'total_points' => 'نقاط مكتسبة',
        'total_badges' => 'شارات مكتسبة',
        'current_streak' => 'أيام متتالية',
        'longest_streak' => 'أطول سلسلة',
        'courses_completed' => 'كورسات مكتملة',
        'perfect_scores' => 'درجات كاملة',
        'current_level' => 'المستوى',
        'assignments_submitted' => 'واجبات مسلّمة',
    ];

    public const REQUIREMENT_TYPE_OPTIONS = [
        'lessons_completed' => 'دروس مكتملة',
        'quizzes_passed' => 'اختبارات ناجحة',
        'points_earned' => 'نقاط مكتسبة',
        'badges_earned' => 'شارات مكتسبة',
        'streak_days' => 'أيام متتالية',
        'courses_completed' => 'كورسات مكتملة',
    ];

    /**
     * @return array{criteria: array{field: string}, target_value: int}|null
     */
    public static function formToAchievementData(?string $requirementType, mixed $requirementValue): ?array
    {
        if (empty($requirementType) || $requirementValue === null || $requirementValue === '') {
            return null;
        }

        $field = self::FORM_TO_FIELD[$requirementType] ?? null;

        if ($field === null) {
            return null;
        }

        return [
            'criteria' => ['field' => $field],
            'target_value' => max(1, (int) $requirementValue),
        ];
    }

    /**
     * @return array{requirement_type: string, requirement_value: int|string}
     */
    public static function criteriaToForm(?array $criteria, ?int $targetValue = null): array
    {
        if (empty($criteria)) {
            return [
                'requirement_type' => '',
                'requirement_value' => $targetValue ?? '',
            ];
        }

        $field = $criteria['field'] ?? null;

        if (!$field) {
            return [
                'requirement_type' => '',
                'requirement_value' => $targetValue ?? '',
            ];
        }

        foreach (self::FORM_TO_FIELD as $formKey => $mappedField) {
            if ($mappedField === $field) {
                return [
                    'requirement_type' => $formKey,
                    'requirement_value' => $targetValue ?? '',
                ];
            }
        }

        return [
            'requirement_type' => '',
            'requirement_value' => $targetValue ?? '',
        ];
    }

    public static function formatForDisplay(?array $criteria, ?int $targetValue = null): string
    {
        if (empty($criteria) || empty($criteria['field'])) {
            return '—';
        }

        $label = self::FIELD_LABELS[$criteria['field']] ?? $criteria['field'];
        $value = $targetValue ?? ($criteria['required_value'] ?? $criteria['value'] ?? '?');

        return "{$label}: {$value}";
    }
}
