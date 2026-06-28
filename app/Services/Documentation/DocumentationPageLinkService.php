<?php

namespace App\Services\Documentation;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\DocumentationPage;
use App\Models\DocumentationPageLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentationPageLinkService
{
    /**
     * @return Collection<int, DocumentationPageLink>
     */
    public function attachFromCourse(Course $course, array $data, ?int $userId = null): Collection
    {
        $pages = DocumentationPage::query()
            ->published()
            ->whereIn('id', $data['documentation_page_ids'])
            ->get();

        if ($pages->count() !== count($data['documentation_page_ids'])) {
            throw ValidationException::withMessages([
                'documentation_page_ids' => 'يجب اختيار صفحات توثيق منشورة فقط.',
            ]);
        }

        $placement = $data['placement'];
        $created = collect();

        DB::transaction(function () use ($pages, $placement, $course, $data, $userId, &$created) {
            foreach ($pages as $page) {
                if ($placement === 'curriculum') {
                    $section = CourseSection::query()
                        ->where('course_id', $course->id)
                        ->findOrFail($data['section_id']);

                    $created->push($this->attachToCurriculum($page, $section, $userId));
                }

                if ($placement === 'reference') {
                    $created->push($this->attachReference($page, $course, $userId));
                }

                foreach ($data['additional_course_ids'] ?? [] as $courseId) {
                    $otherCourse = Course::query()->findOrFail($courseId);
                    $created->push($this->attachReference($page, $otherCourse, $userId));
                }

                foreach ($data['lesson_module_ids'] ?? [] as $moduleId) {
                    $lessonModule = $this->resolveLessonModule((int) $moduleId, $course, $data['additional_course_ids'] ?? []);
                    $created->push($this->attachReference($page, $lessonModule, $userId));
                }
            }
        });

        return $created->unique('id')->values();
    }

    /**
     * @return Collection<int, DocumentationPageLink>
     */
    public function attachFromDocumentationPage(DocumentationPage $page, array $data, ?int $userId = null): Collection
    {
        if (! $page->isPublished()) {
            throw ValidationException::withMessages([
                'documentation_page_id' => 'يجب أن تكون صفحة التوثيق منشورة.',
            ]);
        }

        $course = Course::query()->findOrFail($data['course_id']);

        return $this->attachFromCourse($course, [
            'documentation_page_ids' => [$page->id],
            'placement' => $data['placement'],
            'section_id' => $data['section_id'] ?? null,
            'additional_course_ids' => $data['additional_course_ids'] ?? [],
            'lesson_module_ids' => $data['lesson_module_ids'] ?? [],
        ], $userId);
    }

    public function attachReference(DocumentationPage $page, Model $linkable, ?int $userId = null): DocumentationPageLink
    {
        return DocumentationPageLink::firstOrCreate(
            [
                'documentation_page_id' => $page->id,
                'linkable_type' => $linkable->getMorphClass(),
                'linkable_id' => $linkable->id,
                'placement' => 'reference',
            ],
            [
                'sort_order' => $this->nextReferenceSortOrder($linkable),
                'created_by' => $userId,
            ]
        );
    }

    public function attachToCurriculum(DocumentationPage $page, CourseSection $section, ?int $userId = null): DocumentationPageLink
    {
        $existing = DocumentationPageLink::query()
            ->curriculum()
            ->where('documentation_page_id', $page->id)
            ->whereHas('courseModule', function ($query) use ($section) {
                $query->where('section_id', $section->id);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        $maxOrder = CourseModule::query()
            ->where('section_id', $section->id)
            ->max('sort_order') ?? 0;

        $module = CourseModule::create([
            'course_id' => $section->course_id,
            'section_id' => $section->id,
            'module_type' => 'documentation',
            'modulable_id' => $page->id,
            'modulable_type' => DocumentationPage::class,
            'title' => $page->title,
            'description' => $page->excerpt,
            'sort_order' => $maxOrder + 1,
            'is_visible' => true,
            'is_required' => false,
            'is_graded' => false,
            'completion_type' => 'auto',
        ]);

        return DocumentationPageLink::create([
            'documentation_page_id' => $page->id,
            'linkable_type' => Course::class,
            'linkable_id' => $section->course_id,
            'placement' => 'curriculum',
            'course_module_id' => $module->id,
            'sort_order' => 0,
            'created_by' => $userId,
        ]);
    }

    public function detach(DocumentationPageLink $link): void
    {
        DB::transaction(function () use ($link) {
            if ($link->course_module_id) {
                CourseModule::query()->whereKey($link->course_module_id)->delete();
            }

            $link->delete();
        });
    }

    protected function nextReferenceSortOrder(Model $linkable): int
    {
        $max = DocumentationPageLink::query()
            ->reference()
            ->where('linkable_type', $linkable->getMorphClass())
            ->where('linkable_id', $linkable->id)
            ->max('sort_order');

        return ((int) $max) + 1;
    }

    protected function resolveLessonModule(int $moduleId, Course $course, array $additionalCourseIds): CourseModule
    {
        $allowedCourseIds = collect([$course->id])
            ->merge($additionalCourseIds)
            ->unique()
            ->values()
            ->all();

        $module = CourseModule::query()
            ->where('module_type', 'lesson')
            ->whereIn('course_id', $allowedCourseIds)
            ->find($moduleId);

        if (! $module) {
            throw ValidationException::withMessages([
                'lesson_module_ids' => 'أحد الدروس المحددة غير صالح لهذا الكورس.',
            ]);
        }

        return $module;
    }
}
