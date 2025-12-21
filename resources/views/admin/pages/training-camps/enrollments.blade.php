@extends('admin.layouts.master')

@section('page-title')
    طلبات التسجيل في المعسكرات
@stop

@section('css')
<style>
    .enrollment-card {
        transition: all 0.3s ease;
    }
    .enrollment-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">طلبات التسجيل في المعسكرات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">طلبات التسجيل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form action="{{ route('training-camps.enrollments') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                   placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">حالة الدفع</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="camp_id" class="form-select">
                                <option value="">جميع المعسكرات</option>
                                @foreach($camps as $camp)
                                    <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                        {{ $camp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الطلبات</div>
                </div>
                <div class="card-body">
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
                                                <br><small class="text-muted">{{ $enrollment->student->email }}</small>
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
                                                    'cancelled' => 'bg-secondary'
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
                                                    'refunded' => 'bg-secondary'
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
                                                            'color' => 'warning'
                                                        ],
                                                        'approved' => [
                                                            'class' => 'btn-success',
                                                            'icon' => 'fa-check-circle',
                                                            'label' => 'مقبول',
                                                            'title' => 'تغيير إلى: مقبول',
                                                            'color' => 'success'
                                                        ],
                                                        'rejected' => [
                                                            'class' => 'btn-danger',
                                                            'icon' => 'fa-times-circle',
                                                            'label' => 'مرفوض',
                                                            'title' => 'تغيير إلى: مرفوض',
                                                            'color' => 'danger'
                                                        ],
                                                        'cancelled' => [
                                                            'class' => 'btn-secondary',
                                                            'icon' => 'fa-ban',
                                                            'label' => 'ملغي',
                                                            'title' => 'تغيير إلى: ملغي',
                                                            'color' => 'secondary'
                                                        ]
                                                    ];
                                                @endphp
                                                
                                                @foreach($statusButtons as $status => $button)
                                                    @if($enrollment->status !== $status)
                                                        <button type="button"
                                                                class="btn btn-xs {{ $button['class'] }}"
                                                                title="{{ $button['title'] }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#changeStatusModal"
                                                                data-enrollment-id="{{ $enrollment->id }}"
                                                                data-new-status="{{ $status }}"
                                                                data-status-label="{{ $button['label'] }}"
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
                            {{ $enrollments->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4 px-5">
                    <div class="mb-4" id="statusIconContainer">
                        <div class="avatar avatar-xl bg-warning-transparent mx-auto mb-3" id="statusIconWrapper">
                            <i class="fas fa-clock fs-24 text-warning" id="statusIcon"></i>
                        </div>
                    </div>
                    <h5 class="mb-3" id="changeStatusModalLabel">تغيير حالة التسجيل</h5>
                    <p class="text-muted mb-4" id="statusMessage">
                        هل أنت متأكد من تغيير الحالة إلى <strong id="statusLabelText">قيد الانتظار</strong>؟
                    </p>
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>يمكنك تغيير الحالة في أي وقت</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn" id="confirmStatusChange" style="min-width: 120px;">
                        <i class="fas fa-check me-2"></i>تأكيد التغيير
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.25rem;
    }
    .btn-xs i {
        font-size: 0.875rem;
    }
    #statusIconWrapper {
        transition: all 0.3s ease;
    }
</style>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Status configuration
    const statusConfig = {
        'pending': {
            icon: 'fa-clock',
            label: 'قيد الانتظار',
            color: 'warning',
            bgClass: 'bg-warning-transparent',
            textClass: 'text-warning',
            btnClass: 'btn-warning'
        },
        'approved': {
            icon: 'fa-check-circle',
            label: 'مقبول',
            color: 'success',
            bgClass: 'bg-success-transparent',
            textClass: 'text-success',
            btnClass: 'btn-success'
        },
        'rejected': {
            icon: 'fa-times-circle',
            label: 'مرفوض',
            color: 'danger',
            bgClass: 'bg-danger-transparent',
            textClass: 'text-danger',
            btnClass: 'btn-danger'
        },
        'cancelled': {
            icon: 'fa-ban',
            label: 'ملغي',
            color: 'secondary',
            bgClass: 'bg-secondary-transparent',
            textClass: 'text-secondary',
            btnClass: 'btn-secondary'
        }
    };

    let currentEnrollmentId = null;
    let currentNewStatus = null;

    // Handle modal show
    const changeStatusModal = document.getElementById('changeStatusModal');
    if (changeStatusModal) {
        changeStatusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            currentEnrollmentId = button.getAttribute('data-enrollment-id');
            currentNewStatus = button.getAttribute('data-new-status');
            const statusLabel = button.getAttribute('data-status-label');
            const statusIcon = button.getAttribute('data-status-icon');
            const statusColor = button.getAttribute('data-status-color');

            // Update modal content
            const config = statusConfig[currentNewStatus];
            if (config) {
                // Update icon
                const iconWrapper = document.getElementById('statusIconWrapper');
                const icon = document.getElementById('statusIcon');
                iconWrapper.className = `avatar avatar-xl ${config.bgClass} mx-auto mb-3`;
                icon.className = `fas ${config.icon} fs-24 ${config.textClass}`;

                // Update label
                document.getElementById('statusLabelText').textContent = config.label;

                // Update confirm button
                const confirmBtn = document.getElementById('confirmStatusChange');
                confirmBtn.className = `btn ${config.btnClass}`;
            }
        });
    }

    // Handle confirm button click
    document.getElementById('confirmStatusChange')?.addEventListener('click', function() {
        if (!currentEnrollmentId || !currentNewStatus) {
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/training-camps-enrollments') }}/${currentEnrollmentId}/update-status`;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Add status
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = currentNewStatus;
        form.appendChild(statusInput);

        document.body.appendChild(form);
        form.submit();
    });
</script>
@stop
