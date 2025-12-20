<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\CourseSectionQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseSectionController extends Controller
{
    /**
     * Display a listing of sections for a course.
     */
    public function index($courseId)
    {
        try {
            $course = Course::with(['sections.modules'])->findOrFail($courseId);
            $sections = $course->sections()->orderBy('sort_order')->get();

            return view('admin.courses.sections.index', compact('course', 'sections'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', 'حدث خطأ أثناء تحميل الأقسام: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new section.
     */
    public function create($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            return view('admin.courses.sections.create', compact('course'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.show', $courseId)
                ->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
            'show_unavailable' => 'nullable|boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);

        DB::beginTransaction();
        try {
            // Get next sort_order
            $maxSortOrder = $course->sections()->max('sort_order') ?? 0;

            $validated['course_id'] = $courseId;
            $validated['sort_order'] = $maxSortOrder + 1;
            $validated['order_index'] = $maxSortOrder + 1; // Set order_index same as sort_order
            $validated['created_by'] = auth()->id();
            $validated['is_visible'] = $request->has('is_visible');
            $validated['is_locked'] = $request->has('is_locked');
            $validated['show_unavailable'] = $request->has('show_unavailable');

            $section = CourseSection::create($validated);

            DB::commit();

            return redirect()
                ->route('courses.show', $courseId)
                ->with('success', 'تم إنشاء القسم بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء القسم: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified section.
     */
    public function show($courseId, $sectionId)
    {
        try {
            $section = CourseSection::with(['course', 'modules.modulable'])
                ->where('id', $sectionId)
                ->where('course_id', $courseId)
                ->firstOrFail();
            return view('admin.courses.sections.show', compact('section'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل القسم: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit($courseId, $sectionId)
    {
        try {
            $section = CourseSection::with('course')
                ->where('id', $sectionId)
                ->where('course_id', $courseId)
                ->firstOrFail();
            return view('admin.courses.sections.edit', compact('section'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('courses.show', $courseId)
                ->with('error', 'القسم المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error loading section edit form: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'section_id' => $sectionId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->route('courses.show', $courseId)
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, $courseId, $sectionId)
    {
        try {
            $section = CourseSection::where('id', $sectionId)
                ->where('course_id', $courseId)
                ->firstOrFail();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_visible' => 'nullable|boolean',
                'is_locked' => 'nullable|boolean',
                'show_unavailable' => 'nullable|boolean',
                'available_from' => 'nullable|date',
                'available_until' => 'nullable|date|after:available_from',
            ]);

            DB::beginTransaction();
            try {
                $validated['updated_by'] = auth()->id();
                $validated['is_visible'] = $request->has('is_visible');
                $validated['is_locked'] = $request->has('is_locked');
                $validated['show_unavailable'] = $request->has('show_unavailable');

                $section->update($validated);

                DB::commit();

                return redirect()
                    ->route('courses.show', $courseId)
                    ->with('success', 'تم تحديث القسم بنجاح');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error updating section: ' . $e->getMessage(), [
                    'course_id' => $courseId,
                    'section_id' => $sectionId,
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'حدث خطأ أثناء تحديث القسم: ' . $e->getMessage());
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('courses.show', $courseId)
                ->with('error', 'القسم المطلوب غير موجود');
        } catch (\Exception $e) {
            Log::error('Error in update method: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'section_id' => $sectionId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->route('courses.show', $courseId)
                ->with('error', 'حدث خطأ أثناء تحديث القسم: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified section.
     */
    public function destroy($courseId, $sectionId)
    {
        DB::beginTransaction();
        try {
            $section = CourseSection::where('id', $sectionId)
                ->where('course_id', $courseId)
                ->firstOrFail();

            // Check if section has modules
            if ($section->modules()->count() > 0) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف القسم لاحتوائه على محتوى');
            }

            $section->delete();

            DB::commit();

            return redirect()
                ->route('courses.show', $courseId)
                ->with('success', 'تم حذف القسم بنجاح');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'القسم المطلوب غير موجود');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting section: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'section_id' => $sectionId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف القسم: ' . $e->getMessage());
        }
    }

    /**
     * Reorder sections (drag & drop).
     */
    public function reorder(Request $request, $courseId)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'exists:course_sections,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['sections'] as $index => $sectionId) {
                CourseSection::where('id', $sectionId)
                    ->where('course_id', $courseId)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة ترتيب الأقسام بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة الترتيب: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle section visibility.
     */
    public function toggleVisibility($id)
    {
        try {
            $section = CourseSection::findOrFail($id);
            $section->is_visible = !$section->is_visible;
            $section->updated_by = auth()->id();
            $section->save();

            $status = $section->is_visible ? 'مرئي' : 'مخفي';

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
     * Toggle section lock status.
     */
    public function toggleLock($id)
    {
        try {
            $section = CourseSection::findOrFail($id);
            $section->is_locked = !$section->is_locked;
            $section->updated_by = auth()->id();
            $section->save();

            $status = $section->is_locked ? 'مقفل' : 'مفتوح';

            return redirect()
                ->back()
                ->with('success', "تم تحديث حالة القفل إلى: {$status}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحديث حالة القفل: ' . $e->getMessage());
        }
    }

    // ==================== Question Management ====================

    /**
     * Show questions management page for a section.
     */
    public function manageQuestions($sectionId)
    {
        try {
            $section = CourseSection::with(['course', 'questions.questionType', 'questions.options'])
                ->findOrFail($sectionId);

            // Get IDs of questions already added to this section
            $existingQuestionIds = $section->questions->pluck('id')->toArray();

            // Get available questions from question bank (for import)
            // Get all active questions that are not already in this section
            $availableQuestions = QuestionBank::with(['questionType', 'course'])
                ->where('is_active', true)
                ->when(!empty($existingQuestionIds), function($query) use ($existingQuestionIds) {
                    return $query->whereNotIn('id', $existingQuestionIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Get question types for creating new questions
            $questionTypes = QuestionType::where('is_active', true)
                ->get();

            return view('admin.courses.sections.questions.manage', compact('section', 'availableQuestions', 'questionTypes'));
        } catch (\Exception $e) {
            Log::error('Error loading manage questions page: ' . $e->getMessage(), [
                'section_id' => $sectionId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل صفحة الأسئلة: ' . $e->getMessage());
        }
    }

    /**
     * Import question from question bank to section.
     */
    public function importQuestion(Request $request, $sectionId)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Import question request', [
                'section_id' => $sectionId,
                'request_data' => $request->all(),
                'url' => $request->fullUrl()
            ]);
            
            // Prepare data for validation
            $data = $request->all();
            
            // Convert question_grade to numeric if it's a string
            if (isset($data['question_grade']) && is_string($data['question_grade'])) {
                $data['question_grade'] = $data['question_grade'] === '' || $data['question_grade'] === null 
                    ? null 
                    : (float) $data['question_grade'];
            }
            
            // Convert question_id to integer if it's a string
            if (isset($data['question_id']) && is_string($data['question_id'])) {
                $data['question_id'] = (int) $data['question_id'];
            }
            
            // Convert is_required to boolean
            if (isset($data['is_required'])) {
                $data['is_required'] = filter_var($data['is_required'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $validated = validator($data, [
                'question_id' => 'required|integer|exists:question_bank,id',
                'question_grade' => 'nullable|numeric|min:0',
                'is_required' => 'nullable|boolean',
            ], [
                'question_id.required' => 'معرف السؤال مطلوب',
                'question_id.integer' => 'معرف السؤال يجب أن يكون رقماً',
                'question_id.exists' => 'السؤال المحدد غير موجود في بنك الأسئلة',
                'question_grade.numeric' => 'الدرجة يجب أن تكون رقماً',
                'question_grade.min' => 'الدرجة يجب أن تكون أكبر من أو تساوي صفر',
                'is_required.boolean' => 'حقل الإجباري يجب أن يكون نعم أو لا',
            ])->validate();

            // Try to find the section with better error handling
            $section = CourseSection::find($sectionId);
            
            if (!$section) {
                Log::error('Section not found', [
                    'section_id' => $sectionId,
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'القسم غير موجود. الرجاء التحقق من معرف القسم.'
                ], 404);
            }

            // Check if question already exists in section
            if ($section->questions()->where('question_id', $validated['question_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا السؤال موجود بالفعل في القسم'
                ], 422);
            }

            DB::beginTransaction();

            // Get next order
            $maxOrder = $section->questions()->max('course_section_questions.question_order') ?? 0;

            // Convert is_required to boolean
            $isRequired = isset($validated['is_required']) ? (bool)$validated['is_required'] : true;

            // Attach question to section
            $section->questions()->attach($validated['question_id'], [
                'question_order' => $maxOrder + 1,
                'question_grade' => $validated['question_grade'] ?? null,
                'is_required' => $isRequired,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم استيراد السؤال بنجاح'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'القسم أو السؤال غير موجود'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error importing question: ' . $e->getMessage(), [
                'section_id' => $sectionId,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استيراد السؤال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove question from section.
     */
    public function removeQuestion($sectionId, $questionId)
    {
        DB::beginTransaction();
        try {
            $section = CourseSection::findOrFail($sectionId);
            $section->questions()->detach($questionId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إزالة السؤال من القسم بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إزالة السؤال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder section questions.
     */
    public function reorderQuestions(Request $request, $sectionId)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'exists:question_bank,id',
        ]);

        DB::beginTransaction();
        try {
            $section = CourseSection::findOrFail($sectionId);

            foreach ($validated['questions'] as $index => $questionId) {
                CourseSectionQuestion::where('course_section_id', $sectionId)
                    ->where('question_id', $questionId)
                    ->update(['question_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة ترتيب الأسئلة بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة الترتيب: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update question settings in section.
     */
    public function updateQuestionSettings(Request $request, $sectionId, $questionId)
    {
        $validated = $request->validate([
            'question_grade' => 'nullable|numeric|min:0',
            'is_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $section = CourseSection::findOrFail($sectionId);
            
            // Check if question exists in section
            $pivotRecord = CourseSectionQuestion::where('course_section_id', $sectionId)
                ->where('question_id', $questionId)
                ->first();

            if (!$pivotRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'السؤال غير موجود في هذا القسم'
                ], 404);
            }

            // Prepare update data
            $updateData = [];
            
            if (isset($validated['question_grade'])) {
                $updateData['question_grade'] = $validated['question_grade'];
            }
            
            // Handle is_required properly - check if it's set in request, not just if it's truthy
            if ($request->has('is_required')) {
                $updateData['is_required'] = (bool)$validated['is_required'];
            }

            if (!empty($updateData)) {
                $pivotRecord->update($updateData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث إعدادات السؤال بنجاح',
                'data' => [
                    'is_required' => $pivotRecord->is_required,
                    'question_grade' => $pivotRecord->question_grade
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'القسم أو السؤال غير موجود'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question settings: ' . $e->getMessage(), [
                'section_id' => $sectionId,
                'question_id' => $questionId,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show create question form (redirect to question bank with section context).
     */
    public function createQuestion($sectionId, $type)
    {
        try {
            $section = CourseSection::with('course')->findOrFail($sectionId);

            // Store section context in session for later use
            session(['question_creation_context' => [
                'section_id' => $sectionId,
                'course_id' => $section->course_id,
            ]]);

            // Redirect to question bank creation page with type
            return redirect()->route('question-bank.create.type', $type);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
