<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionModule;
use App\Models\QuestionBank;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Course;
use App\Models\QuestionType;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionModuleController extends Controller
{
    /**
     * Display a listing of the question modules.
     */
    public function index()
    {
        $questionModules = QuestionModule::with(['creator', 'questions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pages.question-modules.index', compact('questionModules'));
    }

    /**
     * Show the form for creating a new question module.
     */
    public function create(Request $request)
    {
        $sectionId = $request->get('section_id');
        $section = null;

        if ($sectionId) {
            $section = CourseSection::with('course')->findOrFail($sectionId);
        }

        return view('admin.pages.question-modules.create', compact('section'));
    }

    /**
     * Store a newly created question module in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'time_limit' => 'nullable|integer|min:0',
            'pass_percentage' => 'nullable|numeric|min:0|max:100',
            'attempts_allowed' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $section = CourseSection::findOrFail($validated['section_id']);

            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible');
            $validated['shuffle_questions'] = $request->has('shuffle_questions');
            $validated['show_results'] = $request->has('show_results');

            // Set creator
            $validated['created_by'] = auth()->id();

            // Create question module
            $questionModule = QuestionModule::create($validated);

            // Get next sort order for the section
            $sortOrder = CourseModule::where('section_id', $section->id)->max('sort_order') + 1;

            // Create corresponding CourseModule
            $courseModule = CourseModule::create([
                'course_id' => $section->course_id,
                'section_id' => $section->id,
                'module_type' => 'question_module',
                'modulable_id' => $questionModule->id,
                'modulable_type' => QuestionModule::class,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'sort_order' => $sortOrder,
                'is_visible' => $validated['is_visible'],
                'is_required' => false,
                'available_from' => $validated['available_from'] ?? null,
                'available_until' => $validated['available_until'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('question-modules.manage-questions', $questionModule->id)
                ->with('success', 'تم إنشاء وحدة الأسئلة بنجاح. يمكنك الآن إضافة الأسئلة');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء وحدة الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified question module.
     */
    public function show($id)
    {
        $questionModule = QuestionModule::with(['questions', 'creator', 'updater'])
            ->findOrFail($id);

        $stats = [
            'total_questions' => $questionModule->getQuestionsCount(),
            'total_grade' => $questionModule->getTotalGrade(),
            'used_in_modules' => $questionModule->courseModules()->count(),
        ];

        return view('admin.pages.question-modules.show', compact('questionModule', 'stats'));
    }

    /**
     * Show the form for editing the specified question module.
     */
    public function edit($id)
    {
        $questionModule = QuestionModule::findOrFail($id);

        return view('admin.pages.question-modules.edit', compact('questionModule'));
    }

    /**
     * Update the specified question module in storage.
     */
    public function update(Request $request, $id)
    {
        $questionModule = QuestionModule::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
            'time_limit' => 'nullable|integer|min:0',
            'shuffle_questions' => 'nullable|boolean',
            'show_results' => 'nullable|boolean',
            'pass_percentage' => 'nullable|numeric|min:0|max:100',
            'attempts_allowed' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Convert boolean fields
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible');
            $validated['shuffle_questions'] = $request->has('shuffle_questions');
            $validated['show_results'] = $request->has('show_results');

            // Set updater
            $validated['updated_by'] = auth()->id();

            $questionModule->update($validated);

            // Update corresponding CourseModule if exists
            $courseModule = $questionModule->module;
            if ($courseModule) {
                $courseModule->update([
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'is_visible' => $validated['is_visible'],
                    'available_from' => $validated['available_from'] ?? null,
                    'available_until' => $validated['available_until'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('question-modules.show', $questionModule->id)
                ->with('success', 'تم تحديث وحدة الأسئلة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث وحدة الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question module from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $questionModule = QuestionModule::findOrFail($id);

            // Delete associated CourseModules
            $questionModule->courseModules()->delete();

            // Delete the question module (soft delete)
            $questionModule->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم حذف وحدة الأسئلة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف وحدة الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * Show the page to manage questions in the module.
     */
    public function manageQuestions($id)
    {
        $questionModule = QuestionModule::with(['questions.questionType', 'questions.options'])
            ->findOrFail($id);

        // Get the course from the first course module
        $courseModule = $questionModule->courseModules()->first();
        $course = $courseModule ? $courseModule->course : null;

        // Get available questions from the question bank
        $availableQuestions = QuestionBank::with(['questionType', 'options'])
            ->where(function($query) use ($course) {
                if ($course) {
                    $query->where('course_id', $course->id)
                          ->orWhereNull('course_id');
                } else {
                    $query->whereNull('course_id');
                }
            })
            ->where('is_active', true)
            ->whereNotIn('id', $questionModule->questions()->pluck('question_bank.id'))
            ->orderBy('created_at', 'desc')
            ->get();

        // Get question types for creating new questions
        $questionTypes = \App\Models\QuestionType::where('is_active', true)->get();

        return view('admin.pages.question-modules.manage-questions', compact('questionModule', 'availableQuestions', 'course', 'questionTypes'));
    }

    /**
     * Add a question to the module (AJAX).
     */
    public function addQuestion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:question_bank,id',
                'question_grade' => 'nullable|numeric|min:0',
            ]);

            $questionModule = QuestionModule::findOrFail($id);
            $grade = $validated['question_grade'] ?? 1.0;

            // Check if question is already added
            if ($questionModule->questions()->where('question_id', $validated['question_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'السؤال موجود بالفعل في هذه الوحدة',
                ], 400);
            }

            $questionModule->addQuestion($validated['question_id'], $grade);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a question from the module (AJAX).
     */
    public function removeQuestion($id, $questionId)
    {
        try {
            $questionModule = QuestionModule::findOrFail($id);
            $questionModule->removeQuestion($questionId);

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
     * Update question settings in the module (AJAX).
     */
    public function updateQuestionSettings(Request $request, $id, $questionId)
    {
        try {
            $validated = $request->validate([
                'question_grade' => 'required|numeric|min:0',
            ]);

            $questionModule = QuestionModule::findOrFail($id);
            $questionModule->updateQuestionSettings($questionId, [
                'question_grade' => $validated['question_grade'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث إعدادات السؤال بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث إعدادات السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder questions in the module (AJAX).
     */
    public function reorderQuestions(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'exists:question_bank,id',
            ]);

            $questionModule = QuestionModule::findOrFail($id);
            $questionModule->reorderQuestions($validated['question_ids']);

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
     * Toggle publish status.
     */
    public function togglePublish($id)
    {
        try {
            $questionModule = QuestionModule::findOrFail($id);
            $questionModule->is_published = !$questionModule->is_published;
            $questionModule->updated_by = auth()->id();
            $questionModule->save();

            $status = $questionModule->is_published ? 'منشور' : 'مسودة';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة النشر إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة النشر: ' . $e->getMessage());
        }
    }

    /**
     * Toggle visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $questionModule = QuestionModule::findOrFail($id);
            $questionModule->is_visible = !$questionModule->is_visible;
            $questionModule->updated_by = auth()->id();
            $questionModule->save();

            // Update corresponding CourseModule
            $courseModule = $questionModule->module;
            if ($courseModule) {
                $courseModule->is_visible = $questionModule->is_visible;
                $courseModule->save();
            }

            $status = $questionModule->is_visible ? 'مرئي' : 'مخفي';

            return redirect()
                ->back()
                ->with('success', "تم تحديث الظهور إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث الظهور: ' . $e->getMessage());
        }
    }

    /**
     * Show import questions page with filters.
     */
    public function importQuestions(Request $request, $id)
    {
        $questionModule = QuestionModule::with(['questions'])
            ->findOrFail($id);

        // Get the course from the first course module
        $courseModule = $questionModule->courseModules()->first();
        $course = $courseModule ? $courseModule->course : null;

        // Build query for available questions
        $query = QuestionBank::with(['questionType', 'course', 'creator', 'programmingLanguages'])
            ->where('is_active', true)
            ->whereNotIn('id', $questionModule->questions()->pluck('question_bank.id'))
            ->orderBy('created_at', 'desc');

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by question type
        if ($request->filled('question_type_id')) {
            $query->where('question_type_id', $request->question_type_id);
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Filter by programming language
        if ($request->filled('language_id')) {
            $query->whereHas('programmingLanguages', function($q) use ($request) {
                $q->where('programming_languages.id', $request->language_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('question_text', 'like', '%' . $request->search . '%')
                  ->orWhere('explanation', 'like', '%' . $request->search . '%');
            });
        }

        $availableQuestions = $query->paginate(20)->withQueryString();
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();

        return view('admin.pages.question-modules.import-questions', compact(
            'questionModule',
            'availableQuestions',
            'courses',
            'questionTypes',
            'programmingLanguages',
            'course'
        ));
    }
}
