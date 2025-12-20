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

class UserController extends Controller
{
    // public function __construct()
    // {
    //     // يمكنه فقط رؤية قائمة المستخدمين (index)
    //     $this->middleware(['permission:user-list'])->only('index');

    //     // يمكنه فقط إنشاء مستخدم جديد (create + store)
    //     $this->middleware(['permission:user-create'])->only(['create', 'store']);

    //     // يمكنه فقط تعديل المستخدم (edit + update)
    //     $this->middleware(['permission:user-edit'])->only(['edit', 'update']);

    //     // يمكنه فقط حذف المستخدم (destroy)
    //     $this->middleware(['permission:user-delete'])->only('destroy');

    //     // يمكنه فقط رؤية ملف المستخدم (show)
    //     $this->middleware(['permission:user-show'])->only('show');
    // }

    public function __construct()
{
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
            'phone' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'full_phone' => 'nullable|string|max:25|unique:users,full_phone',
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
            'full_phone.unique' => 'رقم الهاتف مستخدم بالفعل',
            'national_id.unique' => 'رقم الهوية مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'photo.image' => 'يجب أن يكون الملف صورة',
            'photo.mimes' => 'نوع الصورة غير مدعوم',
            'photo.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
        ]);

        // معالجة الصورة
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('users/photos', $photoName, 'public');
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'full_phone' => $request->full_phone,
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
            'groups'
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

        // تجهيز البيانات للتحديث
        $updateData = [
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'nationality_id' => $request->nationality_id,
            'is_active' => $request->boolean('is_active'),
        ];

        // معالجة الصورة
        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->avatar) {
                \Storage::disk('public')->delete($user->avatar);
            }

            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('users/photos', $photoName, 'public');
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


}
