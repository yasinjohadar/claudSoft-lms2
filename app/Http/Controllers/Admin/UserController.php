<?php

namespace App\Http\Controllers\Admin;

use HashContext;
use App\Models\User;
use App\Models\Nationality;
use App\Events\N8nWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\Storage\StorageHelperService;

class UserController extends Controller
{
    protected StorageHelperService $storageHelper;

    public function __construct(StorageHelperService $storageHelper)
    {
        $this->storageHelper = $storageHelper;
        
        // تأكد أن المستخدم مصادق أولًا ثم تحقق من الصلاحيات
        $this->middleware('auth');

        $this->middleware('permission:user-list')->only('index');
        $this->middleware('permission:user-create')->only(['create', 'store']);
        $this->middleware('permission:user-edit')->only(['edit', 'update']);
        $this->middleware('permission:user-delete')->only('destroy');
        $this->middleware('permission:user-show')->only('show');
    }

    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
    {
        $roles = Role::all();

        // جلب آخر جلسات المستخدمين
        $sessions = DB::table('sessions')
            ->orderByDesc('last_activity')
            ->get()
            ->groupBy('user_id');

        // بدء استعلام المستخدمين
        $usersQuery = User::query();

        // فلترة حسب البحث (name, email, phone)
        if ($request->filled('query')) {
            $search = $request->input('query');
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // فلترة حسب الحالة النشطة
        if ($request->filled('is_active')) {
            $usersQuery->where('is_active', $request->input('is_active'));
        }

        // تنفيذ الاستعلام
        $users = $usersQuery->paginate(10);

        return view("admin.pages.users.index", compact("users", "roles", "sessions"));
    }





    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $nationalities = Nationality::all();
        return view("admin.pages.users.create" ,compact("roles", "nationalities"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'country_code' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:20|unique:users,national_id',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'array',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'national_id.unique' => 'رقم الهوية مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'photo.image' => 'يجب أن يكون الملف صورة',
            'photo.mimes' => 'نوع الصورة غير مدعوم',
            'photo.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
        ]);

        // معالجة الصورة باستخدام النظام الديناميكي
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->storageHelper->storeUploadedFile('public', 'users/photos', $request->file('photo'), 'image');
            if (!$photoPath) {
                // Fallback to direct storage if dynamic storage fails
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photoPath = $photo->storeAs('users/photos', $photoName, 'public');
            }
        }

