<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\QuestionPool;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizRandomSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RandomPoolQuizController extends QuizController
{
    protected function findRandomPoolQuiz(int|string $id): Quiz
    {
        $quiz = Quiz::findOrFail($id);

        if (! $quiz->isRandomPool()) {
            abort(404);
        }

        return $quiz;
    }

    public function index(Request $request)
    {
        $query = Quiz::with(['course', 'lesson', 'creator'])
            ->withCount('attempts')
            ->where('quiz_type', Quiz::TYPE_RANDOM_POOL)
            ->orderBy('created_at', 'desc');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $quizzes = $query->paginate(15)->withQueryString();
        $courses = Course::where('is_published', true)->get();

        $totalQuizzes = Quiz::where('quiz_type', Quiz::TYPE_RANDOM_POOL)->count();
        $publishedQuizzes = Quiz::where('quiz_type', Quiz::TYPE_RANDOM_POOL)->where('is_published', true)->count();
        $draftQuizzes = Quiz::where('quiz_type', Quiz::TYPE_RANDOM_POOL)->where('is_published', false)->count();
        $questionBankCount = \App\Models\QuestionBank::count();

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.random-pool-quizzes._table', compact('quizzes'))->render(),
                'count' => $quizzes->total(),
            ]);
        }

        return view('admin.pages.random-pool-quizzes.index', compact(
            'quizzes',
            'courses',
            'totalQuizzes',
            'publishedQuizzes',
            'draftQuizzes',
            'questionBankCount',
        ));
    }

    public function create(Request $request)
    {
        $courses = Course::where('is_published', true)->get();
        $selectedSection = null;
        $selectedCourse = null;

        if ($request->filled('section_id')) {
            $selectedSection = CourseSection::with('course')->find($request->section_id);
            if ($selectedSection) {
                $selectedCourse = $selectedSection->course;
            }
        } elseif ($request->filled('course_id')) {
            $selectedCourse = Course::find($request->course_id);
        }

        return view('admin.pages.random-pool-quizzes.create', compact(
            'courses',
            'selectedSection',
            'selectedCourse',
        ));
    }

    public function store(Request $request)
    {
        $request->merge([
            'quiz_type' => Quiz::TYPE_RANDOM_POOL,
            'shuffle_questions' => $request->has('shuffle_questions'),
            'shuffle_answers' => $request->has('shuffle_answers'),
            'show_correct_answers' => $request->has('show_correct_answers'),
            'allow_review' => $request->has('allow_review'),
            'show_grade_immediately' => $request->has('show_grade_immediately'),
            'is_published' => $request->has('is_published'),
            'is_visible' => $request->has('is_visible'),
        ]);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'questions_per_attempt' => 'required|integer|min:1',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'attempts_allowed' => 'nullable|integer|min:1',
            'shuffle_questions' => 'sometimes|boolean',
            'shuffle_answers' => 'sometimes|boolean',
            'show_correct_answers' => 'sometimes|boolean',
            'show_correct_answers_after' => 'required|in:immediately,after_due,after_graded,never',
            'feedback_mode' => 'required|in:immediate,after_submission,after_due,manual',
            'allow_review' => 'sometimes|boolean',
            'show_grade_immediately' => 'sometimes|boolean',
            'available_from' => 'nullable|date',
            'due_date' => 'nullable|date|after:available_from',
            'available_until' => 'nullable|date|after:due_date',
            'is_published' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['quiz_type'] = Quiz::TYPE_RANDOM_POOL;
        $validated['max_score'] = 100.00;
        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $quiz = Quiz::create($validated);

            if ($request->has('settings')) {
                $this->createQuizSettings($quiz, $request);
            }

            if ($request->filled('section_id')) {
                $section = CourseSection::find($request->section_id);
                if ($section) {
                    $maxSortOrder = CourseModule::where('section_id', $section->id)->max('sort_order') ?? 0;

                    CourseModule::create([
                        'course_id' => $quiz->course_id,
                        'section_id' => $section->id,
                        'module_type' => 'quiz',
                        'modulable_id' => $quiz->id,
                        'modulable_type' => Quiz::class,
                        'title' => $quiz->title,
                        'description' => $quiz->description,
                        'sort_order' => $maxSortOrder + 1,
                        'is_visible' => $quiz->is_published,
                        'is_required' => false,
                        'is_graded' => true,
                        'max_score' => $quiz->max_score,
                        'completion_type' => 'auto',
                        'time_limit' => $quiz->time_limit,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('random-pool-quizzes.manage-questions', $quiz->id)
                ->with('success', 'تم إنشاء اختبار بنك عشوائي. أضف الأسئلة والمجموعات الآن.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء إنشاء الاختبار: '.$e->getMessage()]);
        }
    }

    public function show($id)
    {
        $quiz = Quiz::with([
            'course',
            'lesson',
            'creator',
            'quizQuestions.question.questionType',
            'quizQuestions.questionPool',
            'settings',
        ])->findOrFail($id);

        if (! $quiz->isRandomPool()) {
            return redirect()->route('quizzes.show', $quiz->id);
        }

        $attempts = $quiz->attempts()
            ->realAttempts()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        $selectionService = app(QuizRandomSelectionService::class);
        $poolStats = [
            'pool_size' => $selectionService->buildCandidatePool($quiz)->count(),
            'per_attempt' => (int) $quiz->questions_per_attempt,
            'config_error' => $selectionService->validateQuizConfiguration($quiz),
        ];

        $stats = [
            'total_attempts' => $quiz->attempts()->realAttempts()->count(),
            'completed_attempts' => $quiz->attempts()->realAttempts()->where('is_completed', true)->count(),
            'in_progress' => $quiz->attempts()->realAttempts()->where('status', 'in_progress')->count(),
            'graded' => $quiz->attempts()->realAttempts()->where('status', 'graded')->count(),
            'pending_grading' => $quiz->attempts()->realAttempts()->where('status', 'submitted')->count(),
            'average_score' => $quiz->attempts()
                ->realAttempts()
                ->where('is_completed', true)
                ->whereNotNull('total_score')
                ->avg('total_score'),
            'pass_rate' => $this->calculatePassRate($quiz),
        ];

        return view('admin.pages.random-pool-quizzes.show', compact('quiz', 'attempts', 'stats', 'poolStats'));
    }

    public function edit($id)
    {
        $quiz = Quiz::with('settings')->findOrFail($id);

        if (! $quiz->isRandomPool()) {
            return redirect()->route('quizzes.edit', $quiz->id);
        }

        $courses = Course::where('is_published', true)->get();
        $lessons = collect([]);

        if ($quiz->course_id) {
            $lessonModules = CourseModule::where('course_id', $quiz->course_id)
                ->where('module_type', 'lesson')
                ->orderBy('sort_order')
                ->get();

            $lessons = $lessonModules->map(function ($module) {
                if ($module->modulable_id) {
                    return Lesson::where('id', $module->modulable_id)
                        ->where('is_published', true)
                        ->first();
                }

                return null;
            })->filter()->values();
        }

        $poolSize = app(QuizRandomSelectionService::class)->buildCandidatePool($quiz)->count();

        return view('admin.pages.random-pool-quizzes.edit', compact('quiz', 'courses', 'lessons', 'poolSize'));
    }

    public function update(Request $request, $id)
    {
        $quiz = $this->findRandomPoolQuiz($id);

        $request->merge([
            'quiz_type' => Quiz::TYPE_RANDOM_POOL,
            'shuffle_questions' => $request->has('shuffle_questions'),
            'shuffle_answers' => $request->has('shuffle_answers'),
            'show_correct_answers' => $request->has('show_correct_answers'),
            'allow_review' => $request->has('allow_review'),
            'show_grade_immediately' => $request->has('show_grade_immediately'),
            'is_published' => $request->has('is_published'),
            'is_visible' => $request->has('is_visible'),
        ]);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'questions_per_attempt' => 'required|integer|min:1',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'attempts_allowed' => 'nullable|integer|min:1',
            'shuffle_questions' => 'sometimes|boolean',
            'shuffle_answers' => 'sometimes|boolean',
            'show_correct_answers' => 'sometimes|boolean',
            'show_correct_answers_after' => 'required|in:immediately,after_due,after_graded,never',
            'feedback_mode' => 'required|in:immediate,after_submission,after_due,manual',
            'allow_review' => 'sometimes|boolean',
            'show_grade_immediately' => 'sometimes|boolean',
            'available_from' => 'nullable|date',
            'due_date' => 'nullable|date|after:available_from',
            'available_until' => 'nullable|date|after:due_date',
            'is_published' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['quiz_type'] = Quiz::TYPE_RANDOM_POOL;
        $validated['updated_by'] = auth()->id();

        $poolSize = app(QuizRandomSelectionService::class)->buildCandidatePool($quiz)->count();
        if ($poolSize > 0 && (int) $validated['questions_per_attempt'] > $poolSize) {
            return back()->withInput()->withErrors([
                'questions_per_attempt' => "عدد الأسئلة لكل محاولة ({$validated['questions_per_attempt']}) أكبر من حجم البنك ({$poolSize}).",
            ]);
        }

        DB::beginTransaction();
        try {
            $quiz->update($validated);

            if ($request->has('settings')) {
                $this->updateQuizSettings($quiz, $request);
            }

            $quiz->update(['max_score' => $quiz->calculateMaxScore()]);

            DB::commit();

            return redirect()->route('random-pool-quizzes.show', $quiz->id)
                ->with('success', 'تم تحديث اختبار بنك عشوائي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث الاختبار: '.$e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $quiz = $this->findRandomPoolQuiz($id);
        $quiz->delete();

        return redirect()->route('random-pool-quizzes.index')
            ->with('success', 'تم حذف اختبار بنك عشوائي بنجاح');
    }

    public function manageQuestions($id)
    {
        $quiz = Quiz::with([
            'questions.questionType',
            'questions.options',
            'course',
            'quizQuestions.questionPool',
        ])->findOrFail($id);

        if (! $quiz->isRandomPool()) {
            return redirect()->route('quizzes.manage-questions', $quiz->id);
        }

        $questionTypes = QuestionType::where('is_active', true)->get();
        $availablePools = QuestionPool::where('course_id', $quiz->course_id)->orderBy('name')->get();
        $linkedPools = $quiz->quizQuestions()->with('questionPool')->whereNotNull('question_pool_id')->get();

        $selectionService = app(QuizRandomSelectionService::class);
        $poolStats = [
            'pool_size' => $selectionService->buildCandidatePool($quiz)->count(),
            'per_attempt' => (int) $quiz->questions_per_attempt,
            'config_error' => $selectionService->validateQuizConfiguration($quiz),
        ];

        return view('admin.pages.random-pool-quizzes.manage-questions', compact(
            'quiz',
            'questionTypes',
            'availablePools',
            'linkedPools',
            'poolStats',
        ));
    }

    public function togglePublish($id)
    {
        $quiz = $this->findRandomPoolQuiz($id);

        $quiz->update([
            'is_published' => ! $quiz->is_published,
            'updated_by' => auth()->id(),
        ]);

        $status = $quiz->is_published ? 'نشر' : 'إلغاء نشر';

        return back()->with('success', "تم {$status} اختبار بنك عشوائي بنجاح");
    }

    public function attachQuestionPool(Request $request, $id): JsonResponse
    {
        return parent::attachQuestionPool($request, $id);
    }

    public function detachQuestionPool($id, $quizQuestionId): JsonResponse
    {
        return parent::detachQuestionPool($id, $quizQuestionId);
    }
}
