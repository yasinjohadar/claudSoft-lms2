<?php

namespace App\Http\Controllers\Admin;

use App\Events\N8nWebhookEvent;
use App\Events\StudentEnrolledInCourse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Nationality;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserAdminNote;
use App\Rules\PhoneMatchesCountryCode;
use App\Services\Storage\StorageHelperService;
use App\Services\TrainingCampEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

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
        $this->middleware('permission:user-edit')->only(['edit', 'update', 'updatePassword']);
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
        $stats = [
            'total' => (clone $usersQuery)->count(),
            'active' => (clone $usersQuery)->where('is_active', true)->count(),
            'online' => (clone $usersQuery)->where('is_connected', true)->count(),
            'students' => (clone $usersQuery)->role('student')->count(),
        ];

        $users = $usersQuery->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.users._users_table', [
                    'users' => $users,
                    'sessions' => $sessions,
                ])->render(),
                'modals_html' => view('admin.pages.users._users_modals', [
                    'users' => $users,
                ])->render(),
                'stats_html' => view('admin.pages.users.partials.stats', compact('stats'))->render(),
                'count' => $users->total(),
            ]);
        }

        return view('admin.pages.users.index', compact('users', 'roles', 'sessions', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $nationalities = Nationality::all();

        return view('admin.pages.users.create', compact('roles', 'nationalities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->input('country_code') === '') {
            $request->merge(['country_code' => null]);
        }

        // التحقق من صحة البيانات
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'country_code' => ['nullable', 'string', 'max:8', Rule::in(config('country_codes.allowed_codes'))],
            'phone' => ['nullable', 'string', 'max:20', new PhoneMatchesCountryCode],
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
            if (! $photoPath) {
                // Fallback to direct storage if dynamic storage fails
                $photo = $request->file('photo');
                $photoName = time().'_'.$photo->getClientOriginalName();
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

        return redirect()->route('users.index')->with('success', 'تم إضافة مستخدم جديد بنجاح');
    }

    /**
     * Display the specified resource (student profile with statistics).
     */
    public function show(string $id)
    {
        $user = User::with('nationality')->findOrFail($id);

        // Enrollments & course stats
        $enrollments = \App\Models\CourseEnrollment::where('student_id', $id)
            ->with(['course.category', 'course.instructor'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        $courseStats = [
            'total_enrollments' => $enrollments->count(),
            'active_enrollments' => $enrollments->where('enrollment_status', 'active')->count(),
            'completed_enrollments' => $enrollments->where('enrollment_status', 'completed')->count(),
            'average_progress' => (float) ($enrollments->avg('completion_percentage') ?? 0),
        ];

        // Quiz attempts
        $quizAttempts = \App\Models\QuizAttempt::where('student_id', $id)
            ->with('quiz')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        $quizStats = [
            'total_attempts' => $quizAttempts->count(),
            'completed_attempts' => $quizAttempts->where('status', 'completed')->count(),
            'average_score' => (float) ($quizAttempts->avg('percentage_score') ?? 0),
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

        $billingStats = $this->buildStudentBillingStats((int) $id);
        $billingStats['payments_count'] = $payments->count();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();

        $payableInvoices = Invoice::where('student_id', $id)
            ->whereIn('status', ['issued', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderByDesc('issue_date')
            ->get(['id', 'invoice_number', 'remaining_amount', 'total_amount', 'status']);

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

        $adminNotes = $user->adminNotes()->with('creator')->get();

        $campEnrollments = \App\Models\CampEnrollment::where('student_id', $id)
            ->with(['camp.category', 'invoice'])
            ->orderByDesc('enrollment_date')
            ->get();

        $campStats = [
            'total' => $campEnrollments->count(),
            'approved' => $campEnrollments->where('status', 'approved')->count(),
            'pending' => $campEnrollments->where('status', 'pending')->count(),
        ];

        $enrolledCampIds = $campEnrollments->pluck('camp_id')->all();

        $availableGroups = \App\Models\CourseGroup::where('is_active', true)
            ->whereDoesntHave('members', function ($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->with('courses:id,title')
            ->orderBy('name')
            ->get(['id', 'name']);

        $availableCamps = \App\Models\TrainingCamp::where('is_active', true)
            ->when(count($enrolledCampIds) > 0, fn ($q) => $q->whereNotIn('id', $enrolledCampIds))
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'start_date', 'end_date', 'location']);

        return view('admin.pages.users.profile', compact(
            'user',
            'adminNotes',
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
            'deviceStats',
            'campEnrollments',
            'campStats',
            'availableGroups',
            'availableCamps',
            'paymentMethods',
            'payableInvoices'
        ));
    }

    /**
     * Record a payment against a student invoice (AJAX from profile).
     */
    public function recordPayment(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ((int) $invoice->student_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'الفاتورة لا تتبع هذا الطالب.',
            ], 422);
        }

        if (! in_array($invoice->status, ['issued', 'partial'], true) || (float) $invoice->remaining_amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسديد هذه الفاتورة.',
            ], 422);
        }

        if ($validated['amount'] > $invoice->remaining_amount) {
            return response()->json([
                'success' => false,
                'message' => 'المبلغ المدخل أكبر من المبلغ المتبقي ($'.number_format((float) $invoice->remaining_amount, 2).')',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = $invoice->recordPayment($validated['amount'], [
                'payment_method_id' => $validated['payment_method_id'],
                'payment_date' => $validated['payment_date'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            $payment->receipt_number = Payment::generateReceiptNumber();
            $payment->save();

            DB::commit();

            $invoice->refresh();
            $payment->load('paymentMethod');

            $billingStats = $this->buildStudentBillingStats($user->id);

            $displayedInvoiceIds = Invoice::where('student_id', $user->id)
                ->orderByDesc('issue_date')
                ->limit(10)
                ->pluck('id');

            $rowIndex = $displayedInvoiceIds->search($invoice->id);
            $invoiceRowNumber = $rowIndex === false ? 1 : $rowIndex + 1;

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدفعة بنجاح',
                'billing_stats' => $billingStats,
                'invoice_row_html' => view('admin.pages.users.partials.profile-invoice-row', [
                    'invoice' => $invoice,
                    'rowNumber' => $invoiceRowNumber,
                ])->render(),
                'payment_row_html' => view('admin.pages.users.partials.profile-payment-row', [
                    'payment' => $payment,
                    'rowNumber' => 1,
                ])->render(),
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدفعة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array{total_invoices: int, total_amount: float, total_paid: float, remaining_amount: float}
     */
    private function buildStudentBillingStats(int $studentId): array
    {
        $aggregates = Invoice::where('student_id', $studentId)
            ->selectRaw('count(*) as total_invoices, coalesce(sum(total_amount), 0) as total_amount, coalesce(sum(paid_amount), 0) as total_paid, coalesce(sum(remaining_amount), 0) as remaining_amount')
            ->first();

        return [
            'total_invoices' => (int) ($aggregates->total_invoices ?? 0),
            'total_amount' => (float) ($aggregates->total_amount ?? 0),
            'total_paid' => (float) ($aggregates->total_paid ?? 0),
            'remaining_amount' => (float) ($aggregates->remaining_amount ?? 0),
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $nationalities = Nationality::all();

        return view('admin.pages.users.edit', compact('roles', 'user', 'nationalities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->input('country_code') === '') {
            $request->merge(['country_code' => null]);
        }

        // التحقق من صحة البيانات
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'country_code' => ['nullable', 'string', 'max:8', Rule::in(config('country_codes.allowed_codes'))],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.$id, new PhoneMatchesCountryCode],
            'national_id' => 'nullable|string|max:20|unique:users,national_id,'.$id,
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
            if (! $photoPath) {
                // Fallback to direct storage if dynamic storage fails
                $photo = $request->file('photo');
                $photoName = time().'_'.$photo->getClientOriginalName();
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

        return redirect()->route('users.index')->with('success', 'تم حذف مستخدم جديد بنجاح');

    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تحديث كلمة المرور بنجاح',
            ]);
        }

        return redirect()->route('users.index')->with('success', 'تم تحديث كلمة المرور بنجاح');
    }

    /**
     * تبديل حالة المستخدم (تفعيل/إلغاء تفعيل)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'لا يمكنك إلغاء تفعيل حسابك الشخصي');
        }

        if ($user->is_active) {
            $validated = $request->validate([
                'admin_note_body' => 'required|string|max:5000',
                'occurred_on' => 'required|date|before_or_equal:today',
            ], [
                'admin_note_body.required' => 'يرجى إدخال سبب أو ملاحظة توقيف المستخدم.',
                'occurred_on.required' => 'يرجى تحديد تاريخ الملاحظة.',
                'occurred_on.before_or_equal' => 'تاريخ الملاحظة لا يمكن أن يكون في المستقبل.',
            ]);

            try {
                DB::transaction(function () use ($user, $validated) {
                    UserAdminNote::create([
                        'user_id' => $user->id,
                        'created_by' => auth()->id(),
                        'body' => $validated['admin_note_body'],
                        'occurred_on' => $validated['occurred_on'],
                        'source' => 'deactivation',
                    ]);
                    $user->is_active = false;
                    $user->save();
                });
            } catch (\Throwable $e) {
                return redirect()->route('users.index')
                    ->with('error', 'حدث خطأ أثناء تحديث حالة المستخدم: '.$e->getMessage());
            }

            return redirect()->route('users.index')
                ->with('success', "تم إيقاف تفعيل المستخدم {$user->name} وتسجيل الملاحظة.");
        }

        $validated = $request->validate([
            'admin_note_body' => 'required|string|max:5000',
            'occurred_on' => 'required|date|before_or_equal:today',
        ], [
            'admin_note_body.required' => 'يرجى إدخال ملاحظة عن سبب التفعيل.',
            'occurred_on.required' => 'يرجى تحديد تاريخ الملاحظة.',
            'occurred_on.before_or_equal' => 'تاريخ الملاحظة لا يمكن أن يكون في المستقبل.',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                UserAdminNote::create([
                    'user_id' => $user->id,
                    'created_by' => auth()->id(),
                    'body' => $validated['admin_note_body'],
                    'occurred_on' => $validated['occurred_on'],
                    'source' => 'reactivation',
                ]);
                $user->is_active = true;
                $user->save();
            });
        } catch (\Throwable $e) {
            return redirect()->route('users.index')
                ->with('error', 'حدث خطأ أثناء تحديث حالة المستخدم: '.$e->getMessage());
        }

        return redirect()->route('users.index')
            ->with('success', "تم تفعيل المستخدم {$user->name} وتسجيل الملاحظة.");
    }

    /**
     * JSON fragment: admin notes table HTML for modal (users list).
     */
    public function adminNotesFragment(User $user)
    {
        $notes = $user->adminNotes()->with('creator')->get();

        return response()->json([
            'html' => view('admin.pages.users.partials.admin-notes-modal-body', compact('user', 'notes'))->render(),
        ]);
    }

    /**
     * Display all courses for a specific student
     */
    public function showCourses($userId)
    {
        $student = User::findOrFail($userId);

        // التحقق من أن المستخدم طالب
        if (! $student->hasRole('student')) {
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
        if (! $user->hasRole('student')) {
            return redirect()->route('users.index')
                ->with('error', 'هذا المستخدم ليس طالباً');
        }

        // جلب المجموعات التي ينتمي إليها الطالب
        $groupMemberships = \App\Models\CourseGroupMember::where('student_id', $user->id)
            ->with(['group.courses' => function ($query) {
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
        $groupCourseIds = $groupMemberships->flatMap(function ($membership) {
            return $membership->group->courses->pluck('id');
        })->unique()->toArray();

        $standaloneCourseIds = array_diff($enrolledCourseIds, $groupCourseIds);
        $standaloneCourses = \App\Models\Course::whereIn('id', $standaloneCourseIds)
            ->with(['category', 'instructor'])
            ->get();

        // جلب جميع المجموعات المتاحة (لإضافة الطالب إليها)
        $availableGroups = \App\Models\CourseGroup::where('is_active', true)
            ->whereDoesntHave('members', function ($query) use ($user) {
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
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
            'role' => 'nullable|in:member,leader',
        ]);

        try {
            $group = \App\Models\CourseGroup::findOrFail($validated['group_id']);

            if ($group->hasMember($user)) {
                $message = 'الطالب موجود بالفعل في هذه المجموعة';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }

            if ($group->isFull()) {
                $message = 'المجموعة ممتلئة';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }

            $role = $validated['role'] ?? 'member';
            $memberRecord = $group->addMember($user, $role);

            if (! $memberRecord) {
                $message = 'فشل إضافة الطالب إلى المجموعة';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }

            $message = "تم إضافة الطالب {$user->name} إلى المجموعة {$group->name} بنجاح";

            if ($request->wantsJson()) {
                $member = \App\Models\CourseGroupMember::query()
                    ->where('group_id', $group->id)
                    ->where('student_id', $user->id)
                    ->with(['group.courses'])
                    ->first();

                $total = \App\Models\CourseGroupMember::where('student_id', $user->id)->count();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'row_html' => view('admin.pages.users.partials.profile-group-row', [
                        'member' => $member,
                        'rowNumber' => $total,
                    ])->render(),
                    'stats' => ['total' => $total],
                    'group_id' => $group->id,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                throw $e;
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $message = 'حدث خطأ: '.$e->getMessage();

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }
    }

    /**
     * Add student to a training camp (AJAX from profile).
     */
    public function addToCamp(Request $request, User $user, TrainingCampEnrollmentService $enrollmentService): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'camp_id' => 'required|exists:training_camps,id',
            'status' => 'required|in:pending,approved,rejected,cancelled',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $camp = \App\Models\TrainingCamp::findOrFail($validated['camp_id']);
            $campFee = array_key_exists('price', $validated) && $validated['price'] !== null
                ? (float) $validated['price']
                : null;

            $enrollment = $enrollmentService->enrollStudent(
                $camp,
                $user->id,
                $validated['status'],
                $validated['payment_status'],
                $validated['notes'] ?? null,
                $campFee
            );

            $enrollment->load(['camp.category']);

            $campEnrollments = \App\Models\CampEnrollment::where('student_id', $user->id)->get();
            $campStats = [
                'total' => $campEnrollments->count(),
                'approved' => $campEnrollments->where('status', 'approved')->count(),
                'pending' => $campEnrollments->where('status', 'pending')->count(),
            ];

            $message = "تم تسجيل الطالب {$user->name} في المعسكر {$camp->name} بنجاح";

            if ($request->wantsJson()) {
                $enrollmentFee = $campFee ?? (float) $camp->price;

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'row_html' => view('admin.pages.users.partials.profile-camp-row', [
                        'campEnrollment' => $enrollment,
                        'rowNumber' => $campStats['total'],
                        'campFee' => $enrollmentFee,
                    ])->render(),
                    'camp_stats' => $campStats,
                    'camp_id' => $camp->id,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                throw $e;
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $message = 'حدث خطأ: '.$e->getMessage();

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
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
            if (! $group->hasMember($user)) {
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
                ->with('error', 'حدث خطأ: '.$e->getMessage());
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
                    \Log::warning('Webhook event failed for enrollment '.$enrollment->id.': '.$e->getMessage());
                }
                event(new StudentEnrolledInCourse($user, $course, $enrollment));
            }

            return redirect()->back()
                ->with('success', "تم تسجيل الطالب {$user->name} في الكورس {$course->title} بنجاح");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ: '.$e->getMessage());
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

            if (! $enrollment) {
                return redirect()->back()
                    ->with('error', 'الطالب غير مسجل في هذا الكورس');
            }

            // التحقق من أن الطالب ليس مسجلاً في الكورس من خلال مجموعة
            $groupEnrollments = \App\Models\CourseGroupMember::where('student_id', $user->id)
                ->whereHas('group.courses', function ($query) use ($course) {
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
                ->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }
}
