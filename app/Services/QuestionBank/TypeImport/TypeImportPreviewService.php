<?php

namespace App\Services\QuestionBank\TypeImport;

use App\Models\Course;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Services\QuestionBankExcelImportService;

class TypeImportPreviewService
{
    public function __construct(
        private readonly QuestionBankExcelImportService $excel = new QuestionBankExcelImportService
    ) {}

    /**
     * @param  list<CanonicalImportRow>  $rows
     * @return array{
     *     data: list<array<string, mixed>>,
     *     errors: list<array{row: int, errors: list<string>}>,
     *     type_mapping: array<string, int>,
     *     course_mapping: array<string, int>,
     *     language_mapping: array<string, int>,
     *     total_rows: int,
     *     valid_rows: int
     * }
     */
    public function buildPreview(
        array $rows,
        QuestionType $questionType,
        ?int $defaultCourseId = null,
        ?int $defaultLanguageId = null
    ): array {
        $resolver = new ImportDefaultsResolver;
        $courseSatisfied = $resolver->hasValidDefaultCourse($defaultCourseId);
        $parsedData = [];
        $errors = [];

        foreach ($rows as $row) {
            $applied = $resolver->apply($row->toArray(), $defaultCourseId, $defaultLanguageId);
            $questionData = $applied['row'];
            $questionData['course_from_default'] = $applied['course_from_default'];
            $questionData['language_from_default'] = $applied['language_from_default'];

            $rowErrors = $this->excel->validateRowForType($questionData, $questionType, $courseSatisfied);

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $questionData['row_number'],
                    'errors' => $rowErrors,
                ];
            }

            $parsedData[] = $questionData;
        }

        $errorRowIds = array_unique(array_column($errors, 'row'));
        $validRows = count(array_filter(
            $parsedData,
            fn ($r) => ! in_array($r['row_number'], $errorRowIds, true)
        ));

        return [
            'data' => $parsedData,
            'errors' => $errors,
            'type_mapping' => $this->buildTypeMapping(),
            'course_mapping' => $this->buildCourseMapping(),
            'language_mapping' => $this->buildLanguageMapping(),
            'total_rows' => count($parsedData),
            'valid_rows' => $validRows,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function buildTypeMapping(): array
    {
        $mapping = [];
        foreach (QuestionType::where('is_active', true)->get() as $type) {
            $mapping[$type->display_name] = $type->id;
            $mapping[$type->name] = $type->id;
        }

        return $mapping;
    }

    /**
     * @return array<string, int>
     */
    public function buildCourseMapping(): array
    {
        $mapping = [];
        foreach (Course::where('is_published', true)->get() as $course) {
            $mapping[$course->title] = $course->id;
        }

        return $mapping;
    }

    /**
     * @return array<string, int>
     */
    public function buildLanguageMapping(): array
    {
        $mapping = [];
        foreach (ProgrammingLanguage::active()->get() as $lang) {
            $mapping[$lang->name] = $lang->id;
            $mapping[$lang->display_name] = $lang->id;
        }

        return $mapping;
    }

    /**
     * @param  list<array<string, mixed>>  $questionsData
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function processRows(
        array $questionsData,
        QuestionType $questionType,
        QuestionBankImportPersister $persister,
        ?int $defaultLanguageId = null,
        ?int $defaultCourseId = null
    ): array {
        $resolver = new ImportDefaultsResolver;
        $typeMapping = [];
        foreach (QuestionType::where('is_active', true)->get() as $type) {
            $typeMapping[$type->display_name] = $type;
            $typeMapping[$type->name] = $type;
        }

        $courseMapping = $this->buildCourseMapping();
        $languageMapping = $this->buildLanguageMapping();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($questionsData as $index => $questionData) {
            try {
                $applied = $resolver->apply($questionData, $defaultCourseId, $defaultLanguageId);
                $questionData = $applied['row'];

                $resolvedType = $typeMapping[$questionData['question_type'] ?? ''] ?? null;
                if (! $resolvedType || $resolvedType->id !== $questionType->id) {
                    $skipped++;
                    $errors[] = 'السطر '.($index + 1).': نوع السؤال غير صحيح';

                    continue;
                }

                if (empty($questionData['course'])) {
                    $skipped++;
                    $errors[] = 'السطر '.($index + 1).': اسم الكورس مطلوب';

                    continue;
                }

                $courseId = $courseMapping[$questionData['course']] ?? null;
                if (! $courseId) {
                    $skipped++;
                    $errors[] = 'السطر '.($index + 1).": الكورس '{$questionData['course']}' غير موجود في النظام";

                    continue;
                }

                $persister->persist(
                    $questionData,
                    $questionType,
                    $courseId,
                    $languageMapping,
                    $defaultLanguageId
                );

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = 'السطر '.($index + 1).': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
