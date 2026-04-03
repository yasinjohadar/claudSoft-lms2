@extends('admin.layouts.master')

@section('page-title')
    تقدم الطلاب — {{ $module->title }}
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
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">{{ \Illuminate\Support\Str::limit($module->title, 40) }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>العودة للكورس
                    </a>
                </div>
            </div>

            <div class="card custom-card mb-3">
                <div class="card-body">
                    <h6 class="mb-2">{{ $module->title }}</h6>
                    <p class="text-muted small mb-0">
                        <span class="badge bg-light text-dark me-1">
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
                    </p>
                </div>
            </div>

            <form id="js-module-completions-filter-form" method="GET" action="{{ route('courses.modules.completions', [$course->id, $module->id]) }}" class="card custom-card mb-3">
                <div class="card-body">
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
                                <i class="fas fa-filter me-1"></i>تطبيق
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
@endsection

@section('scripts')
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
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Request failed');
                return r.json();
            })
            .then(function (data) {
                container.innerHTML = data.html;
            })
            .catch(function () {
                alert('تعذر تحديث النتائج. حاول مرة أخرى.');
            })
            .finally(function () {
                container.classList.remove('opacity-50');
            });
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
        el.addEventListener('change', function () {
            submitFilters();
        });
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
