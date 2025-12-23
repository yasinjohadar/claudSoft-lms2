<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['course', 'lesson', 'creator'])
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

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $assignments = $query->paginate(15);
        $courses = Course::where('is_published', true)->get();

        return view('admin.pages.assignments.index', compact('assignments', 'courses'));
    }

    /**
     * Show the form for creating a new assignment.
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

        return view('admin.pages.assignments.create', compact(
            'courses',
            'selectedSection',
            'selectedCourse'
        ));
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request)
    {
        try {
            // Handle TinyMCE content
            $request->merge([
                'description' => $request->input('description', ''),
                'instructions' => $request->input('instructions', ''),
            ]);

            // Convert checkbox values to booleans before validation
            $request->merge([
                'allow_late_submission' => $request->has('allow_late_submission'),
                'allow_resubmission' => $request->has('allow_resubmission'),
                'resubmit_after_grading_only' => $request->has('resubmit_after_grading_only'),
                'is_published' => $request->has('is_published'),
                'is_visible' => $request->has('is_visible'),
            ]);

            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'lesson_id' => 'nullable|exists:lessons,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'max_grade' => 'required|integer|min:1|max:1000',
                'submission_type' => 'required|in:link,file,both',
                'max_links' => 'nullable|integer|min:0|max:20',
                'max_files' => 'nullable|integer|min:0|max:20',
                'max_file_size' => 'nullable|integer|min:0|max:102400',
                'available_from' => 'nullable|date',
                'due_date' => 'nullable|date|after_or_equal:available_from',
                'late_submission_until' => 'nullable|date|after_or_equal:due_date',
                'allow_late_submission' => 'boolean',
                'late_penalty_percentage' => 'nullable|integer|min:0|max:100',
                'allow_resubmission' => 'boolean',
                'max_resubmissions' => 'nullable|integer|min:1|max:10',
                'resubmit_after_grading_only' => 'boolean',
                'is_published' => 'boolean',
                'is_visible' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'attachments.*' => 'nullable|file|max:10240',
            ], [
                'course_id.required' => 'يجب اختيار الكورس',
                'course_id.exists' => 'الكورس المحدد غير موجود',
                'title.required' => 'عنوان الواجب مطلوب',
                'max_grade.required' => 'الدرجة القصوى مطلوبة',
                'submission_type.required' => 'نوع التسليم مطلوب',
                'due_date.after_or_equal' => 'موعد التسليم يجب أن يكون بعد أو يساوي تاريخ البدء',
                'late_submission_until.after_or_equal' => 'آخر موعد للتسليم المتأخر يجب أن يكون بعد أو يساوي موعد التسليم',
            ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('assignments/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                ];
            }
        }

            $validated['attachments'] = $attachments;
            $validated['created_by'] = auth()->id();
            // Boolean values are already set in $validated from the merged request

            DB::beginTransaction();
            try {
                // إنشاء الواجب
                $assignment = Assignment::create($validated);

                // إذا تم تمرير section_id، نربط الواجب بالقسم عبر course_modules
                if ($request->filled('section_id')) {
                    $section = CourseSection::find($request->section_id);
                    if ($section) {
                        // الحصول على آخر ترتيب في القسم
                        $maxSortOrder = CourseModule::where('section_id', $section->id)->max('sort_order') ?? 0;

                        CourseModule::create([
                            'course_id' => $assignment->course_id,
                            'section_id' => $section->id,
                            'module_type' => 'assignment',
                            'modulable_id' => $assignment->id,
                            'modulable_type' => Assignment::class,
                            'title' => $assignment->title,
                            'description' => $assignment->description,
                            'sort_order' => $maxSortOrder + 1,
                            'is_visible' => $assignment->is_visible,
                            'is_required' => false,
                            'is_graded' => true,
                            'max_score' => $assignment->max_grade,
                            'completion_type' => 'auto',
                        ]);
                    }
                }

                DB::commit();

                // التوجيه: إذا جاء من قسم، نرجع للكورس، وإلا للقائمة
                if ($request->filled('section_id')) {
                    $section = CourseSection::find($request->section_id);
                    return redirect()->route('courses.show', $section->course_id)
                        ->with('success', 'تم إنشاء الواجب وربطه بالقسم بنجاح');
                }

                return redirect()->route('assignments.index')
                    ->with('success', 'تم إنشاء الواجب بنجاح');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error creating assignment: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'حدث خطأ أثناء إنشاء الواجب: ' . $e->getMessage());
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'يرجى التحقق من البيانات المدخلة');
        } catch (\Exception $e) {
            \Log::error('Error in assignment store: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified assignment with submissions.
     */
    public function show($id)
    {
        $assignment = Assignment::with([
            'course',
            'lesson',
            'creator',
            'submissions.student',
            'submissions.grader'
        ])->findOrFail($id);

        $submissions = $assignment->submissions()
            ->with('student')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        // Calculate statistics accurately
        $totalSubmissions = $assignment->submissions()->count();
        $graded = $assignment->submissions()->where('status', 'graded')->count();
        $pending = $assignment->submissions()->where('status', 'submitted')->count();
        $draft = $assignment->submissions()->where('status', 'draft')->count();
        
        // Verify: graded + pending + draft should equal total_submissions
        // (Note: There might be other statuses, so we use total count as source of truth)
        
        $stats = [
            'total_submissions' => $totalSubmissions,
            'graded' => $graded,
            'pending' => $pending,
            'draft' => $draft,
            'average_grade' => $assignment->submissions()
                ->where('status', 'graded')
                ->whereNotNull('grade')
                ->avg('grade'),
        ];

        return view('admin.pages.assignments.show', compact('assignment', 'submissions', 'stats'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);
        $courses = Course::where('is_published', true)->get();

        // Get lessons through course_modules (polymorphic relationship)
        $lessonModules = CourseModule::where('course_id', $assignment->course_id)
            ->where('module_type', 'lesson')
            ->orderBy('sort_order')
            ->get();

        $lessons = $lessonModules->map(function($module) {
            return Lesson::where('id', $module->modulable_id)
                ->where('is_published', true)
                ->first();
        })->filter()->values();

        return view('admin.pages.assignments.edit', compact('assignment', 'courses', 'lessons'));
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        try {
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'lesson_id' => 'nullable|exists:lessons,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'max_grade' => 'required|integer|min:1|max:1000',
                'submission_type' => 'required|in:link,file,both',
                'max_links' => 'nullable|integer|min:0|max:20',
                'max_files' => 'nullable|integer|min:0|max:20',
                'max_file_size' => 'nullable|integer|min:0|max:102400',
                'available_from' => 'nullable|date',
                'due_date' => 'nullable|date|after_or_equal:available_from',
                'late_submission_until' => 'nullable|date|after_or_equal:due_date',
                'late_penalty_percentage' => 'nullable|integer|min:0|max:100',
                'max_resubmissions' => 'nullable|integer|min:1|max:10',
                'sort_order' => 'nullable|integer|min:0',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            // Handle new file uploads
            $attachments = $assignment->attachments ?? [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('assignments/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientOriginalExtension(),
                    ];
                }
            }

            $validated['attachments'] = $attachments;
            $validated['updated_by'] = auth()->id();
            $validated['is_published'] = $request->has('is_published');
            $validated['is_visible'] = $request->has('is_visible') ? true : true;
            $validated['allow_late_submission'] = $request->has('allow_late_submission');
            $validated['allow_resubmission'] = $request->has('allow_resubmission');
            $validated['resubmit_after_grading_only'] = $request->has('resubmit_after_grading_only');

            $assignment->update($validated);

            return redirect()->route('assignments.show', $assignment->id)
                ->with('success', 'تم تحديث الواجب بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $assignment = Assignment::findOrFail($id);

            // Delete attachments
            if ($assignment->attachments) {
                foreach ($assignment->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }

            $assignment->delete();

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف الواجب بنجاح'
                ]);
            }

            return redirect()->route('assignments.index')
                ->with('success', 'تم حذف الواجب بنجاح');
        } catch (\Exception $e) {
            \Log::error('Error deleting assignment: ' . $e->getMessage());
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف الواجب: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الواجب: ' . $e->getMessage());
        }
    }

    /**
     * Grade a submission.
     */
    public function gradeSubmission(Request $request, $submissionId)
    {
        $validated = $request->validate([
            'grade' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $submission = AssignmentSubmission::findOrFail($submissionId);

        // Validate grade doesn't exceed max
        if ($validated['grade'] > $submission->assignment->max_grade) {
            return back()->withErrors(['grade' => 'الدرجة تتجاوز الحد الأقصى المسموح']);
        }

        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
            'status' => 'graded',
            'graded_by' => auth()->id(),
            'graded_at' => now(),
        ]);

        return back()->with('success', 'تم تقييم الواجب بنجاح');
    }

    /**
     * Delete attachment from assignment.
     */
    public function deleteAttachment(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        $attachmentIndex = $request->input('index');

        if (isset($assignment->attachments[$attachmentIndex])) {
            $attachment = $assignment->attachments[$attachmentIndex];
            Storage::disk('public')->delete($attachment['path']);

            $attachments = $assignment->attachments;
            unset($attachments[$attachmentIndex]);
            $assignment->update(['attachments' => array_values($attachments)]);
        }

        return back()->with('success', 'تم حذف المرفق بنجاح');
    }

    /**
     * Get lessons for a course (AJAX).
     */
    public function getLessons(Request $request, $courseId)
    {
        try {
            if (!$courseId) {
                return response()->json([]);
            }

            // Get lessons through course_modules (polymorphic relationship)
            $lessonModules = CourseModule::where('course_id', $courseId)
                ->where('module_type', 'lesson')
                ->where('modulable_type', Lesson::class)
                ->with('modulable')
                ->orderBy('sort_order')
                ->get();

            $lessons = $lessonModules->map(function($module) {
                $lesson = $module->modulable;
                if ($lesson && $lesson->is_published) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title
                    ];
                }
                return null;
            })->filter()->values();

            return response()->json($lessons);
        } catch (\Exception $e) {
            \Log::error('Error fetching lessons for assignment: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'حدث خطأ أثناء جلب الدروس: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Grant additional resubmission attempt to a student.
     */
    public function grantResubmission($submissionId)
    {
        $submission = AssignmentSubmission::with('assignment')->findOrFail($submissionId);
        $assignment = $submission->assignment;

        // Verify that the assignment allows resubmission
        if (!$assignment->allow_resubmission) {
            return back()->with('error', 'هذا الواجب لا يسمح بإعادة التسليم');
        }

        // Verify that the submission is graded
        if ($submission->status !== 'graded') {
            return back()->with('error', 'يجب أن يكون الواجب مُقيّماً أولاً');
        }

        // Grant one extra attempt to this student
        $assignment->grantExtraAttempt($submission->student_id, 1);

        return back()->with('success', 'تم منح الطالب محاولة إضافية بنجاح');
    }
}
