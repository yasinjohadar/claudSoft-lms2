<?php

namespace App\Services\QuestionBank\Export;

use App\Models\QuestionBank;
use Illuminate\Support\Collection;

class QuestionBankExportSerializer
{
    /**
     * @return array<string, mixed> Import-compatible flat row (keys match QuestionBankExcelImportService)
     */
    public function toImportRow(QuestionBank $question): array
    {
        $question->loadMissing(['questionType', 'course', 'options', 'programmingLanguages']);

        $typeName = $question->questionType?->name ?? '';
        $meta = $question->metadata ?? [];

        $row = [
            'question_type' => $question->questionType?->display_name ?? '',
            'question_text' => strip_tags((string) $question->question_text),
            'lesson_name' => (string) ($question->lesson_name ?? ''),
            'option_1' => '',
            'option_2' => '',
            'option_3' => '',
            'option_4' => '',
            'option_5' => '',
            'option_6' => '',
            'correct_answer' => '',
            'accepted_answers' => '',
            'case_sensitive' => '',
            'matching_pairs_raw' => '',
            'points' => (string) ($question->default_grade ?? 1),
            'difficulty' => (string) ($question->difficulty_level ?? 'medium'),
            'course' => $question->course?->title ?? '',
            'explanation' => strip_tags((string) ($question->explanation ?? '')),
            'tags' => $this->formatTags($question->tags),
            'language' => $this->formatLanguage($question),
            'tolerance' => '',
            'unit' => '',
            'min_words' => '',
            'max_words' => '',
            'model_answer' => '',
            'grading_criteria' => '',
            'formula' => '',
        ];

        $options = $question->options->sortBy('option_order')->values();

        return match ($typeName) {
            'multiple_choice_single', 'multiple_choice_multiple' => $this->mapMultipleChoice($row, $options, $typeName),
            'true_false' => $this->mapTrueFalse($row, $options),
            'short_answer', 'fill_blanks' => $this->mapShortAnswer($row, $meta),
            'matching' => $this->mapMatching($row, $options),
            'ordering' => $this->mapOrdering($row, $options),
            'numerical', 'calculated' => $this->mapNumerical($row, $meta, $typeName),
            'essay' => $this->mapEssay($row, $meta),
            default => $row,
        };
    }

    /**
     * @return array<string, mixed> Structured JSON question (matches type JSON import)
     */
    public function toStructuredQuestion(QuestionBank $question): array
    {
        $row = $this->toImportRow($question);
        $typeName = $question->questionType?->name ?? '';

        $base = [
            'question_text' => $row['question_text'],
            'lesson_name' => $row['lesson_name'],
            'course' => $row['course'],
            'default_grade' => (float) ($question->default_grade ?? 1),
            'difficulty' => $row['difficulty'],
            'explanation' => $row['explanation'],
            'tags' => $this->parseTagsList($row['tags']),
            'programming_language' => $row['language'],
        ];

        return match ($typeName) {
            'multiple_choice_single', 'multiple_choice_multiple' => array_merge($base, [
                'options' => $this->buildStructuredOptions($question),
            ]),
            'true_false' => array_merge($base, [
                'correct_answer' => $row['correct_answer'] === 'false' ? false : true,
            ]),
            'short_answer', 'fill_blanks' => array_merge($base, [
                'accepted_answers' => $this->splitPipe($row['accepted_answers']),
                'case_sensitive' => in_array(mb_strtolower($row['case_sensitive']), ['نعم', 'yes', '1', 'true'], true),
            ]),
            'matching' => array_merge($base, [
                'matching_pairs' => $this->decodeMatchingPairs($row['matching_pairs_raw']),
            ]),
            'ordering' => array_merge($base, [
                'items' => array_values(array_filter([
                    $row['option_1'], $row['option_2'], $row['option_3'],
                    $row['option_4'], $row['option_5'], $row['option_6'],
                ], fn ($v) => trim((string) $v) !== '')),
            ]),
            'numerical' => array_merge($base, [
                'correct_answer' => $row['correct_answer'] !== '' ? (float) $row['correct_answer'] : 0,
                'tolerance' => $row['tolerance'] !== '' ? (float) $row['tolerance'] : 0,
                'unit' => $row['unit'],
            ]),
            'calculated' => array_merge($base, [
                'correct_answer' => $row['correct_answer'] !== '' ? (float) $row['correct_answer'] : 0,
                'tolerance' => $row['tolerance'] !== '' ? (float) $row['tolerance'] : 0,
                'formula' => $row['formula'],
            ]),
            'essay' => array_merge($base, [
                'min_words' => $row['min_words'] !== '' ? (int) $row['min_words'] : 0,
                'max_words' => $row['max_words'] !== '' ? (int) $row['max_words'] : 0,
                'model_answer' => $row['model_answer'],
                'grading_criteria' => $row['grading_criteria'],
            ]),
            default => $base,
        };
    }

