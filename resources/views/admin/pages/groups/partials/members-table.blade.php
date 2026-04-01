@if($members && $members->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover text-nowrap">
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
                        <tr>
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
                            <td>{{ $memberRecord->student->email }}</td>
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
                                @endphp
                                @if($lastActivity)
                                    <span title="{{ $lastActivity->format('Y-m-d H:i:s') }}">
                                        {{ $lastActivity->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($isOnline)
                                    <span class="badge bg-success" title="متصل الآن - آخر نشاط: {{ $lastActivity ? $lastActivity->format('Y-m-d H:i:s') : 'الآن' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>متصل
                                    </span>
                                @else
                                    <span class="badge bg-secondary" title="غير متصل{{ $lastActivity ? ' - آخر نشاط: ' . $lastActivity->format('Y-m-d H:i:s') : '' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>غير متصل
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $dueAmount = (float) ($dueAmountsByStudentId[$memberRecord->student_id] ?? 0);
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
@else
    <div class="text-center py-5">
        <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
        <h4 class="text-muted mb-3">لا يوجد أعضاء</h4>
        <p class="text-muted">ابدأ بإضافة أعضاء إلى هذه المجموعة</p>
        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fas fa-user-plus me-2"></i>إضافة عضو
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
                            $dueAmount = (float) ($dueAmountsByStudentId[$studentId] ?? 0);
                            $paidTotal = (float) ($studentPaidTotalsById[$studentId] ?? 0);
                            $outstandingInvoices = $studentOutstandingInvoicesById[$studentId] ?? [];
                            $studentPayments = $studentPaymentsById[$studentId] ?? [];
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
                                            <th>رقم الفاتورة</th>
                                            <th>المبلغ المتبقي</th>
                                            <th>تاريخ الاستحقاق</th>
                                            <th>المصدر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outstandingInvoices as $invoice)
                                            <tr>
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
