<?php

namespace App\Http\Controllers\Admin;

use App\Events\N8nWebhookEvent;
use App\Events\StudentEnrolledInCourse;
use App\Http\Controllers\Controller;
use App\Models\CampEnrollment;
use App\Models\CourseGroup;
use App\Models\Invoice;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\EvolutionInstance;
use App\Models\WhatsAppMessageTemplate;
use App\Models\Nationality;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserAdminNote;
use App\Rules\PhoneMatchesCountryCode;
use App\Rules\UniqueUserFullPhone;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\AdminUserListQueryService;
use App\Services\Storage\StorageHelperService;
use App\Services\Student\StudentAccountTierService;
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
    public function index(Request $request, StudentAccountTierService $tierService, AdminUserListQueryService $listQuery)
    {
        $roles = Role::all();
        $courseGroups = CourseGroup::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        // جلب آخر جلسات المستخدمين
        $sessions = DB::table('sessions')
            ->orderByDesc('last_activity')
            ->get()
            ->groupBy('user_id');

        // بدء استعلام المستخدمين
        $usersQuery = User::query();

        // بحث بالاسم العربي/الإنجليزي، البريد، والهاتف (مطابقة أرقام دقيقة)
        $listQuery->applySearch($usersQuery, $request->input('query'));

        // فلترة حسب الحالة النشطة
        if ($request->filled('is_active')) {
            $usersQuery->where('is_active', $request->input('is_active'));
        }

        // فلترة حسب الدور (Spatie)
        if ($request->filled('role')) {
            $usersQuery->role($request->input('role'));
        }

        // فلترة حسب نوع الحساب (فضي / ذهبي — اشتراك معسكر معتمد)
        if (in_array($request->input('account_tier'), ['gold', 'silver'], true)) {
            $tierService->applyUserQueryTierFilter($usersQuery, $request->input('account_tier'));
        }

        // فلترة حسب اكتمال البروفايل
        if (in_array($request->input('profile_completion'), ['complete', 'incomplete', 'low', 'medium'], true)) {
            $listQuery->applyProfileCompletionFilter($usersQuery, $request->input('profile_completion'));
        }

        // فلترة الطلاب حسب الانتماء لأي مجموعة مختارة
        $listQuery->applyCourseGroupFilter($usersQuery, (array) $request->input('group_ids', []));

        // تنفيذ الاستعلام
        $stats = [
            'total' => (clone $usersQuery)->count(),
            'active' => (clone $usersQuery)->where('is_active', true)->count(),
            'online' => (clone $usersQuery)->where('is_connected', true)->count(),
            'students' => (clone $usersQuery)->role('student')->count(),
        ];

        $users = $usersQuery->paginate(10);
        $tierByUserId = $tierService->tiersForUsers($users->getCollection());

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.users._users_table', [
                    'users' => $users,
                    'sessions' => $sessions,
                    'tierByUserId' => $tierByUserId,
                ])->render(),
                'modals_html' => view('admin.pages.users._users_modals', [
                    'users' => $users,
                ])->render(),
                'stats_html' => view('admin.pages.users.partials.stats', compact('stats'))->render(),
                'count' => $users->total(),
            ]);
        }

        return view('admin.pages.users.index', array_merge(
            compact('users', 'roles', 'courseGroups', 'sessions', 'stats', 'tierByUserId'),
            $this->userMessagingFormData()
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function userMessagingFormData(): array
    {
        return array_merge($this->emailFormData(), $this->whatsappFormData());
    }

    /**
     * @return array{emailTemplates: \Illuminate\Database\Eloquent\Collection, emailSettings: \Illuminate\Database\Eloquent\Collection, defaultEmailSetting: ?EmailSetting}
     */
    private function emailFormData(): array
    {
        return [
            'emailTemplates' => EmailTemplate::active()->orderBy('name_ar')->orderBy('name')->get(['id', 'name', 'name_ar', 'subject']),
            'emailSettings' => EmailSetting::orderByDesc('is_active')->orderBy('id')->get(),
            'defaultEmailSetting' => EmailSetting::getActive(),
        ];
    }

    /**
     * @return array{whatsappTemplates: \Illuminate\Database\Eloquent\Collection, evolutionInstances: \Illuminate\Database\Eloquent\Collection, defaultEvolutionInstance: ?EvolutionInstance}
     */
    private function whatsappFormData(): array
    {
        return [
            'whatsappTemplates' => WhatsAppMessageTemplate::active()
                ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
                ->orderBy('name')
                ->get(['id', 'name']),
            'evolutionInstances' => EvolutionInstance::orderByDesc('is_default')->orderBy('instance_name')->get(),
            'defaultEvolutionInstance' => EvolutionInstance::defaultInstance(),
        ];
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
            'phone' => ['nullable', 'string', 'max:20', new PhoneMatchesCountryCode, new UniqueUserFullPhone],
            'national_id' => 'nullable|string|max:20|unique:users,national_id',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'send_credentials' => 'sometimes|boolean',
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
            $photoPath = $this->storageHelper->storeUploadedFileWithFailover('public', 'users/photos', $request->file('photo'), 'image');
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
            ActivityLogService::logRoleSync(
                $user,
                [],
                $user->roles->pluck('name')->all()
            );
        }

        if ($user->hasRole('student')) {
            $user->assignStudentSerial();
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

        $credentialsMessage = '';
        if ($request->boolean('send_credentials', true)) {
            try {
                $result = app(\App\Services\Auth\AccountCreatedCredentialDeliveryService::class)->deliver(
                    $user,
                    $request->password,
                    \App\Services\Auth\AccountCreatedCredentialDeliveryService::CONTEXT_ADMIN_CREATE,
                );

                $parts = [];
                if ($result['email_sent']) {
                    $parts[] = 'البريد';
                }
                if ($result['whatsapp_sent']) {
                    $parts[] = 'الواتساب';
                }
                if ($parts !== []) {
                    $credentialsMessage = ' وتم إرسال بيانات الدخول عبر '.implode(' و', $parts).'.';
                } elseif ($result['email_error'] || $result['whatsapp_error']) {
                    $credentialsMessage = ' لكن تعذّر إرسال بعض قنوات بيانات الدخول.';
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send account credentials on admin create', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $credentialsMessage = ' لكن تعذّر إرسال بيانات الدخول.';
            }
        }

        return redirect()->route('users.index')->with('success', 'تم إضافة مستخدم جديد بنجاح'.$credentialsMessage);
    }

    /**
     * Display the specified resource (student profile with statistics).
     */
    public function show(string $id, StudentAccountTierService $tierService)
    {
        $user = User::with(['nationality', 'roles'])->findOrFail($id);
        $accountTier = $tierService->resolve($user);
        $accountTierLabel = $tierService->label($accountTier);

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

        $groupMembershipHistories = \App\Models\CourseGroupMembershipHistory::forStudent((int) $id)
            ->with(['group.courses', 'joinedByUser', 'removedByUser'])
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

        return view('admin.pages.users.profile', array_merge(compact(
            'user',
            'accountTier',
            'accountTierLabel',
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
            'groupMembershipHistories',
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
        ), $this->userMessagingFormData()));
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

            $response = [
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
            ];

            $campRowPayload = $this->buildProfileCampRowPayload($user->id, $invoice->id);
            if ($campRowPayload !== null) {
                $response['camp_enrollment_id'] = $campRowPayload['enrollment_id'];
                $response['camp_row_html'] = $campRowPayload['row_html'];
            }

            return response()->json($response);
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

        $activeAggregates = Invoice::where('student_id', $studentId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->selectRaw('coalesce(sum(total_amount), 0) as total_amount, coalesce(sum(paid_amount), 0) as total_paid, coalesce(sum(remaining_amount), 0) as remaining_amount')
            ->first();

        return [
            'total_invoices' => (int) ($aggregates->total_invoices ?? 0),
            'total_amount' => (float) ($activeAggregates->total_amount ?? 0),
            'total_paid' => (float) ($activeAggregates->total_paid ?? 0),
            'remaining_amount' => (float) ($activeAggregates->remaining_amount ?? 0),
        ];
    }

    /**
     * @return array{enrollment_id: int, row_html: string}|null
     */
    private function buildProfileCampRowPayload(int $studentId, int $invoiceId): ?array
    {
        $enrollment = CampEnrollment::query()
            ->where('student_id', $studentId)
            ->whereHas('invoiceItems', fn ($query) => $query->where('invoice_id', $invoiceId))
            ->with(['camp.category', 'invoice'])
            ->first();

        if (! $enrollment) {
            return null;
        }

        $orderedIds = CampEnrollment::query()
            ->where('student_id', $studentId)
            ->orderByDesc('enrollment_date')
            ->pluck('id');

        $rowIndex = $orderedIds->search($enrollment->id);
        $rowNumber = $rowIndex === false ? 1 : $rowIndex + 1;
        $campFee = (float) ($enrollment->invoice?->total_amount ?? $enrollment->camp?->price ?? 0);
        $canRecordCampPayments = PaymentMethod::where('is_active', true)->exists();

        return [
            'enrollment_id' => $enrollment->id,
            'row_html' => view('admin.pages.users.partials.profile-camp-row', [
                'campEnrollment' => $enrollment,
                'rowNumber' => $rowNumber,
                'campFee' => $campFee,
                'canRecordCampPayments' => $canRecordCampPayments,
            ])->render(),
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
            'phone' => ['nullable', 'string', 'max:20', new PhoneMatchesCountryCode, new UniqueUserFullPhone((int) $id)],
            'national_id' => 'nullable|string|max:20|unique:users,national_id,'.$id,
            'nationality_id' => 'nullable|exists:nationalities,id',
            'is_active' => 'boolean',
            'device_lock_mode' => 'nullable|in:inherit,enabled,disabled',
            'roles' => 'array',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
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
            'device_lock_mode' => $request->input('device_lock_mode', 'inherit'),
        ];

        // معالجة الصورة باستخدام النظام الديناميكي
        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->avatar) {
                $this->storageHelper->deleteFile('public', $user->avatar);
            }

            $photoPath = $this->storageHelper->storeUploadedFileWithFailover('public', 'users/photos', $request->file('photo'), 'image');
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
            $oldRoles = $user->roles->pluck('name')->all();
            $user->syncRoles($request->roles);
            ActivityLogService::logRoleSync(
                $user,
                $oldRoles,
                $user->fresh()->roles->pluck('name')->all()
            );
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

    /**
     * Delete multiple selected users in one transaction.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ], [
            'user_ids.required' => 'يرجى تحديد مستخدم واحد على الأقل.',
            'user_ids.*.exists' => 'أحد المستخدمين المحددين غير موجود.',
        ]);

        $userIds = array_map('intval', $validated['user_ids']);

        if (in_array((int) auth()->id(), $userIds, true)) {
            return back()->withErrors([
                'user_ids' => 'لا يمكنك حذف حسابك الشخصي ضمن الحذف الجماعي.',
            ]);
        }

        try {
            $deletedCount = DB::transaction(function () use ($userIds) {
                $users = User::query()
                    ->whereIn('id', $userIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($users as $user) {
                    $user->delete();
                }

                return $users->count();
            });
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'تعذر حذف المستخدمين المحددين بسبب وجود بيانات مرتبطة: '.$e->getMessage()
            );
        }

        return redirect()->route('users.index')
            ->with('success', "تم حذف {$deletedCount} مستخدم بنجاح.");
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
            'send_credentials' => ['sometimes', 'boolean'],
        ], [
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        if ($request->boolean('send_credentials')) {
            app(\App\Services\Auth\PasswordCredentialDeliveryService::class)->deliver(
                $user,
                $validated['password'],
                \App\Services\Auth\PasswordCredentialDeliveryService::CONTEXT_ADMIN_RESET
            );
        }

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
            'reason' => 'nullable|string|max:1000',
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
            $memberRecord = $group->addMember($user, $role, [
                'source' => \App\Models\CourseGroupMembershipHistory::SOURCE_PROFILE,
                'reason' => $validated['reason'] ?? null,
            ]);

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
                $history = \App\Models\CourseGroupMembershipHistory::forStudent($user->id)
                    ->where('group_id', $group->id)
                    ->whereNull('left_at')
                    ->latest('joined_at')
                    ->with(['group.courses', 'joinedByUser', 'removedByUser'])
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'row_html' => view('admin.pages.users.partials.profile-group-row', [
                        'member' => $member,
                        'rowNumber' => $total,
                    ])->render(),
                    'history_row_html' => $history
                        ? view('admin.pages.users.partials.profile-group-history-row', [
                            'history' => $history,
                            'rowNumber' => 1,
                        ])->render()
                        : null,
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

            $enrollment->load(['camp.category', 'invoice']);

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
                        'canRecordCampPayments' => PaymentMethod::where('is_active', true)->exists(),
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
     * Update camp enrollment status/payment from student profile (AJAX).
     */
    public function updateCampEnrollment(
        Request $request,
        User $user,
        CampEnrollment $enrollment,
        TrainingCampEnrollmentService $enrollmentService
    ): JsonResponse {
        if ((int) $enrollment->student_id !== (int) $user->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,approved,rejected,cancelled',
            'payment_status' => 'sometimes|required|in:unpaid,paid,refunded',
        ]);

        if (! array_key_exists('status', $validated) && ! array_key_exists('payment_status', $validated)) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم إرسال أي حقل للتحديث',
            ], 422);
        }

        try {
            $enrollment = $enrollmentService->updateEnrollment(
                $enrollment,
                $validated['status'] ?? null,
                $validated['payment_status'] ?? null
            );

            $campEnrollments = CampEnrollment::where('student_id', $user->id)->get();
            $campStats = [
                'total' => $campEnrollments->count(),
                'approved' => $campEnrollments->where('status', 'approved')->count(),
                'pending' => $campEnrollments->where('status', 'pending')->count(),
            ];

            $parts = [];
            if (array_key_exists('status', $validated)) {
                $parts[] = 'حالة التسجيل: '.$enrollment->status_label;
            }
            if (array_key_exists('payment_status', $validated)) {
                $parts[] = 'حالة الدفع: '.$enrollment->payment_status_label;
            }

            $response = [
                'success' => true,
                'message' => 'تم التحديث — '.implode(' — ', $parts),
                'camp_stats' => $campStats,
                'status' => $enrollment->status,
                'status_label' => $enrollment->status_label,
                'payment_status' => $enrollment->payment_status,
                'payment_status_label' => $enrollment->payment_status_label,
            ];

            if (array_key_exists('status', $validated) && in_array($validated['status'], ['cancelled', 'rejected'], true)) {
                $response['billing_stats'] = $this->buildStudentBillingStats($user->id);
                $response['cancelled_invoice_ids'] = $enrollmentService->findInvoicesForEnrollment($enrollment)
                    ->where('status', 'cancelled')
                    ->pluck('id')
                    ->values()
                    ->all();
            }

            return response()->json($response);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove student from a training camp (AJAX from profile).
     */
    public function removeFromCamp(
        Request $request,
        User $user,
        CampEnrollment $enrollment,
        TrainingCampEnrollmentService $enrollmentService
    ): JsonResponse|\Illuminate\Http\RedirectResponse {
        if ((int) $enrollment->student_id !== (int) $user->id) {
            abort(404);
        }

        try {
            $campName = $enrollment->camp?->name ?? 'المعسكر';
            $enrollmentId = $enrollment->id;
            $removed = $enrollmentService->removeEnrollment($enrollment);

            $campEnrollments = CampEnrollment::where('student_id', $user->id)->get();
            $campStats = [
                'total' => $campEnrollments->count(),
                'approved' => $campEnrollments->where('status', 'approved')->count(),
                'pending' => $campEnrollments->where('status', 'pending')->count(),
            ];

            $message = "تم إلغاء تسجيل الطالب {$user->name} من المعسكر {$campName} بنجاح";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'camp_stats' => $campStats,
                    'enrollment_id' => $enrollmentId,
                    'camp_id' => $removed['camp_id'],
                    'camp' => $removed['camp'],
                    'billing_stats' => $this->buildStudentBillingStats($user->id),
                    'cancelled_invoice_ids' => $removed['cancelled_invoice_ids'] ?? [],
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
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
        $validated = $request->validate([
            'group_id' => 'required|exists:course_groups,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $group = \App\Models\CourseGroup::withCount('courses')->findOrFail($validated['group_id']);

            if (! $group->hasMember($user)) {
                $message = 'الطالب غير موجود في هذه المجموعة';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }

            $groupId = $group->id;
            $groupName = $group->name;
            $coursesCount = (int) $group->courses_count;

            $removed = $group->removeMember($user, [
                'source' => \App\Models\CourseGroupMembershipHistory::SOURCE_PROFILE,
                'reason' => $validated['reason'] ?? null,
            ]);

            if (! $removed) {
                $message = 'فشل إزالة الطالب من المجموعة';

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : redirect()->back()->with('error', $message);
            }

            $message = "تم إلغاء انضمام الطالب {$user->name} من المجموعة {$groupName} بنجاح";

            if ($request->wantsJson()) {
                $total = \App\Models\CourseGroupMember::where('student_id', $user->id)->count();
                $history = \App\Models\CourseGroupMembershipHistory::forStudent($user->id)
                    ->where('group_id', $groupId)
                    ->whereNotNull('left_at')
                    ->latest('left_at')
                    ->with(['group.courses', 'joinedByUser', 'removedByUser'])
                    ->first();

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'stats' => ['total' => $total],
                    'group_id' => $groupId,
                    'group' => [
                        'id' => $groupId,
                        'name' => $groupName,
                        'courses_count' => $coursesCount,
                    ],
                    'history_id' => $history?->id,
                    'history_row_html' => $history
                        ? view('admin.pages.users.partials.profile-group-history-row', [
                            'history' => $history,
                            'rowNumber' => 1,
                        ])->render()
                        : null,
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
