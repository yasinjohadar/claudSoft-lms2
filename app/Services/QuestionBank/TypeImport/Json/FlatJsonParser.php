<?php

namespace App\Services\QuestionBank\TypeImport\Json;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\CanonicalImportRow;

class FlatJsonParser
{
    /**
     * @var array<string, string>
     */
    private const FLAT_KEY_MAP = [
        'question_text' => 'question_text',
        'lesson_name' => 'lesson_name',
        'option_1' => 'option_1',
        'option_2' => 'option_2',
        'option_3' => 'option_3',
        'option_4' => 'option_4',
        'option_5' => 'option_5',
        'option_6' => 'option_6',
        'correct_answer' => 'correct_answer',
        'accepted_answers' => 'accepted_answers',
        'case_sensitive' => 'case_sensitive',
        'matching_pairs_raw' => 'matching_pairs_raw',
        'matching_pairs' => 'matching_pairs_raw',
        'points' => 'points',
        'default_grade' => 'points',
        'difficulty' => 'difficulty',
        'difficulty_level' => 'difficulty',
        'course' => 'course',
        'explanation' => 'explanation',
        'tags' => 'tags',
        'language' => 'language',
        'programming_language' => 'language',
        'tolerance' => 'tolerance',
        'unit' => 'unit',
        'min_words' => 'min_words',
        'max_words' => 'max_words',
        'model_answer' => 'model_answer',
        'grading_criteria' => 'grading_criteria',
        'formula' => 'formula',
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return list<CanonicalImportRow>
     */
    public function parse(array $payload, QuestionType $questionType): array
    {
        $items = $this->extractQuestionItems($payload);
        $typeLabel = $questionType->display_name;
        $parsed = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $rowNumber = $index + 1;
            $values = $this->mapFlatItem($item, $typeName = $questionType->name);

            if ($this->isEmptyRow($values)) {
                continue;
            }

            if (isset($item['question_type']) && $item['question_type'] !== $typeName && $item['question_type'] !== $typeLabel) {
                throw new \InvalidArgumentException('السطر '.($rowNumber).': نوع السؤال لا يطابق النوع المختار');
            }

            $parsed[] = CanonicalImportRow::fromValues($rowNumber, $typeLabel, $values);
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    private function extractQuestionItems(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        if (isset($payload['questions']) && is_array($payload['questions'])) {
            return $payload['questions'];
        }

        throw new \InvalidArgumentException('صيغة JSON مسطحة غير صالحة: يجب أن تكون مصفوفة أو كائن يحتوي questions');
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapFlatItem(array $item, string $typeName): array
    {
        $values = [];

        foreach ($item as $key => $value) {
            $normalizedKey = self::FLAT_KEY_MAP[$key] ?? null;
            if ($normalizedKey === null) {
                continue;
            }

            if ($normalizedKey === 'tags' && is_array($value)) {
                $values[$normalizedKey] = implode(',', $value);
            } elseif (is_scalar($value) || $value === null) {
                $values[$normalizedKey] = $value;
            }
        }

        if ($typeName === 'matching' && isset($item['matching_pairs']) && is_array($item['matching_pairs'])) {
            $values['matching_pairs_raw'] = $this->encodeMatchingPairs($item['matching_pairs']);
        }

        if (($values['points'] ?? '') === '') {
            $values['points'] = '1';
        }
        if (($values['difficulty'] ?? '') === '') {
            $values['difficulty'] = 'medium';
        }

        return $values;
    }

    /**
     * @param  array<int, mixed>  $pairs
     */
    private function encodeMatchingPairs(array $pairs): string
    {
        $chunks = [];
        foreach ($pairs as $pair) {
            if (! is_array($pair)) {
                continue;
            }
            $q = trim((string) ($pair['question'] ?? $pair['left'] ?? ''));
            $a = trim((string) ($pair['answer'] ?? $pair['right'] ?? ''));
            if ($q !== '' && $a !== '') {
                $chunks[] = $q.'||'.$a;
            }
        }

        return implode(';;;', $chunks);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function isEmptyRow(array $values): bool
    {
        $significant = array_filter($values, fn ($v) => trim((string) $v) !== '');

        return $significant === [];
    }
}
