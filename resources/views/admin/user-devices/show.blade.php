@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجهاز
@stop

@section('styles')
    @include('admin.user-devices.partials.page-styles')
@stop

@php
    $deviceTypeNames = [
        'mobile' => 'جوال',
        'tablet' => 'تابلت',
        'desktop' => 'سطح مكتب',
    ];
    $deviceTypeIcons = [
        'mobile' => 'fe-smartphone',
        'tablet' => 'fe-tablet',
        'desktop' => 'fe-monitor',
    ];
    $typeIcon = $deviceTypeIcons[$device->device_type] ?? 'fe-hard-drive';
    $typeLabel = $deviceTypeNames[$device->device_type] ?? ucfirst($device->device_type ?? '—');

    $showKpiCards = [
        ['variant' => 'blue', 'icon' => 'fe-log-in', 'label' => 'مرات الدخول', 'value' => $device->total_logins, 'countup' => true, 'sub' => 'إجمالي تسجيلات الدخول'],
        ['variant' => 'green', 'icon' => 'fe-calendar', 'label' => 'أول استخدام', 'value' => $device->first_used_at?->format('Y-m-d') ?? '—', 'countup' => false, 'sub' => $device->first_used_at?->diffForHumans() ?? 'غير محدد'],
        ['variant' => 'cyan', 'icon' => 'fe-clock', 'label' => 'آخر استخدام', 'value' => $device->last_used_human, 'countup' => false, 'sub' => $device->last_used_at?->format('Y-m-d H:i') ?? '—'],
        ['variant' => 'orange', 'icon' => 'fe-hash', 'label' => 'معرف الجهاز', 'value' => '#' . $device->id, 'countup' => false, 'sub' => 'معرف داخلي'],
    ];
