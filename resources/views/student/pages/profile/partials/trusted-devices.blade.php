<div class="card custom-card student-quizzes-panel mt-4 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-success-transparent">
                    <i class="fe fe-shield text-success"></i>
                </span>
                <div>
                    <h6 class="card-title mb-1">الأجهزة الموثوقة</h6>
                    <p class="text-muted fs-12 mb-0">الأجهزة المعتمدة والمسموح لك بتسجيل الدخول منها.</p>
                </div>
            </div>
            <span class="badge bg-success-transparent rounded-pill">
                {{ number_format($trustedDevices->count()) }} جهاز موثوق
            </span>
        </div>

        @if($trustedDevices->isEmpty())
            <div class="text-center py-4">
                <span class="avatar avatar-lg bg-light mb-3">
                    <i class="fe fe-monitor text-muted fs-4"></i>
                </span>
                <h6 class="mb-1">لا توجد أجهزة موثوقة</h6>
                <p class="text-muted fs-12 mb-0">ستظهر هنا الأجهزة بعد اعتمادها من الإدارة.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach($trustedDevices as $device)
                    @php
                        $deviceTypeLabel = match($device->device_type) {
                            'mobile' => 'جهاز جوال',
                            'tablet' => 'جهاز لوحي',
                            default => 'جهاز كمبيوتر',
                        };
                        $deviceIcon = match($device->device_type) {
                            'mobile' => 'fe-smartphone',
                            'tablet' => 'fe-tablet',
                            default => 'fe-monitor',
                        };
                    @endphp
                    <div class="col-xl-6">
                        <div class="student-profile-field h-100 align-items-start">
                            <span class="student-profile-field__icon flex-shrink-0">
                                <i class="fe {{ $deviceIcon }}"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <span class="student-profile-field__value fw-semibold">
                                        {{ $device->device_name ?: $deviceTypeLabel }}
                                    </span>
                                    <span class="badge bg-success-transparent">
                                        <i class="fe fe-check-circle me-1"></i>موثوق
                                    </span>
                                </div>
                                <div class="row g-2 fs-12">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block">المتصفح</span>
                                        <span class="fw-semibold">
                                            {{ $device->browser ?: 'غير محدد' }}
                                            {{ $device->browser_version ?: '' }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block">نظام التشغيل</span>
                                        <span class="fw-semibold">
                                            {{ $device->platform ?: 'غير محدد' }}
                                            {{ $device->platform_version ?: '' }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block">آخر استخدام</span>
                                        <span class="fw-semibold" dir="ltr">
                                            {{ $device->last_used_at?->format('Y-m-d h:i A') ?? 'غير محدد' }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block">تاريخ التوثيق</span>
                                        <span class="fw-semibold text-success" dir="ltr">
                                            {{ $device->trusted_at?->format('Y-m-d h:i A') ?? 'غير متوفر' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
