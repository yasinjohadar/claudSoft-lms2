<div class="col-xl-2 col-lg-4 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-primary-transparent">
                        <i class="fas fa-money-bill-wave fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">إجمالي المدفوعات</p>
                    <h4 class="fw-bold mb-2">${{ number_format($stats['completed_amount'], 2) }}</h4>
                    <span class="badge bg-primary-transparent">{{ $stats['completed_count'] }} دفعة</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-warning-transparent">
                        <i class="fas fa-clock fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">دفعات معلقة</p>
                    <h4 class="fw-bold mb-2">${{ number_format($stats['pending_amount'], 2) }}</h4>
                    <span class="badge bg-warning-transparent">{{ $stats['pending_count'] }} دفعة</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-danger-transparent">
                        <i class="fas fa-times-circle fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">دفعات ملغاة</p>
                    <h4 class="fw-bold mb-2">${{ number_format($stats['cancelled_amount'], 2) }}</h4>
                    <span class="badge bg-danger-transparent">{{ $stats['cancelled_count'] }} دفعة</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-2 col-lg-4 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-info-transparent">
                        <i class="fas fa-undo fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">مبالغ مستردة</p>
                    <h4 class="fw-bold mb-2">${{ number_format($stats['refunded_amount'], 2) }}</h4>
                    <span class="badge bg-info-transparent">{{ $stats['refunded_count'] }} دفعة</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-2 col-lg-6 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-success-transparent">
                        <i class="fas fa-check-circle fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">القيمة المسددة</p>
                    <h4 class="fw-bold mb-0 text-success">${{ number_format($stats['paid_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-2 col-lg-6 col-md-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-secondary-transparent">
                        <i class="fas fa-hourglass-half fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">القيمة المتبقية</p>
                    <h4 class="fw-bold mb-0 text-secondary">${{ number_format($stats['remaining_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
