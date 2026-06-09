@extends('admin.layouts.master')

@section('page-title', 'التقارير الأسبوعية للطلاب')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'التقارير المسلّمة'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-check-square me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">التقارير المسلّمة</h2>
                    <p class="group-show-hero__desc mb-0">
                        عرض التقارير التي أرسلها الطلاب أو رُاجعت من قبل الإدارة، مجمّعة حسب المجموعة.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.groups-overview') }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                            <span class="group-show-action__text">تقارير المجموعات</span>
                        </a>
                        <a href="{{ route('admin.weekly-reports.create') }}"
                           class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">إنشاء تقرير يدوي</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiCards = [
                [
                    'variant' => 'green',
                    'icon' => 'fe-check-circle',
                    'label' => 'تقارير مسلّمة',
                    'value' => $totalSubmittedReports,
                    'sub' => 'إجمالي التسليمات (مرسل/مراجع)',
                ],
                [
                    'variant' => 'cyan',
                    'icon' => 'fe-users',
                    'label' => 'مجموعات بها تسليم',
                    'value' => $groupsWithSubmissionsCount,
                    'sub' => 'مجموعات لديها طلاب مسلّمون',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'submitted'])

        @include('admin.weekly-reports.partials.filters', [
            'filterAction' => route('admin.weekly-reports.index'),
            'resetRoute' => 'admin.weekly-reports.index',
        ])

        @forelse($groupsWithSubmittedReports as $index => $group)
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 dashboard-stagger-item" style="--stagger-delay: {{ $index * 50 }}ms">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h4 class="card-title mb-1">{{ $group['group_name'] }}</h4>
                            <p class="fs-12 text-muted mb-0">التقارير المسلّمة من طلاب هذه المجموعة</p>
                        </div>
                        <span class="badge bg-success-transparent text-success">
                            {{ $group['submissions_count'] }} تسليم
                        </span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('admin.weekly-reports.partials.reports-table', ['reports' => $group['reports']])
                </div>
            </div>
        @empty
            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-body">
                    @include('admin.weekly-reports.partials.empty-state', [
                        'icon' => 'fe-inbox',
                        'title' => 'لا توجد تقارير مسلّمة حالياً',
                        'description' => 'ستظهر هنا المجموعات التي سلّم طلابها التقارير (مرسل/مراجع) فقط.',
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