    /**
     * @param  Collection<int, QuestionBank>  $questions
     * @return list<array<string, mixed>>
     */
    public function toImportRows(Collection $questions): array
    {
        return $questions->map(fn (QuestionBank $q) => $this->toImportRow($q))->values()->all();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapMultipleChoice(array $row, Collection $options, string $typeName): array
    {
        $correctIndexes = [];

        foreach ($options as $option) {
            $order = (int) $option->option_order;
            if ($order < 1 || $order > 6) {
                continue;
            }
            $row['option_'.$order] = strip_tags((string) $option->option_text);
            if ($option->is_correct) {
                $correctIndexes[] = (string) $order;
            }
        }

        $row['correct_answer'] = $typeName === 'multiple_choice_multiple'
            ? implode(',', $correctIndexes)
            : ($correctIndexes[0] ?? '');

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapTrueFalse(array $row, Collection $options): array
    {
        foreach ($options as $option) {
            if (! $option->is_correct) {
                continue;
            }
            $text = mb_strtolower(trim(strip_tags((string) $option->option_text)));
            $row['correct_answer'] = in_array($text, ['صح', 'صحيح', 'true', 'yes', 'نعم'], true) ? 'true' : 'false';
            break;
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function mapShortAnswer(array $row, array $meta): array
    {
        $answers = $meta['correct_answers'] ?? [];
        if (is_array($answers) && $answers !== []) {
            $row['accepted_answers'] = implode('|', array_map('strval', $answers));
        }
        $row['case_sensitive'] = ! empty($meta['case_sensitive']) ? 'نعم' : 'لا';

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapMatching(array $row, Collection $options): array
    {
        $chunks = [];
        foreach ($options as $option) {
            $left = trim(strip_tags((string) $option->option_text));
            $right = trim(strip_tags((string) ($option->feedback ?? '')));
            if ($left === '' || $right === '') {
                continue;
            }
            $chunks[] = $left.'||'.$right;
        }
        $row['matching_pairs_raw'] = implode(';;;', $chunks);

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapOrdering(array $row, Collection $options): array
    {
        $index = 1;
        foreach ($options as $option) {
            if ($index > 6) {
                break;
            }
            $row['option_'.$index] = strip_tags((string) $option->option_text);
            $index++;
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function mapNumerical(array $row, array $meta, string $typeName): array
    {
        if (isset($meta['correct_answer'])) {
            $row['correct_answer'] = (string) $meta['correct_answer'];
        }
        if (isset($meta['tolerance'])) {
            $row['tolerance'] = (string) $meta['tolerance'];
        }
        if (! empty($meta['unit'])) {
            $row['unit'] = (string) $meta['unit'];
        }
        if ($typeName === 'calculated' && ! empty($meta['formula'])) {
            $row['formula'] = (string) $meta['formula'];
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function mapEssay(array $row, array $meta): array
    {
        if (isset($meta['min_words'])) {
            $row['min_words'] = (string) $meta['min_words'];
        }
        if (isset($meta['max_words'])) {
            $row['max_words'] = (string) $meta['max_words'];
        }
        if (! empty($meta['model_answer'])) {
            $row['model_answer'] = (string) $meta['model_answer'];
        }
        if (! empty($meta['grading_criteria'])) {
            $row['grading_criteria'] = (string) $meta['grading_criteria'];
        }

        return $row;
    }

    /**
     * @return list<array{text: string, is_correct: bool}>
     */
    private function buildStructuredOptions(QuestionBank $question): array
    {
        $options = [];
        foreach ($question->options->sortBy('option_order') as $option) {
            $text = strip_tags((string) $option->option_text);
            if ($text === '') {
                continue;
            }
            $options[] = [
                'text' => $text,
                'is_correct' => (bool) $option->is_correct,
            ];
        }

        return $options;
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

    private function formatTags(mixed $tags): string
    {
        if (! is_array($tags) || $tags === []) {
            return '';
        }

        return implode(',', array_map('strval', $tags));
    }

    /**
     * @return list<string>
     */
    private function parseTagsList(string $tags): array
    {
        if (trim($tags) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }

    private function formatLanguage(QuestionBank $question): string
    {
        $lang = $question->programmingLanguages->first();

        return $lang ? (string) ($lang->display_name ?? $lang->name) : '';
    }

    /**
     * @return list<string>
     */
    private function splitPipe(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode('|', $raw))));
    }
}
