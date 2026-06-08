<div class="card custom-card group-show-members-card dashboard-fade-in mt-3">
    <div class="card-header border-0 pb-0">
        <h4 class="card-title mb-1">آخر الأنشطة</h4>
        <p class="fs-12 text-muted mb-0">آخر محاولات الاختبارات والتفاعلات التعليمية.</p>
    </div>
    <div class="card-body pt-3">
        @if(!empty($questionModuleStats['last_attempt']))
            @php $lastAttempt = $questionModuleStats['last_attempt']; @endphp
            <div class="dashboard-stat-row p-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="avatar avatar-sm bg-success-transparent flex-shrink-0">
                        <i class="fe fe-check-circle text-success"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="fw-semibold mb-1">آخر محاولة اختبار</p>
                        <p class="text-muted fs-13 mb-1">
                            النتيجة: <strong>{{ number_format($lastAttempt->percentage ?? 0, 1) }}%</strong>
                            · {{ $lastAttempt->is_passed ? 'ناجحة' : 'غير ناجحة' }}
                        </p>
                        <small class="text-muted">
                            <i class="fe fe-clock me-1"></i>
                            {{ optional($lastAttempt->completed_at)->diffForHumans() ?? '—' }}
                        </small>
                    </div>
                </div>
            </div>
        @else
            <div class="group-show-empty py-4">
                <i class="fe fe-activity group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد أنشطة حديثة</h5>
                <p class="group-show-empty__desc mb-0">ستظهر آخر أنشطتك هنا عند بدء التعلم.</p>
            </div>
        @endif
    </div>
</div>
