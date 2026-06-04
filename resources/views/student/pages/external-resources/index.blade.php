@extends('student.layouts.master')

@section('page-title')
    الموارد الخارجية
@stop

@section('content')
<div class="main-content app-content student-external-resources-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">الموارد الخارجية</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">الموارد الخارجية</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2" id="external-resources-count">
                    {{ $resources->total() }} مورد
                </span>
            </div>
        </div>

        @include('student.pages.external-resources.partials.external-resources-stats', ['stats' => $stats])

        @include('student.pages.external-resources.partials.external-resources-filters')

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-book-open text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">مكتبة الموارد</h6>
                </div>

                <div id="external-resources-results">
                    @include('student.pages.external-resources.partials.grid', ['resources' => $resources])
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    const resultsEl = document.getElementById('external-resources-results');
    const countEl = document.getElementById('external-resources-count');
    const filteredCountEl = document.getElementById('external-resources-filtered-count');
    const indexUrl = @json(route('student.external-resources.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function animateCount(el, target) {
        if (!el) return;
        var duration = 600;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });

    function buildParams(page) {
        const params = new URLSearchParams();
        const search = document.getElementById('erf-search')?.value.trim() || '';
        const resourceType = document.getElementById('erf-type')?.value || '';
        const classification = document.getElementById('erf-classification')?.value || '';
        const sort = document.getElementById('erf-sort')?.value || 'latest';
        if (search) params.set('search', search);
        if (resourceType) params.set('resource_type', resourceType);
        if (classification) params.set('classification', classification);
        if (sort) params.set('sort', sort);
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
                if (data.meta) {
                    if (countEl) countEl.textContent = data.meta.total + ' مورد';
                    animateCount(filteredCountEl, data.meta.total);
                }
                bindPagination();
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

    document.getElementById('erf-search')?.addEventListener('input', scheduleFetch);
    document.getElementById('erf-type')?.addEventListener('change', function () { fetchGrid(1); });
    document.getElementById('erf-classification')?.addEventListener('change', function () { fetchGrid(1); });
    document.getElementById('erf-sort')?.addEventListener('change', function () { fetchGrid(1); });

    document.getElementById('erf-reset')?.addEventListener('click', function () {
        document.getElementById('erf-search').value = '';
        document.getElementById('erf-type').value = '';
        document.getElementById('erf-classification').value = '';
        document.getElementById('erf-sort').value = 'latest';
        fetchGrid(1);
    });

    function bindPagination() {
        resultsEl.querySelectorAll('.pagination a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                try {
                    const u = new URL(a.href);
                    const p = u.searchParams.get('page') || 1;
                    fetchGrid(parseInt(p, 10) || 1);
                    window.scrollTo({ top: resultsEl.offsetTop - 100, behavior: 'smooth' });
                } catch (err) {
                    window.location.href = a.href;
                }
            });
        });
    }

    bindPagination();
})();
</script>
@endpush
