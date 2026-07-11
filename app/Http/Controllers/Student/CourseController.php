<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use App\Services\AccessControlService;
use App\Services\Gamification\ReferralService;
use App\Services\Student\StudentCourseVisibilityService;
use App\Events\N8nWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of available courses (catalog).
     */
    public function index(Request $request)
    {
        try {
            $query = Course::with(['category', 'enrollments'])
                ->where('is_published', true)
                ->where('is_visible', true);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('course_category_id', $request->category_id);
            }

            // Filter by level
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            // Filter by language
            if ($request->filled('language')) {
                $query->where('language', $request->language);
            }

            // Filter by price
            if ($request->filled('price_filter')) {
                if ($request->price_filter === 'free') {
                    $query->where(function($q) {
                        $q->whereNull('price')->orWhere('price', 0);
                    });
                } elseif ($request->price_filter === 'paid') {
                    $query->where('price', '>', 0);
                }
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'popular') {
                $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            $courses = $query->paginate($request->get('per_page', 12));

            // Get filter options
            $categories = CourseCategory::all();
            $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
            $languages = ['ar', 'en', 'fr'];

            return view('student.pages.courses.index', compact('courses', 'categories', 'levels', 'languages'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل الكورسات: ' . $e->getMessage());
        }
    }

    /**
     * Display my enrolled courses.
     */
    public function myCourses(Request $request)
    {
        try {
            $student = auth()->user();
            $visibility = app(StudentCourseVisibilityService::class);
            $hiddenCourseIds = $visibility->hiddenCourseIds($student);

            $query = $student->courseEnrollments()->with([
                'course.category',
                'course' => fn ($q) => $q->withCount(['sections', 'modules']),
            ]);

            if ($hiddenCourseIds !== []) {
                $query->whereNotIn('course_id', $hiddenCourseIds);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('enrollment_status', $request->status);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('course', function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'enrollment_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $enrollments = $query->paginate($request->get('per_page', 12));

            $statsQuery = function () use ($student, $hiddenCourseIds) {
                $q = $student->courseEnrollments();
                if ($hiddenCourseIds !== []) {
                    $q->whereNotIn('course_id', $hiddenCourseIds);
                }

                return $q;
            };

            // Get statistics
            $stats = [
                'total_courses' => $statsQuery()->count(),
                'active_courses' => $statsQuery()->where('enrollment_status', 'active')->count(),
                'completed_courses' => $statsQuery()->where('enrollment_status', 'completed')->count(),
                'average_progress' => $statsQuery()->avg('completion_percentage') ?? 0,
            ];

            $pendingMembershipNotices = $visibility->pendingNotices($student);

            return view('student.pages.courses.my-courses', compact('enrollments', 'stats', 'pendingMembershipNotices'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل كورساتي: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course details.
     */
    public function show($id)
    {
        try {
            $student = auth()->user();
            $accessControl = new AccessControlService();
            
            $course = Course::with([
                'category',
                'sections' => function($q) {
                    $q->where('course_sections.is_visible', true)->orderBy('sort_order');
                },
                'sections.modules' => function($q) {
                    $q->where('course_modules.is_visible', true)->orderBy('sort_order');
                },
                'sections.modules.modulable',
                'instructors'
            ])->findOrFail($id);

            // Check course access using AccessControlService
            $courseAccess = $accessControl->canAccessCourse($course, $student);
            if (!$courseAccess['can_access']) {
                return redirect()
                    ->route('student.courses.my-courses')
                    ->with('error', $courseAccess['reason'] ?? 'هذا الكورس غير متاح حالياً');
            }

            $enrollment = $courseAccess['enrollment'] ?? null;

            // Filter sections and modules based on access restrictions
            $accessibleSections = collect();
            foreach ($course->sections as $section) {
                $sectionAccess = $accessControl->canAccessSection($section, $student);
                if ($sectionAccess['can_access']) {
                    // Filter modules within accessible sections
                    $accessibleModules = collect();
                    foreach ($section->modules as $module) {
                        $moduleAccess = $accessControl->canAccessModule($module, $student);
                        if ($moduleAccess['can_access']) {
                            $accessibleModules->push($module);
                        }
                    }
                    $section->setRelation('modules', $accessibleModules);
                    $accessibleSections->push($section);
                }
            }
            $course->setRelation('sections', $accessibleSections);

            // Get course statistics (only visible)
            $stats = [
                'total_students' => $course->enrollments()->count(),
                'total_sections' => $course->sections()->where('course_sections.is_visible', true)->count(),
                'total_modules' => $course->modules()->where('course_modules.is_visible', true)->count(),
                'average_rating' => 0, // TODO: Implement rating system
            ];

            // Get completed modules for the student
            $completedModules = [];
            if ($enrollment) {
                $completedModules = ModuleCompletion::where('student_id', $student->id)
                    ->whereIn('module_id', $course->modules()->pluck('course_modules.id'))
                    ->where('completion_status', 'completed')
                    ->pluck('module_id')
                    ->toArray();
            }

            return view('student.pages.courses.show', compact('course', 'enrollment', 'stats', 'completedModules'));
        } catch (\Exception $e) {
            return redirect()
                ->route('student.courses.my-courses')
                ->with('error', 'حدث خطأ أثناء تحميل الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Enroll in a course.
     */
    public function enroll(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $student = auth()->user();

            // Check if course is published and visible
            if (!$course->is_published || !$course->is_visible) {
                return redirect()
                    ->back()
                    ->with('error', 'هذا الكورس غير متاح للتسجيل');
            }

            // Check if already enrolled
            $existingEnrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingEnrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت مسجل بالفعل في هذا الكورس');
            }

            // Check enrollment type
            if ($course->enrollment_type === 'invite_only') {
                return redirect()
                    ->back()
                    ->with('error', 'هذا الكورس يتطلب دعوة للتسجيل');
            }

            // Check max students
            if ($course->max_students) {
                $currentEnrollments = $course->enrollments()->where('enrollment_status', 'active')->count();
                if ($currentEnrollments >= $course->max_students) {
                    return redirect()
                        ->back()
                        ->with('error', 'الكورس مكتمل العدد');
                }
            }

            // Check enrollment dates
            if ($course->enrollment_start_date && now() < $course->enrollment_start_date) {
                return redirect()
                    ->back()
                    ->with('error', 'لم يبدأ التسجيل بعد');
            }

            if ($course->enrollment_end_date && now() > $course->enrollment_end_date) {
                return redirect()
                    ->back()
                    ->with('error', 'انتهى وقت التسجيل');
            }

            // Create enrollment
            $enrollmentStatus = $course->enrollment_type === 'by_approval' ? 'pending' : 'active';

            $enrollment = CourseEnrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'enrollment_date' => now(),
                'enrollment_status' => $enrollmentStatus,
                'enrolled_by' => $student->id,
                'completion_percentage' => 0,
            ]);

            DB::commit();

            // Dispatch n8n webhook event (only for active enrollments)
            if ($enrollmentStatus === 'active') {
                event(new N8nWebhookEvent('student.enrolled', [
                    'student_id' => $enrollment->student_id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'course_id' => $enrollment->course_id,
                    'course_title' => $course->title,
                    'enrollment_id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                    'enrolled_by' => $enrollment->enrolled_by,
                ]));
            }

            $message = $enrollmentStatus === 'pending'
                ? 'تم إرسال طلب التسجيل بنجاح. في انتظار الموافقة'
                : 'تم التسجيل في الكورس بنجاح';

            return redirect()
                ->route('student.courses.my-courses')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Unenroll from a course.
     */
    public function unenroll($id)
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $student = auth()->user();

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Check if can unenroll
            if ($enrollment->enrollment_status === 'completed') {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن إلغاء التسجيل من كورس مكتمل');
            }

            $enrollment->delete();

            DB::commit();

            // Dispatch n8n webhook event
            event(new N8nWebhookEvent('student.unenrolled', [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'unenrolled_at' => now()->toIso8601String(),
            ]));

            return redirect()
                ->route('student.courses.my-courses')
                ->with('success', 'تم إلغاء التسجيل من الكورس بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إلغاء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Continue learning a course.
     */
    public function learn($id)
    {
        try {
            $course = Course::with(['sections.modules'])->findOrFail($id);
            $student = auth()->user();

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->where('enrollment_status', 'active')
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->route('student.courses.show', $course->id)
                    ->with('error', 'يجب التسجيل في الكورس أولاً');
            }

            // Update last accessed
            $enrollment->touchLastAccessed();

            // Find next incomplete module
            $nextModule = null;
            foreach ($course->sections as $section) {
                foreach ($section->modules as $module) {
                    if (!$module->isCompletedBy($student)) {
                        $nextModule = $module;
                        break 2;
                    }
                }
            }

            if ($nextModule) {
                return redirect()->route('student.learn.module', $nextModule->id);
            }

            // All modules completed
            return redirect()
                ->route('student.courses.show', $course->id)
                ->with('success', 'تهانينا! لقد أكملت جميع وحدات الكورس');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Record course share for gamification points.
     */
    public function share(Course $course, ReferralService $referralService)
    {
        $student = auth()->user();
        $result = $referralService->handleCourseShare($student, $course);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => (bool) ($result['success'] ?? false),
                'message' => ($result['success'] ?? false)
                    ? 'تم تسجيل المشاركة ومنح النقاط'
                    : 'تم تسجيل المشاركة مسبقاً أو لا يمكن منح النقاط',
                'points_awarded' => $result['points_awarded'] ?? 0,
            ]);
        }

        return redirect()
            ->back()
            ->with(($result['success'] ?? false) ? 'success' : 'info',
                ($result['success'] ?? false)
                    ? 'شكراً للمشاركة! تم منحك نقاط المشاركة.'
                    : 'تم تسجيل مشاركة هذا الكورس مسبقاً.');
    }
}
