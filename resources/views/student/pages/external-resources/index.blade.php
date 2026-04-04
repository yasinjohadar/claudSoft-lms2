@extends('student.layouts.master')

@section('page-title')
    الموارد الخارجية
@stop

@section('css')
<style>
    .external-resource-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 12px;
    }
    .external-resource-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }
    [data-theme-mode="dark"] .external-resource-card:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
    }
    #external-resources-results.is-loading {
        opacity: 0.55;
        pointer-events: none;
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الموارد الخارجية</h5>
                <nav class="mt-2">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">الموارد الخارجية</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2 my-2 my-xl-0">
                <span class="badge bg-primary-transparent text-primary fs-12" id="external-resources-count">
                    {{ $resources->total() }} مورد
                </span>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header border-0 pb-0">
                        <div class="card-title mb-0">
                            <i class="ri-filter-3-line me-2 text-primary"></i>تصفية النتائج
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="external-resources-filters" class="row g-3 align-items-end">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">بحث</label>
                                <input type="search" name="search" id="erf-search" class="form-control" placeholder="عنوان، وصف، اسم ملف…" value="{{ request('search') }}">
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">نوع المورد</label>
                                <select name="resource_type" id="erf-type" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach(\App\Models\Resource::resourceTypeOptions() as $key => $label)
                                        <option value="{{ $key }}" {{ request('resource_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">التصنيف</label>
                                <select name="classification" id="erf-classification" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach(\App\Models\Resource::classificationOptions() as $key => $label)
                                        <option value="{{ $key }}" {{ request('classification') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <label class="form-label">الترتيب</label>
                                <select name="sort" id="erf-sort" class="form-select">
                                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>الأحدث</option>
                                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>العنوان (أ-ي)</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6 d-flex gap-2">
                                <button type="button" class="btn btn-light flex-grow-1" id="erf-reset">
                                    <i class="ri-refresh-line me-1"></i>إعادة تعيين
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="external-resources-results">
            @include('student.pages.external-resources.partials.grid', ['resources' => $resources])
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    const resultsEl = document.getElementById('external-resources-results');
    const countEl = document.getElementById('external-resources-count');
    const form = document.getElementById('external-resources-filters');
    const indexUrl = @json(route('student.external-resources.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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
                if (data.meta && countEl) {
                    countEl.textContent = data.meta.total + ' مورد';
                }
                bindPagination();
            })
            .catch(function () {
                resultsEl.innerHTML = '<div class="alert alert-danger">تعذر تحميل النتائج. حاول مرة أخرى.</div>';
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
