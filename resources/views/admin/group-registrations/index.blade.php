@extends('admin.layouts.master')

@section('page-title')
    تسجيلات المجموعات
@stop

@section('styles')
    @include('admin.group-registrations.partials.page-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb gr-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">تسجيلات المجموعات</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in gr-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-user-plus me-1"></i>
                        إدارة التسجيل
                    </span>
                    <h2 class="group-show-hero__title mb-2">تسجيلات المجموعات</h2>
                    <p class="group-show-hero__desc mb-0">
                        متابعة طلبات التسجيل في المجموعات، حالة المعالجة، إنشاء الحسابات، وإشعارات البريد والواتساب.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.group-registrations.whatsapp-report') }}" class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-message-circle"></i></span>
                            <span class="group-show-action__text">تقارير الواتساب</span>
                        </a>
                        <a href="{{ route('groups.all') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                            <span class="group-show-action__text">جميع المجموعات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="grStatsContainer" class="mb-4 gr-page-animate">
            @include('admin.group-registrations.partials.stats', ['stats' => $stats])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in gr-page-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية التسجيلات</h4>
                <p class="fs-12 text-muted mb-0">جميع الفلاتر تعمل فوراً عبر AJAX — ابحث بالاسم، البريد، الهاتف، أو فلتر حسب المجموعة والحالة والتواريخ.</p>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('admin.group-registrations.index') }}" id="grFilterForm" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="grSearch">بحث</label>
                            <input type="text" id="grSearch" name="search" class="form-control"
                                   placeholder="اسم، بريد، هاتف، مدينة..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grGroup">المجموعة</label>
                            <select name="group_id" id="grGroup" class="form-select">
                                <option value="">جميع المجموعات</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grStatus">الحالة</label>
                            <select name="status" id="grStatus" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grComputer">حاسوب</label>
                            <select name="has_computer" id="grComputer" class="form-select">
                                <option value="">الكل</option>
                                <option value="yes" {{ request('has_computer') == 'yes' ? 'selected' : '' }}>نعم</option>
                                <option value="no" {{ request('has_computer') == 'no' ? 'selected' : '' }}>لا</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grUserCreated">تم إنشاء الحساب</label>
                            <select name="user_created" id="grUserCreated" class="form-select">
                                <option value="">الكل</option>
                                <option value="1" {{ request('user_created') === '1' ? 'selected' : '' }}>نعم</option>
                                <option value="0" {{ request('user_created') === '0' ? 'selected' : '' }}>لا</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grEmailSent">البريد</label>
                            <select name="email_sent" id="grEmailSent" class="form-select">
                                <option value="">الكل</option>
                                <option value="1" {{ request('email_sent') === '1' ? 'selected' : '' }}>مُرسل</option>
                                <option value="0" {{ request('email_sent') === '0' ? 'selected' : '' }}>غير مُرسل</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grWhatsapp">الواتساب</label>
                            <select name="whatsapp_status" id="grWhatsapp" class="form-select">
                                <option value="">الكل</option>
                                <option value="sent" {{ request('whatsapp_status') == 'sent' ? 'selected' : '' }}>مُرسل</option>
                                <option value="not_sent" {{ request('whatsapp_status') == 'not_sent' ? 'selected' : '' }}>غير مُرسل</option>
                                <option value="failed" {{ request('whatsapp_status') == 'failed' ? 'selected' : '' }}>فشل الإرسال</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grGender">الجنس</label>
                            <select name="gender" id="grGender" class="form-select">
                                <option value="">الكل</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>أخرى</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grNationality">الجنسية</label>
                            <select name="nationality_id" id="grNationality" class="form-select">
                                <option value="">جميع الجنسيات</option>
                                @foreach($nationalities as $nationality)
                                    <option value="{{ $nationality->id }}" {{ request('nationality_id') == $nationality->id ? 'selected' : '' }}>
                                        {{ $nationality->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grDateFrom">من تاريخ</label>
                            <input type="date" id="grDateFrom" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="grDateTo">إلى تاريخ</label>
                            <input type="date" id="grDateTo" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-xl-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fe fe-search me-1"></i>بحث
                                </button>
                                <button type="button" id="grResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                <span id="grSearchFeedback" class="fs-12 text-muted"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in gr-page-animate">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة التسجيلات
                    <span class="group-show-members-card__count" id="grCountBadge">{{ $registrations->total() }}</span>
                </h6>
                @if($registrations->total() > 0)
                    <span class="fs-12 text-muted">
                        عرض {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} من {{ $registrations->total() }}
                    </span>
                @endif
            </div>
            <div class="card-body pt-3" id="grTableContainer">
                @include('admin.group-registrations._table', ['registrations' => $registrations])
            </div>
        </div>

    </div>
</div>
@stop

@section('script')
<script>
(function () {
    function initGrCountup(root) {
        (root || document).querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();
            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    initGrCountup();

    function debounce(fn, delay) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(this, args);
            }, delay);
        };
    }

    function initGrAjaxFilter() {
        const form = document.getElementById('grFilterForm');
        const tableContainer = document.getElementById('grTableContainer');
        const statsContainer = document.getElementById('grStatsContainer');
        const countBadge = document.getElementById('grCountBadge');
        const searchInput = document.getElementById('grSearch');
        const feedback = document.getElementById('grSearchFeedback');
        const resetBtn = document.getElementById('grResetBtn');

        if (!form || !tableContainer) return;

        let currentController = null;

        const getQueryString = function () {
            const formData = new FormData(form);
            const search = (formData.get('search') || '').toString().trim();
            formData.set('search', search);
            return new URLSearchParams(formData).toString();
        };

        const updateBrowserUrl = function (queryString) {
            const baseUrl = form.getAttribute('action');
            const nextUrl = queryString ? (baseUrl + '?' + queryString) : baseUrl;
            window.history.replaceState({}, '', nextUrl);
        };

        const fetchAndRender = function (url) {
            if (currentController) currentController.abort();
            currentController = new AbortController();

            if (feedback) feedback.textContent = 'جاري البحث...';

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('فشل جلب النتائج');
                    return response.json();
                })
                .then(function (data) {
                    if (!data || typeof data.table_html !== 'string') {
                        throw new Error('صيغة استجابة غير متوقعة');
                    }

                    tableContainer.innerHTML = data.table_html;

                    if (statsContainer && typeof data.stats_html === 'string') {
                        statsContainer.innerHTML = data.stats_html;
                        initGrCountup(statsContainer);
                    }

                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }

                    const queryString = url.includes('?') ? url.split('?')[1] : '';
                    updateBrowserUrl(queryString);

                    if (feedback) feedback.textContent = 'تم تحديث النتائج';
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') return;
                    if (feedback) feedback.textContent = 'تعذر تحميل النتائج، حاول مرة أخرى.';
                    console.error(error);
                });
        };

        const triggerSearch = function () {
            const queryString = getQueryString();
            const baseUrl = form.getAttribute('action');
            const url = queryString ? (baseUrl + '?' + queryString) : baseUrl;
            fetchAndRender(url);
        };

        const debouncedSearch = debounce(triggerSearch, 350);

        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }

        form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
            el.addEventListener('change', triggerSearch);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                if (feedback) feedback.textContent = '';
                triggerSearch();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerSearch();
        });

        tableContainer.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('.pagination a');
            if (!paginationLink) return;
            event.preventDefault();
            fetchAndRender(paginationLink.href);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGrAjaxFilter);
    } else {
        initGrAjaxFilter();
    }
})();
</script>
@endsection
