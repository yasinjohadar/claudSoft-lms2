@extends('admin.layouts.master')

@section('page-title', 'التقارير المنشأة من الأدمن')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        @include('admin.weekly-reports.partials.breadcrumb', ['current' => 'التقارير المنشأة'])

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-file-plus me-1"></i>
                        التقارير الأسبوعية
                    </span>
                    <h2 class="group-show-hero__title mb-2">التقارير المنشأة من الأدمن</h2>
                    <p class="group-show-hero__desc mb-0">
                        كل عملية إنشاء تقرير (يدوي أو جدولة). انقر على التقرير أو «عرض الطلاب» لفتح صفحة تفاصيل الطلاب.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.create') }}"
                           class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">إنشاء تقرير يدوي</span>
                        </a>
                        <a href="{{ route('admin.weekly-reports.all', array_filter($filters)) }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-list"></i></span>
                            <span class="group-show-action__text">كافة التقارير</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiCards = [
                [
                    'variant' => 'blue',
                    'icon' => 'fe-layers',
                    'label' => 'دفعات منشأة',
                    'value' => $batchStats['batches_count'],
                    'sub' => 'عمليات إنشاء منفصلة',
                ],
                [
                    'variant' => 'cyan',
                    'icon' => 'fe-users',
                    'label' => 'تقارير طلاب',
                    'value' => $batchStats['students_count'],
                    'sub' => 'إجمالي التقارير الفردية',
                ],
                [
                    'variant' => 'green',
                    'icon' => 'fe-check-circle',
                    'label' => 'مسلّمة',
                    'value' => $batchStats['submitted_count'],
                    'sub' => 'أرسلها الطلاب',
                ],
                [
                    'variant' => 'orange',
                    'icon' => 'fe-clock',
                    'label' => 'بانتظار التسليم',
                    'value' => $batchStats['pending_count'],
                    'sub' => 'لم يُسلّموا بعد',
                ],
            ];
        @endphp

        <div id="weeklyReportsStats" class="mb-4">
            @include('admin.weekly-reports.partials.stats', ['kpiCards' => $kpiCards])
        </div>

        @include('admin.weekly-reports.partials.quick-nav', ['navActive' => 'created'])

        @include('admin.weekly-reports.partials.filters', [
            'filterAction' => route('admin.weekly-reports.created'),
            'resetRoute' => 'admin.weekly-reports.created',
        ])

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="card-title mb-1">دفعات التقارير المنشأة</h4>
                        <p class="fs-12 text-muted mb-0">
                            {{ $batches->total() }} دفعة@if($batches->hasPages()) — الصفحة {{ $batches->currentPage() }} من {{ max(1, $batches->lastPage()) }}@endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                @include('admin.weekly-reports.partials.created-batches-table', ['batches' => $batches])
            </div>
        </div>
    </div>
</div>
@endsection

@include('admin.weekly-reports.partials.countup-script')
