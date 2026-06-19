@extends('admin.layouts.master')

@section('page-title')
    طلبات التسجيل في المعسكرات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                        <li class="breadcrumb-item active">طلبات التسجيل</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-inbox me-1"></i>
                            طلبات المعسكرات
                        </span>
                        <h2 class="group-show-hero__title mb-2">طلبات التسجيل</h2>
                        <p class="group-show-hero__desc mb-0">
                            مراجعة طلبات الانضمام للمعسكرات، الموافقة، الرفض، ومتابعة حالة الدفع.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('training-camps.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-flag"></i></span>
                                <span class="group-show-action__text">قائمة المعسكرات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="enrollmentsStatsContainer" class="mb-4">
                @include('admin.pages.training-camps.partials.enrollments-stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الطلبات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالطالب أو فلتر حسب المعسكر والحالة وحالة الدفع.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="enrollmentsFilterForm" action="{{ route('training-camps.enrollments') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="enrollmentsSearchInput">البحث</label>
                                <input id="enrollmentsSearchInput" type="text" name="search" class="form-control"
                                       placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="enrollmentsStatus">الحالة</label>
                                <select name="status" id="enrollmentsStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="enrollmentsPayment">حالة الدفع</label>
                                <select name="payment_status" id="enrollmentsPayment" class="form-select">
                                    <option value="">حالة الدفع</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="enrollmentsCamp">المعسكر</label>
                                <select name="camp_id" id="enrollmentsCamp" class="form-select">
                                    <option value="">جميع المعسكرات</option>
                                    @foreach($camps as $camp)
                                        <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                            {{ $camp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <button type="button" id="clearEnrollmentsFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح
                                    </button>
                                    <small id="enrollmentsFilterFeedback" class="text-muted ms-1"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الطلبات
                        <span class="group-show-members-card__count" id="enrollmentsTableCount">{{ $enrollments->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div id="enrollmentsTableContainer">
                        @include('admin.pages.training-camps.partials.enrollments-table', ['enrollments' => $enrollments])
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="changeStatusModalLabel">تغيير حالة التسجيل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="group-show-empty__icon mx-auto mb-3" id="statusIconWrapper" style="width:72px;height:72px;font-size:1.75rem;">
                        <i class="fe fe-clock" id="statusIcon"></i>
                    </div>
                    <p class="text-muted mb-0" id="statusMessage">
                        هل أنت متأكد من تغيير الحالة إلى <strong id="statusLabelText">—</strong>؟
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChange">تأكيد التغيير</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    let currentEnrollmentId = null;
    let currentNewStatus = null;
    let enrollmentsFilterController = null;

    const statusConfig = {
        pending: { icon: 'fe-clock', label: 'قيد الانتظار', btn: 'btn-warning', bg: 'rgba(var(--warning-rgb),0.12)', color: 'rgb(var(--warning-rgb))' },
        approved: { icon: 'fe-check-circle', label: 'مقبول', btn: 'btn-success', bg: 'rgba(var(--success-rgb),0.12)', color: 'rgb(var(--success-rgb))' },
        rejected: { icon: 'fe-x-circle', label: 'مرفوض', btn: 'btn-danger', bg: 'rgba(var(--danger-rgb),0.12)', color: 'rgb(var(--danger-rgb))' },
        cancelled: { icon: 'fe-ban', label: 'ملغي', btn: 'btn-secondary', bg: 'rgba(108,117,125,0.12)', color: '#6c757d' },
    };

    function debounce(fn, delay) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initCountup(container) {
        (container || document).querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseInt(el.dataset.countup || '0', 10);
            if (!target) { el.textContent = '0'; return; }
            let current = 0;
            const step = Math.max(1, Math.ceil(target / 20));
            const timer = setInterval(function () {
                current += step;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = current.toLocaleString('ar-EG');
            }, 30);
        });
    }

    function initCopyStudentEmailButtons(root) {
        (root || document).querySelectorAll('.copy-student-email-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const email = btn.getAttribute('data-email');
                if (!email) return;
                navigator.clipboard.writeText(email).then(function () {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fe fe-check text-success"></i>';
                    setTimeout(function () { btn.innerHTML = orig; }, 1500);
                });
            });
        });
    }

    function initEnrollmentsAjaxFilters() {
        const form = document.getElementById('enrollmentsFilterForm');
        const tableContainer = document.getElementById('enrollmentsTableContainer');
        const statsContainer = document.getElementById('enrollmentsStatsContainer');
        const countBadge = document.getElementById('enrollmentsTableCount');
        const searchInput = document.getElementById('enrollmentsSearchInput');
        const feedback = document.getElementById('enrollmentsFilterFeedback');
        const clearBtn = document.getElementById('clearEnrollmentsFilters');

        if (!form || !tableContainer) return;

        initCountup(document);

        const getQueryString = function () {
            const fd = new FormData(form);
            fd.set('search', (fd.get('search') || '').toString().trim());
            return new URLSearchParams(fd).toString();
        };

        const fetchAndRender = function (url) {
            if (enrollmentsFilterController) enrollmentsFilterController.abort();
            enrollmentsFilterController = new AbortController();
            if (feedback) feedback.textContent = 'جاري التحديث...';
            tableContainer.style.opacity = '0.6';

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: enrollmentsFilterController.signal,
                credentials: 'same-origin',
            })
                .then(function (r) { if (!r.ok) throw new Error('fail'); return r.json(); })
                .then(function (data) {
                    if (typeof data.table_html === 'string') tableContainer.innerHTML = data.table_html;
                    if (statsContainer && typeof data.stats_html === 'string') {
                        statsContainer.innerHTML = data.stats_html;
                        initCountup(statsContainer);
                    }
                    if (countBadge && typeof data.count === 'number') countBadge.textContent = data.count;
                    initCopyStudentEmailButtons(tableContainer);
                    if (feedback) feedback.textContent = 'تم تحديث النتائج';
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                    if (feedback) feedback.textContent = 'تعذر تحديث النتائج.';
                })
                .finally(function () { tableContainer.style.opacity = '1'; });
        };

        const triggerSearch = function () {
            const qs = getQueryString();
            const base = form.getAttribute('action');
            fetchAndRender(qs ? base + '?' + qs : base);
        };

        if (searchInput) searchInput.addEventListener('input', debounce(triggerSearch, 350));
        form.querySelectorAll('select').forEach(function (el) { el.addEventListener('change', triggerSearch); });
        form.addEventListener('submit', function (e) { e.preventDefault(); triggerSearch(); });
        if (clearBtn) clearBtn.addEventListener('click', function () { form.reset(); triggerSearch(); });

        tableContainer.addEventListener('click', function (e) {
            const link = e.target.closest('.pagination a');
            if (link) { e.preventDefault(); fetchAndRender(link.href); }
        });
    }

    function initStatusModal() {
        const modal = document.getElementById('changeStatusModal');
        const confirmBtn = document.getElementById('confirmStatusChange');

        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                currentEnrollmentId = button.getAttribute('data-enrollment-id');
                currentNewStatus = button.getAttribute('data-new-status');
                const cfg = statusConfig[currentNewStatus];
                if (!cfg) return;

                const wrapper = document.getElementById('statusIconWrapper');
                const icon = document.getElementById('statusIcon');
                const label = document.getElementById('statusLabelText');
                if (wrapper) { wrapper.style.background = cfg.bg; wrapper.style.color = cfg.color; }
                if (icon) icon.className = 'fe ' + cfg.icon;
                if (label) label.textContent = cfg.label;
                if (confirmBtn) confirmBtn.className = 'btn ' + cfg.btn;
            });
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!currentEnrollmentId || !currentNewStatus) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/admin/training-camps-enrollments') }}/' + currentEnrollmentId + '/update-status';
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="status" value="' + currentNewStatus + '">';
                document.body.appendChild(form);
                form.submit();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initEnrollmentsAjaxFilters();
            initCopyStudentEmailButtons();
            initStatusModal();
        });
    } else {
        initEnrollmentsAjaxFilters();
        initCopyStudentEmailButtons();
        initStatusModal();
    }
})();
</script>
@stop
