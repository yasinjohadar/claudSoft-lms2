@php
    $enrollmentStatusLabels = [
        'active' => ['label' => 'نشط', 'class' => 'text-success'],
        'pending' => ['label' => 'قيد الانتظار', 'class' => 'text-warning'],
        'completed' => ['label' => 'مكتمل', 'class' => 'text-info'],
        'suspended' => ['label' => 'معلق', 'class' => 'text-danger'],
        'cancelled' => ['label' => 'ملغي', 'class' => 'text-muted'],
    ];
@endphp

<div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
    <h5 class="fw-bold mb-3">ملخص سريع</h5>
    <p class="text-muted mb-4">
        متابعة شاملة لكورسات الطالب، اختباراته، مدفوعاته، شهاداته، مجموعاته، جلساته وأجهزته من لوحة واحدة.
    </p>
    <div class="row g-3">
        <div class="col-md-6 col-xl-4">
            <a href="#tab-courses" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-book-open"></i>
                <span>{{ $courseStats['total_enrollments'] }} كورس مسجّل</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="#tab-quizzes" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-clipboard"></i>
                <span>{{ $quizStats['total_attempts'] }} محاولة اختبار</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="#tab-billing" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-credit-card"></i>
                <span>{{ $billingStats['total_invoices'] }} فاتورة</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="#tab-certificates" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-award"></i>
                <span>{{ $certificates->count() }} شهادة</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="#tab-groups" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-layers"></i>
                <span>{{ $groups->count() }} مجموعة</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="#tab-bootcamps" class="admin-profile-quick-link" data-bs-toggle="tab" role="tab">
                <i class="fe fe-zap"></i>
                <span>{{ $campStats['total'] ?? 0 }} معسكر تدريبي</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('admin.users.courses', $user->id) }}" class="admin-profile-quick-link admin-profile-quick-link--external">
                <i class="fe fe-external-link"></i>
                <span>صفحة الكورسات الكاملة</span>
            </a>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-courses" role="tabpanel">
    @if($enrollments->isEmpty())
        <div class="group-show-empty py-4">
            <i class="fe fe-book-open group-show-empty__icon" style="width:64px;height:64px;font-size:1.5rem;"></i>
            <p class="group-show-empty__desc mb-0">لا توجد كورسات مسجلة لهذا الطالب.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكورس</th>
                        <th>الحالة</th>
                        <th>التقدم</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $enrollment)
                        @php $st = $enrollmentStatusLabels[$enrollment->enrollment_status] ?? ['label' => $enrollment->enrollment_status, 'class' => '']; @endphp
                        <tr class="admin-users-table__row">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('courses.show', $enrollment->course_id) }}" class="fw-semibold text-decoration-none admin-users-table__name">
                                    {{ $enrollment->course->title ?? '—' }}
                                </a>
                            </td>
                            <td><span class="group-show-chip group-show-chip--sm {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small fw-semibold">{{ number_format((float) $enrollment->completion_percentage, 1) }}%</span>
                                    <div class="progress flex-fill" style="height:6px;min-width:50px;">
                                        <div class="progress-bar bg-primary" style="width: {{ min(100, max(0, (float) $enrollment->completion_percentage)) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td><small class="text-muted">{{ optional($enrollment->enrollment_date)->format('Y-m-d') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-quizzes" role="tabpanel">
    @if($quizAttempts->isEmpty())
        <div class="group-show-empty py-4">
            <p class="group-show-empty__desc mb-0">لا توجد محاولات اختبارات مسجلة.</p>
        </div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="admin-profile-mini-stat">
                    <small class="text-muted d-block">إجمالي المحاولات</small>
                    <strong class="fs-5">{{ $quizStats['total_attempts'] }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-profile-mini-stat">
                    <small class="text-muted d-block">متوسط الدرجة</small>
                    <strong class="fs-5 text-success">{{ number_format($quizStats['average_score'], 1) }}%</strong>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاختبار</th>
                        <th>الحالة</th>
                        <th>الدرجة</th>
                        <th>تاريخ الإكمال</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizAttempts as $attempt)
                        <tr class="admin-users-table__row">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $attempt->quiz->title ?? '—' }}</td>
                            <td><span class="group-show-chip group-show-chip--sm">{{ $attempt->status }}</span></td>
                            <td><span class="fw-semibold">{{ $attempt->percentage_score }}%</span></td>
                            <td><small class="text-muted">{{ optional($attempt->completed_at ?? $attempt->submitted_at)->format('Y-m-d H:i') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-billing" role="tabpanel">
    <div id="profile-billing-feedback" class="alert mb-3 d-none" role="alert"></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">عدد الفواتير</small>
                <strong class="fs-6" id="profile-billing-stat-total-invoices">{{ $billingStats['total_invoices'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">إجمالي المبلغ</small>
                <strong class="fs-6" id="profile-billing-stat-total-amount">{{ number_format($billingStats['total_amount'], 2) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">المدفوع</small>
                <strong class="fs-6" id="profile-billing-stat-total-paid">{{ number_format($billingStats['total_paid'], 2) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">المتبقي</small>
                <strong class="fs-6" id="profile-billing-stat-remaining">{{ number_format($billingStats['remaining_amount'], 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <h6 class="fw-bold mb-0">آخر الفواتير</h6>
        @if(($payableInvoices ?? collect())->isNotEmpty() && ($paymentMethods ?? collect())->isNotEmpty())
            <button type="button" class="btn btn-sm btn-success js-profile-record-payment" id="profile-open-payment-modal-btn">
                <i class="fe fe-dollar-sign me-1"></i>تسجيل دفعة
            </button>
        @endif
    </div>

    @if($invoices->isEmpty())
        <p class="text-muted small mb-4" id="profile-invoices-empty">لا توجد فواتير.</p>
    @else
        <div class="table-responsive mb-4" id="profile-invoices-table-wrap">
            <table class="table table-sm table-hover align-middle admin-users-table">
                <thead><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                <tbody id="profile-invoices-tbody">
                    @foreach($invoices as $invoice)
                        @include('admin.pages.users.partials.profile-invoice-row', [
                            'invoice' => $invoice,
                            'rowNumber' => $loop->iteration,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h6 class="fw-bold mb-2">آخر المدفوعات</h6>
    <p class="text-muted small mb-0 @if(!$payments->isEmpty()) d-none @endif" id="profile-payments-empty">لا توجد مدفوعات.</p>
    @if(!$payments->isEmpty())
        <div class="table-responsive" id="profile-payments-table-wrap">
            <table class="table table-sm table-hover align-middle admin-users-table">
                <thead><tr><th>رقم الدفعة</th><th>التاريخ</th><th>المبلغ</th><th>طريقة الدفع</th><th>الحالة</th></tr></thead>
                <tbody id="profile-payments-tbody">
                    @foreach($payments as $payment)
                        @include('admin.pages.users.partials.profile-payment-row', [
                            'payment' => $payment,
                            'rowNumber' => $loop->iteration,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="table-responsive d-none" id="profile-payments-table-wrap">
            <table class="table table-sm table-hover align-middle admin-users-table">
                <thead><tr><th>رقم الدفعة</th><th>التاريخ</th><th>المبلغ</th><th>طريقة الدفع</th><th>الحالة</th></tr></thead>
                <tbody id="profile-payments-tbody"></tbody>
            </table>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-certificates" role="tabpanel">
    @if($certificates->isEmpty())
        <div class="group-show-empty py-4">
            <p class="group-show-empty__desc mb-0">لا توجد شهادات صادرة لهذا الطالب.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead><tr><th>#</th><th>الكورس</th><th>تاريخ الإصدار</th></tr></thead>
                <tbody>
                    @foreach($certificates as $certificate)
                        <tr class="admin-users-table__row">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $certificate->course->title ?? '—' }}</td>
                            <td><small class="text-muted">{{ optional($certificate->created_at)->format('Y-m-d') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-groups" role="tabpanel">
    @if(($availableGroups ?? collect())->isNotEmpty())
        <div class="group-show-filters mb-4" id="profile-add-group-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="profile_group_id">المجموعة</label>
                    <select name="group_id" id="profile_group_id" class="form-select" required>
                        <option value="">اختر مجموعة</option>
                        @foreach($availableGroups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }}
                                @if($group->courses->isNotEmpty())
                                    ({{ $group->courses->count() }} كورس)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="profile_group_role">الدور</label>
                    <select name="role" id="profile_group_role" class="form-select">
                        <option value="member">عضو</option>
                        <option value="leader">قائد</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="profile-add-group-btn" class="btn btn-primary w-100">
                        <span class="profile-action-btn__label"><i class="fe fe-plus me-1"></i>إضافة للمجموعة</span>
                        <span class="profile-action-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإضافة...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="profile-groups-feedback" class="alert mb-3 d-none" role="alert"></div>

    <div id="profile-groups-empty" class="group-show-empty py-4 @if(!$groups->isEmpty()) d-none @endif">
        <p class="group-show-empty__desc mb-0">لا توجد مجموعات مسجل بها هذا الطالب.</p>
    </div>

    <div id="profile-groups-table-wrap" class="@if($groups->isEmpty()) d-none @endif">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead><tr><th>#</th><th>المجموعة</th><th>الدور</th><th>الكورسات</th><th>تاريخ الانضمام</th><th></th></tr></thead>
                <tbody id="profile-groups-tbody">
                    @foreach($groups as $member)
                        @include('admin.pages.users.partials.profile-group-row', [
                            'member' => $member,
                            'rowNumber' => $loop->iteration,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-bootcamps" role="tabpanel">
    @if(($availableCamps ?? collect())->isNotEmpty())
        <div class="group-show-filters mb-4" id="profile-add-camp-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="profile_camp_id">المعسكر</label>
                    <select name="camp_id" id="profile_camp_id" class="form-select" required>
                        <option value="">اختر معسكر</option>
                        @foreach($availableCamps as $camp)
                            <option value="{{ $camp->id }}" data-price="{{ $camp->price }}">
                                {{ $camp->name }} — {{ number_format($camp->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="profile_camp_price">رسم المعسكر</label>
                    <input type="number" name="price" id="profile_camp_price" class="form-control" step="0.01" min="0" placeholder="0.00">
                    <small class="text-muted">يُعبَّأ تلقائياً من سعر المعسكر</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="profile_camp_status">حالة التسجيل</label>
                    <select name="status" id="profile_camp_status" class="form-select" required>
                        <option value="pending" selected>قيد الانتظار</option>
                        <option value="approved">مقبول</option>
                        <option value="rejected">مرفوض</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="profile_camp_payment_status">حالة الدفع</label>
                    <select name="payment_status" id="profile_camp_payment_status" class="form-select" required>
                        <option value="unpaid" selected>غير مدفوع</option>
                        <option value="paid">مدفوع</option>
                        <option value="refunded">مسترد</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="profile_camp_notes">ملاحظات</label>
                    <textarea name="notes" id="profile_camp_notes" class="form-control" rows="1" placeholder="اختياري"></textarea>
                </div>
                <div class="col-12">
                    <button type="button" id="profile-add-camp-btn" class="btn btn-primary">
                        <span class="profile-action-btn__label"><i class="fe fe-plus me-1"></i>تسجيل في المعسكر</span>
                        <span class="profile-action-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التسجيل...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="profile-camps-feedback" class="alert mb-3 d-none" role="alert"></div>

    <div id="profile-camps-stats" class="row g-3 mb-4 @if(($campEnrollments ?? collect())->isEmpty()) d-none @endif">
        <div class="col-md-4">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">إجمالي المعسكرات</small>
                <strong class="fs-5" id="profile-camps-stat-total">{{ $campStats['total'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">مقبولة</small>
                <strong class="fs-5 text-success" id="profile-camps-stat-approved">{{ $campStats['approved'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-profile-mini-stat">
                <small class="text-muted d-block">قيد الانتظار</small>
                <strong class="fs-5 text-warning" id="profile-camps-stat-pending">{{ $campStats['pending'] ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <div id="profile-camps-empty" class="group-show-empty py-4 @if(($campEnrollments ?? collect())->isNotEmpty()) d-none @endif">
        <i class="fe fe-zap group-show-empty__icon" style="width:64px;height:64px;font-size:1.5rem;"></i>
        <h5 class="group-show-empty__title">لا توجد معسكرات</h5>
        <p class="group-show-empty__desc mb-0">الطالب غير مسجّل في أي معسكر تدريبي حالياً.</p>
    </div>

    <div id="profile-camps-table-wrap" class="@if(($campEnrollments ?? collect())->isEmpty()) d-none @endif">
        <p class="text-muted fs-12 mb-2">
            <i class="fe fe-info me-1"></i>
            يمكنك تغيير <strong>حالة التسجيل</strong> و<strong>حالة الدفع</strong> مباشرة من القوائم في الجدول أدناه.
        </p>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المعسكر</th>
                        <th>التصنيف</th>
                        <th>تاريخ التسجيل</th>
                        <th>رسم المعسكر</th>
                        <th class="profile-camp-col-status">حالة التسجيل</th>
                        <th class="profile-camp-col-payment">حالة الدفع</th>
                        <th>الفترة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="profile-camps-tbody">
                    @foreach($campEnrollments ?? collect() as $campEnrollment)
                        @include('admin.pages.users.partials.profile-camp-row', [
                            'campEnrollment' => $campEnrollment,
                            'rowNumber' => $loop->iteration,
                            'campFee' => (float) ($campEnrollment->invoice?->total_amount ?? $campEnrollment->camp?->price ?? 0),
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-sessions" role="tabpanel">
    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'إجمالي الجلسات', 'value' => number_format($sessionStats['total'])],
            ['label' => 'نشطة', 'value' => number_format($sessionStats['active']), 'class' => 'text-success'],
            ['label' => 'مكتملة', 'value' => number_format($sessionStats['completed']), 'class' => 'text-info'],
            ['label' => 'متوسط المدة', 'value' => $sessionStats['avg_duration'] ? gmdate('H:i:s', (int) $sessionStats['avg_duration']) : '—'],
        ] as $item)
            <div class="col-6 col-lg-3">
                <div class="admin-profile-mini-stat">
                    <small class="text-muted d-block">{{ $item['label'] }}</small>
                    <strong class="{{ $item['class'] ?? '' }}">{{ $item['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    @if($userSessions->isEmpty())
        <p class="text-muted mb-0">لا توجد جلسات مسجلة.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>البدء</th>
                        <th>الانتهاء</th>
                        <th>المدة</th>
                        <th>الجهاز</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userSessions as $session)
                        <tr class="admin-users-table__row">
                            <td>{{ $loop->iteration }}</td>
                            <td><small>{{ $session->started_at->format('Y-m-d H:i') }}</small></td>
                            <td><small>{{ $session->ended_at ? $session->ended_at->format('Y-m-d H:i') : '—' }}</small></td>
                            <td><span class="group-show-chip group-show-chip--sm">{{ $session->duration_formatted }}</span></td>
                            <td><small>{{ $session->device_info }}</small></td>
                            <td><span class="group-show-chip group-show-chip--sm">{{ $session->status }}</span></td>
                            <td>
                                <a href="{{ route('admin.user-sessions.show', $session->id) }}" class="btn btn-sm btn-info-light" title="التفاصيل">
                                    <i class="fe fe-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.user-sessions.user', $user->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fe fe-list me-1"></i>جميع الجلسات
            </a>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-devices" role="tabpanel">
    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'إجمالي الأجهزة', 'value' => number_format($deviceStats['total'])],
            ['label' => 'موثوقة', 'value' => number_format($deviceStats['trusted']), 'class' => 'text-success'],
            ['label' => 'محظورة', 'value' => number_format($deviceStats['blocked']), 'class' => 'text-danger'],
        ] as $item)
            <div class="col-md-4">
                <div class="admin-profile-mini-stat">
                    <small class="text-muted d-block">{{ $item['label'] }}</small>
                    <strong class="{{ $item['class'] ?? '' }}">{{ $item['value'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    @if($userDevices->isEmpty())
        <p class="text-muted mb-0">لا توجد أجهزة مسجلة.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الجهاز</th>
                        <th>الدخول</th>
                        <th>آخر استخدام</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userDevices as $device)
                        <tr class="admin-users-table__row">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <small>{{ $device->device_info }}</small>
                                @if($device->device_name)
                                    <br><strong class="small text-primary">{{ $device->device_name }}</strong>
                                @endif
                            </td>
                            <td><span class="group-show-chip group-show-chip--sm">{{ number_format($device->total_logins) }}</span></td>
                            <td><small>{{ $device->last_used_human }}</small></td>
                            <td>
                                <span class="{{ $device->status_badge['class'] ?? 'group-show-chip group-show-chip--sm' }}">
                                    {{ $device->status_badge['text'] ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.user-devices.show', $device->id) }}" class="btn btn-sm btn-info-light" title="التفاصيل">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    @if($device->is_blocked)
                                        <form action="{{ route('admin.user-devices.unblock', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('إلغاء حظر هذا الجهاز؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success-light" title="إلغاء الحظر"><i class="fe fe-unlock"></i></button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.user-devices.block', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حظر هذا الجهاز؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger-light" title="حظر"><i class="fe fe-slash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.user-devices.user', $user->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fe fe-list me-1"></i>جميع الأجهزة
            </a>
        </div>
    @endif
</div>

<div class="tab-pane fade" id="tab-admin-notes" role="tabpanel">
    <h5 class="fw-bold mb-2">سجل الملاحظات الإدارية</h5>
    <p class="text-muted small mb-3">تُسجَّل الملاحظات تلقائياً عند إيقاف أو تفعيل المستخدم من لوحة المستخدمين.</p>
    @if(!isset($adminNotes) || $adminNotes->isEmpty())
        <div class="group-show-empty py-4">
            <p class="group-show-empty__desc mb-0">لا توجد ملاحظات مسجلة.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-users-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الملاحظة</th>
                        <th>المصدر</th>
                        <th>سجّلها</th>
                        <th>وقت التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adminNotes as $note)
                        <tr class="admin-users-table__row">
                            <td><small>{{ $note->occurred_on?->format('Y-m-d') }}</small></td>
                            <td class="text-wrap" style="white-space:normal;max-width:320px;">{{ $note->body }}</td>
                            <td>
                                @if($note->source === 'deactivation')
                                    <span class="group-show-chip group-show-chip--sm text-warning">إيقاف تفعيل</span>
                                @elseif($note->source === 'reactivation')
                                    <span class="group-show-chip group-show-chip--sm text-success">تفعيل</span>
                                @else
                                    <span class="group-show-chip group-show-chip--sm">{{ $note->source }}</span>
                                @endif
                            </td>
                            <td>{{ $note->creator?->name ?? '—' }}</td>
                            <td><small class="text-muted">{{ $note->created_at?->format('Y-m-d H:i') }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
