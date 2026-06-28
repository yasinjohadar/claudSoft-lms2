@extends('admin.layouts.master')

@section('page-title', 'صفحات التوثيق')

@section('styles')
@include('admin.docs.categories.partials.styles')
<style>
    html:not(.loaded) .doc-pages-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .doc-pages-animate {
        animation-play-state: running !important;
    }

    .doc-pages-row {
        transition: background-color 0.18s ease;
    }

    .doc-pages-row:hover {
        background: rgba(var(--primary-rgb), 0.03);
    }

    .doc-cat-action-btn--success:hover {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }

    .doc-cat-action-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    #docs-pages-results-card.is-loading {
        opacity: 0.55;
        pointer-events: none;
    }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-pages-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.categories.index') }}">التوثيق</a></li>
                    <li class="breadcrumb-item active">صفحات التوثيق</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-pages-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-file-text me-1"></i>
                        إدارة المحتوى
                    </span>
                    <h2 class="group-show-hero__title mb-2">صفحات التوثيق</h2>
                    <p class="group-show-hero__desc mb-0">
                        إنشاء وتعديل ونشر مقالات التوثيق، ربطها بالكورسات، وتصديرها كـ PDF.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.categories.index') }}" class="btn btn-light border">
                            <i class="fe fe-folder me-1"></i>الأقسام
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.enhance') }}" class="btn btn-outline-primary">
                            <i class="fe fe-plus-circle me-1"></i>إضافة أفكار
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.improve') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-tool me-1"></i>تحسين بالذكاء
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-cpu me-1"></i>توليد
                        </a>
                        <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-1"></i>إضافة صفحة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.docs.pages.partials.stats')

        <div class="card custom-card doc-cat-filter-card doc-pages-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="card-title mb-0">
                    <i class="fe fe-filter me-2 text-primary"></i>تصفية وبحث
                </h6>
            </div>
            <div class="card-body pt-3">
                <form id="docs-pages-filters" method="GET" action="{{ route('admin.docs.pages.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label" for="docs-pages-search">بحث</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                            <input type="text"
                                   name="search"
                                   id="docs-pages-search"
                                   class="form-control"
                                   placeholder="عنوان أو slug..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="docs-pages-category">القسم</label>
                        <select name="documentation_category_id" id="docs-pages-category" class="form-select">
                            <option value="">كل الأقسام</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string) request('documentation_category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label" for="docs-pages-status">الحالة</label>
                        <select name="status" id="docs-pages-status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary flex-fill" id="docs-pages-submit">
                                <i class="fe fe-filter me-1"></i>تصفية
                            </button>
                            <button type="button" class="btn btn-light border" id="docs-pages-reset">
                                <i class="fe fe-rotate-ccw"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card doc-cat-table-card doc-pages-animate" id="docs-pages-results-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="card-title mb-0">قائمة الصفحات</h6>
                <span class="doc-cat-results-meta">
                    {{ $pages->total() }} صفحة
                    @if(request()->hasAny(['search', 'documentation_category_id', 'status']))
                        <span class="text-primary">(مفلتر)</span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive position-relative" id="docs-pages-table-responsive" aria-busy="false">
                    <table class="table doc-cat-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العنوان</th>
                                <th>القسم</th>
                                <th>الأب</th>
                                <th>slug</th>
                                <th>الحالة</th>
                                <th>آخر تحديث</th>
                                <th width="240">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="docs-pages-tbody">
                            @include('admin.docs.pages.partials.table-rows', ['pages' => $pages])
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top-0 pt-0" id="docs-pages-pagination">
                @include('admin.docs.pages.partials.pagination', ['pages' => $pages])
            </div>
        </div>
    </div>
</div>

@include('admin.partials.documentation-link-modal', [
    'modalMode' => 'docs',
    'allCourses' => $allCourses ?? collect(),
])
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
<script>
(function () {
    var form = document.getElementById('docs-pages-filters');
    var tbody = document.getElementById('docs-pages-tbody');
    var paginationEl = document.getElementById('docs-pages-pagination');
    var tableResponsive = document.getElementById('docs-pages-table-responsive');
    var resultsCard = document.getElementById('docs-pages-results-card');
    var resetBtn = document.getElementById('docs-pages-reset');
    var searchInput = document.getElementById('docs-pages-search');
    if (!form || !tbody || !paginationEl) return;

    var listBase = @json(route('admin.docs.pages.index'));
    var debounceTimer = null;
    var debounceMs = 300;

    function buildListUrlFromForm() {
        var params = new URLSearchParams(new FormData(form));
        params.forEach(function (_v, k) {
            if (params.get(k) === '') params.delete(k);
        });
        params.delete('page');
        var qs = params.toString();
        return listBase + (qs ? '?' + qs : '');
    }

    function showLoadState(loading) {
        if (tableResponsive) {
            tableResponsive.setAttribute('aria-busy', loading ? 'true' : 'false');
        }
        if (resultsCard) {
            resultsCard.classList.toggle('is-loading', loading);
        }
    }

    function notifyError() {
        if (typeof toastr !== 'undefined') {
            toastr.error('تعذّر تحميل النتائج. حاول مرة أخرى.');
        } else {
            alert('تعذّر تحميل النتائج. حاول مرة أخرى.');
        }
    }

    function fetchDocsPages(url, options) {
        options = options || {};
        var push = options.push !== false;
        var fetchUrl = (url.indexOf('http') === 0) ? url : new URL(url, window.location.origin).href;

        showLoadState(true);
        fetch(fetchUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (r) {
                if (!r.ok) throw new Error('bad status');
                return r.json();
            })
            .then(function (data) {
                tbody.innerHTML = data.tbody_html || '';
                paginationEl.innerHTML = data.pagination_html || '';
                if (push) {
                    var u = new URL(fetchUrl);
                    window.history.pushState({ docsPages: true }, '', u.pathname + u.search);
                }
            })
            .catch(function () {
                notifyError();
            })
            .finally(function () {
                showLoadState(false);
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchDocsPages(buildListUrlFromForm());
    });

    form.querySelectorAll('select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            fetchDocsPages(buildListUrlFromForm());
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                fetchDocsPages(buildListUrlFromForm());
            }, debounceMs);
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            var cat = document.getElementById('docs-pages-category');
            var st = document.getElementById('docs-pages-status');
            if (cat) cat.value = '';
            if (st) st.value = '';
            fetchDocsPages(listBase);
        });
    }

    paginationEl.addEventListener('click', function (e) {
        var a = e.target.closest('a[href]');
        if (!a || !paginationEl.contains(a)) return;
        var href = a.getAttribute('href');
        if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
        e.preventDefault();
        fetchDocsPages(a.href);
    });

    window.addEventListener('popstate', function () {
        fetchDocsPages(window.location.pathname + window.location.search, { push: false });
    });
})();
</script>
@endsection
