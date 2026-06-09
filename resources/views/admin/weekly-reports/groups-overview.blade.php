@extends('admin.layouts.master')

@section('page-title', 'تقارير المجموعات')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'تقارير المجموعات'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-file-text me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">تقارير المجموعات</h2>
                    <p class="group-show-hero__desc mb-0">
                        متابعة التقارير المنشأة لكل مجموعة والتسليمات الفعلية من الطلاب في مكان واحد.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.index') }}"
                           class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-check-square"></i></span>
                            <span class="group-show-action__text">التقارير المسلّمة</span>
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
            $pendingReports = $totalPendingReports ?? max(0, $totalCreatedReports - $totalSubmittedReports);
            $groupsCount = count($groupsData);
            $pendingUrl = route('admin.weekly-reports.pending', array_filter($filters ?? []));
            $kpiCards = [
                [
                    'variant' => 'blue',
                    'icon' => 'fe-file-plus',
                    'label' => 'تقارير منشأة',
                    'value' => $totalCreatedReports,
                    'sub' => 'إجمالي التقارير المُنشأة للمجموعات',
                ],
                [
                    'variant' => 'green',
                    'icon' => 'fe-check-circle',
                    'label' => 'تقارير مسلّمة',
                    'value' => $totalSubmittedReports,
                    'sub' => 'تم إرسالها من الطلاب',
                ],
                [
                    'variant' => 'orange',
                    'icon' => 'fe-clock',
                    'label' => 'بانتظار التسليم',
                    'value' => $pendingReports,
                    'sub' => 'منشأة ولم تُسلّم بعد',
                    'url' => $pendingUrl,
                ],
                [
                    'variant' => 'cyan',
                    'icon' => 'fe-users',
                    'label' => 'مجموعات نشطة',
                    'value' => $groupsCount,
                    'sub' => 'لديها تقارير منشأة',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'overview'])

        @include('admin.weekly-reports.partials.filters', [
            'filterAction' => route('admin.weekly-reports.groups-overview'),
            'resetRoute' => 'admin.weekly-reports.groups-overview',
        ])

        @forelse($groupsData as $index => $item)
            @php
                /** @var \App\Models\CourseGroup $group */
                $group = $item['group'];
                $submittedReports = $item['submitted_reports'];
            @endphp
            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 dashboard-stagger-item" style="--stagger-delay: {{ $index * 50 }}ms">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h4 class="card-title mb-1">{{ $group->name }}</h4>
                            <p class="fs-12 text-muted mb-0">تقارير المجموعة المنشأة والمسلّمة</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-secondary-transparent text-secondary">
                                {{ $item['total_reports_count'] }} منشأة
                            </span>
                            <span class="badge bg-success-transparent text-success">
                                {{ $item['submitted_reports_count'] }} مسلّمة
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @if($submittedReports->isNotEmpty())
                        @include('admin.weekly-reports.partials.reports-table', ['reports' => $submittedReports])
                    @else
                        <div class="alert alert-light border mb-0 d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-warning-transparent flex-shrink-0">
                                <i class="fe fe-alert-circle text-warning"></i>
                            </span>
                            <span class="fs-13 mb-0">
                                لا يوجد طلاب مسلّمين لهذه المجموعة حتى الآن، رغم وجود
                                <strong>{{ $item['total_reports_count'] }}</strong> تقريراً منشأاً لها.
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-body">
                    @include('admin.weekly-reports.partials.empty-state', [
                        'icon' => 'fe-layers',
                        'title' => 'لا توجد مجموعات لديها تقارير منشأة',
                        'description' => 'أنشئ تقارير للمجموعات أولاً وستظهر هنا تلقائياً.',
                        'actionRoute' => 'admin.weekly-reports.create',
                        'actionLabel' => 'إنشاء تقرير يدوي',
                    ])
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@include('admin.weekly-reports.partials.countup-script')
