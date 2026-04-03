@extends('admin.layouts.master')

@section('page-title')
    ملخص إكمال الدروس — {{ $course->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">ملخص إكمال الدروس</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ \Illuminate\Support\Str::limit($course->title, 50) }}</a></li>
                            <li class="breadcrumb-item active">ملخص الإكمال</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>العودة للكورس
                    </a>
                </div>
            </div>

            <form id="js-completion-summary-filter-form" method="GET" action="{{ route('courses.completion-summary', $course) }}" class="card custom-card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">المجموعة</label>
                            <select name="group_id" class="form-select">
                                <option value="">كل المجموعات</option>
                                @foreach($course->groups as $g)
                                    <option value="{{ $g->id }}" {{ $groupFilterActive && (int) $selectedGroupId === (int) $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>تطبيق
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div id="js-completion-summary-results">
                @include('admin.pages.courses.partials.module-completion-summary-body')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const form = document.getElementById('js-completion-summary-filter-form');
    const container = document.getElementById('js-completion-summary-results');
    if (!form || !container) return;

    const endpoint = form.getAttribute('action');
    const summaryPath = new URL(endpoint, window.location.origin).pathname;

    function loadSummary(url) {
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
                alert('تعذر تحديث الملخص. حاول مرة أخرى.');
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
            window.history.pushState({ completionSummary: true }, '', url);
        }
        loadSummary(url);
    }

    form.addEventListener('submit', submitFilters);

    const groupSelect = form.querySelector('select[name="group_id"]');
    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            submitFilters();
        });
    }

    window.addEventListener('popstate', function () {
        if (window.location.pathname === summaryPath) {
            loadSummary(window.location.href);
        } else {
            window.location.reload();
        }
    });
})();
</script>
@endsection
