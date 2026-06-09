@extends('admin.layouts.master')

@section('page-title', 'بانتظار التسليم')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'بانتظار التسليم'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-clock me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">بانتظار التسليم</h2>
                    <p class="group-show-hero__desc mb-0">
                        الطلاب الذين لم يُسلّموا تقاريرهم بعد — مسودة أو انتهى موعدها بدون تسليم.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.index', array_filter($filters)) }}"
                           class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-check-square"></i></span>
                            <span class="group-show-action__text">التقارير المسلّمة</span>
                        </a>
                        <a href="{{ route('admin.weekly-reports.groups-overview', array_filter($filters)) }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                            <span class="group-show-action__text">تقارير المجموعات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiCards = [
                [
                    'variant' => 'orange',
                    'icon' => 'fe-clock',
                    'label' => 'بانتظار التسليم',
                    'value' => $totalPendingReports,
                    'sub' => 'إجمالي التقارير غير المسلّمة',
                ],
                [
                    'variant' => 'yellow',
                    'icon' => 'fe-edit',
                    'label' => 'مسودة',
                    'value' => $draftCount,
                    'sub' => 'يمكن للطالب الإرسال',
                ],
                [
                    'variant' => 'red',
                    'icon' => 'fe-alert-circle',
                    'label' => 'مغلقة بدون تسليم',
                    'value' => $closedCount,
                    'sub' => 'انتهى الموعد ولم يُرسل',
                ],
                [
                    'variant' => 'cyan',
                    'icon' => 'fe-users',
                    'label' => 'مجموعات متأثرة',
                    'value' => $groupsWithPendingCount,
                    'sub' => 'مجموعات لديها تقارير معلّقة',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'pending'])

        @include('admin.weekly-reports.partials.filters', [
            'filterAction' => route('admin.weekly-reports.pending'),
            'resetRoute' => 'admin.weekly-reports.pending',
        ])

        @forelse($groupsWithPendingReports as $index => $group)
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 dashboard-stagger-item" style="--stagger-delay: {{ $index * 50 }}ms">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h4 class="card-title mb-1">{{ $group['group_name'] }}</h4>
                            <p class="fs-12 text-muted mb-0">طلاب لم يُسلّموا التقرير بعد</p>
                        </div>
                        <span class="badge bg-warning-transparent text-warning">
                            {{ $group['pending_count'] }} بانتظار
                        </span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('admin.weekly-reports.partials.pending-table', ['reports' => $group['reports']])
                </div>
            </div>
        @empty
            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-body">
                    @include('admin.weekly-reports.partials.empty-state', [
                        'icon' => 'fe-check-circle',
                        'title' => 'لا توجد تقارير بانتظار التسليم',
                        'description' => 'جميع الطلاب في النطاق المحدد قد سلّموا تقاريرهم، أو لا توجد تقارير منشأة بعد.',
                        'actionRoute' => 'admin.weekly-reports.create',
                        'actionLabel' => 'إنشاء تقارير للمجموعات',
                    ])
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@include('admin.weekly-reports.partials.countup-script')
