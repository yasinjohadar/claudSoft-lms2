<?php

namespace App\Services\QuestionBank\TypeImport\Json;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\CanonicalImportRow;

class StructuredJsonParser
{
    /**
     * @param  array<string, mixed>  $payload
     * @return list<CanonicalImportRow>
     */
    public function parse(array $payload, QuestionType $questionType): array
    {
        if (! isset($payload['questions']) || ! is_array($payload['questions'])) {
            throw new \InvalidArgumentException('صيغة JSON المنظمة تتطلب حقل questions كمصفوفة');
        }

        if (isset($payload['question_type'])) {
            $fileType = (string) $payload['question_type'];
            if ($fileType !== $questionType->name && $fileType !== $questionType->display_name) {
                throw new \InvalidArgumentException('نوع السؤال في الملف لا يطابق النوع المختار');
            }
        }

        $typeLabel = $questionType->display_name;
        $parsed = [];

        foreach ($payload['questions'] as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $rowNumber = $index + 1;
            $values = $this->mapStructuredItem($item, $questionType->name);

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $parsed[] = CanonicalImportRow::fromValues($rowNumber, $typeLabel, $values);
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapStructuredItem(array $item, string $typeName): array
    {
        $values = [
            'question_text' => (string) ($item['question_text'] ?? ''),
            'lesson_name' => (string) ($item['lesson_name'] ?? ''),
            'points' => (string) ($item['default_grade'] ?? $item['points'] ?? '1'),
            'difficulty' => (string) ($item['difficulty'] ?? $item['difficulty_level'] ?? 'medium'),
            'course' => (string) ($item['course'] ?? ''),
            'explanation' => (string) ($item['explanation'] ?? ''),
            'tags' => $this->normalizeTags($item['tags'] ?? ''),
            'language' => (string) ($item['programming_language'] ?? $item['language'] ?? ''),
        ];

        switch ($typeName) {
            case 'multiple_choice_single':
            case 'multiple_choice_multiple':
                $values = array_merge($values, $this->mapOptions($item, $typeName));
                break;

            case 'true_false':
                $values['correct_answer'] = $this->normalizeTrueFalseValue($item['correct_answer'] ?? $item['is_true'] ?? '');
                break;

            case 'short_answer':
            case 'fill_blanks':
                $values['accepted_answers'] = $this->normalizeAcceptedAnswers($item);
                $values['case_sensitive'] = $this->normalizeCaseSensitive($item['case_sensitive'] ?? false);
                break;

            case 'matching':
                $values['matching_pairs_raw'] = $this->encodeMatchingPairs($item['matching_pairs'] ?? []);
                break;

            case 'ordering':
                $values = array_merge($values, $this->mapOrderingItems($item['items'] ?? $item['order_items'] ?? []));
                break;

            case 'numerical':
            case 'calculated':
                $values['correct_answer'] = (string) ($item['correct_answer'] ?? $item['metadata']['correct_answer'] ?? '');
                $values['tolerance'] = (string) ($item['tolerance'] ?? $item['metadata']['tolerance'] ?? '');
                $values['unit'] = (string) ($item['unit'] ?? $item['metadata']['unit'] ?? '');
                if ($typeName === 'calculated') {
                    $values['formula'] = (string) ($item['formula'] ?? $item['metadata']['formula'] ?? '');
                }
                break;

            case 'essay':
                $values['min_words'] = (string) ($item['min_words'] ?? $item['metadata']['min_words'] ?? '');
                $values['max_words'] = (string) ($item['max_words'] ?? $item['metadata']['max_words'] ?? '');
                $values['model_answer'] = (string) ($item['model_answer'] ?? $item['metadata']['model_answer'] ?? '');
                $values['grading_criteria'] = (string) ($item['grading_criteria'] ?? $item['metadata']['grading_criteria'] ?? '');
                break;
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function mapOptions(array $item, string $typeName): array
    {
        $values = [];
        $options = $item['options'] ?? [];
        $correctIndexes = [];

        if (is_array($options)) {
            foreach ($options as $idx => $option) {
                $col = $idx + 1;
                if ($col > 6) {
                    break;
                }
                if (is_string($option)) {
                    $values['option_'.$col] = $option;
                } elseif (is_array($option)) {
                    $values['option_'.$col] = (string) ($option['text'] ?? $option['option_text'] ?? '');
                    if (! empty($option['is_correct'])) {
                        $correctIndexes[] = (string) $col;
                    }
                }
            }
        }

        if (isset($item['correct_answer'])) {
            $values['correct_answer'] = is_array($item['correct_answer'])
                ? implode(',', $item['correct_answer'])
                : (string) $item['correct_answer'];
        } elseif ($correctIndexes !== []) {
            $values['correct_answer'] = $typeName === 'multiple_choice_multiple'
                ? implode(',', $correctIndexes)
                : $correctIndexes[0];
        }

        return $values;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function mapOrderingItems(array $items): array
    {
        $values = [];
        foreach ($items as $idx => $item) {
            $col = $idx + 1;
            if ($col > 6) {
                break;
            }
            $values['option_'.$col] = is_string($item) ? $item : (string) ($item['text'] ?? '');
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function normalizeAcceptedAnswers(array $item): string
    {
        if (isset($item['accepted_answers'])) {
            if (is_array($item['accepted_answers'])) {
                return implode('|', array_map('strval', $item['accepted_answers']));
            }

            return (string) $item['accepted_answers'];
        }

        if (isset($item['correct_answers']) && is_array($item['correct_answers'])) {
            return implode('|', array_map('strval', $item['correct_answers']));
        }

        return (string) ($item['correct_answer'] ?? '');
    }

    private function normalizeCaseSensitive(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        return (string) $value;
    }

    private function normalizeTrueFalseValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    private function normalizeTags(mixed $tags): string
    {
        if (is_array($tags)) {
            return implode(',', array_map('strval', $tags));
        }

        return (string) $tags;
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
        return trim((string) ($values['question_text'] ?? '')) === ''
            && trim((string) ($values['course'] ?? '')) === '';
    }
}