        // إنشاء المستخدم (full_phone يُحسب تلقائياً في User model boot من country_code + phone)
        $user = User::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'nationality_id' => $request->nationality_id,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
            'avatar' => $photoPath,
        ]);

        // تعيين الأدوار
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // Dispatch n8n webhook event
        event(new N8nWebhookEvent('user.registered', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->roles->pluck('name')->toArray(),
            'registered_at' => now()->toIso8601String(),
        ]));

        return redirect()->route("users.index")->with("success" , "تم إضافة مستخدم جديد بنجاح");
    }

    /**
     * Display the specified resource (student profile with statistics).
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        // Enrollments & course stats
        $enrollments = \App\Models\CourseEnrollment::where('student_id', $id)
            ->with(['course.category', 'course.instructor'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        $courseStats = [
            'total_enrollments'    => $enrollments->count(),
            'active_enrollments'   => $enrollments->where('enrollment_status', 'active')->count(),
            'completed_enrollments'=> $enrollments->where('enrollment_status', 'completed')->count(),
            'average_progress'     => (float) ($enrollments->avg('completion_percentage') ?? 0),
        ];

        // Quiz attempts
        $quizAttempts = \App\Models\QuizAttempt::where('student_id', $id)
            ->with('quiz')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        $quizStats = [
            'total_attempts'   => $quizAttempts->count(),
            'completed_attempts' => $quizAttempts->where('status', 'completed')->count(),
            'average_score'    => (float) ($quizAttempts->avg('percentage_score') ?? 0),
        ];

        // Payments & invoices
        $invoices = \App\Models\Invoice::where('student_id', $id)
            ->orderByDesc('issue_date')
            ->limit(10)
            ->get();

        $payments = \App\Models\Payment::where('student_id', $id)
            ->with('paymentMethod', 'invoice')
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $billingStats = [
            'total_invoices'   => $invoices->count(),
            'total_amount'     => (float) $invoices->sum('total_amount'),
            'total_paid'       => (float) $invoices->sum('paid_amount'),
            'remaining_amount' => (float) $invoices->sum('remaining_amount'),
            'payments_count'   => $payments->count(),
        ];

        // Certificates
        $certificates = \App\Models\Certificate::where('user_id', $id)
            ->with('course')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Groups
        $groups = \App\Models\CourseGroupMember::where('student_id', $id)
            ->with(['group.courses'])
            ->orderByDesc('joined_at')
            ->get();

        // User Sessions
        $userSessions = \App\Models\UserSession::where('user_id', $id)
            ->withCount('activities')
            ->orderByDesc('started_at')
            ->limit(20)
            ->get();

        $sessionStats = [
            'total' => \App\Models\UserSession::where('user_id', $id)->count(),
            'active' => \App\Models\UserSession::where('user_id', $id)->where('status', 'active')->count(),
            'completed' => \App\Models\UserSession::where('user_id', $id)->where('status', 'completed')->count(),
            'avg_duration' => \App\Models\UserSession::where('user_id', $id)
                ->whereNotNull('duration_seconds')
                ->avg('duration_seconds'),
        ];

        // User Devices
        $userDevices = \App\Models\UserDevice::where('user_id', $id)
            ->orderByDesc('last_used_at')
            ->get();

        $deviceStats = [
            'total' => $userDevices->count(),
            'trusted' => $userDevices->where('is_trusted', true)->count(),
            'blocked' => $userDevices->where('is_blocked', true)->count(),
        ];

        return view('admin.pages.users.profile', compact(
            'user',
            'enrollments',
            'courseStats',
            'quizAttempts',
            'quizStats',
            'invoices',
            'payments',
            'billingStats',
            'certificates',
            'groups',
            'userSessions',
            'sessionStats',
            'userDevices',
            'deviceStats'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $nationalities = Nationality::all();
        return view("admin.pages.users.edit" ,compact("roles" , "user", "nationalities"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // التحقق من صحة البيانات
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'country_code' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'national_id' => 'nullable|string|max:20|unique:users,national_id,' . $id,
            'nationality_id' => 'nullable|exists:nationalities,id',
            'is_active' => 'boolean',
            'roles' => 'array',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
            'national_id.unique' => 'رقم الهوية مستخدم بالفعل',
            'nationality_id.exists' => 'الجنسية المحددة غير موجودة',
            'photo.image' => 'يجب أن يكون الملف صورة',
            'photo.mimes' => 'نوع الصورة غير مدعوم',
            'photo.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
        ]);

        // تجهيز البيانات للتحديث (country_code + phone يُحدّثان full_phone تلقائياً في User model boot)
        $updateData = [
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'nationality_id' => $request->nationality_id,
            'is_active' => $request->boolean('is_active'),
        ];

        // معالجة الصورة باستخدام النظام الديناميكي
        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->avatar) {
                $this->storageHelper->deleteFile('public', $user->avatar);
            }

            $photoPath = $this->storageHelper->storeUploadedFile('public', 'users/photos', $request->file('photo'), 'image');
            if (!$photoPath) {
                // Fallback to direct storage if dynamic storage fails
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photoPath = $photo->storeAs('users/photos', $photoName, 'public');
            }
            
            $updateData['avatar'] = $photoPath;
        }

        // تحديث المستخدم
        $user->update($updateData);

        // تحديث الأدوار
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = User::findOrFail($request->id);

        $user->delete();

        return redirect()->route("users.index")->with("success" , "تم حذف مستخدم جديد بنجاح");

    }



    public function updatePassword(Request $request, User $user)
{
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ], [
        'password.required' => 'كلمة المرور مطلوبة',
        'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
    ]);

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    return redirect()->route('users.index')->with('success', 'تم تحديث كلمة المرور بنجاح');
}

/**
 * تبديل حالة المستخدم (تفعيل/إلغاء تفعيل)
 */
public function toggleStatus(Request $request, $id)
{
    try {
        $user = User::findOrFail($id);

        // التحقق من أن المستخدم لا يحاول إلغاء تفعيل نفسه
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'لا يمكنك إلغاء تفعيل حسابك الشخصي');
        }

        // تبديل الحالة
        $newStatus = !$user->is_active;
        $user->is_active = $newStatus;
        $user->save();

        $status = $user->is_active ? 'مفعل' : 'غير مفعل';

        return redirect()->route('users.index')
            ->with('success', "تم تحديث حالة المستخدم {$user->name} إلى: {$status}");

    } catch (\Exception $e) {
        return redirect()->route('users.index')
            ->with('error', 'حدث خطأ أثناء تحديث حالة المستخدم: ' . $e->getMessage());
    }
}

