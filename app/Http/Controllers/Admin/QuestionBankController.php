<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\QuestionOption;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\ProgrammingLanguage;
use App\Services\QuestionBank\TypeImport\ImportDefaultsResolver;
use App\Services\QuestionBankExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuestionBankController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = QuestionBank::with(['questionType', 'course', 'creator', 'programmingLanguages'])
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

        // Filter by tags
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('question_text', 'like', '%' . $request->search . '%')
                  ->orWhere('explanation', 'like', '%' . $request->search . '%');
            });
        }

        $questions = $query->paginate(20)->withQueryString();
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'types' => $questionTypes->count(),
            'courses' => $courses->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.question-bank._questions_table', compact('questions'))->render(),
                'stats_html' => view('admin.pages.question-bank.partials.stats', compact('stats'))->render(),
                'count' => $questions->total(),
            ]);
        }

        return view('admin.pages.question-bank.index', compact(
            'questions',
            'courses',
            'questionTypes',
            'programmingLanguages',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request)
    {
        $questionTypes = QuestionType::where('is_active', true)->get();
        $courses = \App\Models\Course::where('is_published', true)->get();

        return view('admin.pages.question-bank.select-type', compact('questionTypes', 'courses'));
    }

    /**
     * Show the form for creating a question of specific type.
     */
    public function createByType($type, Request $request)
    {
        $questionType = QuestionType::where('name', $type)->where('is_active', true)->firstOrFail();
        $courses = Course::where('is_published', true)->get();

        // Get section or question module context
        $sectionContext = session('question_creation_context');
        $selectedCourseId = $sectionContext['course_id'] ?? null;

        // Check if coming from question module
        if ($request->has('question_module_id')) {
            $questionModule = \App\Models\QuestionModule::find($request->question_module_id);
            if ($questionModule) {
                $courseModule = $questionModule->courseModules()->first();
                if ($courseModule) {
                    $selectedCourseId = $courseModule->course_id;
                    // Store in session for use after creation
                    session(['question_creation_context' => [
                        'question_module_id' => $questionModule->id,
                        'course_id' => $courseModule->course_id,
                    ]]);
                }
            }
        }

        // Map type name to view
        $viewMap = [
            'multiple_choice_single' => 'multiple-choice-single',
            'multiple_choice_multiple' => 'multiple-choice-multiple',
            'true_false' => 'true-false',
            'short_answer' => 'short-answer',
            'essay' => 'essay',
            'matching' => 'matching',
            'ordering' => 'ordering',
            'fill_blank' => 'fill-blank',
            'fill_blanks' => 'fill-blank',
            'numerical' => 'numerical',
            'calculated' => 'numerical',
            'drag_drop' => 'drag-drop',
        ];

        $viewName = $viewMap[$type] ?? 'multiple-choice-single';
        $viewPath = "admin.pages.question-bank.types.{$viewName}";

        // Check if view exists, otherwise use default
        if (!view()->exists($viewPath)) {
            $viewPath = "admin.pages.question-bank.types.multiple-choice-single";
        }

        return view($viewPath, compact('questionType', 'courses', 'selectedCourseId'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'question_type_id' => 'required|exists:question_types,id',
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'default_grade' => 'required|numeric|min:0',
            'difficulty_level' => 'required|in:easy,medium,hard,expert',
            'tags' => 'nullable|string',
            'metadata' => 'nullable|array',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|in:0,1',
            'shuffle_options' => 'nullable',
            // Essay specific
            'min_words' => 'nullable|integer|min:0',
            'max_words' => 'nullable|integer|min:0',
            'allow_attachments' => 'nullable',
            'model_answer' => 'nullable|string',
            'grading_criteria' => 'nullable|string',
            // True/False specific
            'correct_answer' => 'nullable|in:true,false',
            'correct_option' => 'nullable|integer',
        ]);

        // Handle checkbox
        $validated['is_active'] = $request->has('is_active') && $request->is_active == '1' ? 1 : 0;

        // Handle image upload
        if ($request->hasFile('question_image')) {
            $validated['question_image'] = $request->file('question_image')->store('question-images', 'public');
        }

        // Handle tags (convert comma-separated string to array)
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $validated['tags'] = array_filter($tags);
        } else {
            $validated['tags'] = null;
        }

        // Build metadata for specific question types
        $metadata = $validated['metadata'] ?? [];
        if ($request->filled('min_words')) $metadata['min_words'] = $validated['min_words'];
        if ($request->filled('max_words')) $metadata['max_words'] = $validated['max_words'];
        if ($request->has('allow_attachments')) $metadata['allow_attachments'] = $request->has('allow_attachments');
        if ($request->filled('model_answer')) $metadata['model_answer'] = $validated['model_answer'];
        if ($request->filled('grading_criteria')) $metadata['grading_criteria'] = $validated['grading_criteria'];
        if ($request->has('shuffle_options')) $metadata['shuffle_options'] = $request->has('shuffle_options');
        $validated['metadata'] = $metadata;

        // Set creator
        $validated['created_by'] = auth()->id();

        // Get section context before transaction
        $sectionContext = session('question_creation_context');

        DB::beginTransaction();
        try {
            $question = QuestionBank::create($validated);

            // Create question options
            if ($request->has('options')) {
                $correctOption = $request->input('correct_option');
                $correctAnswer = $request->input('correct_answer');
                $this->createQuestionOptions($question, $request->input('options'), $correctOption, $correctAnswer);
            }

            // Handle matching pairs
            if ($request->has('matching_pairs')) {
                $this->createMatchingOptions($question, $request->input('matching_pairs'));
            }

            // Handle drag and drop zones
            if ($request->has('drop_zones')) {
                $this->createDragDropOptions($question, $request->input('drop_zones'));
            }

            // Handle fill in the blanks answers
            if ($request->has('correct_answers')) {
                $this->createFillBlanksOptions($question, $request->input('correct_answers'), $request->has('case_sensitive'));
            }

            // Handle ordering items
            if ($request->has('order_items')) {
                $this->createOrderingOptions($question, $request->input('order_items'));
            }

            // Check if question was created from section context
            if ($sectionContext && isset($sectionContext['section_id'])) {
                // Link question to section
                $section = CourseSection::find($sectionContext['section_id']);
                if ($section) {
                    $maxOrder = $section->questions()->max('course_section_questions.question_order') ?? 0;
                    $section->questions()->attach($question->id, [
                        'question_order' => $maxOrder + 1,
                        'question_grade' => $question->default_grade,
                        'is_required' => true,
                    ]);
                }
            }

            // Check if question was created from question module context
            if ($sectionContext && isset($sectionContext['question_module_id'])) {
                // Link question to question module
                $questionModule = \App\Models\QuestionModule::find($sectionContext['question_module_id']);
                if ($questionModule) {
                    $maxOrder = $questionModule->questions()->max('question_module_questions.question_order') ?? 0;
                    $questionModule->questions()->attach($question->id, [
                        'question_order' => $maxOrder + 1,
                        'question_grade' => $question->default_grade,
                    ]);
                }
            }

            DB::commit();

            // Clear session context after successful commit
            if ($sectionContext) {
                session()->forget('question_creation_context');
            }

            // Redirect based on context
            if ($sectionContext && isset($sectionContext['question_module_id'])) {
                return redirect()->route('question-modules.manage-questions', $sectionContext['question_module_id'])
                    ->with('success', 'تم إنشاء السؤال وربطه بوحدة الأسئلة بنجاح');
            }

            if ($sectionContext && isset($sectionContext['section_id'])) {
                return redirect()->route('sections.questions.manage', $sectionContext['section_id'])
                    ->with('success', 'تم إنشاء السؤال وربطه بالقسم بنجاح');
            }

            return redirect()->route('question-bank.show', $question->id)
                ->with('success', 'تم إنشاء السؤال بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء إنشاء السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified question.
     */
    public function show($id)
    {
        $question = QuestionBank::with([
            'questionType',
            'course',
            'creator',
            'options',
            'quizQuestions.quiz',
            'responses',
            'pools'
        ])->findOrFail($id);

        // Get usage statistics
        $stats = [
            'times_used' => $question->quizQuestions()->count(),
            'total_responses' => $question->responses()->count(),
            'correct_responses' => $question->responses()->where('is_correct', true)->count(),
            'average_score' => $question->responses()
                ->whereNotNull('score_obtained')
                ->avg('score_obtained'),
            'average_time' => $question->responses()
                ->whereNotNull('time_spent')
                ->avg('time_spent'),
        ];

        if ($stats['total_responses'] > 0) {
            $stats['success_rate'] = ($stats['correct_responses'] / $stats['total_responses']) * 100;
        } else {
            $stats['success_rate'] = 0;
        }

        return view('admin.pages.question-bank.show', compact('question', 'stats'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit($id)
    {
        $question = QuestionBank::with(['options', 'questionType'])->findOrFail($id);
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();

        return view('admin.pages.question-bank.edit', compact('question', 'courses', 'questionTypes'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, $id)
    {
        $question = QuestionBank::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'question_type_id' => 'required|exists:question_types,id',
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'default_grade' => 'required|numeric|min:0',
            'difficulty_level' => 'required|in:easy,medium,hard,expert',
            'tags' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle checkbox
        $validated['is_active'] = $request->has('is_active') && $request->is_active == '1' ? 1 : 0;

        // Handle tags (convert comma-separated string to array)
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $validated['tags'] = array_filter($tags);
        } else {
            $validated['tags'] = null;
        }

        // Handle image upload
        if ($request->hasFile('question_image')) {
            // Delete old image if exists
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $validated['question_image'] = $request->file('question_image')->store('question-images', 'public');
        }

        // Set updater
        $validated['updated_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $question->update($validated);

            // Update or create question options
            if ($request->has('options')) {
                // Delete old options
                $question->options()->delete();

                // Create new options
                $this->createQuestionOptions($question, $request->input('options'));
            }

            DB::commit();

            return redirect()->route('question-bank.show', $question->id)
                ->with('success', 'تم تحديث السؤال بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified question.
     */
    public function destroy($id)
    {
        $question = QuestionBank::findOrFail($id);

        try {
            $question->delete();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف السؤال بنجاح',
                    'question_id' => $id
                ]);
            }

            return redirect()->route('question-bank.index')
                ->with('success', 'تم حذف السؤال بنجاح');
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف السؤال: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove multiple questions.
     */
    public function destroyMultiple(Request $request)
    {
        $validated = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:question_bank,id',
        ]);

        try {
            $questionIds = $validated['question_ids'];
            
            // Delete questions
            $deletedCount = QuestionBank::whereIn('id', $questionIds)->delete();

            return response()->json([
                'success' => true,
                'message' => "تم حذف {$deletedCount} سؤال بنجاح",
                'deleted_count' => $deletedCount,
                'question_ids' => $questionIds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الأسئلة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a question.
     */
    public function duplicate($id)
    {
        $question = QuestionBank::with('options')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Create duplicate question
            $duplicate = $question->replicate();
            $duplicate->question_text = $question->question_text . ' (نسخة)';
            $duplicate->created_by = auth()->id();
            $duplicate->updated_by = null;
            $duplicate->save();

            // Duplicate options
            foreach ($question->options as $option) {
                $duplicateOption = $option->replicate();
                $duplicateOption->question_id = $duplicate->id;
                $duplicateOption->save();
            }

            DB::commit();

            return redirect()->route('question-bank.edit', $duplicate->id)
                ->with('success', 'تم نسخ السؤال بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء نسخ السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Get question preview (AJAX).
     */
    public function preview($id)
    {
        $question = QuestionBank::with(['questionType', 'options'])->findOrFail($id);

        return response()->json([
            'question' => $question,
            'html' => view('admin.pages.question-bank.partials.preview', compact('question'))->render()
        ]);
    }

    /**
     * Get questions by course (AJAX).
     */
    public function getQuestionsByCourse($courseId)
    {
        $questions = QuestionBank::where('course_id', $courseId)
            ->where('is_active', true)
            ->where('is_reusable', true)
            ->with('questionType')
            ->get(['id', 'question_text', 'question_type_id', 'points', 'difficulty']);

        return response()->json($questions);
    }

    /**
     * Get questions by type (AJAX).
     */
    public function getQuestionsByType($typeId)
    {
        $questions = QuestionBank::where('question_type_id', $typeId)
            ->where('is_active', true)
            ->where('is_reusable', true)
            ->with('course')
            ->get(['id', 'question_text', 'course_id', 'points', 'difficulty']);

        return response()->json($questions);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete,export',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:question_bank,id',
        ]);

        try {
            $questions = QuestionBank::whereIn('id', $validated['question_ids']);

            switch ($validated['action']) {
                case 'activate':
                    $questions->update(['is_active' => true, 'updated_by' => auth()->id()]);
                    $message = 'تم تفعيل الأسئلة المحددة بنجاح';
                    break;

                case 'deactivate':
                    $questions->update(['is_active' => false, 'updated_by' => auth()->id()]);
                    $message = 'تم إلغاء تفعيل الأسئلة المحددة بنجاح';
                    break;

                case 'delete':
                    $questions->delete();
                    $message = 'تم حذف الأسئلة المحددة بنجاح';
                    break;

                case 'export':
                    // This would export questions to JSON or CSV
                    return $this->exportQuestions($validated['question_ids']);

                default:
                    return back()->withErrors(['error' => 'إجراء غير صالح']);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء تنفيذ الإجراء: ' . $e->getMessage()]);
        }
    }

    /**
     * Create question options.
     */
    private function createQuestionOptions(QuestionBank $question, array $options, $correctOption = null, $correctAnswer = null): void
    {
        foreach ($options as $index => $optionData) {
            // Determine if this option is correct
            $isCorrect = false;

            // For multiple choice single - check if this is the selected correct option
            if ($correctOption !== null && $index == $correctOption) {
                $isCorrect = true;
            }
            // For true/false - check correct_answer
            elseif ($correctAnswer !== null) {
                $optionText = strtolower($optionData['option_text'] ?? '');
                if (($correctAnswer === 'true' && $optionText === 'صح') ||
                    ($correctAnswer === 'false' && $optionText === 'خطأ')) {
                    $isCorrect = true;
                }
            }
            // Check if is_correct is set in option data
            elseif (isset($optionData['is_correct'])) {
                $isCorrect = (bool) $optionData['is_correct'];
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'] ?? null,
                'is_correct' => $isCorrect,
                'option_order' => $optionData['option_order'] ?? $index + 1,
                'score_weight' => $optionData['score_weight'] ?? 1.0,
                'feedback' => $optionData['feedback'] ?? null,
                'match_pair_id' => $optionData['match_pair_id'] ?? null,
                'media_type' => $optionData['media_type'] ?? 'text',
                'media_url' => $optionData['media_url'] ?? null,
            ]);
        }
    }

    /**
     * Create matching question options.
     */
    private function createMatchingOptions(QuestionBank $question, array $matchingPairs): void
    {
        $order = 1;
        foreach ($matchingPairs as $pairId => $pair) {
            if (empty($pair['question']) || empty($pair['answer'])) {
                continue;
            }

            // Create option with question text and matching answer
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $pair['question'],
                'is_correct' => true,
                'option_order' => $order,
                'match_pair_id' => $pairId,
                'feedback' => $pair['answer'], // Store the matching answer in feedback field
            ]);

            $order++;
        }
    }

    /**
     * Create drag and drop question options.
     */
    private function createDragDropOptions(QuestionBank $question, array $dropZones): void
    {
        $order = 1;
        foreach ($dropZones as $zoneId => $zone) {
            if (empty($zone['label']) || empty($zone['correct_item'])) {
                continue;
            }

            // Create option with zone label and correct item
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $zone['label'],
                'is_correct' => true,
                'option_order' => $order,
                'match_pair_id' => $zoneId,
                'feedback' => $zone['correct_item'], // Store the correct item in feedback field
            ]);

            $order++;
        }
    }

    /**
     * Create fill in the blanks question options.
     */
    private function createFillBlanksOptions(QuestionBank $question, array $correctAnswers, bool $caseSensitive = false): void
    {
        $order = 1;
        foreach ($correctAnswers as $answer) {
            if (empty($answer)) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $answer,
                'is_correct' => true,
                'option_order' => $order,
                'feedback' => $caseSensitive ? 'case_sensitive' : null,
            ]);

            $order++;
        }
    }

    /**
     * Create ordering question options.
     */
    private function createOrderingOptions(QuestionBank $question, array $orderItems): void
    {
        $order = 1;
        foreach ($orderItems as $item) {
            if (empty($item)) {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $item,
                'is_correct' => true,
                'option_order' => $order,
            ]);

            $order++;
        }
    }

    /**
     * Export questions to JSON.
     */
    private function exportQuestions(array $questionIds)
    {
        $questions = QuestionBank::with(['questionType', 'options', 'course'])
            ->whereIn('id', $questionIds)
            ->get();

        $exportData = $questions->map(function ($question) {
            return [
                'question_text' => $question->question_text,
                'question_type' => $question->questionType->name,
                'course' => $question->course->title,
                'difficulty' => $question->difficulty,
                'points' => $question->points,
                'explanation' => $question->explanation,
                'tags' => $question->tags,
                'metadata' => $question->metadata,
                'options' => $question->options->map(function ($option) {
                    return [
                        'option_text' => $option->option_text,
                        'is_correct' => $option->is_correct,
                        'option_order' => $option->option_order,
                        'feedback' => $option->feedback,
                    ];
                }),
            ];
        });

        $filename = 'questions_export_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($exportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show Excel import form.
     */
    public function showImportForm()
    {
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();
        
        return view('admin.pages.question-bank.import-excel', compact('courses', 'questionTypes', 'programmingLanguages'));
    }

    /**
     * Preview Excel import data before processing.
     */
    public function previewImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|mimes:xlsx,xls|max:10240',
            'default_course_id' => 'nullable|exists:courses,id',
            'default_programming_language_id' => 'nullable|exists:programming_languages,id',
        ], [
            'excel_file.required' => 'يرجى اختيار ملف Excel',
            'excel_file.mimes' => 'يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)',
            'excel_file.max' => 'حجم الملف يجب أن يكون أقل من 10 ميجابايت',
            'default_course_id.exists' => 'الكورس المحدد غير موجود',
            'default_programming_language_id.exists' => 'اللغة البرمجية المحددة غير موجودة',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $excel = app(QuestionBankExcelImportService::class);
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $excel->getQuestionsWorksheet($spreadsheet);
            $rows = $worksheet->toArray();

            $headerRow = array_shift($rows) ?? [];
            $legacy = $excel->isLegacyHeaderRow($headerRow);

            $parsedData = [];
            $errors = [];
            $questionTypes = QuestionType::where('is_active', true)->get();
            $resolver = new ImportDefaultsResolver;
            $defaultCourseId = $request->filled('default_course_id') ? (int) $request->input('default_course_id') : null;
            $defaultLanguageId = $request->filled('default_programming_language_id') ? (int) $request->input('default_programming_language_id') : null;
            $courseSatisfied = $resolver->hasValidDefaultCourse($defaultCourseId);

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;

                if (empty(array_filter($row, fn ($c) => trim((string) ($c ?? '')) !== ''))) {
                    continue;
                }

                $questionData = $legacy
                    ? $excel->buildRowFromLegacy($row, $rowNumber)
                    : $excel->buildRowFromMapped($headerRow, $row, $rowNumber);

                $applied = $resolver->apply($questionData, $defaultCourseId, $defaultLanguageId);
                $questionData = $applied['row'];
                $questionData['course_from_default'] = $applied['course_from_default'];
                $questionData['language_from_default'] = $applied['language_from_default'];

                $resolvedType = $excel->resolveQuestionType($questionData['question_type'] ?? '', $questionTypes);
                $rowErrors = $excel->validateRowForType($questionData, $resolvedType, $courseSatisfied);

                if ($rowErrors !== []) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $rowErrors,
                    ];
                }

                $parsedData[] = $questionData;
            }

            $errorRowIds = array_unique(array_column($errors, 'row'));
            $validRows = count(array_filter($parsedData, fn ($r) => ! in_array($r['row_number'], $errorRowIds, true)));

            $typeMapping = [];
            foreach ($questionTypes as $type) {
                $typeMapping[$type->display_name] = $type->id;
                $typeMapping[$type->name] = $type->id;
            }

            $courses = Course::where('is_published', true)->get();
            $courseMapping = [];
            foreach ($courses as $course) {
                $courseMapping[$course->title] = $course->id;
            }

            $programmingLanguages = ProgrammingLanguage::active()->get();
            $languageMapping = [];
            foreach ($programmingLanguages as $lang) {
                $languageMapping[$lang->name] = $lang->id;
                $languageMapping[$lang->display_name] = $lang->id;
            }

            return response()->json([
                'success' => true,
                'data' => $parsedData,
                'errors' => $errors,
                'type_mapping' => $typeMapping,
                'course_mapping' => $courseMapping,
                'language_mapping' => $languageMapping,
                'total_rows' => count($parsedData),
                'valid_rows' => $validRows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process and import Excel data.
     */
    public function processImport(Request $request)
    {
        // Logging: Start of processImport
        Log::info('Question Import: Starting processImport', [
            'user_id' => auth()->id(),
            'has_excel_file' => $request->hasFile('excel_file'),
            'has_questions_data' => $request->has('questions_data'),
            'headers' => [
                'X-Requested-With' => $request->header('X-Requested-With'),
                'Accept' => $request->header('Accept'),
                'Content-Type' => $request->header('Content-Type'),
            ],
            'request_method' => $request->method(),
        ]);

        // Check if request expects JSON (AJAX)
        $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        
        Log::info('Question Import: expectsJson check', [
            'expectsJson' => $expectsJson,
            'expectsJson_method' => $request->expectsJson(),
            'wantsJson_method' => $request->wantsJson(),
            'ajax_method' => $request->ajax(),
            'X-Requested-With_header' => $request->header('X-Requested-With'),
        ]);
        
        $validator = Validator::make($request->all(), [
            'excel_file' => 'nullable|mimes:xlsx,xls|max:10240',
            'questions_data' => 'required|json',
            'default_course_id' => 'nullable|exists:courses,id',
            'default_programming_language_id' => 'nullable|exists:programming_languages,id',
        ], [
            'excel_file.mimes' => 'ملف Excel يجب أن يكون بصيغة .xlsx أو .xls',
            'questions_data.required' => 'بيانات الأسئلة مطلوبة',
            'questions_data.json' => 'بيانات الأسئلة يجب أن تكون بصيغة JSON',
            'default_course_id.exists' => 'الكورس المحدد غير موجود',
            'default_programming_language_id.exists' => 'اللغة البرمجية المحددة غير موجودة',
        ]);

        Log::info('Question Import: Validation check', [
            'validation_passed' => !$validator->fails(),
            'validation_errors' => $validator->fails() ? $validator->errors()->toArray() : null,
        ]);

        if ($validator->fails()) {
            Log::warning('Question Import: Validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            Log::info('Question Import: Decoding questions_data', [
                'questions_data_length' => strlen($request->questions_data ?? ''),
            ]);
            
            $questionsData = json_decode($request->questions_data, true);
            
            Log::info('Question Import: After json_decode', [
                'is_array' => is_array($questionsData),
                'questions_count' => is_array($questionsData) ? count($questionsData) : 0,
                'json_error' => json_last_error_msg(),
            ]);
            
            if (!is_array($questionsData)) {
                Log::error('Question Import: questions_data is not an array', [
                    'questions_data_type' => gettype($questionsData),
                    'json_error' => json_last_error_msg(),
                ]);
                
                if ($expectsJson) {
                    return response()->json([
                        'success' => false,
                        'message' => 'بيانات غير صحيحة: ' . json_last_error_msg()
                    ], 422);
                }
                return back()->withErrors(['error' => 'بيانات غير صحيحة'])->withInput();
            }

            $questionTypes = QuestionType::where('is_active', true)->get();
            $typeMapping = [];
            foreach ($questionTypes as $type) {
                $typeMapping[$type->display_name] = $type;
                $typeMapping[$type->name] = $type;
            }

            $courses = Course::where('is_published', true)->get();
            $courseMapping = [];
            foreach ($courses as $course) {
                $courseMapping[$course->title] = $course->id;
            }

            // Get programming languages mapping
            $programmingLanguages = ProgrammingLanguage::active()->get();
            $languageMapping = [];
            foreach ($programmingLanguages as $lang) {
                $languageMapping[$lang->name] = $lang->id;
                $languageMapping[$lang->display_name] = $lang->id;
            }

            // Get default programming language from request (if provided)
            $defaultLanguageId = null;
            if ($request->filled('default_programming_language_id')) {
                $defaultLanguageId = $request->input('default_programming_language_id');
            }

            $defaultCourseId = $request->filled('default_course_id') ? (int) $request->input('default_course_id') : null;
            $resolver = new ImportDefaultsResolver;

            Log::info('Question Import: Before transaction', [
                'questions_count' => count($questionsData),
                'default_course_id' => $defaultCourseId,
                'default_language_id' => $defaultLanguageId,
                'type_mapping_count' => count($typeMapping),
                'course_mapping_count' => count($courseMapping),
                'language_mapping_count' => count($languageMapping),
            ]);

            DB::beginTransaction();
            
            Log::info('Question Import: Transaction started');

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($questionsData as $index => $questionData) {
                try {
                    $applied = $resolver->apply($questionData, $defaultCourseId, $defaultLanguageId ? (int) $defaultLanguageId : null);
                    $questionData = $applied['row'];

                    Log::debug('Question Import: Processing question', [
                        'index' => $index,
                        'row_number' => $questionData['row_number'] ?? null,
                        'question_type' => $questionData['question_type'] ?? null,
                        'question_text_preview' => substr($questionData['question_text'] ?? '', 0, 50),
                    ]);
                    
                    // Get question type
                    $questionTypeName = $questionData['question_type'] ?? '';
                    $questionType = $typeMapping[$questionTypeName] ?? null;
                    
                    if (!$questionType) {
                        Log::warning('Question Import: Question type not found', [
                            'index' => $index,
                            'question_type_name' => $questionTypeName,
                        ]);
                        $skipped++;
                        $errors[] = "السطر " . ($index + 1) . ": نوع السؤال غير صحيح";
                        continue;
                    }

                    // Get course ID - REQUIRED
                    if (empty($questionData['course'])) {
                        $skipped++;
                        $errors[] = "السطر " . ($index + 1) . ": اسم الكورس مطلوب";
                        continue;
                    }

                    $courseId = $courseMapping[$questionData['course']] ?? null;
                    if (!$courseId) {
                        $skipped++;
                        $courseName = $questionData['course'] ?? 'غير محدد';
                        $availableCourses = implode(', ', array_slice(array_keys($courseMapping), 0, 5));
                        $errors[] = "السطر " . ($index + 1) . ": الكورس '" . $courseName . "' غير موجود في النظام. الكورسات المتاحة: " . ($availableCourses ?: 'لا توجد كورسات متاحة');
                        Log::warning('Question Import: Course not found', [
                            'index' => $index,
                            'course_name' => $courseName,
                            'available_courses' => array_keys($courseMapping),
                        ]);
                        continue;
                    }

                    Log::debug('Question Import: Creating question', [
                        'index' => $index,
                        'course_id' => $courseId,
                        'question_type_id' => $questionType->id,
                    ]);

                    $question = $this->createQuestionFromExcelImportRow(
                        $questionData,
                        $questionType,
                        $courseId,
                        $languageMapping,
                        $defaultLanguageId
                    );

                    Log::debug('Question Import: Question imported', [
                        'question_id' => $question->id,
                        'index' => $index,
                    ]);

                    $imported++;
                    Log::debug('Question Import: Question imported successfully', [
                        'question_id' => $question->id,
                        'imported_count' => $imported,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Question Import: Error processing question', [
                        'index' => $index,
                        'row_number' => $questionData['row_number'] ?? null,
                        'error_message' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString(),
                    ]);
                    $skipped++;
                    $errors[] = "السطر " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            Log::info('Question Import: Before commit', [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors_count' => count($errors),
            ]);

            DB::commit();
            
            Log::info('Question Import: Transaction committed successfully');

            $message = "تم استيراد {$imported} سؤال بنجاح";
            if ($skipped > 0) {
                $message .= "، تم تخطي {$skipped} سؤال";
            }

            // Check if request expects JSON (AJAX)
            $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            Log::info('Question Import: Preparing response', [
                'expectsJson' => $expectsJson,
                'imported' => $imported,
                'skipped' => $skipped,
                'message' => $message,
            ]);
            
            if ($expectsJson) {
                $response = [
                    'success' => true,
                    'message' => $message,
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ];
                Log::info('Question Import: Returning JSON response', $response);
                return response()->json($response);
            }

            Log::info('Question Import: Redirecting to index');
            return redirect()->route('question-bank.index')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Question Import: Exception caught', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => [
                    'has_excel_file' => $request->hasFile('excel_file'),
                    'has_questions_data' => $request->has('questions_data'),
                    'questions_data_length' => strlen($request->questions_data ?? ''),
                ],
            ]);
            
            // Check if request expects JSON (AJAX)
            $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null
                ], 500);
            }
            
            return back()->withErrors(['error' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Download Excel template.
     */
    public function downloadTemplate()
    {
        $excel = app(QuestionBankExcelImportService::class);

        $spreadsheet = new Spreadsheet;
        $guide = $spreadsheet->getActiveSheet();
        $guide->setTitle(QuestionBankExcelImportService::SHEET_GUIDE);

        $guide->fromArray(['نوع السؤال', 'الأعمدة المطلوبة', 'ملاحظات'], null, 'A1');
        $guideRows = [
            [
                'الكورس واللغة البرمجية',
                'يمكن تركهما فارغين في الملف',
                'حددهما من واجهة الاستيراد؛ قيم الملف لها الأولوية عند وجودها',
            ],
            [
                'اختيار من متعدد (إجابة واحدة)',
                'الخيارات 1–6، الإجابة الصحيحة (رقم الخيار)',
                'مثال الإجابة: 1',
            ],
            [
                'اختيار من متعدد (إجابات متعددة)',
                'الخيارات، الإجابة الصحيحة كأرقام مفصولة بفاصلة إنجليزية',
                'مثال: 1,3',
            ],
            [
                'صح / خطأ',
                'الإجابة الصحيحة: true أو false أو 1 أو 2 أو صح أو خطأ',
                'لا حاجة لملء أعمدة الخيارات',
            ],
            [
                'إجابة قصيرة',
                'إجابات مقبولة (افصل بين البدائل بـ |) أو عمود الإجابة الصحيحة لبديل واحد',
                'حساس لحالة الأحرف: نعم أو لا',
            ],
            [
                'مقالي (إجابة طويلة)',
                'الحد الأدنى/الأقصى للكلمات، إجابة نموذجية، معايير التقييم (اختياري)',
                'لا حاجة لإجابة صحيحة آلية',
            ],
            [
                'مطابقة',
                'أزواج المطابقة: سؤال1||إجابة1;;;سؤال2||إجابة2',
                'استخدم || بين الطرفين و;;; بين الأزواج',
            ],
            [
                'ملء الفراغات',
                'نص السؤال يحتوي [[blank]] أو ___ ، وإجابات مقبولة بنفس تنسيق الإجابة القصيرة',
                'ترتب الإجابات كتسلسل الفراغات',
            ],
            [
                'ترتيب',
                'الخيار 1 ثم 2… بالترتيب الصحيح (سيتم خلطها للطالب)',
                'على الأقل عنصران',
            ],
            [
                'إجابة رقمية',
                'الإجابة الصحيحة، هامش الخطأ، الوحدة (اختياري)',
                'الأرقام تقبل الفاصلة العشرية بنقطة أو فاصلة',
            ],
            [
                'محسوب (معادلات)',
                'مثل الرقمي + عمود المعادلة (اختياري) والإجابة المتوقعة',
                'التقييم يعتمد على الإجابة الرقمية والهامش',
            ],
        ];
        $guide->fromArray($guideRows, null, 'A2');
        $guide->getStyle('A1:C1')->getFont()->setBold(true);

        $questionsSheet = new Worksheet($spreadsheet, QuestionBankExcelImportService::SHEET_QUESTIONS);
        $spreadsheet->addSheet($questionsSheet, 1);

        $headers = QuestionBankExcelImportService::templateHeadersOrder();
        $questionsSheet->fromArray($headers, null, 'A1');

        $coursePlaceholder = '';

        $exampleRows = [
            ['اختيار من متعدد (إجابة واحدة)', 'ما عاصمة السعودية؟', 'درس الجغرافيا', 'الرياض', 'جدة', 'الدمام', 'مكة', '', '', '1', '', '', '', '1', 'easy', $coursePlaceholder, 'شرح', 'وسم1', '', '', '', '', '', '', '', ''],
            ['اختيار من متعدد (إجابات متعددة)', 'اختر اللغات البرمجية', '', 'PHP', 'Python', 'C#', 'HTML', '', '', '1,2', '', '', '', '2', 'medium', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['صح / خطأ', 'الشمس تشرق من الغرب', '', '', '', '', '', '', '', 'false', '', '', '', '1', 'easy', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['إجابة قصيرة', 'ما ناتج 2+2؟', 'درس الحساب', '', '', '', '', '', '', '', '4|أربعة|٤', 'لا', '', '1', 'easy', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['مقالي (إجابة طويلة)', 'ناقش مفهوم البرمجة كائنية التوجه', 'درس OOP', '', '', '', '', '', '', '', '', '', '', '10', 'medium', $coursePlaceholder, '', '', '', '', '', '50', '500', 'نموذج للمدرس', 'المحتوى والأسلوب', ''],
            ['مطابقة', 'طابق المصطلحات', '', '', '', '', '', '', '', '', '', '', 'متغير||variable;;;دالة||function', '2', 'medium', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['ملء الفراغات', 'عاصمة السعودية [[blank]]', '', '', '', '', '', '', '', '', 'الرياض', 'لا', '', '1', 'easy', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['ترتيب', 'رتّب مراحل الماء', '', 'تبخر', 'تكثف', 'هطول', 'جريان', '', '', '', '', '', '', '2', 'easy', $coursePlaceholder, '', '', '', '', '', '', '', '', '', ''],
            ['إجابة رقمية', 'ما ناتج 15 × 8؟', '', '', '', '', '', '', '', '120', '', '', '', '1', 'easy', $coursePlaceholder, '', '', '', '5', '', '', '', '', ''],
            ['محسوب (معادلات)', 'احسب الناتج', '', '', '', '', '', '', '', '100', '', '', '', '2', 'medium', $coursePlaceholder, '', '', '', '0', '', '', '', '', '2*50'],
        ];

        $questionsSheet->fromArray($exampleRows, null, 'A2');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true);
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $questionsSheet->getStyle('A1:'.$lastCol.'1')->getFont()->getColor()->setARGB('FFFFFFFF');

        for ($ci = 1; $ci <= count($headers); $ci++) {
            $questionsSheet->getColumnDimensionByColumn($ci)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(1);

        $filename = 'question_bank_template_'.date('Y-m-d').'.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'qb_tpl');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildExcelImportBaseMetadata(array $questionData): array
    {
        return [];
    }

    /**
     * @return array<int, string>|null
     */
    private function parseExcelImportTags(?string $tags): ?array
    {
        $tags = trim((string) $tags);
        if ($tags === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $tags))));
    }

    /**
     * Create one question from a parsed Excel row (types supported by import).
     */
    private function createQuestionFromExcelImportRow(
        array $questionData,
        QuestionType $questionType,
        int $courseId,
        array $languageMapping,
        ?int $defaultLanguageId
    ): QuestionBank {
        $excel = app(QuestionBankExcelImportService::class);
        $meta = $this->buildExcelImportBaseMetadata($questionData);
        $name = $questionType->name;

        switch ($name) {
            case 'short_answer':
            case 'fill_blanks':
                $answers = $excel->parseAcceptedAnswersList($questionData);
                $meta['correct_answers'] = $answers;
                $meta['case_sensitive'] = $excel->parseCaseSensitiveFlag($questionData['case_sensitive'] ?? '');
                break;

            case 'essay':
                if (($questionData['min_words'] ?? '') !== '') {
                    $meta['min_words'] = (int) $questionData['min_words'];
                }
                if (($questionData['max_words'] ?? '') !== '') {
                    $meta['max_words'] = (int) $questionData['max_words'];
                }
                if (! empty(trim((string) ($questionData['model_answer'] ?? '')))) {
                    $meta['model_answer'] = trim($questionData['model_answer']);
                }
                if (! empty(trim((string) ($questionData['grading_criteria'] ?? '')))) {
                    $meta['grading_criteria'] = trim($questionData['grading_criteria']);
                }
                break;

            case 'numerical':
            case 'calculated':
                $meta['correct_answer'] = floatval(str_replace(',', '.', (string) $questionData['correct_answer']));
                $tolRaw = trim((string) ($questionData['tolerance'] ?? ''));
                $meta['tolerance'] = $tolRaw !== '' ? floatval(str_replace(',', '.', $tolRaw)) : 0.0;
                if (! empty(trim((string) ($questionData['unit'] ?? '')))) {
                    $meta['unit'] = trim($questionData['unit']);
                }
                if ($name === 'calculated' && ! empty(trim((string) ($questionData['formula'] ?? '')))) {
                    $meta['formula'] = trim($questionData['formula']);
                }
                break;

            default:
                break;
        }

        $lessonName = trim((string) ($questionData['lesson_name'] ?? ''));

        $question = QuestionBank::create([
            'course_id' => $courseId,
            'question_type_id' => $questionType->id,
            'question_text' => $questionData['question_text'],
            'lesson_name' => $lessonName !== '' ? $lessonName : null,
            'explanation' => ! empty(trim((string) ($questionData['explanation'] ?? ''))) ? trim($questionData['explanation']) : null,
            'default_grade' => floatval($questionData['points'] ?? 1),
            'difficulty_level' => $this->mapDifficulty($questionData['difficulty'] ?? 'medium'),
            'tags' => $this->parseExcelImportTags($questionData['tags'] ?? null),
            'metadata' => $meta === [] ? null : $meta,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        switch ($name) {
            case 'multiple_choice_single':
            case 'multiple_choice_multiple':
                for ($i = 1; $i <= 6; $i++) {
                    $optionText = trim((string) ($questionData['option_'.$i] ?? ''));
                    if ($optionText === '') {
                        continue;
                    }
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => $this->isCorrectAnswer($questionData['correct_answer'] ?? '', $i),
                        'option_order' => $i,
                        'score_weight' => 1.0,
                    ]);
                }
                break;

            case 'true_false':
                $norm = $excel->normalizeTrueFalseAnswer($questionData['correct_answer'] ?? '');
                $this->createQuestionOptions($question, [
                    ['option_text' => 'صح', 'option_order' => 1],
                    ['option_text' => 'خطأ', 'option_order' => 2],
                ], null, $norm);
                break;

            case 'matching':
                $pairs = $excel->parseMatchingPairsRaw($questionData['matching_pairs_raw'] ?? '');
                $this->createMatchingOptions($question, $excel->matchingPairsForCreate($pairs));
                break;

            case 'ordering':
                $this->createOrderingOptions($question, $excel->nonEmptyOptions($questionData, 6));
                break;

            case 'fill_blanks':
                $answers = $meta['correct_answers'] ?? [];
                $this->createFillBlanksOptions($question, $answers, (bool) ($meta['case_sensitive'] ?? false));
                break;

            case 'short_answer':
            case 'essay':
            case 'numerical':
            case 'calculated':
                break;

            default:
                throw new \InvalidArgumentException('نوع السؤال غير مدعوم في الاستيراد: '.$name);
        }

        $languageId = null;
        if (! empty($questionData['language'])) {
            $languageName = trim($questionData['language']);
            $languageId = $languageMapping[$languageName] ?? null;
        }
        if (! $languageId && $defaultLanguageId) {
            $languageId = $defaultLanguageId;
        }
        if ($languageId) {
            $question->programmingLanguages()->attach($languageId);
        }

        return $question;
    }

    /**
     * Map difficulty string to enum value.
     */
    private function mapDifficulty($difficulty)
    {
        $mapping = [
            'سهل' => 'easy',
            'easy' => 'easy',
            'متوسط' => 'medium',
            'medium' => 'medium',
            'صعب' => 'hard',
            'hard' => 'hard',
            'خبير' => 'expert',
            'expert' => 'expert',
        ];

        return $mapping[strtolower($difficulty)] ?? 'medium';
    }

    /**
     * Check if option is correct answer.
     */
    private function isCorrectAnswer($correctAnswer, $optionNumber)
    {
        $correctAnswer = trim($correctAnswer);
        
        // Check if it's a number matching option number
        if (is_numeric($correctAnswer) && intval($correctAnswer) == $optionNumber) {
            return true;
        }
        
        // Check if it's a comma-separated list
        if (strpos($correctAnswer, ',') !== false) {
            $answers = array_map('trim', explode(',', $correctAnswer));
            return in_array($optionNumber, $answers) || in_array((string)$optionNumber, $answers);
        }
        
        return false;
    }
}
