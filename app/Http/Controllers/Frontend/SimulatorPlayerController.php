<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\LessonSimulator;
use App\Services\Simulator\SimulatorAccessService;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Support\SimulatorKit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class SimulatorPlayerController extends Controller
{
    public function __construct(
        private SimulatorAccessService $accessService,
        private SimulatorBundleStorage $bundleStorage,
    ) {}

    public function show(Request $request, string $slug)
    {
        $simulator = LessonSimulator::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $module = null;
        if ($request->filled('module')) {
            $module = CourseModule::query()->find((int) $request->get('module'));
        }

        $user = auth()->user();
        if (! $user) {
            abort(403, 'يجب تسجيل الدخول للوصول إلى المحاكاة.');
        }

        $access = $this->accessService->canAccess($user, $simulator, $module);
        if (! $access['allowed']) {
            abort(403, $access['reason'] ?? 'غير مسموح بالوصول.');
        }

        if ($simulator->isHtmlBundle()) {
            $playParams = ['slug' => $slug];
            if ($module) {
                $playParams['module'] = $module->id;
            }

            return view('simulator.html-bundle', [
                'simulator' => $simulator,
                'playUrl' => route('frontend.simulator.play', $playParams),
                'hasContent' => $simulator->hasPlayableContent(),
                'generationMeta' => $simulator->ai_generation_meta ?? [],
                'isPreview' => false,
            ]);
        }

        $spec = $simulator->spec_json ?? [];
        $widgets = collect(config('simulator.widgets', []))
            ->filter(fn ($w, $key) => $this->specUsesWidget($spec, $key))
            ->pluck('script')
            ->values()
            ->all();

        return view('simulator.show', [
            'simulator' => $simulator,
            'spec' => $spec,
            'widgetScripts' => $widgets,
            'generationMeta' => [],
            'hasSections' => count($spec['sections'] ?? []) > 0,
        ]);
    }

    public function play(Request $request, string $slug)
    {
        $simulator = $this->authorizedSimulator($request, $slug);
        if (! $simulator->isHtmlBundle() || ! $simulator->hasPlayableContent()) {
            abort(404);
        }

        $assetsBase = SimulatorKit::bundleAssetsBaseUrl($slug);
        $html = $this->bundleStorage->playHtml($simulator->slug, $assetsBase);
        if (! $html) {
            abort(404);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function asset(Request $request, string $slug, string $file)
    {
        $simulator = $this->authorizedSimulator($request, $slug);
        if (! $simulator->isHtmlBundle()) {
            abort(404);
        }

        $path = $this->bundleStorage->absolutePath($simulator->slug, $file);
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
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function authorizedSimulator(Request $request, string $slug): LessonSimulator
    {
        $simulator = LessonSimulator::query()->where('slug', $slug)->firstOrFail();

        $module = null;
        if ($request->filled('module')) {
            $module = CourseModule::query()->find((int) $request->get('module'));
        }

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($request->boolean('admin_preview') && $user->hasRole('admin')) {
            return $simulator;
        }

        $access = $this->accessService->canAccess($user, $simulator, $module);
        if (! $access['allowed']) {
            abort(403, $access['reason'] ?? 'غير مسموح.');
        }

        return $simulator;
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function specUsesWidget(array $spec, string $widgetKey): bool
    {
        foreach ($spec['sections'] ?? [] as $section) {
            if (($section['type'] ?? '') === 'interactive' && ($section['widget'] ?? '') === $widgetKey) {
                return true;
            }
        }

        return $widgetKey === 'array_playground';
    }
}
