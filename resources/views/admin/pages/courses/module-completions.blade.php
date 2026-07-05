@extends('admin.layouts.master')

@section('page-title')
    تقدم الطلاب — {{ $module->title }}
@stop

@section('css')
<style>
    .module-completions-kpi .admin-stats-card__value { font-size: 1.35rem; }
    .module-completions-table .student-meta { font-size: 0.8rem; color: var(--text-muted, #6c757d); }
    .module-completions-actions .btn { min-width: 2.5rem; }
    .module-completions-preview { white-space: pre-wrap; word-break: break-word; direction: rtl; line-height: 1.6; }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقدم الطلاب للوحدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ \Illuminate\Support\Str::limit($course->title, 40) }}</a></li>
                            <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($module->title, 40) }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-light border">
                        <i class="ri-arrow-right-line me-1"></i>العودة للكورس
                    </a>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <h4 class="mb-2 fw-bold">{{ $module->title }}</h4>
                            <p class="text-muted mb-0 small">
                                <span class="badge bg-primary-transparent text-primary me-1">
                                    @if($module->module_type == 'lesson') درس
                                    @elseif($module->module_type == 'video') فيديو
                                    @elseif($module->module_type == 'quiz') اختبار
                                    @elseif($module->module_type == 'assignment') واجب
                                    @elseif($module->module_type == 'question_module') وحدة أسئلة
                                    @elseif($module->module_type == 'resource') مورد
                                    @else {{ $module->module_type }}
                                    @endif
                                </span>
                                الكورس: <strong>{{ $course->title }}</strong>
                                @if($selectedGroup ?? null)
                                    — المجموعة: <strong>{{ $selectedGroup->name }}</strong>
                                @endif
                            </p>
                        </div>
                        @if($evolutionRotationEnabled ?? false)
                            <div class="text-end small">
                                <span class="badge bg-{{ ($rotationPoolCount ?? 0) >= 2 ? 'success' : 'warning' }}-transparent text-{{ ($rotationPoolCount ?? 0) >= 2 ? 'success' : 'warning' }}">
                                    <i class="ri-shuffle-line me-1"></i>
                                    تبديل واتساب: {{ $rotationPoolCount ?? 0 }} جلسة
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $stats = $stats ?? ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'with_phone' => 0, 'with_email' => 0];
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'ri-group-line', 'label' => 'النتائج', 'value' => $stats['total'], 'sub' => 'حسب الفلتر الحالي'],
                    ['variant' => 'green', 'icon' => 'ri-checkbox-circle-line', 'label' => 'مكتملون', 'value' => $stats['completed'], 'sub' => 'أنهوا الوحدة'],
                    ['variant' => 'orange', 'icon' => 'ri-time-line', 'label' => 'قيد التقدم', 'value' => $stats['in_progress'], 'sub' => 'لم يكملوا بعد'],
                    ['variant' => 'cyan', 'icon' => 'ri-whatsapp-line', 'label' => 'لديهم واتساب', 'value' => $stats['with_phone'], 'sub' => 'يمكن مراسلتهم'],
                    ['variant' => 'purple', 'icon' => 'ri-mail-line', 'label' => 'لديهم بريد', 'value' => $stats['with_email'], 'sub' => 'يمكن إرسال بريد'],
                ];
            @endphp

            <div class="row g-3 mb-4 module-completions-kpi">
                @foreach($kpiCards as $index => $card)
                    <div class="col-xl col-md-4 col-sm-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 60 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }} border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="{{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="admin-stats-card__value mb-0">{{ $card['value'] }}</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form id="js-module-completions-filter-form" method="GET" action="{{ route('courses.modules.completions', [$course->id, $module->id]) }}" class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h6 class="card-title mb-0">تصفية النتائج</h6>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">حالة التقدم</label>
                            <select name="status" class="form-select">
                                <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>مكتملون فقط</option>
                                <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>قيد التقدم فقط</option>
                                <option value="any" {{ $statusFilter === 'any' ? 'selected' : '' }}>مكتمل أو قيد التقدم</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المجموعة</label>
                            <select name="group_id" class="form-select">
                                <option value="">كل المجموعات</option>
                                @foreach($course->groups as $g)
                                    <option value="{{ $g->id }}" {{ (string) request('group_id') === (string) $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">بحث</label>
                            <input type="text" name="search" class="form-control" placeholder="اسم أو بريد…" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-filter-3-line me-1"></i>تطبيق
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div id="js-module-completions-results">
                @include('admin.pages.courses.partials.module-completions-results')
            </div>
        </div>
    </div>

    @include('admin.pages.courses.partials.module-completions-message-modals', [
        'course' => $course,
        'module' => $module,
    ])
@endsection

@section('scripts')
@include('admin.pages.courses.partials.module-completions-message-scripts')
<script>
(function () {
    const form = document.getElementById('js-module-completions-filter-form');
    const container = document.getElementById('js-module-completions-results');
    if (!form || !container) return;

    const endpoint = form.getAttribute('action');
    const completionsPath = new URL(endpoint, window.location.origin).pathname;

    function loadCompletions(url) {
        container.classList.add('opacity-50');
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Request failed');
                return r.json();
            })
            .then(function (data) {
                container.innerHTML = data.html;
                if (typeof window.initModuleCompletionMessageButtons === 'function') {
                    window.initModuleCompletionMessageButtons();
                }
            })
            .catch(function () { alert('تعذر تحديث النتائج. حاول مرة أخرى.'); })
            .finally(function () { container.classList.remove('opacity-50'); });
    }

    function submitFilters(e) {
        if (e) e.preventDefault();
        const params = new URLSearchParams(new FormData(form));
        const qs = params.toString();
        const url = qs ? endpoint + '?' + qs : endpoint;
        if (window.history && window.history.pushState) {
            window.history.pushState({ moduleCompletions: true }, '', url);
        }
        loadCompletions(url);
    }

    form.addEventListener('submit', submitFilters);
    form.querySelectorAll('select[name="status"], select[name="group_id"]').forEach(function (el) {
        el.addEventListener('change', submitFilters);
    });

    container.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link || !link.closest('.pagination')) return;
        const href = link.getAttribute('href');
        if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
        e.preventDefault();
        const absolute = new URL(href, window.location.origin).href;
        if (window.history && window.history.pushState) {
            window.history.pushState({ moduleCompletions: true }, '', absolute);
        }
        loadCompletions(absolute);
    });

    window.addEventListener('popstate', function () {
        if (window.location.pathname === completionsPath) {
            loadCompletions(window.location.href);
        } else {
            window.location.reload();
        }
    });
})();
</script>
@endsection
