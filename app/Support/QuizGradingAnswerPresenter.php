<?php

namespace App\Support;

use App\Models\QuestionBank;
use App\Models\QuizResponse;

/**
 * نصوص موحّدة لإجابة الطالب والإجابة الصحيحة في التصحيح وPDF.
 */
class QuizGradingAnswerPresenter
{
    public const NO_ANSWER = 'لم يتم الإجابة';

    public function studentAnswerPlain(QuizResponse $response): string
    {
        $question = $response->question;
        if ($question === null) {
            return $this->fallbackRawStudentAnswer($response);
        }

        $question->loadMissing(['options', 'questionType']);
        $type = $question->questionType->name ?? '';

        return match ($type) {
            'multiple_choice_single', 'true_false' => $this->studentMultipleChoiceSingleOrTrueFalse($response, $question),
            'multiple_choice_multiple' => $this->studentMultipleChoiceMultiple($response, $question),
            'fill_blanks' => $this->studentFillBlanks($response, $question),
            'matching' => $this->studentMatching($response, $question),
            'ordering' => $this->studentOrdering($response, $question),
            'drag_drop' => $this->studentDragDrop($response, $question),
            'numerical', 'calculated' => $this->studentNumericalOrCalculated($response),
            'short_answer', 'essay' => $this->studentEssayShort($response),
            default => $this->studentGenericFallback($response, $question),
        };
    }

    public function correctAnswerPlain(?QuestionBank $question): string
    {
        if ($question === null) {
            return '—';
        }

        $question->loadMissing(['options', 'questionType']);
        $type = $question->questionType->name ?? '';

        return match ($type) {
            'multiple_choice_single', 'multiple_choice_multiple', 'true_false' => $this->correctOptionsJoined($question),
            'fill_blanks' => $this->correctFillBlanksPlain($question),
            'ordering' => $this->correctOrderingPlain($question),
            'matching' => $this->correctMatchingPlain($question),
            'drag_drop' => $this->correctDragDropPlain($question),
            'numerical' => $this->correctNumericalPlain($question),
            'calculated' => $this->correctCalculatedPlain($question),
            'short_answer' => $this->correctShortAnswerPlain($question),
            'essay' => $this->correctEssayPlain($question),
            default => $this->correctDefaultPlain($question),
        };
    }

    private function fallbackRawStudentAnswer(QuizResponse $response): string
    {
        $parts = [];
        if ($response->response_text !== null && trim((string) $response->response_text) !== '') {
            $parts[] = trim((string) $response->response_text);
        }
        if (! empty($response->response_data) && is_array($response->response_data)) {
            $parts[] = json_encode($response->response_data, JSON_UNESCAPED_UNICODE);
        }
        if ($parts === []) {
            return self::NO_ANSWER;
        }

        return implode(' | ', $parts);
    }

    /**
     * نفس ترتيب مصادر gradeTrueFalse / gradeMultipleChoiceSingle في QuizResponse (لا نستخدم !empty على answer لأن false صالحة).
     *
     * @return mixed|null
     */
    private function extractMcSingleOrTrueFalseRaw(QuizResponse $response): mixed
    {
        $answer = null;

        if (! empty($response->response_data) && is_array($response->response_data)) {
            $answer = $response->response_data['answer'] ?? null;
            if (is_array($answer) && $answer !== []) {
                $answer = array_values($answer)[0] ?? null;
            }
        }

        if ($answer === null && ! empty($response->selected_option_ids)) {
            $answer = is_array($response->selected_option_ids)
                ? ($response->selected_option_ids[0] ?? null)
                : $response->selected_option_ids;
        }

        if ($answer === null && $response->response_text !== null && trim((string) $response->response_text) !== '') {
            $answer = $response->response_text;
        }

        return $answer;
    }

