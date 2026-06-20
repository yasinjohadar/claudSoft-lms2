@extends('student.layouts.master')

@section('page-title')
    جدول أعمالي
@stop

@section('content')
<div class="main-content app-content student-works-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 dashboard-fade-in">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">جدول أعمالي</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">جدول أعمالي</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">أدر مشاريعك وأعمالك وقدّمها للمراجعة لعرضها في بورتفوليوك.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2 align-self-center" id="student-works-count">
                    {{ $works->total() }} عمل
                </span>
                <a href="{{ route('student.works.create') }}" class="btn btn-primary btn-sm rounded-pill">
                    <i class="fe fe-plus me-1"></i>إضافة عمل جديد
                </a>
                <a href="{{ route('student.works.portfolio') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fe fe-image me-1"></i>عرض البورتفوليو
                </a>
            </div>
        </div>

        @include('student.works.partials.stats', ['stats' => $stats])

        @include('student.works.partials.filters', ['categories' => $categories, 'statuses' => $statuses])

        <div class="card custom-card student-quizzes-panel dashboard-fade-in">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-layers text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">أعمالي</h6>
                </div>

                <div id="student-works-results">
                    @include('student.works.partials.grid', compact('works', 'categories', 'statuses'))
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    const resultsEl = document.getElementById('student-works-results');
    const countEl = document.getElementById('student-works-count');
    const indexUrl = @json(route('student.works.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function animateCount(el, target, suffix) {
        if (!el) return;
        suffix = suffix || '';
        var duration = 600;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('.student-works-stats [data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });

    function buildParams(page) {
        const params = new URLSearchParams();
        const search = document.getElementById('sw-search')?.value.trim() || '';
        const status = document.getElementById('sw-status')?.value || '';
        const category = document.getElementById('sw-category')?.value || '';
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (category) params.set('category', category);
        if (page && page > 1) params.set('page', page);
        return params;
    }

    function fetchGrid(page) {
        const params = buildParams(page);
        const url = indexUrl + (params.toString() ? '?' + params.toString() : '');
        resultsEl.classList.add('is-loading');
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(function (data) {
                resultsEl.innerHTML = data.html;
                if (data.meta && countEl) {
                    countEl.textContent = data.meta.total + ' عمل';
                }
                bindPagination();
                bindEmptyReset();
            })
            .catch(function () {
                resultsEl.innerHTML = '<div class="alert alert-danger mb-0">تعذر تحميل النتائج. حاول مرة أخرى.</div>';
            })
            .finally(function () {
                resultsEl.classList.remove('is-loading');
            });
    }

    let searchTimer = null;
    function scheduleFetch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { fetchGrid(1); }, 320);
    }

    document.getElementById('sw-search')?.addEventListener('input', scheduleFetch);
    document.getElementById('sw-status')?.addEventListener('change', function () { fetchGrid(1); });
    document.getElementById('sw-category')?.addEventListener('change', function () { fetchGrid(1); });

    document.getElementById('sw-reset')?.addEventListener('click', function () {
        document.getElementById('sw-search').value = '';
        document.getElementById('sw-status').value = '';
        document.getElementById('sw-category').value = '';
        fetchGrid(1);
    });

    function bindPagination() {
        resultsEl.querySelectorAll('.student-works-pagination a, .pagination a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const href = a.getAttribute('href');
                if (!href) return;
                const page = new URL(href, window.location.origin).searchParams.get('page');
                fetchGrid(page ? parseInt(page, 10) : 1);
            });
        });
    }

    function bindEmptyReset() {
        document.getElementById('sw-empty-reset')?.addEventListener('click', function () {
            document.getElementById('sw-search').value = '';
            document.getElementById('sw-status').value = '';
            document.getElementById('sw-category').value = '';
            fetchGrid(1);
        });
    }

    bindPagination();
    bindEmptyReset();
})();
</script>
@endpush
