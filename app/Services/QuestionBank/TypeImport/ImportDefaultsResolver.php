<?php

namespace App\Services\QuestionBank\TypeImport;

use App\Models\Course;
use App\Models\ProgrammingLanguage;

class ImportDefaultsResolver
{
    private ?array $courseTitlesById = null;

    private ?array $languageLabelsById = null;

    /**
     * @param  array<string, mixed>  $row
     * @return array{row: array<string, mixed>, course_from_default: bool, language_from_default: bool}
     */
    public function apply(array $row, ?int $defaultCourseId = null, ?int $defaultLanguageId = null): array
    {
        $courseFromDefault = false;
        $languageFromDefault = false;

        if (trim((string) ($row['course'] ?? '')) === '' && $defaultCourseId) {
            $title = $this->courseTitle($defaultCourseId);
            if ($title !== null) {
                $row['course'] = $title;
                $courseFromDefault = true;
            }
        }

        if (trim((string) ($row['language'] ?? '')) === '' && $defaultLanguageId) {
            $label = $this->languageLabel($defaultLanguageId);
            if ($label !== null) {
                $row['language'] = $label;
                $languageFromDefault = true;
            }
        }

        return [
            'row' => $row,
            'course_from_default' => $courseFromDefault,
            'language_from_default' => $languageFromDefault,
        ];
    }

    public function hasValidDefaultCourse(?int $defaultCourseId): bool
    {
        return $defaultCourseId !== null && $this->courseTitle($defaultCourseId) !== null;
    }

    private function courseTitle(int $courseId): ?string
    {
        if ($this->courseTitlesById === null) {
            $this->courseTitlesById = [];
            foreach (Course::where('is_published', true)->get(['id', 'title']) as $course) {
                $this->courseTitlesById[$course->id] = $course->title;
            }
        }

        return $this->courseTitlesById[$courseId] ?? null;
    }

    private function languageLabel(int $languageId): ?string
    {
        if ($this->languageLabelsById === null) {
            $this->languageLabelsById = [];
            foreach (ProgrammingLanguage::active()->get(['id', 'name', 'display_name']) as $lang) {
                $this->languageLabelsById[$lang->id] = $lang->display_name ?? $lang->name;
            }
        }

        return $this->languageLabelsById[$languageId] ?? null;
    }
}
