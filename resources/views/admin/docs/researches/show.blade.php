@extends('admin.layouts.master')

@section('page-title', 'توثيقات البحث: ' . $research->name)

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.researches.partials.styles')
<link rel="stylesheet" href="{{ asset('assets/libs/dragula/dragula.min.css') }}">
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-cat-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.pages.index') }}">التوثيق</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.researches.index') }}">الأبحاث</a></li>
                    <li class="breadcrumb-item active">{{ $research->name }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-cat-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-search me-1"></i>بحث</span>
                    <h2 class="group-show-hero__title mb-2">{{ $research->name }}</h2>
                    <p class="group-show-hero__desc mb-2">
                        القسم:
                        @if($research->category)
                            <a href="{{ route('admin.docs.categories.show', $research->category) }}" class="doc-cat-chip doc-cat-chip--section text-decoration-none">
                                <i class="fe fe-folder"></i>{{ $research->category->name }}
                            </a>
                        @else
                            —
                        @endif
                        <span class="doc-cat-status doc-cat-status--{{ $research->is_active ? 'active' : 'inactive' }} ms-2">
                            <span class="doc-cat-status__dot"></span>{{ $research->is_active ? 'مفعّل' : 'معطّل' }}
                        </span>
                    </p>
                    @if($research->description)
                        <p class="group-show-hero__desc mb-0">{{ $research->description }}</p>
                    @endif
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.researches.index') }}" class="btn btn-light border">
                            <i class="fe fe-arrow-right me-1"></i>كل الأبحاث
                        </a>
                        <a href="{{ route('admin.docs.researches.edit', $research) }}" class="btn btn-outline-secondary">
                            <i class="fe fe-edit-2 me-1"></i>تعديل البحث
                        </a>
                        <a href="{{ route('admin.docs.pages.index', ['documentation_category_id' => $research->documentation_category_id, 'documentation_research_id' => 'none']) }}"
                           class="btn btn-primary">
                            <i class="fe fe-plus-circle me-1"></i>إسناد صفحات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiCards = [
                ['variant' => 'blue',   'icon' => 'fe-file-text',   'label' => 'إجمالي الصفحات', 'value' => $stats['total'],     'sub' => 'داخل هذا البحث'],
                ['variant' => 'green',  'icon' => 'fe-check-circle','label' => 'منشورة',         'value' => $stats['published'], 'sub' => 'ظاهرة للمستخدمين'],
                ['variant' => 'orange', 'icon' => 'fe-edit-3',      'label' => 'مسودات',         'value' => $stats['draft'],     'sub' => 'غير منشورة'],
            ];
        @endphp
        <div class="row g-3 dashboard-fade-in doc-cat-animate mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item doc-cat-animate" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap"><i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i></div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                <h3 class="admin-stats-card__value mb-1">{{ number_format($card['value']) }}</h3>
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item doc-cat-animate" style="--stagger-delay: 210ms">
                <div class="card admin-stats-card admin-stats-card--cyan">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-clock admin-stats-card__icon"></i></div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">آخر تحديث</p>
                            <h3 class="admin-stats-card__value mb-1" style="font-size:1.05rem">
                                {{ $stats['last_updated'] ? \Carbon\Carbon::parse($stats['last_updated'])->diffForHumans() : '—' }}
                            </h3>
                            <p class="admin-stats-card__sub mb-0">لصفحات هذا البحث</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card doc-cat-filter-card doc-cat-animate mb-4">
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.docs.researches.show', $research) }}" class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label" for="rs-search">بحث داخل التوثيقات</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                            <input type="text" name="search" id="rs-search" class="form-control"
                                   placeholder="عنوان أو slug..." value="{{ request('search') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="rs-status">الحالة</label>
                        <select name="status" id="rs-status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>منشور</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary flex-fill"><i class="fe fe-filter me-1"></i>تصفية</button>
                            <a href="{{ route('admin.docs.researches.show', $research) }}" class="btn btn-light border"><i class="fe fe-rotate-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card doc-cat-table-card doc-cat-animate">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="card-title mb-0">توثيقات البحث</h6>
                <div class="d-flex align-items-center gap-3">
                    <span class="doc-research-save-state text-muted" id="rs-save-state"></span>
                    <span class="doc-cat-results-meta">{{ $pages->count() }} صفحة</span>
                </div>
            </div>
            <div class="card-body">
                @if($isFiltered)
                    <div class="alert alert-warning border-0 py-2 px-3 mb-3">
                        <i class="fe fe-info me-1"></i>
                        إعادة الترتيب معطّلة أثناء التصفية — القائمة المعروضة جزئية، وحفظ ترتيبها سيُفسد ترتيب البقية.
                        <a href="{{ route('admin.docs.researches.show', $research) }}">أزل التصفية</a> لإعادة الترتيب.
                    </div>
                @endif

                @if($pages->isEmpty())
                    <div class="doc-cat-empty text-center py-5">
                        <div class="doc-cat-empty__icon"><i class="fe fe-file-text"></i></div>
                        <p class="mb-1 fw-semibold">لا توجد توثيقات في هذا البحث بعد</p>
                        <p class="text-muted mb-3">
                            @if($unassignedCount > 0)
                                يوجد {{ number_format($unassignedCount) }} صفحة بلا بحث في قسم «{{ $research->category->name ?? '—' }}».
                            @else
                                أسند صفحات من قسم «{{ $research->category->name ?? '—' }}» إلى هذا البحث.
                            @endif
                        </p>
                        <a href="{{ route('admin.docs.pages.index', ['documentation_category_id' => $research->documentation_category_id, 'documentation_research_id' => 'none']) }}"
                           class="btn btn-primary">
                            <i class="fe fe-layers me-1"></i>إسناد صفحات إلى هذا البحث
                        </a>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        <i class="fe fe-move me-1"></i>
                        اسحب من المقبض لإعادة الترتيب — يُحفظ تلقائياً. هذا الترتيب خاص بصفحة البحث ولا يغيّر ترتيب الموقع العام.
                    </p>
                    <div id="rs-list"
                         class="doc-research-list {{ $isFiltered ? 'doc-research-list--locked' : '' }}"
                         data-locked="{{ $isFiltered ? '1' : '0' }}"
                         data-reorder-url="{{ route('admin.docs.researches.reorder', $research) }}">
                        @foreach($pages as $page)
                            <div class="doc-research-item" data-page-id="{{ $page->id }}">
                                <span class="doc-research-handle" title="اسحب لإعادة الترتيب"><i class="fe fe-menu"></i></span>
                                <span class="doc-research-order">{{ $loop->iteration }}</span>
                                <div class="doc-research-item__main">
                                    <a href="{{ route('admin.docs.pages.edit', $page) }}" class="doc-research-item__title">{{ $page->title }}</a>
                                    <div class="doc-research-item__meta">
                                        <code class="doc-cat-slug">{{ $page->slug }}</code>
                                        <span class="doc-cat-status doc-cat-status--{{ $page->status === 'published' ? 'published' : 'draft' }}">
                                            <span class="doc-cat-status__dot"></span>{{ $page->status === 'published' ? 'منشور' : 'مسودة' }}
                                        </span>
                                        @if($page->parent)
                                            <small class="text-muted"><i class="fe fe-corner-up-left me-1"></i>{{ $page->parent->title }}</small>
                                        @endif
                                        <small class="text-muted">{{ $page->updated_at?->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="doc-cat-actions">
                                    <a href="{{ route('admin.docs.pages.edit', $page) }}" class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary" title="تعديل">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--danger rs-detach"
                                            data-page-id="{{ $page->id }}" data-page-title="{{ $page->title }}" title="إزالة من البحث">
                                        <i class="fe fe-x-circle"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
<script src="{{ asset('assets/libs/dragula/dragula.min.js') }}"></script>
<script>
(function () {
    const list = document.getElementById('rs-list');
    if (!list) return;

    const reorderUrl = list.dataset.reorderUrl;
    const detachUrl  = @json(route('admin.docs.pages.bulk-assign-research'));
    const csrfToken  = @json(csrf_token());
    const locked     = list.dataset.locked === '1';
    const stateEl    = document.getElementById('rs-save-state');

    function setState(text, cls) {
        stateEl.textContent = text;
        stateEl.className = 'doc-research-save-state ' + (cls || 'text-muted');
        if (text) {
            clearTimeout(setState._t);
            setState._t = setTimeout(function () { stateEl.textContent = ''; }, 2500);
        }
    }

    function renumber() {
        list.querySelectorAll('.doc-research-order').forEach(function (el, i) {
            el.textContent = i + 1;
        });
    }

    function persistOrder() {
        const orders = Array.from(list.querySelectorAll('.doc-research-item'))
            .map(function (el) { return parseInt(el.dataset.pageId, 10); });

        setState('جارٍ الحفظ…', 'text-muted');

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ orders: orders }),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (res.body.success) {
                    setState('تم حفظ الترتيب', 'text-success');
                    return;
                }
                // The stored order is now out of sync with the DOM — reload rather
                // than leave the admin looking at an order that was never saved.
                if (window.toastr) toastr.error(res.body.message || 'تعذّر حفظ الترتيب');
                setTimeout(function () { window.location.reload(); }, 1200);
            })
            .catch(function () {
                if (window.toastr) toastr.error('تعذّر حفظ الترتيب — تحقق من الاتصال');
                setState('لم يُحفظ', 'text-danger');
            });
    }

    if (!locked && typeof dragula !== 'undefined') {
        const drake = dragula([list], {
            moves: function (el, container, handle) {
                return !!(handle && handle.closest('.doc-research-handle'));
            },
            direction: 'vertical',
        });
        drake.on('drop', function () {
            renumber();
            persistOrder();
        });
    }

    // Detach one page from this research, reusing the bulk endpoint.
    list.addEventListener('click', function (e) {
        const btn = e.target.closest('.rs-detach');
        if (!btn) return;

        if (!confirm('إزالة «' + btn.dataset.pageTitle + '» من هذا البحث؟ الصفحة نفسها لن تُحذف.')) return;

        btn.disabled = true;

        fetch(detachUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                page_ids: [parseInt(btn.dataset.pageId, 10)],
                documentation_research_id: null,
            }),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (res.body.success) {
                    if (window.toastr) toastr.success(res.body.message);
                    window.location.reload();
                    return;
                }
                btn.disabled = false;
                if (window.toastr) toastr.error(res.body.message || 'تعذّرت الإزالة');
            })
            .catch(function () {
                btn.disabled = false;
                if (window.toastr) toastr.error('تعذّر الاتصال');
            });
    });
})();
</script>
@endsection
