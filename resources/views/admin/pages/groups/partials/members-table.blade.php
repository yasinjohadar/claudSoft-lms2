@php
    $paymentMethodsList = $paymentMethods ?? collect();
    $trainingCampsForModal = $trainingCampsForModal ?? collect();
    $groupMembersFilterActive = request()->filled('search')
        || request()->filled('other_group_id')
        || request()->filled('groups_count')
        || request()->filled('online_status')
        || request()->filled('login_status');
    $groupStats = $stats ?? [];
    $groupTotalMemberCount = $groupStats['total_members'] ?? null;
@endphp
@if($groupMembersFilterActive && isset($members))
    <div class="alert alert-light border py-2 px-3 mb-3 small" role="status">
        عدد النتائج المطابقة للفلتر:
        <strong class="text-dark">{{ $members->total() }}</strong>
        @if($groupTotalMemberCount !== null)
            <span class="text-muted ms-1">من أصل <strong>{{ $groupTotalMemberCount }}</strong> عضواً في المجموعة</span>
        @endif
    </div>
@endif
@if($members && $members->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllMembers" title="تحديد الكل">
                    </th>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الهاتف</th>
                    <th>الدور</th>
                    <th>تاريخ الانضمام</th>
                    <th>آخر دخول</th>
                    <th>الحالة</th>
                    <th>المبلغ المستحق</th>
                    <th>المجموعات الأخرى</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $index => $memberRecord)
                    @if($memberRecord->student)
                        <tr data-student-row-id="{{ $memberRecord->student_id }}">
                            <td>
                                <input type="checkbox" class="member-checkbox" value="{{ $memberRecord->student_id }}" data-member-name="{{ $memberRecord->student->name }}">
                            </td>
                            <td>{{ ($members->currentPage() - 1) * $members->perPage() + $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($memberRecord->student->avatar)
                                        <img src="{{ asset('storage/' . $memberRecord->student->avatar) }}" alt="{{ $memberRecord->student->name }}" class="avatar avatar-sm rounded-circle me-2">
                                    @else
                                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                            <span class="fw-bold">{{ substr($memberRecord->student->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <a href="{{ route('users.show', $memberRecord->student_id) }}" class="text-decoration-none">
                                        <strong>{{ $memberRecord->student->name }}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="text-break">{{ $memberRecord->student->email }}</span>
                                    @if($memberRecord->student->email)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary py-0 px-1 js-copy-member-email"
                                                title="نسخ البريد"
                                                data-email="{{ $memberRecord->student->email }}"
                                                aria-label="نسخ البريد الإلكتروني">
                                            <i class="fas fa-copy fa-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($memberRecord->student->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $memberRecord->student->phone) }}" target="_blank" class="text-success" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp me-1"></i>{{ $memberRecord->student->phone }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($memberRecord->role == 'leader')
                                    <span class="badge bg-warning">قائد</span>
                                @else
                                    <span class="badge bg-info">عضو</span>
                                @endif
                            </td>
                            <td>{{ $memberRecord->joined_at ? $memberRecord->joined_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                @php
                                    $studentId = $memberRecord->student_id;
                                    $lastActivity = $lastActivityByUserId[$studentId] ?? null;
                                    $isOnline = in_array($studentId, $onlineUserIds ?? []);
                                    $lastLoginAt = $memberRecord->student->last_login_at;
                                @endphp
                                @if($lastLoginAt)
                                    @php
                                        $lastLoginCarbon = \Carbon\Carbon::parse($lastLoginAt);
                                    @endphp
                                    <span title="آخر تسجيل دخول: {{ $lastLoginCarbon->format('Y-m-d H:i:s') }}">
                                        {{ $lastLoginCarbon->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($isOnline)
                                    <span class="badge bg-success" title="نشاط جلسة خلال آخر 5 دقائق — آخر نشاط: {{ $lastActivity ? $lastActivity->format('Y-m-d H:i:s') : 'الآن' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>نشط حالياً
                                    </span>
                                @else
                                    <span class="badge bg-secondary" title="لا يوجد نشاط جلسة خلال آخر 5 دقائق{{ $lastActivity ? ' — آخر نشاط جلسة: ' . $lastActivity->format('Y-m-d H:i:s') : '' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>غير نشط حالياً
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sidDue = (int) $memberRecord->student_id;
                                    $dueAmount = (float) (
                                        $dueAmountsByStudentId[$sidDue]
                                        ?? $dueAmountsByStudentId[$memberRecord->student_id]
                                        ?? $dueAmountsByStudentId[(string) $memberRecord->student_id]
                                        ?? 0
                                    );
                                @endphp
                                @if($dueAmount > 0)
                                    <span class="badge bg-danger">${{ number_format($dueAmount, 2) }}</span>
                                @else
                                    <span class="badge bg-success">لا يوجد مستحقات</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $otherGroups = $memberRecord->student->courseGroupMemberships
                                        ->filter(function($membership) use ($group) {
                                            return $membership->group_id != $group->id && $membership->group;
                                        })
                                        ->pluck('group')
                                        ->filter();
                                @endphp
                                
                                @if($otherGroups->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($otherGroups->take(3) as $otherGroup)
                                            @php
                                                $otherGroupCourse = $otherGroup->courses->first();
                                            @endphp
                                            @if($otherGroupCourse)
                                                <a href="{{ route('courses.groups.show', [$otherGroupCourse->id, $otherGroup->id]) }}" class="badge bg-primary-transparent text-primary" title="{{ $otherGroup->name }}">
                                                    {{ $otherGroup->name }}
                                                </a>
                                            @else
                                                <span class="badge bg-primary-transparent text-primary" title="{{ $otherGroup->name }}">
                                                    {{ $otherGroup->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($otherGroups->count() > 3)
                                            <span class="badge bg-secondary-transparent text-secondary" title="{{ $otherGroups->skip(3)->pluck('name')->implode(', ') }}">
                                                +{{ $otherGroups->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($memberRecord->student->hasRole('student') && $memberRecord->student->is_active)
                                        <button type="button"
                                                class="btn btn-sm btn-success impersonate-btn"
                                                data-user-id="{{ $memberRecord->student_id }}"
                                                data-user-name="{{ $memberRecord->student->name }}"
                                                title="الدخول كطالب في تبويب جديد">
                                            <i class="fas fa-user-secret"></i>
                                        </button>
                                    @endif
                                    @if($memberRecord->student->hasRole('student') && $trainingCampsForModal->isNotEmpty())
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary js-open-attach-camp-modal"
                                                title="إضافة إلى معسكر تدريبي"
                                                data-student-id="{{ $memberRecord->student_id }}"
                                                data-student-name="{{ $memberRecord->student->name }}">
                                            <i class="fas fa-campground"></i>
                                        </button>
                                    @endif
                                    @if($dueAmount > 0 && $paymentMethodsList->isNotEmpty())
                                        @php
                                            $sid = (int) $memberRecord->student_id;
                                            $invoicesPayload = $studentOutstandingInvoicesById[$sid]
                                                ?? $studentOutstandingInvoicesById[$memberRecord->student_id]
                                                ?? $studentOutstandingInvoicesById[(string) $memberRecord->student_id]
                                                ?? [];
                                        @endphp
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success js-open-record-payment"
                                                title="تسجيل دفعة"
                                                data-student-id="{{ $memberRecord->student_id }}"
                                                data-student-name="{{ $memberRecord->student->name }}"
                                                data-total-due="{{ number_format($dueAmount, 2, '.', '') }}"
                                                data-invoices='@json($invoicesPayload)'>
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="تغيير الدور">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info"
                                            title="تفاصيل الدفع"
                                            data-bs-toggle="modal"
                                            data-bs-target="#paymentDetailsModal{{ $memberRecord->student_id }}">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="إزالة"
                                            data-bs-toggle="modal"
                                            data-bs-target="#removeMemberModal{{ $memberRecord->student_id }}"
                                            data-member-name="{{ $memberRecord->student->name }}"
                                            data-member-id="{{ $memberRecord->student_id }}">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $members->withQueryString()->links() }}
    </div>
@elseif($members && $groupMembersFilterActive && $members->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-filter fa-3x text-muted mb-3 opacity-50"></i>
        <h5 class="text-muted mb-2">لا توجد نتائج مطابقة للفلتر</h5>
        <p class="text-muted small mb-0">جرّب تغيير معايير البحث أو <a href="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}">إعادة التعيين</a>.</p>
    </div>
@else
    <div class="group-show-empty">
        <div class="group-show-empty__icon">
            <i class="fe fe-users"></i>
        </div>
        <h4 class="group-show-empty__title">لا يوجد أعضاء</h4>
        <p class="text-muted mb-0">ابدأ بإضافة أعضاء إلى هذه المجموعة</p>
        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fe fe-user-plus me-1"></i>إضافة عضو
        </button>
    </div>
@endif

@foreach($members as $memberRecord)
    @if($memberRecord->student)
        <div class="modal fade" id="paymentDetailsModal{{ $memberRecord->student_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-wallet me-2"></i>
                            تفاصيل الدفع - {{ $memberRecord->student->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $studentId = $memberRecord->student_id;
                            $sidModal = (int) $studentId;
                            $dueAmount = (float) (
                                $dueAmountsByStudentId[$sidModal]
                                ?? $dueAmountsByStudentId[$studentId]
                                ?? $dueAmountsByStudentId[(string) $studentId]
                                ?? 0
                            );
                            $paidTotal = (float) (
                                $studentPaidTotalsById[$sidModal]
                                ?? $studentPaidTotalsById[$studentId]
                                ?? $studentPaidTotalsById[(string) $studentId]
                                ?? 0
                            );
                            $outstandingInvoices = $studentOutstandingInvoicesById[$sidModal]
                                ?? $studentOutstandingInvoicesById[$studentId]
                                ?? $studentOutstandingInvoicesById[(string) $studentId]
                                ?? [];
                            $studentPayments = $studentPaymentsById[$sidModal]
                                ?? $studentPaymentsById[$studentId]
                                ?? $studentPaymentsById[(string) $studentId]
                                ?? [];
                        @endphp

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card border-danger h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">إجمالي المبلغ المستحق</span>
                                            @if($dueAmount > 0)
                                                <span class="badge bg-danger">${{ number_format($dueAmount, 2) }}</span>
                                            @else
                                                <span class="badge bg-success">لا يوجد مستحقات</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">إجمالي الدفعات المسددة</span>
                                            <span class="badge bg-success">${{ number_format($paidTotal, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-2 text-danger">المبالغ المستحقة ومصدرها</h6>
                        @if(!empty($outstandingInvoices))
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>رقم الفاتورة</th>
                                            <th>المبلغ المتبقي</th>
                                            <th>تاريخ الاستحقاق</th>
                                            <th>المصدر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outstandingInvoices as $invoice)
                                            <tr>
                                                <td class="text-muted small">{{ $invoice['id'] ?? '-' }}</td>
                                                <td>{{ $invoice['invoice_number'] ?? '-' }}</td>
                                                <td><span class="text-danger fw-bold">${{ number_format((float) ($invoice['remaining_amount'] ?? 0), 2) }}</span></td>
                                                <td>
                                                    {{ $invoice['due_date'] ?? '-' }}
                                                    @if(!empty($invoice['is_overdue']))
                                                        <span class="badge bg-danger ms-1">متأخرة</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($invoice['camp_names']))
                                                        @foreach($invoice['camp_names'] as $campName)
                                                            <span class="badge bg-primary-transparent text-primary me-1">{{ $campName }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">بدون معسكر مرتبط</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success py-2 mb-4">
                                <i class="fas fa-check-circle me-1"></i>
                                لا يوجد مستحقات على هذا الطالب.
                            </div>
                        @endif

                        <h6 class="mb-2 text-success">الدفعات التي قام الطالب بتسديدها</h6>
                        @if(!empty($studentPayments))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>رقم الدفعة</th>
                                            <th>المبلغ</th>
                                            <th>التاريخ</th>
                                            <th>رقم الفاتورة</th>
                                            <th>طريقة الدفع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($studentPayments as $payment)
                                            <tr>
                                                <td>{{ $payment['payment_number'] ?? '-' }}</td>
                                                <td><span class="text-success fw-bold">${{ number_format((float) ($payment['amount'] ?? 0), 2) }}</span></td>
                                                <td>{{ $payment['payment_date'] ?? '-' }}</td>
                                                <td>{{ $payment['invoice_number'] ?? '-' }}</td>
                                                <td>{{ $payment['payment_method'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                لا توجد دفعات مسددة لهذا الطالب حتى الآن.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="removeMemberModal{{ $memberRecord->student_id }}" tabindex="-1" aria-labelledby="removeMemberModalLabel{{ $memberRecord->student_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                            <i class="fas fa-user-times text-danger fs-24"></i>
                        </div>
                        <h5 class="mb-3">تأكيد إزالة العضو</h5>
                        <p class="text-muted mb-4">
                            هل أنت متأكد من إزالة
                            <strong class="text-dark">{{ $memberRecord->student->name }}</strong>
                            من المجموعة؟
                        </p>
                        <div class="alert alert-warning py-2 mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            سيتم أيضاً إلغاء تسجيله من الكورسات المرتبطة بهذه المجموعة
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>إلغاء
                            </button>
                            <form action="{{ route('groups.remove-member', [$group->id, $memberRecord->student_id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="fas fa-user-times me-1"></i>نعم، إزالة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