/**
 * Display all courses for a specific student
 */
public function showCourses($userId)
{
    $student = User::findOrFail($userId);

    // التحقق من أن المستخدم طالب
    if (!$student->hasRole('student')) {
        return redirect()->route('users.index')
            ->with('error', 'هذا المستخدم ليس طالباً');
    }

    // جلب كل التسجيلات مع الكورسات
    $enrollments = \App\Models\CourseEnrollment::where('student_id', $userId)
        ->with(['course.category', 'course.instructor'])
        ->orderBy('enrollment_date', 'desc')
        ->get();

    // حساب الإحصائيات
    $stats = [
        'total_enrollments' => $enrollments->count(),
        'active_enrollments' => $enrollments->where('enrollment_status', 'active')->count(),
        'completed_enrollments' => $enrollments->where('enrollment_status', 'completed')->count(),
        'pending_enrollments' => $enrollments->where('enrollment_status', 'pending')->count(),
        'suspended_enrollments' => $enrollments->where('enrollment_status', 'suspended')->count(),
        'cancelled_enrollments' => $enrollments->where('enrollment_status', 'cancelled')->count(),
        'average_progress' => $enrollments->avg('completion_percentage') ?? 0,
        'average_grade' => $enrollments->whereNotNull('grade')->avg('grade') ?? 0,
    ];

    return view('admin.pages.users.courses', compact('student', 'enrollments', 'stats'));
}

/**
 * Display student details including groups and courses
 */
public function studentDetails(User $user)
{
    // التحقق من أن المستخدم طالب
    if (!$user->hasRole('student')) {
        return redirect()->route('users.index')
            ->with('error', 'هذا المستخدم ليس طالباً');
    }

    // جلب المجموعات التي ينتمي إليها الطالب
    $groupMemberships = \App\Models\CourseGroupMember::where('student_id', $user->id)
        ->with(['group.courses' => function($query) {
            $query->wherePivot('is_visible', true);
        }])
        ->orderByDesc('joined_at')
        ->get();

    // جلب جميع الكورسات المسجلة فيها الطالب
    $enrollments = \App\Models\CourseEnrollment::where('student_id', $user->id)
        ->with(['course.category', 'course.instructor'])
        ->orderBy('enrollment_date', 'desc')
        ->get();

    // جلب الكورسات المنفصلة (غير المرتبطة بمجموعات)
    $enrolledCourseIds = $enrollments->pluck('course_id')->toArray();
    $groupCourseIds = $groupMemberships->flatMap(function($membership) {
        return $membership->group->courses->pluck('id');
    })->unique()->toArray();
    
    $standaloneCourseIds = array_diff($enrolledCourseIds, $groupCourseIds);
    $standaloneCourses = \App\Models\Course::whereIn('id', $standaloneCourseIds)
        ->with(['category', 'instructor'])
        ->get();

    // جلب جميع المجموعات المتاحة (لإضافة الطالب إليها)
    $availableGroups = \App\Models\CourseGroup::where('is_active', true)
        ->whereDoesntHave('members', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })
        ->with('courses')
        ->orderBy('name')
        ->get();

    // جلب جميع الكورسات المتاحة (غير المسجلة فيها الطالب)
    $availableCourses = \App\Models\Course::where('is_published', true)
        ->where('is_visible', true)
        ->whereNotIn('id', $enrolledCourseIds)
        ->with(['category', 'instructor'])
        ->orderBy('title')
        ->get();

    return view('admin.pages.users.student-details', compact(
        'user',
        'groupMemberships',
        'enrollments',
        'standaloneCourses',
        'availableGroups',
        'availableCourses'
    ));
}

/**
 * Add student to a group
 */
