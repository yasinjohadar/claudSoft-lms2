@extends('admin.layouts.master')

@section('page-title')
    اختر نوع السؤال
@stop

@section('styles')
<style>
    .qb-select-type-page .qb-type-create-card {
        --qb-accent: rgb(var(--primary-rgb));
        --qb-accent-soft: rgba(var(--primary-rgb), 0.12);
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 220px;
        padding: 1.35rem 1.2rem 1.15rem;
        border-radius: 18px;
        border: 1px solid var(--default-border, #eef1f6);
        background:
            linear-gradient(160deg, var(--qb-accent-soft) 0%, transparent 42%),
            var(--custom-white, #fff);
        text-decoration: none;
        color: inherit;
        overflow: hidden;
        transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                    box-shadow 0.28s ease,
                    border-color 0.28s ease;
    }

    .qb-select-type-page .qb-type-create-card::after {
        content: '';
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--qb-accent);
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .qb-select-type-page .qb-type-create-card:hover,
    .qb-select-type-page .qb-type-create-card:focus-visible {
        transform: translateY(-6px);
        border-color: color-mix(in srgb, var(--qb-accent) 45%, transparent);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
        color: inherit;
        outline: none;
    }

    .qb-select-type-page .qb-type-create-card:hover::after,
    .qb-select-type-page .qb-type-create-card:focus-visible::after {
        opacity: 1;
    }

    .qb-select-type-page .qb-type-create-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .qb-select-type-page .qb-type-create-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--qb-accent);
        background: var(--qb-accent-soft);
        transition: transform 0.28s ease, background 0.28s ease;
    }

    .qb-select-type-page .qb-type-create-card:hover .qb-type-create-card__icon {
        transform: scale(1.08) rotate(-3deg);
    }

    .qb-select-type-page .qb-type-create-card__arrow {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--qb-accent);
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid color-mix(in srgb, var(--qb-accent) 20%, transparent);
        opacity: 0;
        transform: translateX(6px);
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    [dir="rtl"] .qb-select-type-page .qb-type-create-card__arrow {
        transform: translateX(-6px);
    }

    .qb-select-type-page .qb-type-create-card:hover .qb-type-create-card__arrow {
        opacity: 1;
        transform: translateX(0);
    }

    .qb-select-type-page .qb-type-create-card__title {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.45rem;
        color: var(--default-text-color);
        line-height: 1.4;
    }

    .qb-select-type-page .qb-type-create-card__desc {
        font-size: 0.84rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 1rem;
        flex: 1;
    }

    .qb-select-type-page .qb-type-create-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 0.75rem;
        border-top: 1px solid var(--default-border, #eef1f6);
    }

    .qb-select-type-page .qb-type-create-card__cta {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--qb-accent);
    }

    .qb-select-type-page .qb-type-filter-chip {
        border: 1px solid var(--default-border, #eef1f6);
        background: var(--custom-white, #fff);
        color: var(--text-muted);
        border-radius: 999px;
        padding: 0.4rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .qb-select-type-page .qb-type-filter-chip:hover {
        border-color: rgba(var(--primary-rgb), 0.35);
        color: rgb(var(--primary-rgb));
    }

    .qb-select-type-page .qb-type-filter-chip.is-active {
        background: rgba(var(--primary-rgb), 0.12);
        border-color: rgba(var(--primary-rgb), 0.35);
        color: rgb(var(--primary-rgb));
    }

    .qb-select-type-page .qb-type-search {
        max-width: 280px;
    }

    .qb-select-type-page .qb-type-create-col.is-hidden {
        display: none !important;
    }

    .qb-select-type-page .qb-type-empty {
        display: none;
    }

    .qb-select-type-page .qb-type-empty.is-visible {
        display: block;
    }

    /* Accent variants */
    .qb-select-type-page .qb-type-create-card[data-accent="indigo"] { --qb-accent: #4f46e5; --qb-accent-soft: rgba(79, 70, 229, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="teal"] { --qb-accent: #0d9488; --qb-accent-soft: rgba(13, 148, 136, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="amber"] { --qb-accent: #d97706; --qb-accent-soft: rgba(217, 119, 6, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="rose"] { --qb-accent: #e11d48; --qb-accent-soft: rgba(225, 29, 72, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="violet"] { --qb-accent: #7c3aed; --qb-accent-soft: rgba(124, 58, 237, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="cyan"] { --qb-accent: #0891b2; --qb-accent-soft: rgba(8, 145, 178, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="pink"] { --qb-accent: #db2777; --qb-accent-soft: rgba(219, 39, 119, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="blue"] { --qb-accent: #2563eb; --qb-accent-soft: rgba(37, 99, 235, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="green"] { --qb-accent: #16a34a; --qb-accent-soft: rgba(22, 163, 74, 0.12); }
    .qb-select-type-page .qb-type-create-card[data-accent="slate"] { --qb-accent: #475569; --qb-accent-soft: rgba(71, 85, 105, 0.12); }

    [data-theme-mode="dark"] .qb-select-type-page .qb-type-create-card {
        background:
            linear-gradient(160deg, var(--qb-accent-soft) 0%, transparent 42%),
            rgba(255, 255, 255, 0.03);
    }

    [data-theme-mode="dark"] .qb-select-type-page .qb-type-create-card__arrow {
        background: rgba(255, 255, 255, 0.06);
    }
</style>
@stop

@section('content')
@php
    $feIcons = [
        'multiple_choice_single' => 'fe-circle',
        'multiple_choice_multiple' => 'fe-check-square',
        'true_false' => 'fe-toggle-right',
        'short_answer' => 'fe-type',
        'essay' => 'fe-file-text',
        'matching' => 'fe-link',
        'ordering' => 'fe-list',
        'fill_blank' => 'fe-edit-2',
        'fill_blanks' => 'fe-edit-2',
        'numerical' => 'fe-hash',
        'calculated' => 'fe-percent',
        'drag_drop' => 'fe-move',
    ];

    $accents = [
        'multiple_choice_single' => 'indigo',
        'multiple_choice_multiple' => 'teal',
        'true_false' => 'amber',
        'short_answer' => 'rose',
        'essay' => 'violet',
        'matching' => 'cyan',
        'ordering' => 'pink',
        'fill_blank' => 'blue',
        'fill_blanks' => 'blue',
        'numerical' => 'green',
        'calculated' => 'slate',
        'drag_drop' => 'cyan',
    ];

    $descriptions = [
        'multiple_choice_single' => 'اختيار إجابة واحدة صحيحة من عدة خيارات',
        'multiple_choice_multiple' => 'اختيار أكثر من إجابة صحيحة',
        'true_false' => 'تحديد إذا كانت العبارة صحيحة أم خاطئة',
        'short_answer' => 'إجابة نصية قصيرة',
        'essay' => 'إجابة مقالية طويلة',
        'matching' => 'مطابقة العناصر ببعضها',
        'ordering' => 'ترتيب العناصر بالتسلسل الصحيح',
        'fill_blank' => 'ملء الفراغات في النص',
        'fill_blanks' => 'ملء الفراغات في النص',
        'numerical' => 'إجابة رقمية مع هامش خطأ',
        'calculated' => 'سؤال محسوب بمعادلات',
        'drag_drop' => 'سحب العناصر وإفلاتها في أماكنها الصحيحة',
    ];

    $autoCount = $questionTypes->where('requires_manual_grading', false)->count();
    $manualCount = $questionTypes->where('requires_manual_grading', true)->count();
@endphp

<div class="main-content app-content qb-select-type-page">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item active">اختر نوع السؤال</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-plus-circle me-1"></i>
                        إنشاء سؤال جديد
                    </span>
                    <h2 class="group-show-hero__title mb-2">اختر نوع السؤال</h2>
                    <p class="group-show-hero__desc mb-0">
                        كل نوع له واجهة مخصصة لتسهيل الإدخال. اختر النوع المناسب لبدء الإنشاء.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('question-bank.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">العودة لبنك الأسئلة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 dashboard-fade-in mb-4">
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 0ms">
                <div class="card admin-stats-card admin-stats-card--blue">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-layers admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">أنواع متاحة</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $questionTypes->count() }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 70ms">
                <div class="card admin-stats-card admin-stats-card--green">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-zap admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">تصحيح تلقائي</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $autoCount }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 140ms">
                <div class="card admin-stats-card admin-stats-card--orange">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-edit-3 admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">تصحيح يدوي</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $manualCount }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1">أنواع الأسئلة</h4>
                        <p class="fs-12 text-muted mb-0">انقر على البطاقة للانتقال إلى نموذج الإنشاء.</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group qb-type-search">
                            <span class="input-group-text"><i class="fe fe-search"></i></span>
                            <input type="search" id="qbTypeSearch" class="form-control form-control-sm"
                                   placeholder="بحث عن نوع..." autocomplete="off">
                        </div>
                        <div class="d-flex flex-wrap gap-2" id="qbTypeFilters" role="group" aria-label="تصفية الأنواع">
                            <button type="button" class="qb-type-filter-chip is-active" data-filter="all">الكل</button>
                            <button type="button" class="qb-type-filter-chip" data-filter="auto">تلقائي</button>
                            <button type="button" class="qb-type-filter-chip" data-filter="manual">يدوي</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3" id="qbTypeGrid">
                    @foreach($questionTypes as $index => $type)
                        @php
                            $grading = $type->requires_manual_grading ? 'manual' : 'auto';
                            $accent = $accents[$type->name] ?? 'indigo';
                            $icon = $feIcons[$type->name] ?? 'fe-help-circle';
                            $desc = $descriptions[$type->name] ?? ($type->description ?? '');
                        @endphp
                        <div class="col-sm-6 col-lg-4 col-xl-3 dashboard-stagger-item qb-type-create-col"
                             style="--stagger-delay: {{ $index * 45 }}ms"
                             data-grading="{{ $grading }}"
                             data-search="{{ Str::lower($type->display_name.' '.$desc.' '.$type->name) }}">
                            <a href="{{ route('question-bank.create.type', $type->name) }}"
                               class="qb-type-create-card"
                               data-accent="{{ $accent }}">
                                <div class="qb-type-create-card__top">
                                    <span class="qb-type-create-card__icon">
                                        <i class="fe {{ $icon }}"></i>
                                    </span>
                                    <span class="qb-type-create-card__arrow" aria-hidden="true">
                                        <i class="fe fe-arrow-left"></i>
                                    </span>
                                </div>
                                <h5 class="qb-type-create-card__title">{{ $type->display_name }}</h5>
                                <p class="qb-type-create-card__desc">{{ $desc }}</p>
                                <div class="qb-type-create-card__footer">
                                    @if($type->requires_manual_grading)
                                        <span class="badge bg-warning-transparent">
                                            <i class="fe fe-edit-3 me-1"></i>تصحيح يدوي
                                        </span>
                                    @else
                                        <span class="badge bg-success-transparent">
                                            <i class="fe fe-zap me-1"></i>تصحيح تلقائي
                                        </span>
                                    @endif
                                    <span class="qb-type-create-card__cta">ابدأ الإنشاء</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="group-show-empty py-5 qb-type-empty" id="qbTypeEmpty">
                    <i class="fe fe-search group-show-empty__icon"></i>
                    <h5 class="group-show-empty__title">لا توجد أنواع مطابقة</h5>
                    <p class="group-show-empty__desc mb-0">جرّب كلمة بحث أخرى أو غيّر التصفية.</p>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
(function () {
    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var duration = 800;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });

    var searchInput = document.getElementById('qbTypeSearch');
    var filterWrap = document.getElementById('qbTypeFilters');
    var cols = Array.prototype.slice.call(document.querySelectorAll('.qb-type-create-col'));
    var emptyState = document.getElementById('qbTypeEmpty');
    var activeFilter = 'all';

    function applyFilters() {
        var term = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
        var visible = 0;

        cols.forEach(function (col) {
            var grading = col.getAttribute('data-grading') || 'auto';
            var haystack = col.getAttribute('data-search') || '';
            var matchFilter = activeFilter === 'all' || grading === activeFilter;
            var matchSearch = !term || haystack.indexOf(term) !== -1;
            var show = matchFilter && matchSearch;
            col.classList.toggle('is-hidden', !show);
            if (show) visible += 1;
        });

        if (emptyState) {
            emptyState.classList.toggle('is-visible', visible === 0);
        }
    }

    if (filterWrap) {
        filterWrap.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-filter]');
            if (!btn) return;
            activeFilter = btn.getAttribute('data-filter') || 'all';
            filterWrap.querySelectorAll('.qb-type-filter-chip').forEach(function (chip) {
                chip.classList.toggle('is-active', chip === btn);
            });
            applyFilters();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
})();
</script>
@stop
