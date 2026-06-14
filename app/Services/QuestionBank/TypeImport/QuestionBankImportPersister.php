<?php

namespace App\Services\QuestionBank\TypeImport;

use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Services\QuestionBankExcelImportService;

class QuestionBankImportPersister
{
    public function __construct(
        private readonly QuestionBankExcelImportService $excel = new QuestionBankExcelImportService
    ) {}

    /**
     * @param  array<string, mixed>  $questionData
     * @param  array<string, int>  $languageMapping
     */
    public function persist(
        array $questionData,
        QuestionType $questionType,
        int $courseId,
        array $languageMapping,
        ?int $defaultLanguageId = null
    ): QuestionBank {
        $meta = $this->buildMetadata($questionData, $questionType->name);
        $lessonName = trim((string) ($questionData['lesson_name'] ?? ''));

        $question = QuestionBank::create([
            'course_id' => $courseId,
            'question_type_id' => $questionType->id,
            'question_text' => $questionData['question_text'],
            'lesson_name' => $lessonName !== '' ? $lessonName : null,
            'explanation' => ! empty(trim((string) ($questionData['explanation'] ?? ''))) ? trim($questionData['explanation']) : null,
            'default_grade' => floatval($questionData['points'] ?? 1),
            'difficulty_level' => $this->mapDifficulty($questionData['difficulty'] ?? 'medium'),
            'tags' => $this->parseTags($questionData['tags'] ?? null),
            'metadata' => $meta === [] ? null : $meta,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        $this->createOptionsForType($question, $questionData, $questionType->name, $meta);
        $this->attachProgrammingLanguage($question, $questionData, $languageMapping, $defaultLanguageId);

        return $question;
    }

    /**
     * @param  array<string, mixed>  $questionData
     * @return array<string, mixed>
     */
    private function buildMetadata(array $questionData, string $typeName): array
    {
        $meta = [];

        switch ($typeName) {
            case 'short_answer':
            case 'fill_blanks':
                $answers = $this->excel->parseAcceptedAnswersList($questionData);
                $meta['correct_answers'] = $answers;
                $meta['case_sensitive'] = $this->excel->parseCaseSensitiveFlag($questionData['case_sensitive'] ?? '');
                break;

            case 'essay':
                if (($questionData['min_words'] ?? '') !== '') {
                    $meta['min_words'] = (int) $questionData['min_words'];
                }
                if (($questionData['max_words'] ?? '') !== '') {
                    $meta['max_words'] = (int) $questionData['max_words'];
                }
                if (! empty(trim((string) ($questionData['model_answer'] ?? '')))) {
                    $meta['model_answer'] = trim($questionData['model_answer']);
                }
                if (! empty(trim((string) ($questionData['grading_criteria'] ?? '')))) {
                    $meta['grading_criteria'] = trim($questionData['grading_criteria']);
                }
                break;

            case 'numerical':
            case 'calculated':
                $meta['correct_answer'] = floatval(str_replace(',', '.', (string) $questionData['correct_answer']));
                $tolRaw = trim((string) ($questionData['tolerance'] ?? ''));
                $meta['tolerance'] = $tolRaw !== '' ? floatval(str_replace(',', '.', $tolRaw)) : 0.0;
                if (! empty(trim((string) ($questionData['unit'] ?? '')))) {
                    $meta['unit'] = trim($questionData['unit']);
                }
                if ($typeName === 'calculated' && ! empty(trim((string) ($questionData['formula'] ?? '')))) {
                    $meta['formula'] = trim($questionData['formula']);
                }
                break;
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $questionData
     * @param  array<string, mixed>  $meta
     */
    private function createOptionsForType(QuestionBank $question, array $questionData, string $typeName, array $meta): void
    {
        switch ($typeName) {
            case 'multiple_choice_single':
            case 'multiple_choice_multiple':
                for ($i = 1; $i <= 6; $i++) {
                    $optionText = trim((string) ($questionData['option_'.$i] ?? ''));
                    if ($optionText === '') {
                        continue;
                    }
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => $this->isCorrectAnswer($questionData['correct_answer'] ?? '', $i),
                        'option_order' => $i,
                        'score_weight' => 1.0,
                    ]);
                }
                break;

            case 'true_false':
                $norm = $this->excel->normalizeTrueFalseAnswer($questionData['correct_answer'] ?? '');
                $this->createQuestionOptions($question, [
                    ['option_text' => 'صح', 'option_order' => 1],
                    ['option_text' => 'خطأ', 'option_order' => 2],
                ], null, $norm);
                break;

            case 'matching':
                $pairs = $this->excel->parseMatchingPairsRaw($questionData['matching_pairs_raw'] ?? '');
                $this->createMatchingOptions($question, $this->excel->matchingPairsForCreate($pairs));
                break;

            case 'ordering':
                $this->createOrderingOptions($question, $this->excel->nonEmptyOptions($questionData, 6));
                break;

            case 'fill_blanks':
                $answers = $meta['correct_answers'] ?? [];
                $this->createFillBlanksOptions($question, $answers, (bool) ($meta['case_sensitive'] ?? false));
                break;
        }
    }

    /**
     * @param  array<string, mixed>  $questionData
     * @param  array<string, int>  $languageMapping
     */
    private function attachProgrammingLanguage(
        QuestionBank $question,
        array $questionData,
        array $languageMapping,
        ?int $defaultLanguageId
    ): void {
        $languageId = null;
        if (! empty($questionData['language'])) {
            $languageName = trim($questionData['language']);
            $languageId = $languageMapping[$languageName] ?? null;
        }
        if (! $languageId && $defaultLanguageId) {
            $languageId = $defaultLanguageId;
        }
        if ($languageId) {
            $question->programmingLanguages()->attach($languageId);
        }
    }

    /**
     * @return array<int, string>|null
     */
    private function parseTags(?string $tags): ?array
    {
        $tags = trim((string) $tags);
        if ($tags === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }

    private function mapDifficulty(mixed $difficulty): string
    {
        $mapping = [
            'سهل' => 'easy',
            'easy' => 'easy',
            'متوسط' => 'medium',
            'medium' => 'medium',
            'صعب' => 'hard',
            'hard' => 'hard',
            'خبير' => 'expert',
            'expert' => 'expert',
        ];

        return $mapping[strtolower((string) $difficulty)] ?? 'medium';
    }

    private function isCorrectAnswer(string $correctAnswer, int $optionNumber): bool
    {
        $correctAnswer = trim($correctAnswer);

        if (is_numeric($correctAnswer) && intval($correctAnswer) == $optionNumber) {
            return true;
        }

        if (str_contains($correctAnswer, ',')) {
            $answers = array_map('trim', explode(',', $correctAnswer));

            return in_array($optionNumber, $answers, false) || in_array((string) $optionNumber, $answers, true);
        }

        return false;
    }

    private function createQuestionOptions(QuestionBank $question, array $options, $correctOption = null, $correctAnswer = null): void
    {
        foreach ($options as $index => $optionData) {
            $isCorrect = false;

            if ($correctOption !== null && $index == $correctOption) {
                $isCorrect = true;
            } elseif ($correctAnswer !== null) {
                $optionText = strtolower($optionData['option_text'] ?? '');
                if (($correctAnswer === 'true' && $optionText === 'صح') ||
                    ($correctAnswer === 'false' && $optionText === 'خطأ')) {
                    $isCorrect = true;
                }
            } elseif (isset($optionData['is_correct'])) {
                $isCorrect = (bool) $optionData['is_correct'];
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'] ?? null,
                'is_correct' => $isCorrect,
                'option_order' => $optionData['option_order'] ?? $index + 1,
                'score_weight' => $optionData['score_weight'] ?? 1.0,
                'feedback' => $optionData['feedback'] ?? null,
                'match_pair_id' => $optionData['match_pair_id'] ?? null,
            ]);
        }
    }

    private function createMatchingOptions(QuestionBank $question, array $matchingPairs): void
    {
        $order = 1;
        foreach ($matchingPairs as $pairId => $pair) {
            if (empty($pair['question']) || empty($pair['answer'])) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $pair['question'],
                'is_correct' => true,
                'option_order' => $order,
                'match_pair_id' => $pairId,
                'feedback' => $pair['answer'],
            ]);

            $order++;
        }
    }

    private function createFillBlanksOptions(QuestionBank $question, array $correctAnswers, bool $caseSensitive = false): void
    {
        $order = 1;
        foreach ($correctAnswers as $answer) {
            if (empty($answer)) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $answer,
                'is_correct' => true,
                'option_order' => $order,
                'feedback' => $caseSensitive ? 'case_sensitive' : null,
            ]);

            $order++;
        }
    }

    private function createOrderingOptions(QuestionBank $question, array $orderItems): void
    {
        $order = 1;
        foreach ($orderItems as $item) {
            if (empty($item)) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $item,
                'is_correct' => true,
                'option_order' => $order,
            ]);

            $order++;
        }
    }
}
