@extends('admin.layouts.master')

@section('page-title')
    شارات الطلاب
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
                    <li class="breadcrumb-item active">شارات الطلاب</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-users me-1"></i>تقارير الشارات</span>
                    <h2 class="group-show-hero__title mb-2">شارات الطلاب بالتفصيل</h2>
                    <p class="group-show-hero__desc mb-0">استعرض شارات كل طالب، المكتسبة والتقدم نحو الشارات غير المكتسبة.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.gamification.badges.reports.distribution') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-bar-chart-2"></i></span>
                            <span class="group-show-action__text">تقرير التوزيع</span>
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
                'context' => 'students',
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
                    'showRarity' => false,
                    'searchLabel' => 'بحث بالطالب',
                    'searchPlaceholder' => 'الاسم أو البريد...',
                ])
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    الطلاب
                    <span class="group-show-members-card__count" id="badge-report-total">{{ $students->total() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 group-show-table text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>الشارات المكتسبة</th>
                                <th>نسبة الإكمال</th>
                                <th>التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody id="badge-report-table-body">
                            @include('admin.pages.gamification.badges.reports.partials.students-table', compact('students'))
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-center" id="badge-report-pagination">
                    @if ($students->hasPages())
                        {{ $students->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="badgeStudentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="badgeStudentDetailModalTitle">تفاصيل شارات الطالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body" id="badgeStudentDetailModalBody">
                <div class="text-center py-4 text-muted">اختر طالباً لعرض التفاصيل.</div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
    @include('admin.pages.gamification.badges.reports.partials.filter-scripts', [
        'reportMode' => 'students',
        'indexUrl' => route('admin.gamification.badges.reports.students'),
        'studentDetailUrlTemplate' => route('admin.gamification.badges.reports.students.detail', ['user' => '__ID__']),
        'allGroups' => $allGroups,
    ])
@stop
