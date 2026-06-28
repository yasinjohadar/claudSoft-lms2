<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonSimulator;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Services\Simulator\SimulatorBundleValidator;
use App\Services\Simulator\SimulatorGlobalAssets;
use App\Services\Simulator\SimulatorSpecValidator;
use App\Services\Simulator\SimulatorTopicRegistry;
use App\Support\SimulatorKit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LessonSimulatorController extends Controller
{
    public function __construct(
        private SimulatorSpecValidator $validator,
        private SimulatorBundleValidator $bundleValidator,
        private SimulatorBundleStorage $bundleStorage,
        private SimulatorGlobalAssets $globalAssets,
    ) {}

    public function index(Request $request)
    {
        $query = LessonSimulator::query()->with('creator')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('topic_key')) {
            $query->where('topic_key', $request->get('topic_key'));
        }
        if ($request->filled('course_id')) {
            $courseId = (int) $request->get('course_id');
            $query->where(function ($q) use ($courseId) {
                $q->whereHas('courses', fn ($c) => $c->where('courses.id', $courseId))
                    ->orWhereHas('courseModules', fn ($m) => $m->where('course_id', $courseId));
            });
        }

        $simulators = $query->paginate(20)->withQueryString();
        $topics = SimulatorTopicRegistry::groupedForSelect();
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $statuses = LessonSimulator::STATUSES;

        return view('admin.lesson-simulators.index', compact('simulators', 'topics', 'courses', 'statuses'));
    }

    public function create()
    {
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $statuses = LessonSimulator::STATUSES;
        $bundle = ['html' => '', 'css' => '', 'js' => ''];

        return view('admin.lesson-simulators.create', compact('courses', 'statuses', 'bundle'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBundleRequest($request);

        $bundle = [
            'html' => $validated['bundle_html'],
            'css' => $validated['bundle_css'] ?? '',
            'js' => $validated['bundle_js'] ?? '',
        ];

        $validation = $this->bundleValidator->validateManual($bundle);
        if (! $validation['valid']) {
            return back()->withInput()->withErrors(['bundle_html' => implode(' ', $validation['errors'])]);
        }

        $slug = $validated['slug'] ?? LessonSimulator::uniqueSlug($validated['title']);

        $simulator = LessonSimulator::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'topic_key' => 'custom.'.Str::slug($validated['title']) ?: 'manual',
            'render_mode' => 'html_bundle',
            'spec_json' => ['meta' => [], 'sections' => []],
            'spec_version' => config('simulator.spec_version', '1.0'),
            'status' => $validated['status'] ?? 'published',
            'languages' => ['html', 'css', 'javascript'],
            'created_by' => Auth::id(),
        ]);

        $path = $this->bundleStorage->save($simulator->slug, $bundle);
        $simulator->update(['bundle_path' => $path]);

        if (! empty($validated['course_ids'])) {
            $simulator->courses()->sync($validated['course_ids']);
        }

        return redirect()
            ->route('admin.lesson-simulators.edit', $simulator)
            ->with('success', 'تم حفظ المحاكاة — يمكنك المعاينة أو التعديل.');
    }

    public function edit(LessonSimulator $lessonSimulator)
    {
        $lessonSimulator->load('courses');
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $statuses = LessonSimulator::STATUSES;

        if (! $lessonSimulator->isHtmlBundle()) {
            $topics = SimulatorTopicRegistry::groupedForSelect();

            return view('admin.lesson-simulators.edit-legacy', compact('lessonSimulator', 'topics', 'courses', 'statuses'));
        }

        $bundle = $this->bundleStorage->load($lessonSimulator->slug) ?? ['html' => '', 'css' => '', 'js' => ''];

        return view('admin.lesson-simulators.edit', compact('lessonSimulator', 'courses', 'statuses', 'bundle'));
    }

    public function update(Request $request, LessonSimulator $lessonSimulator)
    {
        if (! $lessonSimulator->isHtmlBundle()) {
            return $this->updateLegacySpec($request, $lessonSimulator);
        }

        $validated = $this->validateBundleRequest($request, $lessonSimulator->id);

        $bundle = [
            'html' => $validated['bundle_html'],
            'css' => $validated['bundle_css'] ?? '',
            'js' => $validated['bundle_js'] ?? '',
        ];

        $validation = $this->bundleValidator->validateManual($bundle);
        if (! $validation['valid']) {
            return back()->withInput()->withErrors(['bundle_html' => implode(' ', $validation['errors'])]);
        }

        $oldSlug = $lessonSimulator->slug;
        $newSlug = $validated['slug'] ?? $lessonSimulator->slug;

        $lessonSimulator->update([
            'title' => $validated['title'],
            'slug' => $newSlug,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'published',
        ]);

        if ($oldSlug !== $newSlug && $this->bundleStorage->exists($oldSlug)) {
            $this->bundleStorage->delete($oldSlug);
        }

        $path = $this->bundleStorage->save($newSlug, $bundle);
        $lessonSimulator->update(['bundle_path' => $path]);

        $lessonSimulator->courses()->sync($validated['course_ids'] ?? []);

        return redirect()
            ->route('admin.lesson-simulators.edit', $lessonSimulator)
            ->with('success', 'تم تحديث المحاكاة.');
    }

    public function destroy(LessonSimulator $lessonSimulator)
    {
        $lessonSimulator->courses()->detach();
        $lessonSimulator->delete();

        return redirect()
            ->route('admin.lesson-simulators.index')
            ->with('success', 'تم حذف المحاكاة.');
    }

    public function globalAssets()
    {
        $assets = $this->globalAssets->load();

        return view('admin.lesson-simulators.global-assets', [
            'globalCss' => $assets['css'],
            'globalJs' => $assets['js'],
            'cssUrl' => $this->globalAssets->cssUrl(),
            'jsUrl' => $this->globalAssets->jsUrl(),
        ]);
    }

    public function updateGlobalAssets(Request $request)
    {
        $validated = $request->validate([
            'global_css' => 'nullable|string',
            'global_js' => 'nullable|string',
        ]);

        $this->globalAssets->save(
            $validated['global_css'] ?? '',
            $validated['global_js'] ?? '',
        );

        return redirect()
            ->route('admin.lesson-simulators.global-assets')
            ->with('success', 'تم حفظ الملفات المركزية — ستُطبَّق على جميع المحاكيات.');
    }

    public function preview(LessonSimulator $lessonSimulator)
    {
        if (! $lessonSimulator->isHtmlBundle() || ! $lessonSimulator->hasPlayableContent()) {
            return view('simulator.html-bundle', [
                'simulator' => $lessonSimulator,
                'playUrl' => '',
                'hasContent' => false,
                'generationMeta' => [],
                'isPreview' => true,
            ]);
        }

        $html = $this->bundleStorage->playHtml($lessonSimulator->slug);
        if (! $html) {
            abort(404);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function playDocument(LessonSimulator $lessonSimulator)
    {
        if (! $lessonSimulator->isHtmlBundle() || ! $lessonSimulator->hasPlayableContent()) {
            abort(404);
        }

        $html = $this->bundleStorage->playHtml($lessonSimulator->slug);
        if (! $html) {
            abort(404);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function playAsset(LessonSimulator $lessonSimulator, string $file)
    {
        if (! $lessonSimulator->isHtmlBundle()) {
            abort(404);
        }

        $path = $this->bundleStorage->absolutePath($lessonSimulator->slug, $file);
        if (! $path) {
            abort(404);
        }

        $mime = match ($file) {
            'page.css' => 'text/css',
            'simulator.js' => 'application/javascript',
            default => File::mimeType($path) ?: 'application/octet-stream',
        };

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=60',
        ]);
    }

    public function previewBundle(Request $request)
    {
        $validated = $request->validate([
            'bundle_html' => 'required|string',
            'bundle_css' => 'nullable|string',
            'bundle_js' => 'nullable|string',
        ]);

        $global = $this->globalAssets->load();

        $html = SimulatorKit::buildInlinePreviewDocument(
            $validated['bundle_html'],
            $validated['bundle_css'] ?? '',
            $validated['bundle_js'] ?? '',
            $global['css'],
            $global['js'],
        );

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBundleRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:lesson_simulators,slug'.($ignoreId ? ",{$ignoreId}" : ''),
            'description' => 'nullable|string',
            'status' => 'required|in:'.implode(',', array_keys(LessonSimulator::STATUSES)),
            'bundle_html' => 'required|string',
            'bundle_css' => 'nullable|string',
            'bundle_js' => 'nullable|string',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);
    }

    private function updateLegacySpec(Request $request, LessonSimulator $lessonSimulator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:lesson_simulators,slug,'.$lessonSimulator->id,
            'description' => 'nullable|string',
            'topic_key' => 'required|string|max:128',
            'status' => 'required|in:'.implode(',', array_keys(LessonSimulator::STATUSES)),
            'spec_json' => 'required|json',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $spec = json_decode($validated['spec_json'], true);
        $validation = $this->validator->validate($spec);
        if (! $validation['valid']) {
            return back()->withInput()->withErrors(['spec_json' => implode(' ', $validation['errors'])]);
        }

        $lessonSimulator->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? $lessonSimulator->slug,
            'description' => $validated['description'] ?? null,
            'topic_key' => $validated['topic_key'],
            'spec_json' => $spec,
            'status' => $validated['status'] ?? 'published',
            'languages' => $spec['meta']['languages'] ?? [],
        ]);

        $lessonSimulator->courses()->sync($validated['course_ids'] ?? []);

        return redirect()
            ->route('admin.lesson-simulators.edit', $lessonSimulator)
            ->with('success', 'تم تحديث المحاكاة.');
    }
}
