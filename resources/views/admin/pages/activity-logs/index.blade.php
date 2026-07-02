@extends('admin.layouts.master')

@section('page-title')
    سجل النشاط
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">سجل النشاط</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="col-lg-8">
                <span class="group-show-hero__eyebrow"><i class="fe fe-shield me-1"></i>Audit Trail</span>
                <h2 class="group-show-hero__title mb-2">سجل النشاط</h2>
                <p class="group-show-hero__desc mb-0">تتبع من قام بماذا ومتى — تغييرات البيانات والأحداث الأمنية.</p>
            </div>
        </div>

        <div class="card custom-card group-show-members-card mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية السجل</h4>
            </div>
            <div class="card-body pt-3">
                <form id="activityLogsFilterForm" action="{{ route('admin.activity-logs.index') }}" method="GET" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label" for="activityQuery">بحث</label>
                            <input type="text" name="query" id="activityQuery" class="form-control" value="{{ request('query') }}" placeholder="الوصف أو الكيان...">
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label" for="activityLogName">النوع</label>
                            <select name="log_name" id="activityLogName" class="form-select">
                                <option value="">الكل</option>
                                @foreach($logNameLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(request('log_name') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label" for="activityEvent">الحدث</label>
                            <select name="event" id="activityEvent" class="form-select">
                                <option value="">الكل</option>
                                @foreach($eventLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label" for="activityCauser">المستخدم</label>
                            <select name="causer_id" id="activityCauser" class="form-select">
                                <option value="">الكل</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" @selected((string) request('causer_id') === (string) $admin->id)>{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label" for="activityDateFrom">من تاريخ</label>
                            <input type="date" name="date_from" id="activityDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label" for="activityDateTo">إلى تاريخ</label>
                            <input type="date" name="date_to" id="activityDateTo" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-xl-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fe fe-search me-1"></i>بحث</button>
                                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm" id="activityLogsResetBtn">مسح</a>
                                <small id="activityLogsFeedback" class="text-muted ms-1"></small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card">
            <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    السجلات
                    <span class="group-show-members-card__count" id="activityLogsCount">{{ $activities->total() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3" id="activityLogsTableContainer">
                @include('admin.pages.activity-logs._table', compact('activities', 'logNameLabels', 'eventLabels'))
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('activityLogsFilterForm');
    const container = document.getElementById('activityLogsTableContainer');
    const countEl = document.getElementById('activityLogsCount');
    const feedback = document.getElementById('activityLogsFeedback');
    const resetBtn = document.getElementById('activityLogsResetBtn');

    const getQueryString = function () {
        return new URLSearchParams(new FormData(form)).toString();
    };

    const fetchAndRender = function (url) {
        if (feedback) feedback.textContent = 'جاري التحميل...';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (container) container.innerHTML = data.table_html;
                if (countEl) countEl.textContent = data.count;
                if (feedback) feedback.textContent = '';
                bindPagination();
            })
            .catch(function () {
                if (feedback) feedback.textContent = 'تعذر تحميل النتائج.';
            });
    };

    const triggerSearch = function () {
        const qs = getQueryString();
        const base = form.getAttribute('action');
        fetchAndRender(qs ? base + '?' + qs : base);
    };

    form.addEventListener('submit', function (e) { e.preventDefault(); triggerSearch(); });
    form.querySelectorAll('select').forEach(function (el) { el.addEventListener('change', triggerSearch); });

    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            form.reset();
            triggerSearch();
        });
    }

    function bindPagination() {
        container.querySelectorAll('.pagination a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                fetchAndRender(link.href);
            });
        });
    }

    bindPagination();
});
</script>
@stop
