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

        $quizzes = $query->paginate(15);
        $courses = Course::where('is_published', true)->get();

        return view('admin.pages.quizzes.index', compact('quizzes', 'courses'));
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

        return view('admin.pages.quizzes.create', compact(
            'courses',
            'selectedSection',
            'selectedCourse'
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

            // التوجيه: إذا جاء من قسم، نرجع للكورس، وإلا لصفحة الاختبار
            if ($request->filled('section_id')) {
                $section = CourseSection::find($request->section_id);
                return redirect()->route('courses.show', $section->course_id)
                    ->with('success', 'تم إنشاء الاختبار وربطه بالقسم بنجاح');
            }

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('success', 'تم إنشاء الاختبار بنجاح');
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
}
