<?php

namespace App\Services\QuestionBank\TypeImport\Json;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;

class TypeJsonTemplateGenerator
{
    public function generate(QuestionType $questionType): string
    {
        $typeName = $questionType->name;
        $examples = TypeImportColumnRegistry::exampleRowsForType($typeName);

        $questions = [];
        foreach ($examples as $example) {
            $questions[] = $this->buildStructuredQuestion($typeName, $example);
        }

        $payload = [
            'version' => '1.0',
            'question_type' => $typeName,
            'questions' => $questions,
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, string>  $example
     * @return array<string, mixed>
     */
    private function buildStructuredQuestion(string $typeName, array $example): array
    {
        $base = [
            'question_text' => $example['question_text'] ?? '',
            'lesson_name' => $example['lesson_name'] ?? '',
            'course' => $example['course'] ?? '',
            'default_grade' => (float) ($example['points'] ?? 1),
            'difficulty' => $example['difficulty'] ?? 'medium',
            'explanation' => $example['explanation'] ?? '',
            'tags' => $this->parseTags($example['tags'] ?? ''),
            'programming_language' => $example['language'] ?? '',
        ];

        return match ($typeName) {
            'multiple_choice_single', 'multiple_choice_multiple' => array_merge($base, [
                'options' => $this->buildOptionsFromExample($example, $typeName),
            ]),
            'true_false' => array_merge($base, [
                'correct_answer' => $this->normalizeBoolString($example['correct_answer'] ?? 'true'),
            ]),
            'short_answer', 'fill_blanks' => array_merge($base, [
                'accepted_answers' => $this->splitPipe($example['accepted_answers'] ?? ''),
                'case_sensitive' => in_array(mb_strtolower($example['case_sensitive'] ?? ''), ['نعم', 'yes', '1', 'true'], true),
            ]),
            'matching' => array_merge($base, [
                'matching_pairs' => $this->decodeMatchingPairs($example['matching_pairs_raw'] ?? ''),
            ]),
            'ordering' => array_merge($base, [
                'items' => array_values(array_filter([
                    $example['option_1'] ?? '',
                    $example['option_2'] ?? '',
                    $example['option_3'] ?? '',
                    $example['option_4'] ?? '',
                    $example['option_5'] ?? '',
                    $example['option_6'] ?? '',
                ], fn ($v) => trim($v) !== '')),
            ]),
            'numerical' => array_merge($base, [
                'correct_answer' => (float) ($example['correct_answer'] ?? 0),
                'tolerance' => (float) ($example['tolerance'] ?? 0),
                'unit' => $example['unit'] ?? '',
            ]),
            'calculated' => array_merge($base, [
                'correct_answer' => (float) ($example['correct_answer'] ?? 0),
                'tolerance' => (float) ($example['tolerance'] ?? 0),
                'formula' => $example['formula'] ?? '',
            ]),
            'essay' => array_merge($base, [
                'min_words' => (int) ($example['min_words'] ?? 0),
                'max_words' => (int) ($example['max_words'] ?? 0),
                'model_answer' => $example['model_answer'] ?? '',
                'grading_criteria' => $example['grading_criteria'] ?? '',
            ]),
            default => $base,
        };
    }

    /**
     * @param  array<string, string>  $example
     * @return list<array{text: string, is_correct: bool}>
     */
    private function buildOptionsFromExample(array $example, string $typeName): array
    {
        $options = [];
        $correctRaw = (string) ($example['correct_answer'] ?? '');
        $correctList = $correctRaw !== '' ? array_map('trim', explode(',', $correctRaw)) : [];

        for ($i = 1; $i <= 6; $i++) {
            $text = trim((string) ($example['option_'.$i] ?? ''));
            if ($text === '') {
                continue;
            }
            $options[] = [
                'text' => $text,
                'is_correct' => in_array((string) $i, $correctList, true),
            ];
        }

        if ($typeName === 'multiple_choice_single' && $options !== [] && ! $this->hasCorrectOption($options)) {
            $options[0]['is_correct'] = true;
        }

        return $options;
    }

    /**
     * @param  list<array{text: string, is_correct: bool}>  $options
     */
    private function hasCorrectOption(array $options): bool
    {
        foreach ($options as $option) {
            if (! empty($option['is_correct'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function splitPipe(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode('|', $raw))));
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function decodeMatchingPairs(string $raw): array
    {
        $pairs = [];
        foreach (explode(';;;', $raw) as $chunk) {
            $parts = explode('||', $chunk, 2);
            if (count($parts) < 2) {
                continue;
            }
            $pairs[] = [
                'question' => trim($parts[0]),
                'answer' => trim($parts[1]),
            ];
        }

        return $pairs;
    }

    /**
     * @return list<string>
     */
    private function parseTags(string $tags): array
    {
        if (trim($tags) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }

    private function normalizeBoolString(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['true', '1', 'صح', 'صحيح', 'yes', 'نعم'], true);
    }
}
