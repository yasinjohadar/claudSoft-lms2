@extends('admin.layouts.master')

@section('page-title')
    المعسكرات التدريبية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">المعسكرات التدريبية</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-flag me-1"></i>
                            إدارة المعسكرات
                        </span>
                        <h2 class="group-show-hero__title mb-2">المعسكرات التدريبية</h2>
                        <p class="group-show-hero__desc mb-0">
                            إدارة المعسكرات، التصنيفات، المشاركين، والحالات من لوحة واحدة منظّمة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('training-camps.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة معسكر جديد</span>
                            </a>
                            <a href="{{ route('training-camps.enrollments') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-inbox"></i></span>
                                <span class="group-show-action__text">طلبات التسجيل</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="campsStatsContainer" class="mb-4">
                @include('admin.pages.training-camps.partials.camps-stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية المعسكرات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو المدرب أو الموقع، أو فلتر حسب الحالة والتصنيف.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="campsFilterForm" action="{{ route('training-camps.index') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="campsSearchInput">البحث</label>
                                <input type="text" name="search" id="campsSearchInput" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن معسكر...">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="campsStatus">الحالة</label>
                                <select name="status" id="campsStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>قادم</option>
                                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>جاري</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>منتهي</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="campsCategory">التصنيف</label>
                                <select name="category_id" id="campsCategory" class="form-select">
                                    <option value="">جميع التصنيفات</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="campsActive">النشاط</label>
                                <select name="is_active" id="campsActive" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('training-camps.index') }}" class="btn btn-outline-secondary btn-sm" id="campsResetBtn">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                    <small id="campsFilterFeedback" class="text-muted ms-1"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة المعسكرات
                        <span class="group-show-members-card__count" id="campsTableCount">{{ $camps->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div id="campsTableContainer">
                        @include('admin.pages.training-camps._camps_table', ['camps' => $camps])
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteCampModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fe fe-trash-2 me-2 text-danger"></i>حذف المعسكر
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="group-show-empty__icon mx-auto mb-3" style="width:72px;height:72px;font-size:1.75rem;background:rgba(var(--danger-rgb),0.12);color:rgb(var(--danger-rgb));">
                        <i class="fe fe-alert-triangle"></i>
                    </div>
                    <p class="text-muted mb-2" id="deleteCampMessage">هل أنت متأكد من حذف هذا المعسكر؟</p>
                    <p class="text-danger small mb-0">لن يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCamp">
                        <i class="fe fe-trash-2 me-1"></i>حذف
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    let currentCampId = null;
    let campsFilterController = null;

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
            if (!target) {
                el.textContent = '0';
                return;
            }
            let current = 0;
            const step = Math.max(1, Math.ceil(target / 20));
            const timer = setInterval(function () {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = current.toLocaleString('ar-EG');
            }, 30);
        });
    }

    function initCampsAjaxFilters() {
        const form = document.getElementById('campsFilterForm');
        const tableContainer = document.getElementById('campsTableContainer');
        const statsContainer = document.getElementById('campsStatsContainer');
        const countBadge = document.getElementById('campsTableCount');
        const searchInput = document.getElementById('campsSearchInput');
        const feedback = document.getElementById('campsFilterFeedback');
        const resetBtn = document.getElementById('campsResetBtn');

        if (!form || !tableContainer) return;

        initCountup(document);

        const getQueryString = function () {
            const formData = new FormData(form);
            formData.set('search', (formData.get('search') || '').toString().trim());
            return new URLSearchParams(formData).toString();
        };

        const fetchAndRender = function (url) {
            if (campsFilterController) campsFilterController.abort();
            campsFilterController = new AbortController();

            if (feedback) feedback.textContent = 'جاري التحديث...';
            tableContainer.style.opacity = '0.6';

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: campsFilterController.signal,
                credentials: 'same-origin',
            })
                .then(function (r) { if (!r.ok) throw new Error('fetch failed'); return r.json(); })
                .then(function (data) {
                    if (typeof data.table_html === 'string') {
                        tableContainer.innerHTML = data.table_html;
                    }
                    if (statsContainer && typeof data.stats_html === 'string') {
                        statsContainer.innerHTML = data.stats_html;
                        initCountup(statsContainer);
                    }
                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }
                    if (feedback) feedback.textContent = 'تم تحديث النتائج';
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                    if (feedback) feedback.textContent = 'تعذر تحديث النتائج.';
                })
                .finally(function () {
                    tableContainer.style.opacity = '1';
                });
        };

        const triggerSearch = function () {
            const qs = getQueryString();
            const base = form.getAttribute('action');
            fetchAndRender(qs ? base + '?' + qs : base);
        };

        const debouncedSearch = debounce(triggerSearch, 350);

        if (searchInput) searchInput.addEventListener('input', debouncedSearch);

        form.querySelectorAll('select[name="status"], select[name="category_id"], select[name="is_active"]').forEach(function (el) {
            el.addEventListener('change', triggerSearch);
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerSearch();
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                triggerSearch();
            });
        }

        tableContainer.addEventListener('click', function (e) {
            const link = e.target.closest('.pagination a');
            if (link) {
                e.preventDefault();
                fetchAndRender(link.href);
            }
        });
    }

    function initDeleteCamp() {
        const modalEl = document.getElementById('deleteCampModal');
        const confirmBtn = document.getElementById('confirmDeleteCamp');
        const messageEl = document.getElementById('deleteCampMessage');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-delete-camp');
            if (!btn || !modalEl) return;
            currentCampId = btn.getAttribute('data-camp-id');
            const name = btn.getAttribute('data-camp-name') || '';
            if (messageEl) {
                messageEl.innerHTML = 'هل أنت متأكد من حذف المعسكر<br><strong>' + name + '</strong>؟';
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!currentCampId) return;
                confirmBtn.disabled = true;

                fetch('{{ url('/admin/training-camps') }}/' + currentCampId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (r) { return r.json().catch(function () { return { success: true }; }); })
                    .then(function (data) {
                        bootstrap.Modal.getInstance(modalEl)?.hide();
                        if (data.success !== false) {
                            const form = document.getElementById('campsFilterForm');
                            if (form) form.dispatchEvent(new Event('submit'));
                        } else {
                            alert(data.message || 'تعذر الحذف');
                        }
                    })
                    .catch(function () { alert('تعذر الحذف'); })
                    .finally(function () {
                        confirmBtn.disabled = false;
                        currentCampId = null;
                    });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initCampsAjaxFilters();
            initDeleteCamp();
        });
    } else {
        initCampsAjaxFilters();
        initDeleteCamp();
    }
})();
</script>
@stop
