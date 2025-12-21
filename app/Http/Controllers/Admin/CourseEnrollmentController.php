<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\BulkEnrollmentSession;
use App\Models\User;
use App\Events\N8nWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CourseEnrollmentController extends Controller
{
    /**
     * Display enrollments for a course.
     */
    public function index($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            $enrollments = CourseEnrollment::with(['student', 'enrolledBy'])
                ->where('course_id', $courseId)
                ->orderBy('enrollment_date', 'desc')
                ->paginate(20);

            $stats = [
                'total' => $course->enrollments()->count(),
                'active' => $course->enrollments()->where('enrollment_status', 'active')->count(),
                'completed' => $course->enrollments()->where('enrollment_status', 'completed')->count(),
                'pending' => $course->enrollments()->where('enrollment_status', 'pending')->count(),
                'suspended' => $course->enrollments()->where('enrollment_status', 'suspended')->count(),
            ];

            // Legacy variable names for backward compatibility with views
            $activeCount = $stats['active'];
            $completedCount = $stats['completed'];
            $suspendedCount = $stats['suspended'];
            $totalCount = $stats['total'];
            $pendingCount = $stats['pending'];

            return view('admin.pages.enrollments.index', compact('course', 'enrollments', 'stats', 'activeCount', 'completedCount', 'suspendedCount', 'totalCount', 'pendingCount'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', 'حدث خطأ أثناء تحميل التسجيلات: ' . $e->getMessage());
        }
    }

    /**
     * Show enrollment form.
     */
    public function create($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            
            // Get students not yet enrolled in this course
            $enrolledIds = CourseEnrollment::where('course_id', $courseId)
                ->pluck('student_id')
                ->toArray();

            $students = User::role('student')
                ->whereNotIn('id', $enrolledIds)
                ->orderBy('name')
                ->get();

            return view('admin.pages.enrollments.create', compact('course', 'students'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Enroll individual student.
     */
    public function enrollIndividual(Request $request, $courseId)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'enrollment_status' => 'nullable|in:active,pending,suspended',
            ], [
                'student_id.required' => 'يرجى اختيار طالب',
                'student_id.exists' => 'الطالب المحدد غير موجود',
            ]);

            DB::beginTransaction();
            
            $course = Course::findOrFail($courseId);

            // Check if already enrolled
            $exists = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $validated['student_id'])
                ->exists();

            if ($exists) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['student_id' => 'الطالب مسجل بالفعل في هذا الكورس'])
                    ->with('error', 'الطالب مسجل بالفعل في هذا الكورس');
            }

            // Check if course is full
            if ($course->isFull()) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['course' => 'الكورس ممتلئ ولا يمكن إضافة طلاب جدد'])
                    ->with('error', 'الكورس ممتلئ ولا يمكن إضافة طلاب جدد');
            }

            $enrollment = CourseEnrollment::create([
                'course_id' => $courseId,
                'student_id' => $validated['student_id'],
                'enrollment_date' => now(),
                'enrollment_status' => $validated['enrollment_status'] ?? 'active',
                'enrolled_by' => auth()->id(),
                'completion_percentage' => 0,
            ]);

            DB::commit();

            // Dispatch n8n webhook event
            if ($enrollment->enrollment_status === 'active') {
                try {
                    event(new N8nWebhookEvent('student.enrolled', [
                        'student_id' => $enrollment->student_id,
                        'student_name' => $enrollment->student->name ?? null,
                        'student_email' => $enrollment->student->email ?? null,
                        'course_id' => $enrollment->course_id,
                        'course_title' => $course->title ?? null,
                        'enrollment_id' => $enrollment->id,
                        'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                        'enrolled_by' => $enrollment->enrolled_by,
                    ]));
                } catch (\Exception $e) {
                    // Log webhook error but don't fail enrollment
                    \Log::warning('Webhook event failed: ' . $e->getMessage());
                }
            }

            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('success', 'تم تسجيل الطالب بنجاح');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'يرجى التحقق من البيانات المدخلة');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Enrollment error: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'student_id' => $request->input('student_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk enrollment form (Excel upload).
     */
    public function showBulkEnroll($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            return view('admin.pages.enrollments.bulk-enroll', compact('course'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Process bulk enrollment from Excel/CSV file.
     */
    public function processBulkEnroll(Request $request, $courseId)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);

            // Store file
            $filePath = $request->file('file')->store('enrollments', 'local');
            $fileName = $request->file('file')->getClientOriginalName();

            // Create session
            $session = BulkEnrollmentSession::create([
                'course_id' => $courseId,
                'uploaded_by' => auth()->id(),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'enrollment_type' => 'individual',
                'status' => 'processing',
                'total_students' => 0,
                'successful_enrollments' => 0,
                'failed_enrollments' => 0,
                'skipped_enrollments' => 0,
            ]);

            // Read Excel file
            $fullPath = storage_path('app/' . $filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            array_shift($rows);

            $session->update(['total_students' => count($rows)]);

            $successCount = 0;
            $failCount = 0;
            $skipCount = 0;
            $errors = [];
            $successDetails = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and Excel starts at 1

                try {
                    // Expected columns: email, name (optional)
                    $email = trim($row[0] ?? '');

                    if (empty($email)) {
                        $skipCount++;
                        $session->addSkipped();
                        continue;
                    }

                    // Find student by email
                    $student = User::where('email', $email)->first();

                    if (!$student) {
                        $failCount++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'email' => $email,
                            'error' => 'الطالب غير موجود في النظام'
                        ];
                        $session->addFailure([
                            'row' => $rowNumber,
                            'email' => $email,
                            'error' => 'الطالب غير موجود في النظام'
                        ]);
                        continue;
                    }

                    // Check if already enrolled
                    $exists = CourseEnrollment::where('course_id', $courseId)
                        ->where('student_id', $student->id)
                        ->exists();

                    if ($exists) {
                        $skipCount++;
                        $session->addSkipped();
                        continue;
                    }

                    // Enroll student
                    CourseEnrollment::create([
                        'course_id' => $courseId,
                        'student_id' => $student->id,
                        'enrollment_date' => now(),
                        'enrollment_status' => 'active',
                        'enrolled_by' => auth()->id(),
                        'completion_percentage' => 0,
                    ]);

                    $successCount++;
                    $successDetails[] = [
                        'row' => $rowNumber,
                        'email' => $email,
                        'name' => $student->name
                    ];
                    $session->addSuccess([
                        'row' => $rowNumber,
                        'email' => $email,
                        'name' => $student->name
                    ]);

                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'email' => $email ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                    $session->addFailure([
                        'row' => $rowNumber,
                        'email' => $email ?? 'N/A',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update session
            $session->update([
                'successful_enrollments' => $successCount,
                'failed_enrollments' => $failCount,
                'skipped_enrollments' => $skipCount,
            ]);

            $session->markAsCompleted();

            DB::commit();

            $message = "تم التسجيل الجماعي: {$successCount} ناجح، {$failCount} فاشل، {$skipCount} متخطى";

            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('success', $message)
                ->with('bulk_result', [
                    'success' => $successCount,
                    'failed' => $failCount,
                    'skipped' => $skipCount,
                    'errors' => $errors,
                    'success_details' => $successDetails
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($session)) {
                $session->markAsFailed(['error' => $e->getMessage()]);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء التسجيل الجماعي: ' . $e->getMessage());
        }
    }

    /**
     * Show form to select multiple students.
     */
    public function showSelectEnroll($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            // Get students not yet enrolled
            $enrolledIds = CourseEnrollment::where('course_id', $courseId)
                ->pluck('student_id')
                ->toArray();

            $students = User::role('student')
                ->whereNotIn('id', $enrolledIds)
                ->get();

            // Get departments for filtering (if departments table exists)
            try {
                $departments = DB::table('departments')->get();
            } catch (\Exception $e) {
                $departments = collect(); // Empty collection if departments table doesn't exist
            }

            return view('admin.pages.enrollments.select-multiple', compact('course', 'students', 'departments'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Process multiple selected students enrollment.
     */
    public function processSelectEnroll(Request $request, $courseId)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $successCount = 0;
            $skipCount = 0;

            foreach ($validated['student_ids'] as $studentId) {
                // Check if already enrolled
                $exists = CourseEnrollment::where('course_id', $courseId)
                    ->where('student_id', $studentId)
                    ->exists();

                if ($exists) {
                    $skipCount++;
                    continue;
                }

                $enrollment = CourseEnrollment::create([
                    'course_id' => $courseId,
                    'student_id' => $studentId,
                    'enrollment_date' => now(),
                    'enrollment_status' => 'active',
                    'enrolled_by' => auth()->id(),
                    'completion_percentage' => 0,
                ]);

                // Dispatch n8n webhook event
                event(new N8nWebhookEvent('student.enrolled', [
                    'student_id' => $enrollment->student_id,
                    'student_name' => $enrollment->student->name ?? null,
                    'student_email' => $enrollment->student->email ?? null,
                    'course_id' => $enrollment->course_id,
                    'course_title' => $course->title ?? null,
                    'enrollment_id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                    'enrolled_by' => $enrollment->enrolled_by,
                ]));

                $successCount++;
            }

            DB::commit();

            $message = "تم تسجيل {$successCount} طالب بنجاح";
            if ($skipCount > 0) {
                $message .= " وتم تخطي {$skipCount} طالب (مسجل مسبقاً)";
            }

            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Show form to enroll entire group.
     */
    public function showGroupEnroll($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            $groups = CourseGroup::whereHas('courses', function($query) use ($courseId) {
                    $query->where('courses.id', $courseId);
                })
                ->withCount('members')
                ->get();

            return view('admin.pages.enrollments.group-enroll', compact('course', 'groups'));
        } catch (\Exception $e) {
            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Process group enrollment.
     */
    public function processGroupEnroll(Request $request, $courseId)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($courseId);
            $group = CourseGroup::with('members')->findOrFail($validated['group_id']);

            $successCount = 0;
            $skipCount = 0;

            foreach ($group->members as $member) {
                // Check if already enrolled
                $exists = CourseEnrollment::where('course_id', $courseId)
                    ->where('student_id', $member->student_id)
                    ->exists();

                if ($exists) {
                    $skipCount++;
                    continue;
                }

                CourseEnrollment::create([
                    'course_id' => $courseId,
                    'student_id' => $member->student_id,
                    'enrollment_date' => now(),
                    'enrollment_status' => 'active',
                    'enrolled_by' => auth()->id(),
                    'completion_percentage' => 0,
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "تم تسجيل {$successCount} طالب من المجموعة بنجاح";
            if ($skipCount > 0) {
                $message .= " وتم تخطي {$skipCount} طالب (مسجل مسبقاً)";
            }

            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تسجيل المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * Unenroll a student.
     */
    public function unenroll($enrollmentId)
    {
        DB::beginTransaction();
        try {
            $enrollment = CourseEnrollment::with(['student', 'course'])->findOrFail($enrollmentId);
            $courseId = $enrollment->course_id;
            $studentId = $enrollment->student_id;
            $courseTitle = $enrollment->course->title ?? null;

            $enrollment->delete();

            DB::commit();

            // Dispatch n8n webhook event
            event(new N8nWebhookEvent('student.unenrolled', [
                'student_id' => $studentId,
                'student_name' => $enrollment->student->name ?? null,
                'student_email' => $enrollment->student->email ?? null,
                'course_id' => $courseId,
                'course_title' => $courseTitle,
                'unenrolled_at' => now()->toIso8601String(),
            ]));

            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('success', 'تم إلغاء تسجيل الطالب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إلغاء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Show progress details for a specific enrollment.
     */
    public function showProgress($enrollmentId)
    {
        try {
            $enrollment = CourseEnrollment::with(['student', 'course.sections.modules'])
                ->findOrFail($enrollmentId);

            // Calculate progress
            if (method_exists($enrollment, 'calculateCompletionPercentage')) {
                $enrollment->calculateCompletionPercentage();
                $enrollment->refresh();
            }

            // Get section progress
            $sectionsProgress = [];
            foreach ($enrollment->course->sections as $section) {
                // Try required modules first, then fall back to all modules
                $requiredModules = $section->modules()->where('is_required', true);
                $totalModules = $requiredModules->count();
                
                // If no required modules, count all modules
                if ($totalModules === 0) {
                    $modulesToCheck = $section->modules()->get();
                    $totalModules = $modulesToCheck->count();
                } else {
                    $modulesToCheck = $requiredModules->get();
                }
                
                $completedModules = 0;
                foreach ($modulesToCheck as $module) {
                    if ($module->isCompletedBy($enrollment->student)) {
                        $completedModules++;
                    }
                }

                $sectionsProgress[] = [
                    'section' => $section,
                    'total_modules' => $totalModules,
                    'completed_modules' => $completedModules,
                    'percentage' => $totalModules > 0 ? ($completedModules / $totalModules * 100) : 0,
                ];
            }

            // Get recent completions
            $recentCompletions = \App\Models\ModuleCompletion::where('student_id', $enrollment->student_id)
                ->whereHas('module', function($q) use ($enrollment) {
                    $q->where('course_id', $enrollment->course_id);
                })
                ->with('module')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();

            // Get statistics - use required modules, fallback to all modules
            $requiredModulesQuery = $enrollment->course->modules()->where('is_required', true);
            $totalModulesCount = $requiredModulesQuery->count();
            
            if ($totalModulesCount === 0) {
                // Fallback to all modules if no required modules
                $moduleIds = $enrollment->course->modules()->pluck('course_modules.id');
                $totalModulesCount = $moduleIds->count();
            } else {
                $moduleIds = $requiredModulesQuery->pluck('course_modules.id');
            }
            
            $stats = [
                'total_modules' => $totalModulesCount,
                'completed_modules' => \App\Models\ModuleCompletion::where('student_id', $enrollment->student_id)
                    ->whereIn('module_id', $moduleIds)
                    ->where('completion_status', 'completed')
                    ->count(),
                'completion_percentage' => $enrollment->completion_percentage ?? 0,
                'enrollment_status' => $enrollment->enrollment_status,
                'enrollment_date' => $enrollment->enrollment_date,
                'last_accessed' => $enrollment->last_accessed_at,
                'completed_at' => $enrollment->completed_at,
                'grade' => $enrollment->grade,
            ];

            return view('admin.pages.enrollments.progress', compact('enrollment', 'sectionsProgress', 'recentCompletions', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل تفاصيل التقدم: ' . $e->getMessage());
        }
    }

    /**
     * Show progress report for all students.
     */
    public function progressReport($courseId)
    {
        try {
            $course = Course::with(['sections.modules'])->findOrFail($courseId);

            $baseQuery = CourseEnrollment::with('student')
                ->where('course_id', $courseId);

            // Basic statistics
            $totalEnrollments = (clone $baseQuery)->count();
            $completedCount = (clone $baseQuery)
                ->where('enrollment_status', 'completed')
                ->count();
            $inProgressCount = (clone $baseQuery)
                ->where('enrollment_status', 'active')
                ->count();
            $averageProgress = (clone $baseQuery)->avg('completion_percentage') ?? 0;

            // Progress distribution buckets
            $distributionQuery = (clone $baseQuery);
            $progressDistribution = [
                'not_started' => (clone $distributionQuery)
                    ->where('completion_percentage', 0)
                    ->count(),
                'low' => (clone $distributionQuery)
                    ->whereBetween('completion_percentage', [1, 25])
                    ->count(),
                'medium' => (clone $distributionQuery)
                    ->whereBetween('completion_percentage', [26, 75])
                    ->count(),
                'high' => (clone $distributionQuery)
                    ->whereBetween('completion_percentage', [76, 99])
                    ->count(),
                'completed' => (clone $distributionQuery)
                    ->where('completion_percentage', 100)
                    ->count(),
            ];

            // Paginated enrollments ordered by progress
            $enrollments = $baseQuery
                ->orderBy('completion_percentage', 'desc')
                ->paginate(20);

            // Total modules in course (for ratio display)
            $totalModules = $course->modules()->count();

            return view('admin.pages.enrollments.progress-report', compact(
                'course',
                'enrollments',
                'totalEnrollments',
                'completedCount',
                'inProgressCount',
                'averageProgress',
                'progressDistribution',
                'totalModules'
            ));

        } catch (\Exception $e) {
            return redirect()
                ->route('courses.enrollments.index', $courseId)
                ->with('error', 'حدث خطأ أثناء تحميل التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Display all enrollments from all courses.
     */
    public function allEnrollments(Request $request)
    {
        try {
            $query = CourseEnrollment::with(['student', 'course', 'enrolledBy']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('course', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            // Filter by course
            if ($request->filled('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }

            // Sort
            $sortBy = $request->get('sort', 'enrollment_date');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $enrollments = $query->paginate(20);

            // Get courses for filter
            $courses = Course::select('id', 'title')->get();

            // Get statistics
            $totalEnrollments = CourseEnrollment::count();
            $activeCount = CourseEnrollment::where('enrollment_status', 'active')->count();
            $completedCount = CourseEnrollment::where('enrollment_status', 'completed')->count();
            $suspendedCount = CourseEnrollment::where('enrollment_status', 'suspended')->count();
            $pendingCount = CourseEnrollment::where('enrollment_status', 'pending')->count();

            return view('admin.pages.enrollments.all', compact(
                'enrollments',
                'courses',
                'totalEnrollments',
                'activeCount',
                'completedCount',
                'suspendedCount',
                'pendingCount'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل الانضمامات: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for bulk enrollment.
     */
    public function downloadTemplate()
    {
        try {
            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet name
            $sheet->setTitle('قالب التسجيل الجماعي');

            // Header styling
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];

            // Set headers
            $sheet->setCellValue('A1', 'student_id');
            $sheet->setCellValue('B1', 'email');
            $sheet->setCellValue('C1', 'name');

            // Apply header style
            $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(25);

            // Add sample data
            $sheet->setCellValue('A2', 'ST001');
            $sheet->setCellValue('B2', 'student1@example.com');
            $sheet->setCellValue('C2', 'أحمد محمد');

            $sheet->setCellValue('A3', 'ST002');
            $sheet->setCellValue('B3', 'student2@example.com');
            $sheet->setCellValue('C3', 'فاطمة علي');

            $sheet->setCellValue('A4', 'ST003');
            $sheet->setCellValue('B4', 'student3@example.com');
            $sheet->setCellValue('C4', 'محمود حسن');

            // Set row height
            $sheet->getRowDimension(1)->setRowHeight(25);

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            $fileName = 'enrollment_template_' . date('Y-m-d') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            // Save to output
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل القالب: ' . $e->getMessage());
        }
    }

    /**
     * Approve enrollment request.
     */
    public function approve($enrollmentId)
    {
        try {
            $enrollment = CourseEnrollment::findOrFail($enrollmentId);

            if ($enrollment->enrollment_status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'هذا الطلب تمت معالجته بالفعل');
            }

            $enrollment->update([
                'enrollment_status' => 'active',
                'enrolled_by' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'تم قبول طلب التسجيل بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء قبول الطلب: ' . $e->getMessage());
        }
    }

    /**
     * Reject enrollment request.
     */
    public function reject($enrollmentId)
    {
        try {
            $enrollment = CourseEnrollment::findOrFail($enrollmentId);

            if ($enrollment->enrollment_status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'هذا الطلب تمت معالجته بالفعل');
            }

            $enrollment->update([
                'enrollment_status' => 'cancelled',
            ]);

            return redirect()
                ->back()
                ->with('success', 'تم رفض طلب التسجيل');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء رفض الطلب: ' . $e->getMessage());
        }
    }
}
