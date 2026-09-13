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

    /* العنوان يلتف كاملاً بدل القص بثلاث نقاط */
    .doc-pages-title {
        display: block;
        min-width: 220px;
        max-width: 420px;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.55;
    }

    /* الأب: سطران كحد أقصى مع تلميح بالنص الكامل */
    .doc-pages-parent {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-width: 180px;
        white-space: normal;
        overflow-wrap: anywhere;
        line-height: 1.4;
    }

    /* الصفوف صارت متعددة الأسطر — المحاذاة للأعلى أوضح */
    #docs-pages-tbody td {
        vertical-align: top;
    }

    .doc-pages-select-cell {
        width: 38px;
    }

    #docs-bulk-bar {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 1030;
        background: var(--custom-white);
        border-top: 1px solid var(--default-border);
        box-shadow: 0 -6px 24px rgba(0, 0, 0, .08);
        padding: .75rem 1rem;
    }

    #docs-bulk-bar[hidden] {
        display: none !important;
    }

    body.has-bulk-bar {
        padding-bottom: 88px;
    }

    .docs-bulk-count {
        font-weight: 700;
        color: rgb(var(--primary-rgb));
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
                        <a href="{{ route('admin.docs.ai-pages.batch.create') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-layers me-1"></i>توليد دفعة مواضيع
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
                        <label class="form-label" for="docs-pages-research">البحث</label>
                        <select name="documentation_research_id" id="docs-pages-research" class="form-select">
                            <option value="">كل الأبحاث</option>
                            <option value="none" {{ request('documentation_research_id') === 'none' ? 'selected' : '' }}>— بلا بحث —</option>
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
                    <div class="col-lg-2 col-md-6">
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
                    @if(request()->hasAny(['search', 'documentation_category_id', 'documentation_research_id', 'status']))
                        <span class="text-primary">(مفلتر)</span>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive position-relative" id="docs-pages-table-responsive" aria-busy="false">
                    <table class="table doc-cat-table mb-0">
                        <thead>
                            <tr>
                                <th class="doc-pages-select-cell">
                                    <input type="checkbox" class="form-check-input" id="docs-pages-select-all" aria-label="تحديد كل الصفحات الظاهرة">
                                </th>
                                <th>#</th>
                                <th style="min-width:220px">العنوان</th>
                                <th>القسم</th>
                                <th>البحث</th>
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

{{-- Lives outside the card so the AJAX innerHTML swap never touches it. --}}
<div id="docs-bulk-bar" hidden>
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="me-2">
                حُدِّد <span class="docs-bulk-count" id="docs-bulk-count">0</span> صفحة
            </span>
            <button type="button" class="btn btn-sm btn-light border" id="docs-bulk-select-matching" hidden></button>
            <div class="flex-grow-1"></div>
            <select id="docs-bulk-research" class="form-select form-select-sm" style="max-width: 320px;">
                <option value="">— اختر البحث —</option>
                <option value="none">— إزالة من البحث —</option>
            </select>
            <button type="button" class="btn btn-sm btn-primary" id="docs-bulk-apply">
                <i class="fe fe-check me-1"></i>تطبيق
            </button>
            <button type="button" class="btn btn-sm btn-light border" id="docs-bulk-clear">
                <i class="fe fe-x me-1"></i>إلغاء التحديد
            </button>
        </div>
        <small class="text-muted d-block mt-1" id="docs-bulk-hint"></small>
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
    var bulkUrl = @json(route('admin.docs.pages.bulk-assign-research'));
    var csrfToken = @json(csrf_token());
    var researches = @json($researchesJson);
    var debounceTimer = null;
    var debounceMs = 300;

    var categorySel = document.getElementById('docs-pages-category');
    var researchSel = document.getElementById('docs-pages-research');
    var selectAllBox = document.getElementById('docs-pages-select-all');
    var bulkBar = document.getElementById('docs-bulk-bar');
    var bulkCount = document.getElementById('docs-bulk-count');
    var bulkResearch = document.getElementById('docs-bulk-research');
    var bulkApply = document.getElementById('docs-bulk-apply');
    var bulkClear = document.getElementById('docs-bulk-clear');
    var bulkHint = document.getElementById('docs-bulk-hint');
    var bulkMatching = document.getElementById('docs-bulk-select-matching');

    // Selection lives here, outside the DOM the AJAX swap replaces, so it
    // survives filtering and paging — which is the whole point when several
    // hundred pages have to be sorted into researches.
    var selectedPages = new Map();   // id (string) -> categoryId (string)
    var selectAllMatching = false;
    var totalMatching = {{ $pages->total() }};

    function researchesForCategory(catId) {
        if (!catId) return researches;
        return researches.filter(function (r) { return String(r.category_id) === String(catId); });
    }

    function fillResearchSelect(sel, list, keep, placeholder, extraOption) {
        var current = keep !== undefined ? keep : sel.value;
        sel.innerHTML = '';
        sel.appendChild(new Option(placeholder, ''));
        if (extraOption) sel.appendChild(new Option(extraOption.label, extraOption.value));

        var groups = {};
        list.forEach(function (r) {
            (groups[r.group] = groups[r.group] || []).push(r);
        });
        Object.keys(groups).forEach(function (g) {
            var og = document.createElement('optgroup');
            og.label = g;
            groups[g].forEach(function (r) { og.appendChild(new Option(r.label, r.id)); });
            sel.appendChild(og);
        });

        sel.value = current;
        if (sel.value !== current) sel.value = '';   // the old pick is gone
        return sel.value === current;
    }

    function refreshResearchFilterOptions() {
        fillResearchSelect(
            researchSel,
            researchesForCategory(categorySel ? categorySel.value : ''),
            undefined,
            'كل الأبحاث',
            { value: 'none', label: '— بلا بحث —' }
        );
    }

    /** Distinct categories among the current selection. */
    function selectedCategoryIds() {
        var set = new Set();
        selectedPages.forEach(function (catId) { set.add(String(catId)); });
        return Array.from(set);
    }

    function refreshBulkResearchOptions() {
        var cats = selectedCategoryIds();
        var list;

        if (selectAllMatching) {
            list = researchesForCategory(categorySel ? categorySel.value : '');
        } else if (cats.length === 1) {
            list = researchesForCategory(cats[0]);
        } else {
            list = [];
        }

        fillResearchSelect(bulkResearch, list, undefined, '— اختر البحث —', { value: 'none', label: '— إزالة من البحث —' });

        if (!selectAllMatching && cats.length > 1) {
            bulkHint.textContent = 'الصفحات المحددة من أقسام مختلفة — يمكنك إزالتها من البحث فقط. صفِّ على قسم واحد للإسناد.';
        } else if (selectAllMatching && categorySel && !categorySel.value) {
            bulkHint.textContent = 'لم تختر قسماً — اختر قسماً من الفلاتر لتتمكن من الإسناد إلى بحث.';
        } else {
            bulkHint.textContent = '';
        }
    }

    function updateBulkBar() {
        var n = selectAllMatching ? totalMatching : selectedPages.size;
        var visible = n > 0;

        bulkBar.hidden = !visible;
        document.body.classList.toggle('has-bulk-bar', visible);
        bulkCount.textContent = n;

        if (bulkMatching) {
            var canOfferAll = !selectAllMatching && selectedPages.size > 0 && totalMatching > selectedPages.size;
            bulkMatching.hidden = !canOfferAll;
            if (canOfferAll) {
                bulkMatching.textContent = 'تحديد كل النتائج المطابقة (' + totalMatching + ')';
            }
        }

        refreshBulkResearchOptions();
    }

    /** Re-apply selection to the freshly rendered rows. */
    function syncSelectionUi() {
        var boxes = tbody.querySelectorAll('.docs-page-select');
        var checkedCount = 0;

        boxes.forEach(function (box) {
            box.checked = selectAllMatching || selectedPages.has(box.value);
            box.disabled = selectAllMatching;
            if (box.checked) checkedCount++;
        });

        if (selectAllBox) {
            selectAllBox.checked = boxes.length > 0 && checkedCount === boxes.length;
            selectAllBox.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
        }

        updateBulkBar();
    }

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
                if (typeof data.total === 'number') totalMatching = data.total;
                // The swap just destroyed every checkbox — restore from the Map.
                syncSelectionUi();
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
            // The research list depends on the category, so rebuild it before
            // the request is built from the form.
            if (sel === categorySel) refreshResearchFilterOptions();
            fetchDocsPages(buildListUrlFromForm());
        });
    });

    // --- selection -------------------------------------------------------
    // Delegated: per-row listeners would die on the first innerHTML swap.
    tbody.addEventListener('change', function (e) {
        var box = e.target.closest('.docs-page-select');
        if (!box) return;

        if (selectAllMatching) {          // manual pick leaves "all matching" mode
            selectAllMatching = false;
            selectedPages.clear();
        }

        if (box.checked) {
            selectedPages.set(box.value, box.dataset.categoryId);
        } else {
            selectedPages.delete(box.value);
        }

        syncSelectionUi();
    });

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function () {
            selectAllMatching = false;
            tbody.querySelectorAll('.docs-page-select').forEach(function (box) {
                if (selectAllBox.checked) {
                    selectedPages.set(box.value, box.dataset.categoryId);
                } else {
                    selectedPages.delete(box.value);
                }
            });
            syncSelectionUi();
        });
    }

    if (bulkMatching) {
        bulkMatching.addEventListener('click', function () {
            selectAllMatching = true;
            syncSelectionUi();
        });
    }

    if (bulkClear) {
        bulkClear.addEventListener('click', function () {
            selectedPages.clear();
            selectAllMatching = false;
            syncSelectionUi();
        });
    }

    if (bulkApply) {
        bulkApply.addEventListener('click', function () {
            var choice = bulkResearch.value;
            if (choice === '') {
                if (typeof toastr !== 'undefined') toastr.warning('اختر البحث أولاً');
                return;
            }

            var payload = { documentation_research_id: choice === 'none' ? null : choice };

            if (selectAllMatching) {
                // Filters go in their own key — documentation_research_id means
                // "assign to this" at the top level and "filter by this" here.
                payload.select_all = true;
                payload.filters = {};
                new URLSearchParams(new FormData(form)).forEach(function (v, k) {
                    if (v !== '') payload.filters[k] = v;
                });
            } else {
                payload.page_ids = Array.from(selectedPages.keys()).map(Number);
            }

            bulkApply.disabled = true;

            fetch(bulkUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    bulkApply.disabled = false;
                    if (!res.body.success) {
                        if (typeof toastr !== 'undefined') toastr.error(res.body.message || 'تعذّر الإسناد');
                        return;
                    }
                    if (typeof toastr !== 'undefined') toastr.success(res.body.message);
                    selectedPages.clear();
                    selectAllMatching = false;
                    fetchDocsPages(window.location.pathname + window.location.search, { push: false });
                })
                .catch(function () {
                    bulkApply.disabled = false;
                    if (typeof toastr !== 'undefined') toastr.error('تعذّر الاتصال — أعد المحاولة');
                });
        });
    }

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
            var st = document.getElementById('docs-pages-status');
            if (categorySel) categorySel.value = '';
            if (st) st.value = '';
            refreshResearchFilterOptions();
            selectedPages.clear();
            selectAllMatching = false;
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

    // Initial paint: the research filter is rendered with only its two static
    // options, so fill it before anything else and honour ?documentation_research_id=.
    var initialResearch = @json(request('documentation_research_id'));
    refreshResearchFilterOptions();
    if (initialResearch) researchSel.value = initialResearch;
    syncSelectionUi();
})();
</script>
@endsection
