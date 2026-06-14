<?php

namespace App\Services;

use App\Models\QuestionType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionBankExcelImportService
{
    public const SHEET_GUIDE = 'دليل';

    public const SHEET_QUESTIONS = 'أسئلة';

    /**
     * @return array<int, string> ordered legacy header labels (13 columns)
     */
    public static function legacyHeadersOrder(): array
    {
        return [
            'نوع السؤال',
            'نص السؤال',
            'الخيار 1',
            'الخيار 2',
            'الخيار 3',
            'الخيار 4',
            'الإجابة الصحيحة',
            'الدرجة',
            'الصعوبة',
            'الكورس',
            'الشرح',
            'العلامات',
            'اللغة البرمجية',
        ];
    }

    /**
     * @return array<int, string> ordered headers for the new template
     */
    public static function templateHeadersOrder(): array
    {
        return [
            'نوع السؤال',
            'نص السؤال',
            'اسم الدرس',
            'الخيار 1',
            'الخيار 2',
            'الخيار 3',
            'الخيار 4',
            'الخيار 5',
            'الخيار 6',
            'الإجابة الصحيحة',
            'إجابات مقبولة',
            'حساس لحالة الأحرف',
            'أزواج المطابقة',
            'الدرجة',
            'الصعوبة',
            'الكورس',
            'الشرح',
            'العلامات',
            'اللغة البرمجية',
            'هامش الخطأ',
            'الوحدة',
            'الحد الأدنى للكلمات',
            'الحد الأقصى للكلمات',
            'إجابة نموذجية',
            'معايير التقييم',
            'معادلة',
        ];
    }

    /**
     * Map Arabic header (trimmed) to internal field key.
     *
     * @return array<string, string>
     */
    public static function headerLabelToKeyMap(): array
    {
        return [
            'نوع السؤال' => 'question_type',
            'نص السؤال' => 'question_text',
            'اسم الدرس' => 'lesson_name',
            'الخيار 1' => 'option_1',
            'الخيار 2' => 'option_2',
            'الخيار 3' => 'option_3',
            'الخيار 4' => 'option_4',
            'الخيار 5' => 'option_5',
            'الخيار 6' => 'option_6',
            'الإجابة الصحيحة' => 'correct_answer',
            'إجابات مقبولة' => 'accepted_answers',
            'حساس لحالة الأحرف' => 'case_sensitive',
            'أزواج المطابقة' => 'matching_pairs_raw',
            'الدرجة' => 'points',
            'الصعوبة' => 'difficulty',
            'الكورس' => 'course',
            'الشرح' => 'explanation',
            'العلامات' => 'tags',
            'اللغة البرمجية' => 'language',
            'هامش الخطأ' => 'tolerance',
            'الوحدة' => 'unit',
            'الحد الأدنى للكلمات' => 'min_words',
            'الحد الأقصى للكلمات' => 'max_words',
            'إجابة نموذجية' => 'model_answer',
            'معايير التقييم' => 'grading_criteria',
            'معادلة' => 'formula',
        ];
    }

    public function getQuestionsWorksheet(Spreadsheet $spreadsheet): Worksheet
    {
        if ($spreadsheet->sheetNameExists(self::SHEET_QUESTIONS)) {
            return $spreadsheet->getSheetByName(self::SHEET_QUESTIONS);
        }
        if ($spreadsheet->getSheetCount() > 1) {
            return $spreadsheet->getSheet(1);
        }

        return $spreadsheet->getActiveSheet();
    }

    /**
     * True if first cells match the old 13-column template (no extra columns required).
     */
    public function isLegacyHeaderRow(array $headerRow): bool
    {
        $legacy = self::legacyHeadersOrder();
        $normalized = array_map(fn ($h) => trim((string) ($h ?? '')), $headerRow);
        if (count($normalized) < count($legacy)) {
            return false;
        }
        foreach ($legacy as $i => $label) {
            if (($normalized[$i] ?? '') !== $label) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRowFromLegacy(array $row, int $rowNumber): array
    {
        $row = array_pad($row, 13, '');
        $base = self::emptyQuestionDataRow($rowNumber);
        $base['question_type'] = trim((string) ($row[0] ?? ''));
        $base['question_text'] = trim((string) ($row[1] ?? ''));
        for ($i = 1; $i <= 4; $i++) {
            $base['option_'.$i] = trim((string) ($row[$i + 1] ?? ''));
        }
        $base['correct_answer'] = trim((string) ($row[6] ?? ''));
        $base['points'] = trim((string) ($row[7] ?? '1')) ?: '1';
        $base['difficulty'] = trim((string) ($row[8] ?? 'medium')) ?: 'medium';
        $base['course'] = trim((string) ($row[9] ?? ''));
        $base['explanation'] = trim((string) ($row[10] ?? ''));
        $base['tags'] = trim((string) ($row[11] ?? ''));
        $base['language'] = trim((string) ($row[12] ?? ''));

        return $base;
    }

    /**
     * @param  array<int, string|null>  $headerRow
     * @param  array<int, mixed>  $row
     * @return array<string, mixed>
     */
    public function buildRowFromMapped(array $headerRow, array $row, int $rowNumber): array
    {
        $map = self::headerLabelToKeyMap();
        $out = self::emptyQuestionDataRow($rowNumber);

        foreach ($headerRow as $colIndex => $headerCell) {
            $label = trim((string) ($headerCell ?? ''));
            if ($label === '' || ! isset($map[$label])) {
                continue;
            }
            $key = $map[$label];
            $out[$key] = trim((string) ($row[$colIndex] ?? ''));
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyQuestionDataRow(int $rowNumber): array
    {
        return [
            'row_number' => $rowNumber,
            'question_type' => '',
            'question_text' => '',
            'lesson_name' => '',
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
            'points' => '1',
            'difficulty' => 'medium',
            'course' => '',
            'explanation' => '',
            'tags' => '',
            'language' => '',
            'tolerance' => '',
            'unit' => '',
            'min_words' => '',
            'max_words' => '',
            'model_answer' => '',
            'grading_criteria' => '',
            'formula' => '',
        ];
    }

    public function resolveQuestionType(string $label, $types): ?QuestionType
    {
        $label = trim($label);
        if ($label === '') {
            return null;
        }

        foreach ($types as $type) {
            if ($type->display_name === $label || $type->name === $label) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function validateRowForType(array $q, ?QuestionType $type, bool $courseSatisfied = false): array
    {
        $errors = [];
        if (empty($q['question_type'])) {
            $errors[] = 'نوع السؤال مطلوب';
        }
        if (empty($q['question_text'])) {
            $errors[] = 'نص السؤال مطلوب';
        }
        if (empty($q['course']) && ! $courseSatisfied) {
            $errors[] = 'اسم الكورس مطلوب';
        }
        if (! $type) {
            $errors[] = 'نوع السؤال غير معروف في النظام';

            return $errors;
        }

        $name = $type->name;

        switch ($name) {
            case 'multiple_choice_single':
            case 'multiple_choice_multiple':
                $opts = $this->nonEmptyOptions($q, 6);
                if (count($opts) < 2) {
                    $errors[] = 'يلزم خياران على الأقل للاختيار من متعدد';
                }
                if (empty($q['correct_answer'])) {
                    $errors[] = 'الإجابة الصحيحة مطلوبة (رقم الخيار أو أكثر مفصولة بفاصلة)';
                }
                break;

            case 'true_false':
                if (empty($q['correct_answer'])) {
                    $errors[] = 'أدخل الإجابة الصحيحة: true أو false أو 1 أو 2 أو صح أو خطأ';
                } elseif ($this->normalizeTrueFalseAnswer($q['correct_answer']) === null) {
                    $errors[] = 'قيمة غير صالحة للصح/خطأ';
                }
                break;

            case 'short_answer':
            case 'fill_blanks':
                $accepted = $this->parseAcceptedAnswersList($q);
                if (count($accepted) === 0) {
                    $errors[] = 'أدخل إجابات مقبولة (عمود إجابات مقبولة أو الإجابة الصحيحة لإجابة واحدة)';
                }
                break;

            case 'matching':
                $pairs = $this->parseMatchingPairsRaw($q['matching_pairs_raw'] ?? '');
                if (count($pairs) < 1) {
                    $errors[] = 'عمود أزواج المطابقة مطلوب بالنسق: سؤال1||إجابة1;;;سؤال2||إجابة2';
                }
                break;

            case 'ordering':
                $opts = $this->nonEmptyOptions($q, 6);
                if (count($opts) < 2) {
                    $errors[] = 'يلزم عنصران على الأقل في أعمدة الخيار للترتيب (بالترتيب الصحيح)';
                }
                break;

            case 'numerical':
            case 'calculated':
                if (empty($q['correct_answer']) || ! is_numeric(str_replace(',', '.', $q['correct_answer']))) {
                    $errors[] = 'الإجابة الصحيحة رقمية مطلوبة';
                }
                break;

            case 'essay':
                // لا إجابة آلية مطلوبة
                break;

            default:
                $errors[] = 'نوع السؤال غير مدعوم في الاستيراد: '.$name;
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    public function parseAcceptedAnswersList(array $q): array
    {
        $raw = trim((string) ($q['accepted_answers'] ?? ''));
        if ($raw !== '') {
            $parts = preg_split('/\|+/', $raw);

            return array_values(array_filter(array_map('trim', $parts), fn ($s) => $s !== ''));
        }
        $single = trim((string) ($q['correct_answer'] ?? ''));

        return $single !== '' ? [$single] : [];
    }

    public function parseCaseSensitiveFlag(string $raw): bool
    {
        $v = mb_strtolower(trim($raw));

        return in_array($v, ['نعم', 'yes', '1', 'true', 'y'], true);
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function parseMatchingPairsRaw(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $pairs = [];
        foreach (explode(';;;', $raw) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
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
    public function nonEmptyOptions(array $q, int $max = 6): array
    {
        $out = [];
        for ($i = 1; $i <= $max; $i++) {
            $t = trim((string) ($q['option_'.$i] ?? ''));
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }

    public function normalizeTrueFalseAnswer(string $raw): ?string
    {
        $s = mb_strtolower(trim($raw));
        if (in_array($s, ['true', '1', 'صح', 'صحيح', 'yes', 'نعم'], true)) {
            return 'true';
        }
        if (in_array($s, ['false', '2', '0', 'خطأ', 'no', 'لا'], true)) {
            return 'false';
        }

        return null;
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public function matchingPairsForCreate(array $pairs): array
    {
        $assoc = [];
        $i = 1;
        foreach ($pairs as $pair) {
            if (($pair['question'] ?? '') === '' || ($pair['answer'] ?? '') === '') {
                continue;
            }
            $assoc[$i] = ['question' => $pair['question'], 'answer' => $pair['answer']];
            $i++;
        }

        return $assoc;
    }
}
