@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجلسة
@stop

@section('styles')
    @include('admin.user-sessions.partials.page-styles')
@stop

@php
    $activityTypeLabels = [
        'session_start' => 'بدء الجلسة',
        'session_end' => 'إنهاء الجلسة',
        'page_view' => 'مشاهدة صفحة',
        'action' => 'إجراء',
        'disconnect' => 'انقطاع',
        'reconnect' => 'إعادة اتصال',
        'idle_start' => 'بدء خمول',
        'idle_end' => 'نهاية خمول',
        'focus_lost' => 'فقدان التركيز',
        'focus_gained' => 'استعادة التركيز',
    ];

    $isActive = $session->status === 'active';
    $startedAtIso = $session->started_at?->toIso8601String();
    $endedAtIso = $session->ended_at?->toIso8601String();

    $showKpiCards = [
        ['variant' => 'blue', 'icon' => 'fe-activity', 'label' => 'إجمالي الأنشطة', 'value' => $activityStats['total']],
        ['variant' => 'cyan', 'icon' => 'fe-eye', 'label' => 'مشاهدات الصفحات', 'value' => $activityStats['page_views']],
        ['variant' => 'green', 'icon' => 'fe-layers', 'label' => 'الصفحات الفريدة', 'value' => $activityStats['unique_pages']],
        ['variant' => 'orange', 'icon' => 'fe-clock', 'label' => 'المدة', 'value' => $session->duration_formatted, 'live' => $isActive],
    ];
