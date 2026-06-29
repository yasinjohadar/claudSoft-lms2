<?php

namespace App\Services\Ai;

class GeneratedQuestionValidator
{
    private const TRUE_VARIANTS = ['صح', 'true', '1', 'صحيح', 'نعم', 'yes'];

    private const FALSE_VARIANTS = ['خطأ', 'false', '0', 'خاطئ', 'لا', 'no'];

    private const STUB_EXPLANATIONS = [
        'صح',
        'خطأ',
        'صح وخطأ',
        'صح / خطأ',
        'صح/خطأ',
        'true',
        'false',
        'true/false',
        'true / false',
        'صحيح',
        'خاطئ',
    ];

    /**
     * @param  array<int, array<string, mixed>>  $questions
     * @return array<int, array<string, mixed>>
     */
    public static function validate(array $questions): array
    {
        $validated = [];

        foreach ($questions as $question) {
            if (! isset($question['question']) || $question['question'] === '' || $question['question'] === null) {
                continue;
            }

            $type = $question['type'] ?? 'single_choice';
            $validatedQuestion = [
                'type' => $type,
                'question' => $question['question'],
                'options' => $question['options'] ?? [],
                'correct_answer' => $question['correct_answer'] ?? '',
                'explanation' => $question['explanation'] ?? '',
                'difficulty' => $question['difficulty'] ?? 'medium',
                'points' => $question['points'] ?? 10,
            ];

            if ($type === 'matching' && isset($question['pairs'])) {
                $validatedQuestion['pairs'] = $question['pairs'];
            }

            if ($type === 'ordering') {
                if (isset($question['items'])) {
                    $validatedQuestion['items'] = $question['items'];
                }
                if (isset($question['correct_order'])) {
                    $validatedQuestion['correct_order'] = $question['correct_order'];
                }
            }

            if ($type === 'fill_blanks' && isset($question['correct_answers'])) {
                $validatedQuestion['correct_answers'] = $question['correct_answers'];
            }

            if ($type === 'numerical') {
                if (isset($question['expected_value'])) {
                    $validatedQuestion['expected_value'] = $question['expected_value'];
                }
                if (isset($question['tolerance'])) {
                    $validatedQuestion['tolerance'] = $question['tolerance'];
                }
            }

            if ($type === 'calculated') {
                if (isset($question['formula'])) {
                    $validatedQuestion['formula'] = $question['formula'];
                }
                if (isset($question['variables'])) {
                    $validatedQuestion['variables'] = $question['variables'];
                }
            }

            $validated[] = $validatedQuestion;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $questionData
     * @return array<string, mixed>
     */
    public static function normalize(array $questionData): array
    {
        $type = $questionData['type'] ?? 'single_choice';

        if ($type === 'true_false') {
            $normalized = self::normalizeTrueFalseAnswer($questionData['correct_answer'] ?? null);
            if ($normalized !== null) {
                $questionData['correct_answer'] = $normalized;
            }
            $questionData['options'] = ['صح', 'خطأ'];
        }

        if ($type === 'fill_blanks') {
            $questionData['question'] = self::normalizeFillBlanksText((string) ($questionData['question'] ?? ''));
        }

        if ($type === 'numerical') {
            if (! isset($questionData['expected_value']) && isset($questionData['correct_answer'])) {
                $questionData['expected_value'] = $questionData['correct_answer'];
            }
        }

        return $questionData;
    }

    public static function sanitizeExplanation(mixed $explanation): ?string
    {
        if ($explanation === null) {
            return null;
        }

        $text = trim((string) $explanation);
        if ($text === '') {
            return null;
        }

        if (self::isStubExplanation($text)) {
            return null;
        }

        if (mb_strlen($text) < 8) {
            return null;
        }

        return $text;
    }

    public static function isStubExplanation(string $explanation): bool
    {
        $normalized = mb_strtolower(trim($explanation));

        foreach (self::STUB_EXPLANATIONS as $stub) {
            if ($normalized === mb_strtolower($stub)) {
                return true;
            }
        }

        return false;
    }

    public static function normalizeTrueFalseAnswer(mixed $correctAnswer): ?string
    {
        if ($correctAnswer === null || $correctAnswer === '') {
            return null;
        }

        if (is_bool($correctAnswer)) {
            return $correctAnswer ? 'صح' : 'خطأ';
        }

        if (is_numeric($correctAnswer)) {
            return (int) $correctAnswer === 1 ? 'صح' : 'خطأ';
        }

        $value = mb_strtolower(trim((string) $correctAnswer));

        if (in_array($value, self::TRUE_VARIANTS, true)) {
            return 'صح';
        }

        if (in_array($value, self::FALSE_VARIANTS, true)) {
            return 'خطأ';
        }

        return null;
    }

    public static function normalizeFillBlanksText(string $text): string
    {
        $text = preg_replace('/\[\s*_{2,}\s*\]/u', '[[blank]]', $text) ?? $text;
        $text = preg_replace('/(?<!\[)\[\s*blank\s*\](?!\])/iu', '[[blank]]', $text) ?? $text;
        $text = preg_replace('/(?<!\[)_{3,}(?!\])/u', '[[blank]]', $text) ?? $text;

        return $text;
    }

    public static function isRecognizedTrueFalseAnswer(mixed $correctAnswer): bool
    {
        return self::normalizeTrueFalseAnswer($correctAnswer) !== null;
    }
}