    /**
     * عرض إجابة صح/خطأ بما يتوافق مع QuizResponse::gradeTrueFalse (يشمل answer المنطقي false وسلسلة "false").
     */
    private function formatStudentTrueFalseAnswer(mixed $answer, QuestionBank $question): string
    {
        if ($answer === null) {
            return self::NO_ANSWER;
        }
        if (is_string($answer) && trim($answer) === '') {
            return self::NO_ANSWER;
        }
        if (is_array($answer)) {
            $answer = array_values($answer)[0] ?? null;
            if ($answer === null || (is_string($answer) && trim($answer) === '')) {
                return self::NO_ANSWER;
            }
        }

        $options = $question->options;
        $answerValue = null;

        if (is_numeric($answer)) {
            $selectedOption = $options->find((int) $answer) ?? $options->find($answer);
            if ($selectedOption) {
                $optionText = strtolower(trim(strip_tags($selectedOption->option_text)));
                $answerValue = ($optionText === 'صح' || $optionText === 'true' || $optionText === '1' || $optionText === 'صحيح') ? 'true' : 'false';
            }
        } elseif (is_bool($answer)) {
            $answerValue = $answer ? 'true' : 'false';
        } else {
            $answerStr = strtolower(trim(strip_tags((string) $answer)));
            if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1' || $answerStr === 'صحيح') {
                $answerValue = 'true';
            } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                $answerValue = 'false';
            }
        }

        if ($answerValue === null) {
            return self::NO_ANSWER;
        }

        foreach ($options as $option) {
            $t = strtolower(trim(strip_tags($option->option_text)));
            $optVal = ($t === 'صح' || $t === 'true' || $t === '1' || $t === 'صحيح') ? 'true' : 'false';
            if ($optVal === $answerValue) {
                return $this->stripOptionText($option->option_text);
            }
        }

        return $answerValue === 'true' ? 'صح' : 'خطأ';
    }

    private function studentMultipleChoiceSingleOrTrueFalse(QuizResponse $response, QuestionBank $question): string
    {
        $raw = $this->extractMcSingleOrTrueFalseRaw($response);

        $type = $question->questionType->name ?? '';
        if ($type === 'true_false') {
            return $this->formatStudentTrueFalseAnswer($raw, $question);
        }

        if ($raw === null) {
            return self::NO_ANSWER;
        }
        if (is_string($raw) && trim($raw) === '') {
            return self::NO_ANSWER;
        }

        if (is_bool($raw)) {
            return $this->formatStudentTrueFalseAnswer($raw, $question);
        }

        $selectedOptionId = is_array($raw) ? ($raw[0] ?? null) : $raw;
        if ($selectedOptionId === null || $selectedOptionId === '') {
            return self::NO_ANSWER;
        }

        if (is_numeric($selectedOptionId)) {
            $selectedOption = $question->options->find((int) $selectedOptionId) ?? $question->options->find($selectedOptionId);

            return $selectedOption ? $this->stripOptionText($selectedOption->option_text) : self::NO_ANSWER;
        }

        $selectedOption = $question->options->find($selectedOptionId);

        return $selectedOption ? $this->stripOptionText($selectedOption->option_text) : (string) $selectedOptionId;
    }

    private function studentMultipleChoiceMultiple(QuizResponse $response, QuestionBank $question): string
    {
        $selectedOptionIds = [];
        if (! empty($response->selected_option_ids)) {
            $selectedOptionIds = is_array($response->selected_option_ids)
                ? $response->selected_option_ids
                : [$response->selected_option_ids];
        } elseif (! empty($response->response_data)) {
            $responseData = $this->asArray($response->response_data);
            $answer = $responseData['answer'] ?? null;
            if ($answer !== null) {
                $selectedOptionIds = is_array($answer) ? $answer : [$answer];
            }
        }

        if ($selectedOptionIds === []) {
            return self::NO_ANSWER;
        }

        $texts = [];
        foreach ($question->options as $option) {
            if (in_array($option->id, $selectedOptionIds, false)) {
                $texts[] = $this->stripOptionText($option->option_text);
            }
        }

        return $texts === [] ? self::NO_ANSWER : implode(' ؛ ', $texts);
    }

    private function studentFillBlanks(QuizResponse $response, QuestionBank $question): string
    {
        $map = $this->fillBlanksAnswerMap($response);
        if ($map === []) {
            return self::NO_ANSWER;
        }

        $blankCount = substr_count((string) $question->question_text, '[[blank]]');
        if ($blankCount < 1) {
            $blankCount = substr_count((string) preg_replace('/_{3,}/', '[[blank]]', (string) $question->question_text), '[[blank]]');
        }
        if ($blankCount < 1) {
            $blankCount = count($map);
        }

        $parts = [];
        for ($i = 0; $i < $blankCount; $i++) {
            if (! array_key_exists($i, $map) || $map[$i] === '' || $map[$i] === null) {
                continue;
            }
            $parts[] = 'فراغ '.($i + 1).': '.trim((string) $map[$i]);
        }

        if ($parts === [] && $map !== []) {
            foreach ($map as $k => $v) {
                if ($v !== '' && $v !== null) {
                    $parts[] = (is_int($k) ? 'فراغ '.($k + 1).': ' : '').trim((string) $v);
                }
            }
        }

        return $parts === [] ? self::NO_ANSWER : implode(' ؛ ', $parts);
    }

    /**
     * @return array<int, mixed>
     */
    private function fillBlanksAnswerMap(QuizResponse $response): array
    {
        $raw = $response->response_data;
        if (! is_array($raw) || $raw === []) {
            return [];
        }
        if (isset($raw['answer']) && is_array($raw['answer'])) {
            return $this->normalizeNumericKeys($raw['answer']);
        }
        if (isset($raw['answers']) && is_array($raw['answers'])) {
            return $this->normalizeNumericKeys($raw['answers']);
        }

        $reserved = ['answer', 'answers', 'numeric_value'];
        $out = [];
        foreach ($raw as $k => $v) {
            if (in_array($k, $reserved, true)) {
                continue;
            }
            if (is_int($k)) {
                $out[$k] = $v;
            } elseif (is_string($k) && $k !== '' && ctype_digit($k)) {
                $out[(int) $k] = $v;
            }
        }

        return $out;
    }

    /**
     * @param  array<mixed, mixed>  $arr
     * @return array<int, mixed>
     */
    private function normalizeNumericKeys(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_int($k)) {
                $out[$k] = $v;
            } elseif (is_string($k) && ctype_digit($k)) {
                $out[(int) $k] = $v;
            }
        }

        return $out;
    }

    private function studentMatching(QuizResponse $response, QuestionBank $question): string
    {
        $rd = $this->asArray($response->response_data);
        if ($rd === []) {
            return self::NO_ANSWER;
        }

        $matchingAnswer = $rd['answer'] ?? $rd['pairs'] ?? $rd;
        if (! is_array($matchingAnswer)) {
            return self::NO_ANSWER;
        }

        $lines = [];
        if ($this->isMatchingPairsList($matchingAnswer)) {
            foreach ($matchingAnswer as $pair) {
                if (! is_array($pair)) {
                    continue;
                }
                $leftId = $pair['left'] ?? null;
                $rightValue = $pair['right'] ?? null;
                if ($leftId === null || $rightValue === null) {
                    continue;
                }
                $option = $question->options->find($leftId);
                if ($option) {
                    $lines[] = $this->stripOptionText($option->option_text).': '.(string) $rightValue;
                }
            }
        } else {
            foreach ($question->options as $option) {
                if (isset($matchingAnswer[$option->id])) {
                    $lines[] = $this->stripOptionText($option->option_text).': '.$matchingAnswer[$option->id];
                }
            }
        }

        return $lines === [] ? self::NO_ANSWER : implode(' ؛ ', $lines);
    }

    /**
     * @param  array<int, mixed>  $matchingAnswer
     */
    private function isMatchingPairsList(array $matchingAnswer): bool
    {
        if ($matchingAnswer === []) {
            return false;
        }
        $first = reset($matchingAnswer);

        return is_array($first) && (isset($first['left']) || isset($first['right']));
    }

    private function studentOrdering(QuizResponse $response, QuestionBank $question): string
    {
        $orderingAnswer = null;
        if (! empty($response->response_data)) {
            $responseData = $this->asArray($response->response_data);
            if (isset($responseData['answer'])) {
                $orderingAnswer = $responseData['answer'];
            } elseif (isset($responseData['sequence'])) {
                $orderingAnswer = $responseData['sequence'];
            } else {
                $orderingAnswer = $responseData;
            }
        }
        if (is_string($orderingAnswer)) {
            $decoded = json_decode($orderingAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $orderingAnswer = $decoded;
            }
        }
        if (! is_array($orderingAnswer) || $orderingAnswer === []) {
            return self::NO_ANSWER;
        }

        $parts = [];
        $i = 1;
        foreach ($orderingAnswer as $optionId) {
            $option = $question->options->find($optionId);
            if ($option) {
                $parts[] = $i.'. '.$this->stripOptionText($option->option_text);
                $i++;
            }
        }

        return $parts === [] ? self::NO_ANSWER : implode(' ؛ ', $parts);
    }

    private function studentDragDrop(QuizResponse $response, QuestionBank $question): string
    {
        $answerData = null;
        if (! empty($response->response_data)) {
            $responseData = $this->asArray($response->response_data);
            $answerData = $responseData['answer'] ?? $responseData;
        }
        if (! is_array($answerData) || $answerData === []) {
            return self::NO_ANSWER;
        }

        $lines = [];
        foreach ($answerData as $optionId => $feedbackValue) {
            if (! is_numeric($optionId)) {
                continue;
            }
            $option = $question->options->find($optionId);
            if ($option) {
                $lines[] = $this->stripOptionText($option->option_text).': '.(string) $feedbackValue;
            }
        }

        return $lines === [] ? self::NO_ANSWER : implode(' ؛ ', $lines);
    }

    private function studentNumericalOrCalculated(QuizResponse $response): string
    {
        $numericalAnswer = null;
        if (! empty($response->response_text)) {
            $numericalAnswer = trim((string) $response->response_text);
        } elseif (! empty($response->response_data)) {
            $responseData = $this->asArray($response->response_data);
            if (isset($responseData['answer'])) {
                $a = $responseData['answer'];
                $numericalAnswer = is_array($a)
                    ? (string) ($a['numeric_value'] ?? $a[0] ?? '')
                    : (string) $a;
            } elseif (isset($responseData['numeric_value'])) {
                $numericalAnswer = (string) $responseData['numeric_value'];
            } elseif (is_numeric($responseData)) {
                $numericalAnswer = (string) $responseData;
            }
        }

        if ($numericalAnswer === null || $numericalAnswer === '') {
            return self::NO_ANSWER;
        }

        return $numericalAnswer;
    }

    private function studentEssayShort(QuizResponse $response): string
    {
        $text = trim((string) ($response->response_text ?? ''));
        if ($text !== '') {
            return $text;
        }

        return $this->studentGenericFallback($response, $response->question);
    }

    private function studentGenericFallback(QuizResponse $response, ?QuestionBank $question): string
    {
        if (! empty($response->response_text)) {
            return trim((string) $response->response_text);
        }
        if (! empty($response->selected_option_ids) && $question) {
            $ids = is_array($response->selected_option_ids) ? $response->selected_option_ids : [$response->selected_option_ids];
            $texts = [];
            foreach ($question->options as $option) {
                if (in_array($option->id, $ids, false)) {
                    $texts[] = $this->stripOptionText($option->option_text);
                }
            }
            if ($texts !== []) {
                return implode(' ؛ ', $texts);
            }
        }
        if (! empty($response->response_data)) {
            $responseData = $this->asArray($response->response_data);
            $genericAnswer = $responseData['answer'] ?? $responseData;
            if (is_array($genericAnswer)) {
                return json_encode($genericAnswer, JSON_UNESCAPED_UNICODE);
            }

            return (string) $genericAnswer;
        }

        return self::NO_ANSWER;
    }

    private function correctOptionsJoined(QuestionBank $question): string
    {
        $opts = $question->getCorrectOptions();
        if ($opts->isEmpty()) {
            return $this->explanationOrDash($question);
        }

        return $opts->map(fn ($o) => $this->stripOptionText($o->option_text))->filter()->unique()->implode(' ؛ ');
    }

    private function correctFillBlanksPlain(QuestionBank $question): string
    {
        $normalized = preg_replace('/_{3,}/', '[[blank]]', (string) $question->question_text);
        $blankCount = substr_count((string) $normalized, '[[blank]]');
        $metadata = $question->metadata ?? [];
        $correctOptions = $question->options()->where('is_correct', true)->orderBy('option_order')->orderBy('id')->get();

        if ($correctOptions->isEmpty()) {
            $ca = $metadata['correct_answers'] ?? [];
            if (is_array($ca) && $ca !== []) {
                $lines = [];
                foreach ($ca as $idx => $val) {
                    $lines[] = 'فراغ '.(((int) $idx) + 1).': '.(string) $val;
                }

                return implode(' ؛ ', $lines);
            }

            return $this->explanationOrDash($question);
        }

        if ($blankCount < 1) {
            $blankCount = max(1, $correctOptions->pluck('option_order')->max() ?? 1);
        }

        $byOrder = $correctOptions->groupBy(fn ($o) => (int) $o->option_order);
        $lines = [];
        for ($i = 0; $i < $blankCount; $i++) {
            $alts = $byOrder->get($i + 1, collect());
            if ($alts->isEmpty() && $blankCount === 1 && $i === 0) {
                $alts = $correctOptions;
            }
            if ($alts->isNotEmpty()) {
                $texts = $alts->pluck('option_text')->map(fn ($t) => $this->stripOptionText((string) $t))->unique()->values()->all();
                $lines[] = 'فراغ '.($i + 1).': '.implode(' / ', $texts);
            }
        }

        return $lines === [] ? $this->explanationOrDash($question) : implode(' ؛ ', $lines);
    }

    private function correctOrderingPlain(QuestionBank $question): string
    {
        $ordered = $question->options()->orderBy('option_order')->get();
        if ($ordered->isEmpty()) {
            return $this->explanationOrDash($question);
        }
        $parts = [];
        $i = 1;
        foreach ($ordered as $opt) {
            $parts[] = $i.'. '.$this->stripOptionText($opt->option_text);
            $i++;
        }

        return implode(' ؛ ', $parts);
    }

    private function correctMatchingPlain(QuestionBank $question): string
    {
        $lines = [];
        foreach ($question->options as $option) {
            if ($option->feedback !== null && $option->feedback !== '') {
                $lines[] = $this->stripOptionText($option->option_text).' ← '.trim((string) $option->feedback);
            }
        }

        return $lines === [] ? $this->explanationOrDash($question) : implode(' ؛ ', $lines);
    }

    private function correctDragDropPlain(QuestionBank $question): string
    {
        $lines = [];
        foreach ($question->options as $option) {
            if ($option->feedback !== null && $option->feedback !== '') {
                $lines[] = $this->stripOptionText($option->option_text).': '.trim((string) $option->feedback);
            }
        }

        return $lines === [] ? $this->explanationOrDash($question) : implode(' ؛ ', $lines);
    }

    private function correctNumericalPlain(QuestionBank $question): string
    {
        $metadata = $question->metadata ?? [];
        $ans = $metadata['correct_answer'] ?? null;
        if ($ans === null || $ans === '') {
            return $this->explanationOrDash($question);
        }
        $tol = $metadata['tolerance'] ?? null;
        $suffix = ($tol !== null && $tol !== '' && (float) $tol > 0) ? ' (تسامح: '.$tol.')' : '';

        return (string) $ans.$suffix;
    }

    private function correctCalculatedPlain(QuestionBank $question): string
    {
        $metadata = $question->metadata ?? [];
        $ans = $metadata['correct_answer'] ?? null;
        if ($ans === null || $ans === '') {
            return $this->explanationOrDash($question);
        }
        $tol = $metadata['tolerance'] ?? null;
        $suffix = ($tol !== null && $tol !== '' && (float) $tol > 0) ? ' (تسامح: '.$tol.')' : '';

        return (string) $ans.$suffix;
    }

    private function correctShortAnswerPlain(QuestionBank $question): string
    {
        $metadata = $question->metadata ?? [];
        $ca = $metadata['correct_answers'] ?? [];
        if (is_array($ca) && $ca !== []) {
            return implode(' ؛ ', array_map('strval', $ca));
        }
        $fromOpts = $question->options()->where('is_correct', true)->pluck('option_text')->map(fn ($t) => $this->stripOptionText((string) $t))->filter()->unique()->values()->all();
        if ($fromOpts !== []) {
            return implode(' ؛ ', $fromOpts);
        }

        return $this->explanationOrDash($question);
    }

    private function correctEssayPlain(QuestionBank $question): string
    {
        $ex = trim((string) ($question->explanation ?? ''));

        return $ex !== '' ? 'مرجع / شرح: '.$ex : '—';
    }

    private function correctDefaultPlain(QuestionBank $question): string
    {
        return $this->explanationOrDash($question);
    }

    private function explanationOrDash(QuestionBank $question): string
    {
        $ex = trim((string) ($question->explanation ?? ''));

        return $ex !== '' ? $ex : '—';
    }

    private function stripOptionText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param  array<string, mixed>|mixed  $data
     * @return array<string, mixed>
     */
    private function asArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        if (is_string($data)) {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

}
