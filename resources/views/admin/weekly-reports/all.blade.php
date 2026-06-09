@extends('admin.layouts.master')

@section('page-title', 'كافة التقارير')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'كافة التقارير'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-list me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">كافة التقارير</h2>
                    <p class="group-show-hero__desc mb-0">
                        قائمة شاملة بكل التقارير المنشأة بجميع الحالات — مع إمكانية التصفية والانتقال لتفاصيل كل تقرير وطالب.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.groups-overview', array_filter($filters)) }}"
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
                    'variant' => 'blue',
                    'icon' => 'fe-file-text',
                    'label' => 'إجمالي التقارير',
                    'value' => $statusCounts['total'],
                    'sub' => 'كل الحالات ضمن الفلتر',
                ],
                [
                    'variant' => 'yellow',
                    'icon' => 'fe-edit',
                    'label' => 'مسودة',
                    'value' => $statusCounts['draft'],
                    'sub' => 'لم يُرسل بعد',
                ],
                [
                    'variant' => 'green',
                    'icon' => 'fe-check-circle',
                    'label' => 'مسلّمة',
                    'value' => $statusCounts['submitted'],
                    'sub' => 'أرسلها الطلاب',
                ],
                [
                    'variant' => 'cyan',
                    'icon' => 'fe-award',
                    'label' => 'مراجع',
                    'value' => $statusCounts['reviewed'],
                    'sub' => 'بها تعليق إدارة',
                ],
                [
                    'variant' => 'orange',
                    'icon' => 'fe-alert-circle',
                    'label' => 'مغلقة',
                    'value' => $statusCounts['closed'],
                    'sub' => 'انتهى الموعد',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'all'])

        @include('admin.weekly-reports.partials.filters', [
            'filterAction' => route('admin.weekly-reports.all'),
            'resetRoute' => 'admin.weekly-reports.all',
            'showStatusFilter' => true,
        ])

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="card-title mb-1">قائمة التقارير</h4>
                        <p class="fs-12 text-muted mb-0">
                            عرض {{ $reports->total() }} تقريراً@if($reports->hasPages()) — الصفحة {{ $reports->currentPage() }} من {{ max(1, $reports->lastPage()) }}@endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                @include('admin.weekly-reports.partials.all-reports-table', ['reports' => $reports])
            </div>
        </div>
    </div>
</div>
@endsection

@include('admin.weekly-reports.partials.countup-script')
