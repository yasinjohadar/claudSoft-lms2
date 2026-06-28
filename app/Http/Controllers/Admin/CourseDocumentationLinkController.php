<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachDocumentationPageRequest;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\DocumentationPageLink;
use App\Services\Documentation\DocumentationPageLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourseDocumentationLinkController extends Controller
{
    public function __construct(
        protected DocumentationPageLinkService $linkService
    ) {}

    public function categories(Request $request): JsonResponse
    {
        $kind = trim((string) $request->query('kind', ''));

        $categories = DocumentationCategory::query()
            ->active()
            ->when($kind !== '', fn ($query) => $query->where('kind', $kind))
            ->ordered()
            ->get(['id', 'name', 'slug', 'kind'])
            ->map(fn (DocumentationCategory $category) => [
                'id' => $category->id,
                'text' => $category->name,
                'slug' => $category->slug,
                'kind' => $category->kind,
                'kind_label' => $category->kind === 'technology' ? 'تقنية' : 'قسم',
            ]);

        return response()->json(['results' => $categories]);
    }

    public function searchPages(Course $course, Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $kind = trim((string) $request->query('kind', ''));

        if (empty($categoryId) && $kind === '') {
            return response()->json([
                'results' => [],
                'message' => 'اختر التصنيف أو النوع أولاً.',
            ]);
        }

        $pages = DocumentationPage::query()
            ->published()
            ->with('category:id,name,slug,kind')
            ->when($categoryId, fn ($query) => $query->where('documentation_category_id', $categoryId))
            ->when(! $categoryId && $kind !== '', function ($query) use ($kind) {
                $query->whereHas('category', fn ($cat) => $cat->where('kind', $kind));
            })
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->ordered()
            ->limit(50)
            ->get()
            ->map(fn (DocumentationPage $page) => [
                'id' => $page->id,
                'text' => $page->title,
                'slug' => $page->slug,
                'category' => $page->category?->name,
                'category_kind' => $page->category?->kind,
            ]);

        return response()->json(['results' => $pages]);
    }

    public function sections(Course $course): JsonResponse
    {
        $sections = CourseSection::query()
            ->where('course_id', $course->id)
            ->orderBy('order_index')
            ->get(['id', 'title'])
            ->map(fn (CourseSection $section) => [
                'id' => $section->id,
                'text' => $section->title,
            ]);

        return response()->json(['results' => $sections]);
    }

    public function lessonModules(Course $course): JsonResponse
    {
        $modules = CourseModule::query()
            ->where('course_id', $course->id)
            ->where('module_type', 'lesson')
            ->orderBy('section_id')
            ->orderBy('sort_order')
            ->get(['id', 'title', 'section_id'])
            ->map(fn (CourseModule $module) => [
                'id' => $module->id,
                'text' => $module->title,
                'section_id' => $module->section_id,
            ]);

        return response()->json(['results' => $modules]);
    }

    public function store(AttachDocumentationPageRequest $request, Course $course): RedirectResponse|JsonResponse
    {
        try {
            $links = $this->linkService->attachFromCourse(
                $course,
                $request->validated(),
                $request->user()?->id
            );

            $message = 'تم ربط '.$links->count().' توثيق/روابط بنجاح.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('courses.show', $course->id),
                ]);
            }

            return redirect()
                ->route('courses.show', $course->id)
                ->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر ربط التوثيق.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر ربط التوثيق: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'تعذّر ربط التوثيق: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy(DocumentationPageLink $documentationLink): RedirectResponse
    {
        $courseId = null;

        if ($documentationLink->linkable_type === Course::class) {
            $courseId = $documentationLink->linkable_id;
        } elseif ($documentationLink->course_module_id) {
            $courseId = CourseModule::query()
                ->whereKey($documentationLink->course_module_id)
                ->value('course_id');
        }

        try {
            $this->linkService->detach($documentationLink);

            return redirect()
                ->back()
                ->with('success', 'تم إزالة ربط التوثيق.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'تعذّر إزالة الربط: '.$e->getMessage());
        }
    }
}
