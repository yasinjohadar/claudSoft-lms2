<?php

namespace App\Support\Gamification;

class BadgeCriteriaMapper
{
    public const FORM_TO_CRITERIA_KEY = [
        'lessons_completed' => 'lessons_completed',
        'quizzes_passed' => 'quizzes_completed',
        'streak_days' => 'current_streak',
        'points_earned' => 'total_points',
        'courses_completed' => 'courses_completed',
    ];

    public const CRITERIA_KEY_LABELS = [
        'lessons_completed' => 'دروس مكتملة',
        'quizzes_completed' => 'اختبارات مكتملة',
        'current_streak' => 'أيام متتالية',
        'total_points' => 'نقاط مكتسبة',
        'courses_completed' => 'كورسات مكتملة',
        'perfect_scores' => 'درجات كاملة',
        'total_badges' => 'عدد الشارات',
        'current_level' => 'المستوى',
        'assignments_submitted' => 'واجبات مسلّمة',
        'comments_count' => 'تعليقات',
    ];

    /**
     * تحويل حقول نموذج الأدمن إلى criteria JSON
     */
    public static function formToCriteria(?string $requirementType, mixed $requirementValue): ?array
    {
        if (empty($requirementType) || $requirementValue === null || $requirementValue === '') {
            return null;
        }

        $criteriaKey = self::FORM_TO_CRITERIA_KEY[$requirementType] ?? null;

        if ($criteriaKey === null) {
            return null;
        }

        return [$criteriaKey => (int) $requirementValue];
    }

    /**
     * استخراج حقول النموذج من criteria (أول معيار فقط للشارات متعددة المعايير)
     */
    public static function criteriaToForm(?array $criteria): array
    {
        if (empty($criteria)) {
            return [
                'requirement_type' => '',
                'requirement_value' => '',
            ];
        }

        if (isset($criteria['field'])) {
            $field = $criteria['field'];
            $value = $criteria['required_value'] ?? $criteria['value'] ?? '';

            foreach (self::FORM_TO_CRITERIA_KEY as $formKey => $criteriaKey) {
                if ($criteriaKey === $field) {
                    return [
                        'requirement_type' => $formKey,
                        'requirement_value' => $value,
                    ];
                }
            }
        }

        $criteriaKey = array_key_first($criteria);
        $value = $criteria[$criteriaKey];

        foreach (self::FORM_TO_CRITERIA_KEY as $formKey => $mappedKey) {
            if ($mappedKey === $criteriaKey) {
                return [
                    'requirement_type' => $formKey,
                    'requirement_value' => $value,
                ];
            }
        }

        return [
            'requirement_type' => '',
            'requirement_value' => '',
        ];
    }

    /**
     * نص عربي لعرض المعايير في لوحة الأدمن
     */
    public static function formatForDisplay(?array $criteria): string
    {
        if (empty($criteria)) {
            return 'شارة يدوية (بدون معايير تلقائية)';
        }

        if (isset($criteria['field'])) {
            $field = $criteria['field'];
            $value = $criteria['required_value'] ?? $criteria['value'] ?? 0;
            $label = self::CRITERIA_KEY_LABELS[$field] ?? $field;

            return "{$label}: {$value}";
        }

        $parts = [];

        foreach ($criteria as $key => $value) {
            $label = self::CRITERIA_KEY_LABELS[$key] ?? $key;
            $parts[] = "{$label}: {$value}";
        }

        return implode(' — ', $parts);
    }
}
