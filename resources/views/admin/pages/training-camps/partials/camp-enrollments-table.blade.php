<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-camp-enrollments-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">الطالب</th>
                <th scope="col">تاريخ التسجيل</th>
                <th scope="col">الحالة</th>
                <th scope="col">حالة الدفع</th>
                <th scope="col">الإيصال</th>
                <th scope="col" style="width: 150px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($enrollments as $enrollment)
                @php
                    $student = $enrollment->student;
                    $initial = $student ? mb_strtoupper(mb_substr($student->name, 0, 1)) : '?';
                    $statusClasses = [
                        'pending' => 'admin-camp-enrollment-status--pending',
                        'approved' => 'admin-camp-enrollment-status--approved',
                        'rejected' => 'admin-camp-enrollment-status--rejected',
                        'cancelled' => 'admin-camp-enrollment-status--cancelled',
                    ];
                    $paymentClasses = [
                        'unpaid' => 'admin-camp-enrollment-payment--unpaid',
                        'paid' => 'admin-camp-enrollment-payment--paid',
                        'refunded' => 'admin-camp-enrollment-payment--refunded',
                    ];
                @endphp
                <tr class="admin-camp-enrollments-table__row" id="enrollment-row-{{ $enrollment->id }}">
                    <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>

                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="admin-camps-table__thumb flex-shrink-0" style="width:34px;height:34px;border-radius:10px;">
                                <span>{{ $initial }}</span>
                            </div>
                            <div class="min-w-0">
                                @if($student)
                                    <a href="{{ route('users.show', $student->id) }}"
                                       class="fw-semibold text-decoration-none d-block text-truncate admin-camps-table__name">
                                        {{ $student->name }}
                                    </a>
                                    @if($student->email)
                                        <small class="text-muted d-block text-truncate">{{ $student->email }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="small text-muted">
                            <i class="fe fe-calendar me-1"></i>
                            {{ $enrollment->enrollment_date?->format('Y-m-d') ?? $enrollment->created_at->format('Y-m-d') }}
                        </span>
                    </td>

                    <td>
                        <span class="admin-camp-enrollment-status {{ $statusClasses[$enrollment->status] ?? '' }}">
                            {{ $enrollment->status_label }}
                        </span>
                    </td>

                    <td>
                        <span class="admin-camp-enrollment-payment {{ $paymentClasses[$enrollment->payment_status] ?? '' }}">
                            {{ $enrollment->payment_status_label }}
                        </span>
                    </td>

                    <td>
                        @if($enrollment->hasReceipt())
                            <a href="{{ route('training-camps.enrollments.receipt', [$camp->id ?? $enrollment->camp_id, $enrollment->id]) }}"
                               class="btn btn-sm btn-outline-primary"
                               target="_blank"
                               title="عرض الإيصال">
                                <i class="fe fe-paperclip"></i>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-info-light js-view-camp-enrollment"
                                    data-enrollment-id="{{ $enrollment->id }}" title="عرض التفاصيل">
                                <i class="fe fe-eye"></i>
                            </button>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-primary-light"
                                        data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed","placement":"bottom-end"}'
                                        aria-expanded="false" title="تغيير الحالة">
                                    <i class="fe fe-more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><h6 class="dropdown-header">حالة التسجيل</h6></li>
                                    @foreach(['pending', 'approved', 'rejected', 'cancelled'] as $status)
                                        @if($enrollment->status !== $status)
                                            @php
                                                $labels = ['pending' => 'قيد المراجعة', 'approved' => 'مقبول', 'rejected' => 'مرفوض', 'cancelled' => 'ملغي'];
                                                $icons = ['pending' => 'fe-clock', 'approved' => 'fe-check-circle', 'rejected' => 'fe-x-circle', 'cancelled' => 'fe-ban'];
                                            @endphp
                                            <li>
                                                <button type="button" class="dropdown-item js-update-camp-enrollment-status"
                                                        data-enrollment-id="{{ $enrollment->id }}"
                                                        data-new-status="{{ $status }}">
                                                    <i class="fe {{ $icons[$status] }} me-2"></i>{{ $labels[$status] }}
                                                </button>
                                            </li>
                                        @endif
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">حالة الدفع</h6></li>
                                    @foreach(['unpaid', 'paid', 'refunded'] as $paymentStatus)
                                        @if($enrollment->payment_status !== $paymentStatus)
                                            @php
                                                $paymentLabels = ['unpaid' => 'غير مدفوع', 'paid' => 'مدفوع', 'refunded' => 'مسترجع'];
                                                $paymentIcons = ['unpaid' => 'fe-alert-circle', 'paid' => 'fe-dollar-sign', 'refunded' => 'fe-rotate-ccw'];
                                            @endphp
                                            <li>
                                                <button type="button" class="dropdown-item js-update-camp-enrollment-payment"
                                                        data-enrollment-id="{{ $enrollment->id }}"
                                                        data-new-payment-status="{{ $paymentStatus }}">
                                                    <i class="fe {{ $paymentIcons[$paymentStatus] }} me-2"></i>{{ $paymentLabels[$paymentStatus] }}
                                                </button>
                                            </li>
                                        @endif
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger js-delete-camp-enrollment"
                                                data-enrollment-id="{{ $enrollment->id }}">
                                            <i class="fe fe-trash-2 me-2"></i>حذف
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-users group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا يوجد أعضاء</h5>
                            <p class="group-show-empty__desc mb-3">لم يتم العثور على أعضاء مطابقين للفلاتر الحالية.</p>
                            <a href="{{ route('training-camps.enrollments.create-individual', $camp->id) }}" class="btn btn-primary btn-sm">
                                <i class="fe fe-user-plus me-1"></i>إضافة عضو
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($enrollments->count() > 0)
    <div class="d-flex justify-content-center mt-4 admin-camp-enrollments-pagination">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
