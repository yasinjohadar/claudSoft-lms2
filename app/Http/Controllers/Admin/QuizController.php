<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizSettings;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes.
     */
    public function index(Request $request)
    {
        $query = Quiz::with(['course', 'lesson', 'creator'])
            ->withCount('attempts')
            ->orderBy('created_at', 'desc');

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Filter by type
        if ($request->filled('quiz_type')) {
            $query->where('quiz_type', $request->quiz_type);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $quizzes = $query->paginate(15)->withQueryString();
        $courses = Course::where('is_published', true)->get();

        $totalQuizzes = Quiz::count();
        $publishedQuizzes = Quiz::where('is_published', true)->count();
        $draftQuizzes = Quiz::where('is_published', false)->count();
        $questionBankCount = \App\Models\QuestionBank::count();

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.quizzes._quizzes_table', compact('quizzes'))->render(),
                'count' => $quizzes->total(),
            ]);
        }

        return view('admin.pages.quizzes.index', compact(
            'quizzes',
            'courses',
            'totalQuizzes',
            'publishedQuizzes',
            'draftQuizzes',
            'questionBankCount',
        ));
    }

    /**
     * Show the form for creating a new quiz.
     */
    public function create(Request $request)
    {
        $courses = Course::where('is_published', true)->get();
        $selectedSection = null;
        $selectedCourse = null;

        // إذا تم تمرير section_id من رابط القسم
        if ($request->filled('section_id')) {
            $selectedSection = CourseSection::with('course')->find($request->section_id);
            if ($selectedSection) {
                $selectedCourse = $selectedSection->course;
            }
        }
        // إذا تم اختيار كورس من الـ dropdown
        elseif ($request->filled('course_id')) {
            $selectedCourse = Course::find($request->course_id);
        }

        $questionTypes = \App\Models\QuestionType::where('is_active', true)->get();

        return view('admin.pages.quizzes.create', compact(
            'courses',
            'selectedSection',
            'selectedCourse',
            'questionTypes'
        ));
    }

    /**
     * Store a newly created quiz.
     */
    public function store(Request $request)
    {
        // Handle checkboxes before validation (convert to boolean)
        $request->merge([
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
            'quiz_type' => 'required|in:practice,graded,final_exam,survey',
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

        // Set max_score (will be calculated later from questions)
        $validated['max_score'] = 100.00;

        // Set creator
        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $quiz = Quiz::create($validated);

            // Create quiz settings if provided
            if ($request->has('settings')) {
                $this->createQuizSettings($quiz, $request);
            }

            // إذا تم تمرير section_id، نربط الاختبار بالقسم عبر course_modules
            if ($request->filled('section_id')) {
                $section = CourseSection::find($request->section_id);
                if ($section) {
                    // الحصول على آخر ترتيب في القسم
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

            // التوجيه: إذا جاء من قسم، نرجع لصفحة إدارة الأسئلة، وإلا لصفحة الاختبار
            if ($request->filled('section_id')) {
                return redirect()->route('quizzes.manage-questions', $quiz->id)
                    ->with('success', 'تم إنشاء الاختبار بنجاح. يمكنك الآن إضافة الأسئلة');
            }

            return redirect()->route('quizzes.manage-questions', $quiz->id)
                ->with('success', 'تم إنشاء الاختبار بنجاح. يمكنك الآن إضافة الأسئلة');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء إنشاء الاختبار: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified quiz.
     */
    public function show($id)
    {
        $quiz = Quiz::with([
            'course',
            'lesson',
            'creator',
            'quizQuestions.question.questionType',
            'quizQuestions.question.options',
            'settings'
        ])->findOrFail($id);

        // Get attempts statistics
        $attempts = $quiz->attempts()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'completed_attempts' => $quiz->attempts()->where('is_completed', true)->count(),
            'in_progress' => $quiz->attempts()->where('status', 'in_progress')->count(),
            'graded' => $quiz->attempts()->where('status', 'graded')->count(),
            'pending_grading' => $quiz->attempts()->where('status', 'submitted')->count(),
            'average_score' => $quiz->attempts()
                ->where('is_completed', true)
                ->whereNotNull('total_score')
                ->avg('total_score'),
            'pass_rate' => $this->calculatePassRate($quiz),
        ];

        return view('admin.pages.quizzes.show', compact('quiz', 'attempts', 'stats'));
    }

    /**
     * Show the form for editing the specified quiz.
     */
    public function edit($id)
    {
        try {
            $quiz = Quiz::with('settings')->findOrFail($id);
            $courses = Course::where('is_published', true)->get();
            
            $lessons = collect([]);
            
            // Get lessons through course_modules (polymorphic relationship)
            if ($quiz->course_id) {
                \Log::info('Loading lessons for quiz', [
                    'quiz_id' => $quiz->id,
                    'course_id' => $quiz->course_id
                ]);
                
                $lessonModules = CourseModule::where('course_id', $quiz->course_id)
                    ->where('module_type', 'lesson')
                    ->orderBy('sort_order')
                    ->get();

                \Log::info('Found lesson modules', [
                    'count' => $lessonModules->count(),
                    'modules' => $lessonModules->pluck('id', 'modulable_id')->toArray()
                ]);

                $lessons = $lessonModules->map(function($module) {
                    if ($module->modulable_id) {
                        $lesson = Lesson::where('id', $module->modulable_id)
                            ->where('is_published', true)
                            ->first();
                        return $lesson;
                    }
                    return null;
                })->filter()->values();
                
                \Log::info('Final lessons count', ['count' => $lessons->count()]);
            } else {
                \Log::warning('Quiz has no course_id', ['quiz_id' => $quiz->id]);
            }

            return view('admin.pages.quizzes.edit', compact('quiz', 'courses', 'lessons'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('quizzes.index')
                ->withErrors(['error' => 'الاختبار المطلوب غير موجود']);
        } catch (\Exception $e) {
            // Log the error for debugging
            $quizCourseId = null;
            try {
                $quiz = Quiz::find($id);
                $quizCourseId = $quiz->course_id ?? null;
            } catch (\Exception $ex) {
                // Ignore
            }
            
            \Log::error('Quiz edit error: ' . $e->getMessage(), [
                'quiz_id' => $id,
                'quiz_course_id' => $quizCourseId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('quizzes.index')
                ->withErrors(['error' => 'حدث خطأ أثناء تحميل الصفحة: ' . $e->getMessage() . ' (السطر: ' . $e->getLine() . ')']);
        }
    }

    /**
     * Update the specified quiz.
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        // Handle checkboxes before validation (convert to boolean)
        $request->merge([
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
            'quiz_type' => 'required|in:practice,graded,final_exam,survey',
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

        // Set updater
        $validated['updated_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $quiz->update($validated);

            // Update quiz settings if provided
            if ($request->has('settings')) {
                $this->updateQuizSettings($quiz, $request);
            }

            DB::commit();

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('success', 'تم تحديث الاختبار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث الاختبار: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified quiz.
     */
    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);

        try {
            $quiz->delete();

            return redirect()->route('quizzes.index')
                ->with('success', 'تم حذف الاختبار بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف الاختبار: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle quiz publish status.
     */
    public function togglePublish($id)
    {
        $quiz = Quiz::findOrFail($id);

        $quiz->update([
            'is_published' => !$quiz->is_published,
            'updated_by' => auth()->id(),
        ]);

        $status = $quiz->is_published ? 'نشر' : 'إلغاء نشر';

        return back()->with('success', "تم {$status} الاختبار بنجاح");
    }

    /**
     * Get lessons for a specific course (AJAX).
     */
    public function getLessons($courseId)
    {
        try {
            // Get lessons through course_modules (polymorphic relationship)
            $lessonModules = CourseModule::where('course_id', $courseId)
                ->where('module_type', 'lesson')
                ->orderBy('sort_order')
                ->get();

            $lessons = $lessonModules->map(function($module) {
                $lesson = Lesson::where('id', $module->modulable_id)
                    ->where('is_published', true)
                    ->first();
                return $lesson ? ['id' => $lesson->id, 'title' => $lesson->title] : null;
            })->filter()->values();

            return response()->json($lessons);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Get lessons error: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ أثناء تحميل الدروس: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate quiz max score based on questions.
     */
    public function recalculateScore($id)
    {
        $quiz = Quiz::findOrFail($id);

        try {
            $maxScore = $quiz->calculateMaxScore();

            $quiz->update([
                'max_score' => $maxScore,
                'updated_by' => auth()->id(),
            ]);

            return back()->with('success', "تم إعادة حساب الدرجة القصوى: {$maxScore}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء إعادة حساب الدرجة']);
        }
    }

    /**
     * Create quiz settings.
     */
    private function createQuizSettings(Quiz $quiz, Request $request): void
    {
        $settings = $request->input('settings', []);

        QuizSettings::create([
            'quiz_id' => $quiz->id,
            'require_password' => $settings['require_password'] ?? false,
            'quiz_password' => $settings['quiz_password'] ?? null,
            'browser_security' => $settings['browser_security'] ?? 'none',
            'allow_navigation' => $settings['allow_navigation'] ?? true,
            'navigation_method' => $settings['navigation_method'] ?? 'free',
            'show_question_numbers' => $settings['show_question_numbers'] ?? true,
            'questions_per_page' => $settings['questions_per_page'] ?? 1,
            'show_timer' => $settings['show_timer'] ?? true,
            'auto_submit' => $settings['auto_submit'] ?? true,
            'allow_pause' => $settings['allow_pause'] ?? false,
            'show_progress_bar' => $settings['show_progress_bar'] ?? true,
            'enable_calculator' => $settings['enable_calculator'] ?? false,
            'decimal_places' => $settings['decimal_places'] ?? 2,
        ]);
    }

    /**
     * Update quiz settings.
     */
    private function updateQuizSettings(Quiz $quiz, Request $request): void
    {
        $settings = $request->input('settings', []);

        $quiz->settings()->updateOrCreate(
            ['quiz_id' => $quiz->id],
            [
                'require_password' => $settings['require_password'] ?? false,
                'quiz_password' => $settings['quiz_password'] ?? null,
                'browser_security' => $settings['browser_security'] ?? 'none',
                'allow_navigation' => $settings['allow_navigation'] ?? true,
                'navigation_method' => $settings['navigation_method'] ?? 'free',
                'show_question_numbers' => $settings['show_question_numbers'] ?? true,
                'questions_per_page' => $settings['questions_per_page'] ?? 1,
                'show_timer' => $settings['show_timer'] ?? true,
                'auto_submit' => $settings['auto_submit'] ?? true,
                'allow_pause' => $settings['allow_pause'] ?? false,
                'show_progress_bar' => $settings['show_progress_bar'] ?? true,
                'enable_calculator' => $settings['enable_calculator'] ?? false,
                'decimal_places' => $settings['decimal_places'] ?? 2,
            ]
        );
    }

    /**
     * Calculate pass rate for a quiz.
     */
    private function calculatePassRate(Quiz $quiz): float
    {
        $completedAttempts = $quiz->attempts()
            ->where('is_completed', true)
            ->count();

        if ($completedAttempts === 0) {
            return 0;
        }

        $passedAttempts = $quiz->attempts()
            ->where('is_completed', true)
            ->where('passed', true)
            ->count();

        return ($passedAttempts / $completedAttempts) * 100;
    }

    /**
     * Show the page to manage questions in the quiz.
     */
    public function manageQuestions($id)
    {
        $quiz = Quiz::with(['questions.questionType', 'questions.options', 'course'])
            ->findOrFail($id);

        // Get available questions from the question bank
        // Get IDs of questions already in the quiz to exclude them
        // Use pivot table directly for accurate results
        $existingQuestionIds = DB::table('quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->pluck('question_id')
            ->toArray();
        
        // Get all active questions (no automatic course filtering)
        $availableQuestions = \App\Models\QuestionBank::with(['questionType', 'options', 'course'])
            ->where('is_active', true)
            ->when(!empty($existingQuestionIds), function($query) use ($existingQuestionIds) {
                return $query->whereNotIn('id', $existingQuestionIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get question types for filtering
        $questionTypes = \App\Models\QuestionType::where('is_active', true)->get();

        // Get all courses for filtering dropdown
        $courses = \App\Models\Course::where('is_published', true)->get();

        $bankLessonNames = $availableQuestions
            ->map(function (\App\Models\QuestionBank $q) {
                $name = $q->lesson_name ?? ($q->metadata['lesson_name'] ?? null);

                return $name !== null && trim((string) $name) !== '' ? trim((string) $name) : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.pages.quizzes.manage-questions', compact('quiz', 'availableQuestions', 'questionTypes', 'courses', 'bankLessonNames'));
    }

    /**
     * Add a question to the quiz (AJAX).
     */
    public function addQuestion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:question_bank,id',
                'question_grade' => 'nullable|numeric|min:0',
                'is_required' => 'nullable|boolean',
            ]);

            $quiz = Quiz::findOrFail($id);
            $grade = $validated['question_grade'] ?? 1.0;
            $isRequired = $validated['is_required'] ?? false;

            // If update_existing is true, update existing question settings
            if ($request->input('update_existing') === true || $request->input('update_existing') === 'true') {
                // Check if question exists in quiz using pivot table
                if (!DB::table('quiz_questions')
                    ->where('quiz_id', $quiz->id)
                    ->where('question_id', $validated['question_id'])
                    ->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'السؤال غير موجود في هذا الاختبار',
                    ], 404);
                }

                $quiz->questions()->updateExistingPivot($validated['question_id'], [
                    'question_grade' => $grade,
                    'is_required' => $isRequired,
                ]);

                // Recalculate max_score
                $maxScore = $quiz->calculateMaxScore();
                $quiz->update(['max_score' => $maxScore]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث إعدادات السؤال بنجاح',
                ]);
            }

            // Use transaction with lock to prevent race conditions
            return DB::transaction(function() use ($quiz, $validated, $grade, $isRequired) {
                // Lock the quiz row for update
                $quiz = Quiz::lockForUpdate()->findOrFail($quiz->id);

                // Check if question is already added - use pivot table directly for accurate check
                if (DB::table('quiz_questions')
                    ->where('quiz_id', $quiz->id)
                    ->where('question_id', $validated['question_id'])
                    ->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'السؤال موجود بالفعل في هذا الاختبار',
                    ], 400);
                }

                // Get next order
                $maxOrder = $quiz->quizQuestions()->max('question_order') ?? 0;

                // Add question to quiz
                $quiz->questions()->attach($validated['question_id'], [
                    'question_order' => $maxOrder + 1,
                    'question_grade' => $grade,
                    'is_required' => $isRequired,
                ]);

                // Recalculate max_score
                $maxScore = $quiz->calculateMaxScore();
                $quiz->update(['max_score' => $maxScore]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة السؤال بنجاح',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a question from the quiz (AJAX).
     */
    public function removeQuestion($id, $questionId)
    {
        try {
            $quiz = Quiz::findOrFail($id);
            $quiz->questions()->detach($questionId);

            // Recalculate max_score
            $maxScore = $quiz->calculateMaxScore();
            $quiz->update(['max_score' => $maxScore]);

            return response()->json([
                'success' => true,
                'message' => 'تم إزالة السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إزالة السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove multiple questions from the quiz (AJAX).
     */
    public function removeMultipleQuestions(Request $request, $id)
    {
        try {
            $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'required|integer|exists:question_bank,id',
            ]);

            $quiz = Quiz::findOrFail($id);
            $questionIds = $request->input('question_ids');
            $count = count($questionIds);

            // Detach all questions at once
            $quiz->questions()->detach($questionIds);

            // Recalculate max_score
            $maxScore = $quiz->calculateMaxScore();
            $quiz->update(['max_score' => $maxScore]);

            return response()->json([
                'success' => true,
                'message' => "تم حذف {$count} سؤال بنجاح",
                'deleted_count' => $count,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الأسئلة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder questions in the quiz (AJAX).
     */
    public function reorderQuestions(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'exists:question_bank,id',
            ]);

            $quiz = Quiz::findOrFail($id);

            foreach ($validated['question_ids'] as $order => $questionId) {
                $quiz->questions()->updateExistingPivot($questionId, [
                    'question_order' => $order + 1,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة ترتيب الأسئلة بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة ترتيب الأسئلة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show import questions page/modal.
     */
    public function importQuestions($id)
    {
        $quiz = Quiz::with('course')->findOrFail($id);

        // Get available questions from the question bank
        // Get IDs of questions already in the quiz to exclude them
        // Use pivot table directly for accurate results
        $existingQuestionIds = DB::table('quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->pluck('question_id')
            ->toArray();
        
        // Get all active questions (no automatic course filtering)
        $availableQuestions = \App\Models\QuestionBank::with(['questionType', 'options', 'course'])
            ->where('is_active', true)
            ->when(!empty($existingQuestionIds), function($query) use ($existingQuestionIds) {
                return $query->whereNotIn('id', $existingQuestionIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get question types for filtering
        $questionTypes = \App\Models\QuestionType::where('is_active', true)->get();

        // Get courses for filtering
        $courses = \App\Models\Course::where('is_published', true)->get();

        return view('admin.pages.quizzes.import-questions', compact('quiz', 'availableQuestions', 'questionTypes', 'courses'));
    }

}