@endphp

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb ud-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-devices.index') }}">أجهزة المستخدمين</a></li>
                    <li class="breadcrumb-item active">تفاصيل الجهاز</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in ud-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe {{ $typeIcon }} me-1"></i>
                        تفاصيل الجهاز
                    </span>
                    <h2 class="group-show-hero__title mb-2">
                        {{ $device->device_name ?: ($device->user?->name ?? 'جهاز #' . $device->id) }}
                    </h2>
                    <p class="group-show-hero__desc mb-2">
                        {{ $device->device_info }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @if($device->is_blocked)
                            <span class="ud-status-chip ud-status-chip--blocked"><i class="fe fe-slash me-1"></i>محظور</span>
                        @elseif($device->is_trusted)
                            <span class="ud-status-chip ud-status-chip--trusted"><i class="fe fe-shield me-1"></i>موثوق</span>
                        @else
                            <span class="ud-status-chip ud-status-chip--normal">عادي</span>
                        @endif
                        <span class="group-show-chip group-show-chip--sm">{{ $typeLabel }}</span>
                        <span class="ud-logins-chip">
                            <i class="fe fe-log-in"></i>{{ number_format($device->total_logins) }} دخول
                        </span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-devices.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">العودة للقائمة</span>
                        </a>
                        @if($device->user)
                            <a href="{{ route('admin.user-devices.user', $device->user_id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-smartphone"></i></span>
                                <span class="group-show-action__text">أجهزة المستخدم</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 ud-page-animate dashboard-fade-in">
            @foreach($showKpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                @if($card['countup'])
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                @else
                                    <h3 class="admin-stats-card__value mb-1 fs-16">{{ $card['value'] }}</h3>
                                @endif
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                            معلومات الجهاز
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        @if($device->user)
                            <div class="ud-show-user-card mb-3">
                                <span class="ud-user-avatar">
                                    @if($device->user->avatar)
                                        <img src="{{ asset('storage/' . $device->user->avatar) }}" alt="">
                                    @else
                                        {{ mb_substr($device->user->name, 0, 1) }}
                                    @endif
                                </span>
                                <div class="ud-show-user-card__meta">
                                    <div class="fw-bold">{{ $device->user->name }}</div>
                                    <small class="text-muted">{{ $device->user->email }}</small>
                                </div>
                                <a href="{{ route('admin.user-devices.user', $device->user_id) }}" class="btn btn-outline-primary btn-sm flex-shrink-0">
                                    <i class="fe fe-list me-1"></i>كل الأجهزة
                                </a>
                            </div>
                        @endif

                        <div class="assignments-info-grid mb-3">
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">اسم الجهاز</div>
                                <div class="assignments-info-item__value d-flex align-items-center gap-2">
                                    <span>{{ $device->device_name ?: 'غير محدد' }}</span>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2"
                                            data-bs-toggle="modal" data-bs-target="#editDeviceNameModal" title="تعديل">
                                        <i class="fe fe-edit-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">نوع الجهاز</div>
                                <div class="assignments-info-item__value">
                                    <i class="fe {{ $typeIcon }} me-1 text-muted"></i>{{ $typeLabel }}
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">المتصفح</div>
                                <div class="assignments-info-item__value">
                                    {{ $device->browser ?? '—' }}
                                    @if($device->browser_version)
                                        <small class="text-muted">({{ $device->browser_version }})</small>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">المنصة</div>
                                <div class="assignments-info-item__value">
                                    {{ $device->platform ?? '—' }}
                                    @if($device->platform_version)
                                        <small class="text-muted">({{ $device->platform_version }})</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="assignments-info-item__label mb-1">بصمة الجهاز</div>
                            <code class="ud-show-fingerprint">{{ $device->device_fingerprint }}</code>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-globe"></i></span>
                            الشبكة والموقع
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <div class="assignments-info-grid mb-3">
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">عنوان IP الحالي</div>
                                <div class="assignments-info-item__value">
                                    @if($device->ip_address)
                                        <span class="ud-show-ip-chip"><i class="fe fe-globe"></i>{{ $device->ip_address }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">آخر عنوان IP</div>
                                <div class="assignments-info-item__value">
                                    @if($device->last_ip_address)
                                        <span class="ud-show-ip-chip"><i class="fe fe-globe"></i>{{ $device->last_ip_address }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            @if($device->location)
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الموقع الجغرافي</div>
                                    <div class="assignments-info-item__value">
                                        <i class="fe fe-map-pin me-1 text-primary"></i>{{ $device->location_formatted }}
                                        @if(!empty($device->location['region']))
                                            <small class="d-block text-muted mt-1">{{ $device->location['region'] }}</small>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($device->location['timezone']))
                                    <div class="assignments-info-item">
                                        <div class="assignments-info-item__label">المنطقة الزمنية</div>
                                        <div class="assignments-info-item__value">
                                            <i class="fe fe-clock me-1 text-muted"></i>{{ $device->location['timezone'] }}
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($device->location['isp']))
                                    <div class="assignments-info-item">
                                        <div class="assignments-info-item__label">مزود الخدمة</div>
                                        <div class="assignments-info-item__value">{{ $device->location['isp'] }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        @if($device->user_agent)
                            <div class="pt-3 border-top">
                                <div class="assignments-info-item__label mb-1">User Agent</div>
                                <small class="text-muted d-block" style="word-break: break-all;">{{ $device->user_agent }}</small>
                            </div>
                        @endif

                        @if($device->meta)
                            <div class="pt-3 mt-3 border-top">
                                <button class="btn btn-sm btn-outline-secondary mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#deviceMetaCollapse">
                                    <i class="fe fe-code me-1"></i>عرض البيانات التقنية
                                </button>
                                <div class="collapse" id="deviceMetaCollapse">
                                    <pre class="bg-light p-3 ud-meta-pre">{{ json_encode($device->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-bar-chart-2"></i></span>
                            إحصائيات الاستخدام
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <div class="assignments-info-grid">
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">عدد مرات الدخول</div>
                                <div class="assignments-info-item__value">
                                    <span class="ud-logins-chip"><i class="fe fe-log-in"></i>{{ number_format($device->total_logins) }}</span>
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">أول استخدام</div>
                                <div class="assignments-info-item__value">
                                    @if($device->first_used_at)
                                        {{ $device->first_used_at->format('Y-m-d H:i:s') }}
                                        <small class="d-block text-muted">{{ $device->first_used_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">آخر استخدام</div>
                                <div class="assignments-info-item__value">
                                    @if($device->last_used_at)
                                        {{ $device->last_used_at->format('Y-m-d H:i:s') }}
                                        <small class="d-block text-muted">{{ $device->last_used_human }}</small>
                                    @else
                                        <span class="text-muted">لم يُستخدم</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">تاريخ التسجيل</div>
                                <div class="assignments-info-item__value">{{ $device->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card group-show-members-card ud-action-card dashboard-fade-in ud-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-settings"></i></span>
                            الحالة والإجراءات
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <div class="text-center mb-4 py-3 rounded-3" style="background: rgba(var(--primary-rgb), 0.04);">
                            <p class="text-muted mb-2 fs-12">الحالة الحالية</p>
                            @if($device->is_blocked)
                                <span class="ud-status-chip ud-status-chip--blocked fs-14 px-3 py-2"><i class="fe fe-slash me-1"></i>محظور</span>
                            @elseif($device->is_trusted)
                                <span class="ud-status-chip ud-status-chip--trusted fs-14 px-3 py-2"><i class="fe fe-shield me-1"></i>موثوق</span>
                            @else
                                <span class="ud-status-chip ud-status-chip--pending fs-14 px-3 py-2"><i class="fe fe-clock me-1"></i>بانتظار الموافقة</span>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            @if($device->is_blocked)
                                <form action="{{ route('admin.user-devices.unblock', $device->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من إلغاء حظر هذا الجهاز؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fe fe-unlock me-1"></i>إلغاء الحظر
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.user-devices.block', $device->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حظر هذا الجهاز؟ سيتم منع المستخدم من الوصول من هذا الجهاز.');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fe fe-slash me-1"></i>حظر الجهاز
                                    </button>
                                </form>
                            @endif

                            @if($device->is_trusted)
                                <form action="{{ route('admin.user-devices.untrust', $device->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من إلغاء الثقة من هذا الجهاز؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100">
                                        <i class="fe fe-shield-off me-1"></i>إلغاء الثقة
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.user-devices.trust', $device->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من تعيين هذا الجهاز كموثوق؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fe fe-shield me-1"></i>تعيين كموثوق
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-zap"></i></span>
                            ملخص سريع
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <ul class="list-unstyled mb-0 fs-13">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">المستخدم</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 55%;">{{ $device->user?->name ?? '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">النوع</span>
                                <span class="fw-semibold">{{ $typeLabel }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">IP</span>
                                <span class="fw-semibold ud-ip-text">{{ $device->last_ip_address ?? $device->ip_address ?? '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">الموقع</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 55%;">{{ $device->location_formatted ?: '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between pt-2">
                                <span class="text-muted">آخر نشاط</span>
                                <span class="fw-semibold">{{ $device->last_used_human }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Edit Device Name Modal -->
<div class="modal fade" id="editDeviceNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fe fe-edit-2 me-2"></i>تعديل اسم الجهاز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.update-name', $device->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label">اسم الجهاز</label>
                        <input type="text" name="device_name" class="form-control"
                               value="{{ $device->device_name }}"
                               placeholder="مثال: جهاز العمل، جهاز المنزل...">
                        <small class="text-muted">يمكنك ترك هذا الحقل فارغاً</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        const target = parseFloat(el.dataset.countup || '0');
        const duration = 800;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });
})();
</script>
@endsection
