<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\QuestionOption;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\ProgrammingLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

        $questions = $query->paginate(20);
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();

        return view('admin.pages.question-bank.index', compact('questions', 'courses', 'questionTypes', 'programmingLanguages'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request)
    {
        $questionTypes = QuestionType::where('is_active', true)->get();

        return view('admin.pages.question-bank.select-type', compact('questionTypes'));
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
            'is_active' => 'nullable|boolean',
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

        // Check if question is used in any quiz
        if ($question->quizQuestions()->count() > 0) {
            return back()->withErrors(['error' => 'لا يمكن حذف السؤال لأنه مستخدم في اختبار واحد أو أكثر']);
        }

        try {
            $question->delete();

            return redirect()->route('question-bank.index')
                ->with('success', 'تم حذف السؤال بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف السؤال: ' . $e->getMessage()]);
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
                    // Check if any question is used in a quiz
                    $usedQuestions = QuestionBank::whereIn('id', $validated['question_ids'])
                        ->whereHas('quizQuestions')
                        ->count();

                    if ($usedQuestions > 0) {
                        return back()->withErrors(['error' => 'بعض الأسئلة المحددة مستخدمة في اختبارات ولا يمكن حذفها']);
                    }

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
        
        return view('admin.pages.question-bank.import-excel', compact('courses', 'questionTypes'));
    }

    /**
     * Preview Excel import data before processing.
     */
    public function previewImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'excel_file.required' => 'يرجى اختيار ملف Excel',
            'excel_file.mimes' => 'يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)',
            'excel_file.max' => 'حجم الملف يجب أن يكون أقل من 10 ميجابايت',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            $headerRow = array_shift($rows);
            
            $parsedData = [];
            $errors = [];
            
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and array is 0-indexed
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $questionData = [
                    'row_number' => $rowNumber,
                    'question_type' => trim($row[0] ?? ''),
                    'question_text' => trim($row[1] ?? ''),
                    'option_1' => trim($row[2] ?? ''),
                    'option_2' => trim($row[3] ?? ''),
                    'option_3' => trim($row[4] ?? ''),
                    'option_4' => trim($row[5] ?? ''),
                    'correct_answer' => trim($row[6] ?? ''),
                    'points' => trim($row[7] ?? '1'),
                    'difficulty' => trim($row[8] ?? 'medium'),
                    'course' => trim($row[9] ?? ''),
                    'explanation' => trim($row[10] ?? ''),
                    'tags' => trim($row[11] ?? ''),
                ];

                // Validate required fields
                $rowErrors = [];
                if (empty($questionData['question_type'])) {
                    $rowErrors[] = 'نوع السؤال مطلوب';
                }
                if (empty($questionData['question_text'])) {
                    $rowErrors[] = 'نص السؤال مطلوب';
                }
                if (empty($questionData['correct_answer'])) {
                    $rowErrors[] = 'الإجابة الصحيحة مطلوبة';
                }
                if (empty($questionData['course'])) {
                    $rowErrors[] = 'اسم الكورس مطلوب';
                }

                if (!empty($rowErrors)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $rowErrors
                    ];
                }

                $parsedData[] = $questionData;
            }

            // Get question types mapping
            $questionTypes = QuestionType::where('is_active', true)->get();
            $typeMapping = [];
            foreach ($questionTypes as $type) {
                $typeMapping[$type->display_name] = $type->id;
                $typeMapping[$type->name] = $type->id;
            }

            // Get courses mapping
            $courses = Course::where('is_published', true)->get();
            $courseMapping = [];
            foreach ($courses as $course) {
                $courseMapping[$course->title] = $course->id;
            }

            return response()->json([
                'success' => true,
                'data' => $parsedData,
                'errors' => $errors,
                'type_mapping' => $typeMapping,
                'course_mapping' => $courseMapping,
                'total_rows' => count($parsedData),
                'valid_rows' => count($parsedData) - count($errors),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and import Excel data.
     */
    public function processImport(Request $request)
    {
        // Check if request expects JSON (AJAX)
        $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax();
        
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|mimes:xlsx,xls|max:10240',
            'questions_data' => 'required|json',
        ], [
            'excel_file.required' => 'يرجى اختيار ملف Excel',
            'questions_data.required' => 'بيانات الأسئلة مطلوبة',
        ]);

        if ($validator->fails()) {
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
            $questionsData = json_decode($request->questions_data, true);
            
            if (!is_array($questionsData)) {
                if ($expectsJson) {
                    return response()->json([
                        'success' => false,
                        'message' => 'بيانات غير صحيحة'
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

            DB::beginTransaction();

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($questionsData as $index => $questionData) {
                try {
                    // Get question type
                    $questionTypeName = $questionData['question_type'] ?? '';
                    $questionType = $typeMapping[$questionTypeName] ?? null;
                    
                    if (!$questionType) {
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
                        $errors[] = "السطر " . ($index + 1) . ": الكورس '" . $questionData['course'] . "' غير موجود في النظام";
                        continue;
                    }

                    // Create question
                    $question = QuestionBank::create([
                        'course_id' => $courseId,
                        'question_type_id' => $questionType->id,
                        'question_text' => $questionData['question_text'],
                        'explanation' => $questionData['explanation'] ?? null,
                        'default_grade' => floatval($questionData['points'] ?? 1),
                        'difficulty_level' => $this->mapDifficulty($questionData['difficulty'] ?? 'medium'),
                        'tags' => !empty($questionData['tags']) ? explode(',', $questionData['tags']) : null,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]);

                    // Create options
                    $options = [];
                    for ($i = 1; $i <= 4; $i++) {
                        $optionText = $questionData['option_' . $i] ?? '';
                        if (!empty($optionText)) {
                            $options[] = [
                                'option_text' => $optionText,
                                'is_correct' => $this->isCorrectAnswer($questionData['correct_answer'], $i),
                                'option_order' => $i,
                            ];
                        }
                    }

                    foreach ($options as $optionData) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionData['option_text'],
                            'is_correct' => $optionData['is_correct'],
                            'option_order' => $optionData['option_order'],
                            'score_weight' => 1.0,
                        ]);
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "السطر " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "تم استيراد {$imported} سؤال بنجاح";
            if ($skipped > 0) {
                $message .= "، تم تخطي {$skipped} سؤال";
            }

            // Check if request expects JSON (AJAX)
            $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            if ($expectsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]);
            }

            return redirect()->route('question-bank.index')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if request expects JSON (AJAX)
            $expectsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage()
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'نوع السؤال', 'نص السؤال', 'الخيار 1', 'الخيار 2', 
            'الخيار 3', 'الخيار 4', 'الإجابة الصحيحة', 
            'الدرجة', 'الصعوبة', 'الكورس', 'الشرح', 'العلامات'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Add example row
        $exampleRow = [
            'اختيار من متعدد (إجابة واحدة)',
            'ما هي عاصمة المملكة العربية السعودية؟',
            'الرياض',
            'جدة',
            'الدمام',
            'مكة المكرمة',
            '1',
            '1',
            'easy',
            'اسم الكورس هنا', // Required - يجب أن يطابق اسم كورس موجود في النظام
            'الرياض هي عاصمة المملكة العربية السعودية',
            'جغرافيا,عواصم'
        ];
        $sheet->fromArray($exampleRow, null, 'A2');

        // Style header row
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'question_bank_template_' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
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
