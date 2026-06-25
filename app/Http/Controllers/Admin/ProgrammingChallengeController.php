<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeFile;
use App\Models\ProgrammingChallengeTestCase;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgrammingChallengeController extends Controller
{
    public function index()
    {
        $challenges = ProgrammingChallenge::with(['creator', 'languages'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.pages.programming-challenges.index', compact('challenges'));
    }

    public function create(Request $request)
    {
        $sectionId = $request->get('section_id');
        $section = $sectionId ? CourseSection::with('course')->findOrFail($sectionId) : null;

        return view('admin.pages.programming-challenges.create', compact('section'));
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
        ]);

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
                'created_by' => auth()->id(),
            ]);

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
        $challenge = ProgrammingChallenge::findOrFail($id);

        return view('admin.pages.programming-challenges.edit', compact('challenge'));
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
        ]);

        $challenge->update([
            ...$validated,
            'max_score' => $validated['max_score'] ?? 100,
            'attempts_allowed' => $validated['attempts_allowed'] ?? 3,
            'allow_resubmit' => $request->boolean('allow_resubmit'),
            'is_published' => $request->boolean('is_published'),
            'is_standalone' => $request->boolean('is_standalone'),
            'updated_by' => auth()->id(),
        ]);

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
        $languages = ProgrammingLanguage::active()->runnable()->orderBy('sort_order')->get();
        $selectedIds = $challenge->languages->pluck('id')->toArray();

        return view('admin.pages.programming-challenges.languages', compact('challenge', 'languages', 'selectedIds'));
    }

    public function updateLanguages(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        $validated = $request->validate([
            'languages' => 'nullable|array',
            'languages.*' => 'exists:programming_languages,id',
            'default_language' => 'nullable|exists:programming_languages,id',
        ]);

        $languageIds = $validated['languages'] ?? [];
        $sync = [];

        foreach ($languageIds as $index => $langId) {
            $sync[$langId] = [
                'is_default' => (int) $langId === (int) ($validated['default_language'] ?? $languageIds[0] ?? 0),
                'sort_order' => $index,
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
}