@endphp

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb us-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                    <li class="breadcrumb-item active">تفاصيل الجلسة</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in us-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-monitor me-1"></i>
                        تفاصيل الجلسة
                    </span>
                    <h2 class="group-show-hero__title mb-2">
                        {{ $session->session_name ?: ($session->user?->name ?? 'جلسة #' . $session->id) }}
                    </h2>
                    <p class="group-show-hero__desc mb-2">
                        @if($session->user)
                            {{ $session->user->email }}
                        @else
                            جلسة بدون مستخدم مرتبط
                        @endif
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @if($session->status == 'active')
                            <span class="us-status-chip us-status-chip--active"><i class="fe fe-radio me-1"></i>نشطة</span>
                        @elseif($session->status == 'completed')
                            <span class="us-status-chip us-status-chip--completed">مكتملة</span>
                        @elseif($session->status == 'disconnected')
                            <span class="us-status-chip us-status-chip--disconnected">منفصلة</span>
                        @else
                            <span class="us-status-chip us-status-chip--timeout">انتهت</span>
                        @endif
                        @if($isActive)
                            <span class="us-show-live-duration" id="usLiveDuration" data-started-at="{{ $startedAtIso }}">
                                <i class="fe fe-clock"></i>
                                <span>{{ $session->duration_formatted }}</span>
                            </span>
                        @else
                            <span class="us-duration-chip">
                                <i class="fe fe-clock"></i>{{ $session->duration_formatted }}
                            </span>
                        @endif
                        <span class="group-show-chip group-show-chip--sm">
                            <i class="fe fe-calendar me-1"></i>{{ $session->started_at->format('Y-m-d H:i') }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-sessions.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">العودة للقائمة</span>
                        </a>
                        @if($session->user)
                            <a href="{{ route('admin.user-sessions.user', $session->user_id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-user"></i></span>
                                <span class="group-show-action__text">جلسات المستخدم</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 us-page-animate dashboard-fade-in">
            @foreach($showKpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                @if(!empty($card['live']))
                                    <h3 class="admin-stats-card__value mb-1" id="usLiveDurationKpi">{{ $card['value'] }}</h3>
                                @elseif(is_numeric($card['value']))
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                @else
                                    <h3 class="admin-stats-card__value mb-1">{{ $card['value'] }}</h3>
                                @endif
                                <p class="admin-stats-card__sub mb-0">
                                    @if(!empty($card['live']))
                                        تحديث مباشر
                                    @elseif($index === 0)
                                        كل الأنشطة المسجّلة
                                    @elseif($index === 1)
                                        زيارات الصفحات
                                    @elseif($index === 2)
                                        مسارات مختلفة
                                    @else
                                        مدة الجلسة
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                            معلومات الجلسة
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        @if($session->user)
                            <div class="us-show-user-card mb-3">
                                <span class="us-user-avatar">
                                    @if($session->user->avatar)
                                        <img src="{{ asset('storage/' . $session->user->avatar) }}" alt="">
                                    @else
                                        {{ mb_substr($session->user->name, 0, 1) }}
                                    @endif
                                </span>
                                <div class="us-show-user-card__meta">
                                    <div class="fw-bold">{{ $session->user->name }}</div>
                                    <small class="text-muted">{{ $session->user->email }}</small>
                                </div>
                            </div>
                        @endif

                        <div class="assignments-info-grid mb-3">
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">تاريخ البدء</div>
                                <div class="assignments-info-item__value">
                                    <i class="fe fe-calendar me-1 text-muted"></i>{{ $session->started_at->format('Y-m-d H:i:s') }}
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">تاريخ الانتهاء</div>
                                <div class="assignments-info-item__value">
                                    @if($session->ended_at)
                                        <i class="fe fe-calendar me-1 text-muted"></i>{{ $session->ended_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-success fw-semibold"><i class="fe fe-radio me-1"></i>لا يزال نشطاً</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">الحالة</div>
                                <div class="assignments-info-item__value">
                                    @if($session->status == 'active')
                                        <span class="us-status-chip us-status-chip--active">نشطة</span>
                                    @elseif($session->status == 'completed')
                                        <span class="us-status-chip us-status-chip--completed">مكتملة</span>
                                    @elseif($session->status == 'disconnected')
                                        <span class="us-status-chip us-status-chip--disconnected">منفصلة</span>
                                    @else
                                        <span class="us-status-chip us-status-chip--timeout">انتهت</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">المدة</div>
                                <div class="assignments-info-item__value">
                                    @if($isActive)
                                        <span id="usLiveDurationInfo">{{ $session->duration_formatted }}</span>
                                    @else
                                        {{ $session->duration_formatted }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="assignments-info-item__label mb-1">معرف الجلسة</div>
                            <code class="us-show-uuid d-block">{{ $session->session_uuid ?? $session->id }}</code>
                        </div>

                        @if($session->session_name || $session->session_description)
                            <div class="assignments-info-grid">
                                @if($session->session_name)
                                    <div class="assignments-info-item">
                                        <div class="assignments-info-item__label">اسم الجلسة</div>
                                        <div class="assignments-info-item__value">{{ $session->session_name }}</div>
                                    </div>
                                @endif
                                @if($session->session_description)
                                    <div class="assignments-info-item" style="grid-column: 1 / -1;">
                                        <div class="assignments-info-item__label">الوصف</div>
                                        <div class="assignments-info-item__value fw-normal">{{ $session->session_description }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-smartphone"></i></span>
                            معلومات الجهاز والاتصال
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <div class="assignments-info-grid">
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">عنوان IP</div>
                                <div class="assignments-info-item__value">
                                    @if($session->ip_address)
                                        <span class="us-ip-chip"><i class="fe fe-globe"></i>{{ $session->ip_address }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">الموقع الجغرافي</div>
                                <div class="assignments-info-item__value">
                                    @if($session->location)
                                        <i class="fe fe-map-pin me-1 text-primary"></i>{{ $session->location_formatted }}
                                        @if(!empty($session->location['region']))
                                            <small class="d-block text-muted mt-1">{{ $session->location['region'] }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">نوع الجهاز</div>
                                <div class="assignments-info-item__value">
                                    <i class="fe fe-monitor me-1 text-muted"></i>{{ ucfirst($session->device_type ?? '—') }}
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">المتصفح</div>
                                <div class="assignments-info-item__value">
                                    {{ $session->browser ?? '—' }}
                                    @if($session->browser_version)
                                        <small class="text-muted">({{ $session->browser_version }})</small>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">المنصة</div>
                                <div class="assignments-info-item__value">
                                    {{ $session->platform ?? '—' }}
                                    @if($session->platform_version)
                                        <small class="text-muted">({{ $session->platform_version }})</small>
                                    @endif
                                </div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">دقة الشاشة</div>
                                <div class="assignments-info-item__value">{{ $session->screen_resolution ?? '—' }}</div>
                            </div>
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">نوع الاتصال</div>
                                <div class="assignments-info-item__value">
                                    @if($session->connection_type)
                                        {{ ucfirst($session->connection_type) }}
                                        @if($session->bandwidth_mbps)
                                            <small class="text-muted">({{ $session->bandwidth_mbps }} Mbps)</small>
                                        @endif
                                    @else
                                        <span class="text-muted">غير معروف</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($session->user_agent)
                            <div class="mt-3 pt-3 border-top">
                                <div class="assignments-info-item__label mb-1">User Agent</div>
                                <small class="text-muted d-block" style="word-break: break-all;">{{ $session->user_agent }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-pie-chart"></i></span>
                            توزيع الأنشطة
                        </h4>
                        <p class="fs-12 text-muted mb-0 mt-1">حسب نوع النشاط خلال الجلسة</p>
                    </div>
                    <div class="card-body pt-3">
                        @if($activityStats['by_type']->isNotEmpty())
                            @php $distributionTotal = max(1, $activityStats['total']); @endphp
                            @foreach($activityStats['by_type'] as $type => $count)
                                @php
                                    $pct = round(($count / $distributionTotal) * 100, 1);
                                    $label = $activityTypeLabels[$type] ?? $type;
                                @endphp
                                <div class="us-distribution-row">
                                    <div class="us-distribution-row__head">
                                        <span>{{ $label }}</span>
                                        <span class="fw-semibold">{{ number_format($count) }} <small class="text-muted">({{ $pct }}%)</small></span>
                                    </div>
                                    <div class="us-distribution-row__bar">
                                        <div class="us-distribution-row__fill" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="group-show-empty py-4">
                                <i class="fe fe-activity group-show-empty__icon"></i>
                                <p class="group-show-empty__desc mb-0">لا توجد أنشطة بعد</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-zap"></i></span>
                            ملخص سريع
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <ul class="list-unstyled mb-0 fs-13">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">الجهاز</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 60%;" title="{{ $session->device_info }}">{{ $session->device_info ?: '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">IP</span>
                                <span class="fw-semibold">{{ $session->ip_address ?? '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">الموقع</span>
                                <span class="fw-semibold text-truncate ms-2" style="max-width: 60%;">{{ $session->location_formatted ?: '—' }}</span>
                            </li>
                            <li class="d-flex justify-content-between pt-2">
                                <span class="text-muted">معرف داخلي</span>
                                <span class="fw-semibold">#{{ $session->id }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    سجل الأنشطة
                    <span class="group-show-members-card__count">{{ $session->activities->count() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                @if($session->activities->count() > 0)
                    <div class="table-responsive us-activities-scroll">
                        <table class="table table-hover text-nowrap dashboard-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">الوقت</th>
                                    <th style="width: 140px;">نوع النشاط</th>
                                    <th>الصفحة / الرابط</th>
                                    <th style="width: 80px;">التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->activities as $activity)
                                    @php
                                        $typeClass = 'us-activity-chip--' . preg_replace('/[^a-z0-9_]/', '', $activity->activity_type);
                                        if (!in_array($activity->activity_type, array_keys($activityTypeLabels), true)) {
                                            $typeClass = 'us-activity-chip--default';
                                        }
                                        $typeLabel = $activityTypeLabels[$activity->activity_type] ?? $activity->activity_type;
                                    @endphp
                                    <tr class="us-table-row">
                                        <td>
                                            <small class="text-muted d-block">{{ $activity->occurred_at->format('Y-m-d') }}</small>
                                            <small class="fw-semibold">{{ $activity->occurred_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <span class="us-activity-chip {{ $typeClass }}">{{ $typeLabel }}</span>
                                        </td>
                                        <td>
                                            @if($activity->page_url)
                                                <a href="{{ $activity->page_url }}" target="_blank" rel="noopener" class="text-primary text-truncate d-inline-block" style="max-width: 420px;" title="{{ $activity->page_url }}">
                                                    {{ Str::limit($activity->page_url, 60) }}
                                                    <i class="fe fe-external-link ms-1"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->activity_details && is_array($activity->activity_details))
                                                <button type="button"
                                                        class="btn btn-info-light btn-sm assignments-actions__btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#activityDetailsModal{{ $activity->id }}"
                                                        title="عرض التفاصيل">
                                                    <i class="fe fe-info"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="group-show-empty py-5">
                        <i class="fe fe-inbox group-show-empty__icon"></i>
                        <h5 class="group-show-empty__title">لا توجد أنشطة مسجّلة</h5>
                        <p class="group-show-empty__desc mb-0">لم يُسجَّل أي نشاط خلال هذه الجلسة.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@foreach($session->activities as $activity)
    @if($activity->activity_details && is_array($activity->activity_details))
        <div class="modal fade" id="activityDetailsModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fe fe-info me-2"></i>تفاصيل النشاط</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2 fs-12 text-muted">{{ $activity->occurred_at->format('Y-m-d H:i:s') }} · {{ $activityTypeLabels[$activity->activity_type] ?? $activity->activity_type }}</div>
                        <pre class="bg-light p-3 rounded mb-0 fs-12" style="max-height: 400px; overflow: auto;">{{ json_encode($activity->activity_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
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

    @if($isActive && $startedAtIso)
    const startedAt = new Date(@json($startedAtIso));

    function formatDuration(totalSeconds) {
        totalSeconds = Math.max(0, totalSeconds);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        if (hours > 0) {
            return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }
        return minutes + ':' + String(seconds).padStart(2, '0');
    }

    function tickLiveDuration() {
        const elapsed = Math.floor((Date.now() - startedAt.getTime()) / 1000);
        const formatted = formatDuration(elapsed);
        ['usLiveDuration', 'usLiveDurationKpi', 'usLiveDurationInfo'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            const target = el.querySelector('span') || el;
            target.textContent = formatted;
        });
    }

    tickLiveDuration();
    setInterval(tickLiveDuration, 1000);
    @endif
})();
</script>
@endsection