public function addToGroup(Request $request, User $user)
{
    $request->validate([
        'group_id' => 'required|exists:course_groups,id',
        'role' => 'nullable|in:member,leader',
    ]);

    try {
        $group = \App\Models\CourseGroup::findOrFail($request->group_id);

        // التحقق من أن الطالب ليس في المجموعة بالفعل
        if ($group->hasMember($user)) {
            return redirect()->back()
                ->with('error', 'الطالب موجود بالفعل في هذه المجموعة');
        }

        // التحقق من أن المجموعة ليست ممتلئة
        if ($group->isFull()) {
            return redirect()->back()
                ->with('error', 'المجموعة ممتلئة');
        }

        // إضافة الطالب للمجموعة
        $role = $request->input('role', 'member');
        $member = $group->addMember($user, $role);

        if ($member) {
            return redirect()->back()
                ->with('success', "تم إضافة الطالب {$user->name} إلى المجموعة {$group->name} بنجاح");
        } else {
            return redirect()->back()
                ->with('error', 'فشل إضافة الطالب إلى المجموعة');
        }
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

/**
 * Remove student from a group
 */
public function removeFromGroup(Request $request, User $user)
{
    $request->validate([
        'group_id' => 'required|exists:course_groups,id',
    ]);

    try {
        $group = \App\Models\CourseGroup::findOrFail($request->group_id);

        // التحقق من أن الطالب موجود في المجموعة
        if (!$group->hasMember($user)) {
            return redirect()->back()
                ->with('error', 'الطالب غير موجود في هذه المجموعة');
        }

        // إزالة الطالب من المجموعة
        $removed = $group->removeMember($user);

        if ($removed) {
            return redirect()->back()
                ->with('success', "تم إزالة الطالب {$user->name} من المجموعة {$group->name} بنجاح");
        } else {
            return redirect()->back()
                ->with('error', 'فشل إزالة الطالب من المجموعة');
        }
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

/**
 * Enroll student in a course
 */
public function enrollCourse(Request $request, User $user)
{
    $request->validate([
        'course_id' => 'required|exists:courses,id',
        'enrollment_status' => 'nullable|in:active,pending,suspended',
    ]);

    try {
        $course = \App\Models\Course::findOrFail($request->course_id);

        // التحقق من أن الطالب ليس مسجلاً في الكورس بالفعل
        $existingEnrollment = \App\Models\CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()
                ->with('error', 'الطالب مسجل بالفعل في هذا الكورس');
        }

        // التحقق من أن الكورس ليس ممتلئاً
        if ($course->max_students) {
            $currentEnrollments = \App\Models\CourseEnrollment::where('course_id', $course->id)->count();
            if ($currentEnrollments >= $course->max_students) {
                return redirect()->back()
                    ->with('error', 'الكورس ممتلئ');
            }
        }

        // تسجيل الطالب في الكورس
        $enrollment = \App\Models\CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'enrollment_date' => now(),
            'enrollment_status' => $request->input('enrollment_status', 'active'),
            'enrolled_by' => auth()->id(),
            'completion_percentage' => 0,
            'certificate_issued' => false,
        ]);

        // Dispatch n8n webhook event
        if ($enrollment->enrollment_status === 'active') {
            try {
                event(new N8nWebhookEvent('student.enrolled', [
                    'student_id' => $user->id,
                    'student_name' => $user->name ?? null,
                    'student_email' => $user->email ?? null,
                    'course_id' => $course->id,
                    'course_title' => $course->title ?? null,
                    'enrollment_id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                    'enrolled_by' => $enrollment->enrolled_by,
                ]));
            } catch (\Exception $e) {
                \Log::warning('Webhook event failed for enrollment ' . $enrollment->id . ': ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', "تم تسجيل الطالب {$user->name} في الكورس {$course->title} بنجاح");
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

/**
 * Unenroll student from a course
 */
public function unenrollCourse(Request $request, User $user)
{
    $request->validate([
        'course_id' => 'required|exists:courses,id',
    ]);

    try {
        $course = \App\Models\Course::findOrFail($request->course_id);

        // البحث عن التسجيل
        $enrollment = \App\Models\CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()
                ->with('error', 'الطالب غير مسجل في هذا الكورس');
        }

        // التحقق من أن الطالب ليس مسجلاً في الكورس من خلال مجموعة
        $groupEnrollments = \App\Models\CourseGroupMember::where('student_id', $user->id)
            ->whereHas('group.courses', function($query) use ($course) {
                $query->where('courses.id', $course->id);
            })
            ->exists();

        if ($groupEnrollments) {
            return redirect()->back()
                ->with('error', 'لا يمكن إزالة الطالب من الكورس لأنه مسجل من خلال مجموعة. يرجى إزالته من المجموعة أولاً');
        }

        // حذف التسجيل
        $enrollment->delete();

        return redirect()->back()
            ->with('success', "تم إزالة الطالب {$user->name} من الكورس {$course->title} بنجاح");
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

}
