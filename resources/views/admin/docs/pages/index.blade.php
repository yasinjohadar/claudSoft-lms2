@extends('admin.layouts.master')

@section('page-title', 'صفحات التوثيق')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">صفحات التوثيق</h4>
                <p class="mb-0 text-muted">إدارة محتوى التوثيق من لوحة التحكم</p>
            </div>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.docs.ai-pages.improve') }}" class="btn btn-outline-primary">
                    <i class="bi bi-stars me-2"></i>فحص وتعديل بالذكاء الاصطناعي
                </a>
                <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>إضافة صفحة
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card custom-card mb-4">
            <div class="card-body">
                <form id="docs-pages-filters" method="GET" action="{{ route('admin.docs.pages.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" id="docs-pages-search" class="form-control" placeholder="بحث (عنوان أو slug)..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <select name="documentation_category_id" id="docs-pages-category" class="form-select">
                            <option value="">كل الأقسام</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) request('documentation_category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" id="docs-pages-status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary" id="docs-pages-submit">تصفية</button>
                        <button type="button" class="btn btn-secondary" id="docs-pages-reset">إعادة</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card" id="docs-pages-results-card">
            <div class="card-body p-0">
                <div class="table-responsive position-relative" id="docs-pages-table-responsive" aria-busy="false">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العنوان</th>
                                <th>القسم</th>
                                <th>الأب</th>
                                <th>slug</th>
                                <th>الحالة</th>
                                <th>آخر تحديث</th>
                                <th width="340">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="docs-pages-tbody">
                            @include('admin.docs.pages.partials.table-rows', ['pages' => $pages])
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer" id="docs-pages-pagination">
                @include('admin.docs.pages.partials.pagination', ['pages' => $pages])
            </div>
        </div>
    </div>
</div>
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
            resultsCard.style.opacity = loading ? '0.55' : '1';
            resultsCard.style.pointerEvents = loading ? 'none' : '';
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
        var nextUrl = a.href;
        fetchDocsPages(nextUrl);
    });

    window.addEventListener('popstate', function () {
        fetchDocsPages(window.location.pathname + window.location.search, { push: false });
    });
})();
</script>
@endsection
