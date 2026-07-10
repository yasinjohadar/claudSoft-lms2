@extends('admin.layouts.master')

@section('page-title')
    تقرير توزيع الشارات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.gamification.badges.index') }}">الشارات</a></li>
                    <li class="breadcrumb-item active">تقرير التوزيع</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-bar-chart-2 me-1"></i>تقارير الشارات</span>
                    <h2 class="group-show-hero__title mb-2">توزيع الشارات حسب الكورس والمجموعة</h2>
                    <p class="group-show-hero__desc mb-0">اعرف نسبة منح كل شارة ضمن نطاق الطلاب المحدد.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.gamification.badges.reports.students') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-users"></i></span>
                            <span class="group-show-action__text">شارات الطلاب</span>
                        </a>
                        <a href="{{ route('admin.gamification.badges.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">إدارة الشارات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4" id="badge-report-stats-container">
            @include('admin.pages.gamification.badges.reports.partials.scope-stats', [
                'stats' => $stats,
                'context' => 'distribution',
            ])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية التقرير</h4>
            </div>
            <div class="card-body pt-3">
                @include('admin.pages.gamification.badges.reports.partials.filters', [
                    'courses' => $courses,
                    'allGroups' => $allGroups,
                    'showRarity' => true,
                    'searchLabel' => 'بحث بالشارة',
                    'searchPlaceholder' => 'اسم الشارة...',
                ])
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    الشارات
                    <span class="group-show-members-card__count" id="badge-report-total">{{ $badges->total() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 group-show-table text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الأيقونة</th>
                                <th>الاسم</th>
                                <th>الندرة</th>
                                <th>عدد الحاصلين</th>
                                <th>نسبة المنح</th>
                                <th>النقاط</th>
                                <th>عمليات</th>
                            </tr>
                        </thead>
                        <tbody id="badge-report-table-body">
                            @include('admin.pages.gamification.badges.reports.partials.distribution-table', compact('badges'))
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-center" id="badge-report-pagination">
                    @if ($badges->hasPages())
                        {{ $badges->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
    @include('admin.pages.gamification.badges.reports.partials.filter-scripts', [
        'reportMode' => 'distribution',
        'indexUrl' => route('admin.gamification.badges.reports.distribution'),
        'allGroups' => $allGroups,
    ])
@stop
