<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-camp-enrollments-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">الطالب</th>
                <th scope="col">المعسكر</th>
                <th scope="col">تاريخ الطلب</th>
                <th scope="col">الحالة</th>
                <th scope="col">حالة الدفع</th>
                <th scope="col" style="width: 140px;">الإجراءات</th>
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
                <tr class="admin-camp-enrollments-table__row">
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
                                        <div class="d-flex align-items-center gap-1 min-w-0">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary py-0 px-1 copy-student-email-btn flex-shrink-0"
                                                    data-email="{{ $student->email }}" title="نسخ البريد">
                                                <i class="fe fe-copy"></i>
                                            </button>
                                            <small class="text-muted text-truncate">{{ $student->email }}</small>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        @if($enrollment->camp)
                            <a href="{{ route('training-camps.show', $enrollment->camp_id) }}"
                               class="admin-camps-table__name text-decoration-none d-block text-truncate"
                               style="max-width: 12rem;">
                                {{ $enrollment->camp->name }}
                            </a>
                            <small class="text-muted d-block">
                                {{ $enrollment->camp->start_date?->format('Y-m-d') }}
                            </small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        <span class="small text-muted">
                            <i class="fe fe-clock me-1"></i>{{ $enrollment->created_at->format('Y-m-d H:i') }}
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
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-primary-light"
                                    data-bs-toggle="dropdown"
                                    data-bs-popper-config='{"strategy":"fixed","placement":"bottom-end"}'
                                    aria-expanded="false" title="المزيد">
                                <i class="fe fe-more-horizontal"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                @foreach([
                                    'pending' => ['label' => 'قيد الانتظار', 'icon' => 'fe-clock', 'class' => 'text-warning'],
                                    'approved' => ['label' => 'مقبول', 'icon' => 'fe-check-circle', 'class' => 'text-success'],
                                    'rejected' => ['label' => 'مرفوض', 'icon' => 'fe-x-circle', 'class' => 'text-danger'],
                                    'cancelled' => ['label' => 'ملغي', 'icon' => 'fe-ban', 'class' => 'text-secondary'],
                                ] as $status => $meta)
                                    @if($enrollment->status !== $status)
                                        <li>
                                            <button type="button" class="dropdown-item {{ $meta['class'] }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#changeStatusModal"
                                                    data-enrollment-id="{{ $enrollment->id }}"
                                                    data-new-status="{{ $status }}"
                                                    data-status-label="{{ $meta['label'] }}"
                                                    data-status-icon="{{ $meta['icon'] }}"
                                                    data-status-color="{{ str_replace('text-', '', $meta['class']) }}">
                                                <i class="fe {{ $meta['icon'] }} me-2"></i>{{ $meta['label'] }}
                                            </button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-inbox group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد طلبات تسجيل</h5>
                            <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو انتظر طلبات جديدة.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($enrollments->count() > 0)
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
