<div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card custom-card mb-0">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-success-transparent">
                        <i class="fas fa-check-circle fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">المبالغ المسددة</p>
                    <h4 class="fw-bold mb-0 text-success">${{ number_format($stats['paid_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card custom-card mb-0">
        <div class="card-body">
            <div class="d-flex align-items-top">
                <div class="me-3">
                    <span class="avatar avatar-md bg-danger-transparent">
                        <i class="fas fa-hourglass-half fs-18"></i>
                    </span>
                </div>
                <div class="flex-fill">
                    <p class="fw-semibold mb-1">المبالغ غير المسددة</p>
                    <h4 class="fw-bold mb-0 text-danger">${{ number_format($stats['unpaid_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
