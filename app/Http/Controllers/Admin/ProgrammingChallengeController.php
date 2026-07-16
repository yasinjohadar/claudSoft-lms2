<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeFile;
use App\Models\ProgrammingChallengeTestCase;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgrammingChallengeController extends Controller
{
    public function index()
    {
        $challenges = ProgrammingChallenge::with([
                'creator',
                'languages',
                'targets.course',
                'targets.group',
            ])
            ->withCount([
                'attempts as submitted_attempts_count' => function ($q) {
                    $q->whereIn('status', ['submitted', 'graded', 'returned']);
                },
                'attempts as pending_attempts_count' => function ($q) {
                    $q->where('status', 'submitted')
                        ->where(function ($inner) {
                            $inner->whereNull('grade_status')->orWhere('grade_status', 'pending');
                        });
                },
                'targets',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => ProgrammingChallenge::count(),
            'published' => ProgrammingChallenge::where('is_published', true)->count(),
            'pending' => \App\Models\ProgrammingChallengeAttempt::pendingGrading()->count(),
        ];

        return view('admin.pages.programming-challenges.index', compact('challenges', 'stats'));
    }

    public function create(Request $request)
    {
        $sectionId = $request->get('section_id');
        $section = $sectionId ? CourseSection::with('course')->findOrFail($sectionId) : null;
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.pages.programming-challenges.create', compact('section', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'nullable|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'challenge_type' => 'required|in:web_sandbox,code_runner',
            'grading_mode' => 'required|in:manual,auto,hybrid',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'max_score' => 'nullable|numeric|min:0',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'attempts_allowed' => 'nullable|integer|min:1',
            'targets' => 'nullable|array',
            'targets.*.course_id' => 'required_with:targets|exists:courses,id',
            'targets.*.group_ids' => 'nullable|array',
            'targets.*.group_ids.*' => 'integer|exists:course_groups,id',
        ]);

        $audienceRows = $this->normalizeAudienceRows($request);
        $legacy = $this->legacyColumnsFromAudience($audienceRows);

        DB::beginTransaction();
        try {
            $slug = $this->uniqueSlug($validated['title']);

            $challenge = ProgrammingChallenge::create([
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'challenge_type' => $validated['challenge_type'],
                'grading_mode' => $validated['grading_mode'],
                'difficulty' => $validated['difficulty'],
                'max_score' => $validated['max_score'] ?? 100,
                'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
                'attempts_allowed' => $validated['attempts_allowed'] ?? 3,
                'allow_resubmit' => $request->boolean('allow_resubmit'),
                'is_published' => $request->boolean('is_published'),
                'is_standalone' => $request->boolean('is_standalone', ! $request->filled('section_id')),
                'course_id' => $legacy['course_id'],
                'target_group_id' => $legacy['target_group_id'],
                'created_by' => auth()->id(),
            ]);

            $this->syncAudienceTargets($challenge, $audienceRows);

            if (! empty($validated['section_id'])) {
                $section = CourseSection::findOrFail($validated['section_id']);
                $sortOrder = CourseModule::where('section_id', $section->id)->max('sort_order') + 1;

                CourseModule::create([
                    'course_id' => $section->course_id,
                    'section_id' => $section->id,
                    'module_type' => 'programming_challenge',
                    'modulable_id' => $challenge->id,
                    'modulable_type' => ProgrammingChallenge::class,
                    'title' => $challenge->title,
                    'description' => $challenge->description,
                    'sort_order' => $sortOrder,
                    'is_visible' => true,
                    'is_required' => false,
                    'is_graded' => true,
                    'max_score' => $challenge->max_score,
                    'attempts_allowed' => $challenge->attempts_allowed,
                    'time_limit' => $challenge->time_limit_seconds ? (int) ceil($challenge->time_limit_seconds / 60) : null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('programming-challenges.manage-languages', $challenge->id)
                ->with('success', 'تم إنشاء التحدي البرمجي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $challenge = ProgrammingChallenge::with(['targets.course', 'targets.group'])->findOrFail($id);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.pages.programming-challenges.edit', compact('challenge', 'courses'));
    }

    public function update(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'challenge_type' => 'required|in:web_sandbox,code_runner',
            'grading_mode' => 'required|in:manual,auto,hybrid',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'max_score' => 'nullable|numeric|min:0',
            'time_limit_seconds' => 'nullable|integer|min:0',
            'attempts_allowed' => 'nullable|integer|min:1',
            'targets' => 'nullable|array',
            'targets.*.course_id' => 'required_with:targets|exists:courses,id',
            'targets.*.group_ids' => 'nullable|array',
            'targets.*.group_ids.*' => 'integer|exists:course_groups,id',
        ]);

        $audienceRows = $this->normalizeAudienceRows($request);
        $legacy = $this->legacyColumnsFromAudience($audienceRows);

        $challenge->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'challenge_type' => $validated['challenge_type'],
            'grading_mode' => $validated['grading_mode'],
            'difficulty' => $validated['difficulty'],
            'max_score' => $validated['max_score'] ?? 100,
            'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
            'attempts_allowed' => $validated['attempts_allowed'] ?? 3,
            'allow_resubmit' => $request->boolean('allow_resubmit'),
            'is_published' => $request->boolean('is_published'),
            'is_standalone' => $request->boolean('is_standalone'),
            'course_id' => $legacy['course_id'],
            'target_group_id' => $legacy['target_group_id'],
            'updated_by' => auth()->id(),
        ]);

        $this->syncAudienceTargets($challenge, $audienceRows);

        $challenge->courseModules()->update([
            'title' => $challenge->title,
            'description' => $challenge->description,
            'max_score' => $challenge->max_score,
            'attempts_allowed' => $challenge->attempts_allowed,
        ]);

        return redirect()
            ->route('programming-challenges.index')
            ->with('success', 'تم تحديث التحدي بنجاح');
    }

    public function destroy(string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);
        $challenge->delete();

        return redirect()
            ->route('programming-challenges.index')
            ->with('success', 'تم حذف التحدي بنجاح');
    }

    public function manageLanguages(string $id)
    {
        $challenge = ProgrammingChallenge::with('languages')->findOrFail($id);

        if ($challenge->isWebSandbox()) {
            $languages = $this->ensureClientWebLanguages();
            $selectedIds = $challenge->languages->pluck('id')->toArray();
            if ($selectedIds === []) {
                $selectedIds = $languages->pluck('id')->all();
            }
        } else {
            $languages = ProgrammingLanguage::active()
                ->runnable()
                ->where('execution_mode', 'server')
                ->orderBy('sort_order')
                ->get();
            $selectedIds = $challenge->languages->pluck('id')->toArray();
        }

        return view('admin.pages.programming-challenges.languages', compact('challenge', 'languages', 'selectedIds'));
    }

    public function updateLanguages(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        if ($challenge->isWebSandbox()) {
            $webLanguages = $this->ensureClientWebLanguages();
            $languageIds = $webLanguages->pluck('id')->all();
            $defaultId = (int) ($request->input('default_language') ?: ($languageIds[0] ?? 0));
        } else {
            $validated = $request->validate([
                'languages' => 'required|array|min:1',
                'languages.*' => 'exists:programming_languages,id',
                'default_language' => 'nullable|exists:programming_languages,id',
            ]);
            $languageIds = $validated['languages'];
            $defaultId = (int) ($validated['default_language'] ?? $languageIds[0]);
        }

        $sync = [];
        foreach ($languageIds as $index => $langId) {
            $sync[$langId] = [
                'is_default' => (int) $langId === $defaultId,
                'sort_order' => $index,
                'editor_tab_label' => null,
            ];
        }

        $challenge->languages()->sync($sync);

        return redirect()
            ->route('programming-challenges.manage-starter', $challenge->id)
            ->with('success', 'تم حفظ اللغات بنجاح');
    }

    public function manageStarter(string $id)
    {
        $challenge = ProgrammingChallenge::with(['languages', 'files'])->findOrFail($id);

        return view('admin.pages.programming-challenges.starter', compact('challenge'));
    }

    public function updateStarter(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*.file_role' => 'required|string',
            'files.*.filename' => 'required|string|max:255',
            'files.*.content' => 'nullable|string',
            'files.*.programming_language_id' => 'nullable|exists:programming_languages,id',
            'files.*.is_readonly' => 'nullable|boolean',
        ]);

        $challenge->files()->delete();

        foreach ($validated['files'] ?? [] as $fileData) {
            ProgrammingChallengeFile::create([
                'programming_challenge_id' => $challenge->id,
                'programming_language_id' => $fileData['programming_language_id'] ?? null,
                'file_role' => $fileData['file_role'],
                'filename' => $fileData['filename'],
                'content' => $fileData['content'] ?? '',
                'is_readonly' => ! empty($fileData['is_readonly']),
            ]);
        }

        return redirect()
            ->route('programming-challenges.manage-test-cases', $challenge->id)
            ->with('success', 'تم حفظ الكود الابتدائي بنجاح');
    }

    public function manageTestCases(string $id)
    {
        $challenge = ProgrammingChallenge::with('testCases')->findOrFail($id);

        return view('admin.pages.programming-challenges.test-cases', compact('challenge'));
    }

    public function updateTestCases(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        $validated = $request->validate([
            'test_cases' => 'nullable|array',
            'test_cases.*.input' => 'nullable|string',
            'test_cases.*.expected_output' => 'nullable|string',
            'test_cases.*.is_hidden' => 'nullable|boolean',
            'test_cases.*.points' => 'nullable|numeric|min:0',
            'test_cases.*.timeout_ms' => 'nullable|integer|min:100',
        ]);

        $challenge->testCases()->delete();

        foreach ($validated['test_cases'] ?? [] as $index => $case) {
            ProgrammingChallengeTestCase::create([
                'programming_challenge_id' => $challenge->id,
                'input' => $case['input'] ?? '',
                'expected_output' => $case['expected_output'] ?? '',
                'is_hidden' => ! empty($case['is_hidden']),
                'points' => $case['points'] ?? 1,
                'timeout_ms' => $case['timeout_ms'] ?? 5000,
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('programming-challenges.index')
            ->with('success', 'تم حفظ حالات الاختبار بنجاح');
    }

    public function attempts(string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        $attempts = $challenge->attempts()
            ->with([
                'student',
                'grader',
                'submissions' => function ($q) {
                    $q->where('status', '!=', 'draft')
                        ->orderByDesc('submission_number')
                        ->orderByDesc('id')
                        ->with('files');
                },
            ])
            ->whereIn('status', ['submitted', 'graded', 'returned', 'in_progress'])
            ->orderByDesc('attempt_number')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.pages.programming-challenges.attempts', compact('challenge', 'attempts'));
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'challenge';
        $slug = $base;
        $counter = 1;

        while (ProgrammingChallenge::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * @return array<int, array{course_id: int, group_ids: array<int, int>}>
     */
    protected function normalizeAudienceRows(Request $request): array
    {
        $raw = $request->input('targets', []);
        if (! is_array($raw)) {
            return [];
        }

        $rows = [];
        $seenCourses = [];

        foreach ($raw as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $courseId = isset($row['course_id']) && $row['course_id'] !== ''
                ? (int) $row['course_id']
                : null;

            if (! $courseId) {
                continue;
            }

            if (isset($seenCourses[$courseId])) {
                throw ValidationException::withMessages([
                    "targets.{$index}.course_id" => 'لا تكرر نفس الكورس أكثر من مرة — اختر مجموعات متعددة داخل صف واحد.',
                ]);
            }
            $seenCourses[$courseId] = true;

            $groupIds = collect($row['group_ids'] ?? [])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            foreach ($groupIds as $groupId) {
                $this->assertTargetGroupLinkedToCourse($groupId, $courseId);
            }

            $rows[] = [
                'course_id' => $courseId,
                'group_ids' => $groupIds,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array{course_id: int, group_ids: array<int, int>}>  $rows
     * @return array{course_id: ?int, target_group_id: ?int}
     */
    protected function legacyColumnsFromAudience(array $rows): array
    {
        if ($rows === []) {
            return ['course_id' => null, 'target_group_id' => null];
        }

        $first = $rows[0];
        $groupIds = $first['group_ids'];

        return [
            'course_id' => $first['course_id'],
            'target_group_id' => count($groupIds) === 1 ? $groupIds[0] : null,
        ];
    }

    /**
     * @param  array<int, array{course_id: int, group_ids: array<int, int>}>  $rows
     */
    protected function syncAudienceTargets(ProgrammingChallenge $challenge, array $rows): void
    {
        $challenge->targets()->delete();

        foreach ($rows as $row) {
            $groupIds = $row['group_ids'];

            if ($groupIds === []) {
                $challenge->targets()->create([
                    'course_id' => $row['course_id'],
                    'group_id' => null,
                ]);
                continue;
            }

            foreach ($groupIds as $groupId) {
                $challenge->targets()->create([
                    'course_id' => $row['course_id'],
                    'group_id' => $groupId,
                ]);
            }
        }
    }

    protected function assertTargetGroupLinkedToCourse(?int $groupId, ?int $courseId): void
    {
        if ($groupId === null || $groupId <= 0) {
            return;
        }

        if ($courseId === null || $courseId <= 0) {
            throw ValidationException::withMessages([
                'targets' => 'لا يمكن تحديد مجموعة بدون كورس.',
            ]);
        }

        $isLinked = DB::table('course_group_courses')
            ->where('course_id', $courseId)
            ->where('group_id', $groupId)
            ->exists();

        if (! $isLinked) {
            throw ValidationException::withMessages([
                'targets' => 'إحدى المجموعات غير مرتبطة بالكورس المحدد.',
            ]);
        }
    }

    /**
     * Ensure HTML/CSS/JS exist and are marked as client_web for web sandbox challenges.
     *
     * @return \Illuminate\Support\Collection<int, ProgrammingLanguage>
     */
    protected function ensureClientWebLanguages()
    {
        $configs = [
            'html' => [
                'name' => 'HTML',
                'display_name' => 'HTML',
                'description' => 'لغة ترميز صفحات الويب',
                'category' => 'frontend',
                'icon' => 'fab fa-html5',
                'color' => '#E34F26',
                'monaco_language_id' => 'html',
                'execution_mode' => 'client_web',
                'file_extension' => 'html',
                'default_filename' => 'index.html',
                'sort_order' => 1,
            ],
            'css' => [
                'name' => 'CSS',
                'display_name' => 'CSS',
                'description' => 'تنسيق مظهر الصفحة',
                'category' => 'frontend',
                'icon' => 'fab fa-css3-alt',
                'color' => '#1572B6',
                'monaco_language_id' => 'css',
                'execution_mode' => 'client_web',
                'file_extension' => 'css',
                'default_filename' => 'style.css',
                'sort_order' => 2,
            ],
            'javascript' => [
                'name' => 'JavaScript',
                'display_name' => 'JavaScript',
                'description' => 'تفاعل وسلوك الصفحة',
                'category' => 'frontend',
                'icon' => 'fab fa-js',
                'color' => '#B8A000',
                'monaco_language_id' => 'javascript',
                'execution_mode' => 'client_web',
                'file_extension' => 'js',
                'default_filename' => 'script.js',
                'sort_order' => 3,
            ],
        ];

        foreach ($configs as $slug => $data) {
            ProgrammingLanguage::updateOrCreate(
                ['slug' => $slug],
                array_merge($data, ['is_active' => true])
            );
        }

        return ProgrammingLanguage::query()
            ->whereIn('slug', array_keys($configs))
            ->orderBy('sort_order')
            ->get();
    }
}
