<div class="table-responsive">
    <table class="table table-striped table-hover align-middle table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">الطالب</th>
                <th scope="col">المعسكر</th>
                <th scope="col">تاريخ الطلب</th>
                <th scope="col">الحالة</th>
                <th scope="col">حالة الدفع</th>
                <th scope="col">العمليات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($enrollments as $enrollment)
                <tr>
                    <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>

                    <td>
                        <div>
                            <strong>{{ $enrollment->student->name }}</strong>
                            <br>
                            <small class="text-muted">
                                <button type="button" class="btn btn-xs btn-outline-secondary copy-student-email-btn me-1"
                                    data-email="{{ $enrollment->student->email }}" title="نسخ البريد">
                                    <i class="fas fa-copy"></i>
                                </button>
                                {{ $enrollment->student->email }}
                            </small>
                        </div>
                    </td>

                    <td>
                        <div>
                            <strong>{{ $enrollment->camp->name }}</strong>
                            <br><small class="text-muted">
                                {{ $enrollment->camp->start_date->format('Y-m-d') }}
                            </small>
                        </div>
                    </td>

                    <td>
                        <small>{{ $enrollment->created_at->format('Y-m-d H:i') }}</small>
                    </td>

                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'bg-warning text-dark',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'cancelled' => 'bg-secondary',
                            ];
                        @endphp
                        <span class="badge {{ $statusColors[$enrollment->status] ?? 'bg-secondary' }}">
                            {{ $enrollment->status_label }}
                        </span>
                    </td>

                    <td>
                        @php
                            $paymentColors = [
                                'unpaid' => 'bg-warning text-dark',
                                'paid' => 'bg-success',
                                'refunded' => 'bg-secondary',
                            ];
                        @endphp
                        <span class="badge {{ $paymentColors[$enrollment->payment_status] ?? 'bg-secondary' }}">
                            {{ $enrollment->payment_status_label }}
                        </span>
                    </td>

                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            @php
                                $statusButtons = [
                                    'pending' => [
                                        'class' => 'btn-warning',
                                        'icon' => 'fa-clock',
                                        'label' => 'قيد الانتظار',
                                        'title' => 'تغيير إلى: قيد الانتظار',
                                        'color' => 'warning',
                                    ],
                                    'approved' => [
                                        'class' => 'btn-success',
                                        'icon' => 'fa-check-circle',
                                        'label' => 'مقبول',
                                        'title' => 'تغيير إلى: مقبول',
                                        'color' => 'success',
                                    ],
                                    'rejected' => [
                                        'class' => 'btn-danger',
                                        'icon' => 'fa-times-circle',
                                        'label' => 'مرفوض',
                                        'title' => 'تغيير إلى: مرفوض',
                                        'color' => 'danger',
                                    ],
                                    'cancelled' => [
                                        'class' => 'btn-secondary',
                                        'icon' => 'fa-ban',
                                        'label' => 'ملغي',
                                        'title' => 'تغيير إلى: ملغي',
                                        'color' => 'secondary',
                                    ],
                                ];
                            @endphp

                            @foreach($statusButtons as $status => $button)
                                @if($enrollment->status !== $status)
                                    <button type="button" class="btn btn-xs {{ $button['class'] }}"
                                        title="{{ $button['title'] }}" data-bs-toggle="modal"
                                        data-bs-target="#changeStatusModal" data-enrollment-id="{{ $enrollment->id }}"
                                        data-new-status="{{ $status }}" data-status-label="{{ $button['label'] }}"
                                        data-status-icon="{{ $button['icon'] }}"
                                        data-status-color="{{ $button['color'] }}">
                                        <i class="fas {{ $button['icon'] }}"></i>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <h5>لا توجد طلبات تسجيل</h5>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($enrollments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
