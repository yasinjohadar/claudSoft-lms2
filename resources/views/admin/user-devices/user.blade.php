@extends('admin.layouts.master')

@section('page-title')
    أجهزة {{ $user->name }}
@stop

@section('styles')
    @include('admin.user-devices.partials.page-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb ud-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-devices.index') }}">أجهزة المستخدمين</a></li>
                    <li class="breadcrumb-item active">أجهزة المستخدم</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in ud-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <span class="ud-user-avatar flex-shrink-0" style="width: 3rem; height: 3rem; min-width: 3rem; font-size: 1.1rem;">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="">
                            @else
                                {{ mb_substr($user->name, 0, 1) }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <span class="group-show-hero__eyebrow">
                                <i class="fe fe-user me-1"></i>
                                أجهزة المستخدم
                            </span>
                            <h2 class="group-show-hero__title mb-2">{{ $user->name }}</h2>
                            <p class="group-show-hero__desc mb-2">{{ $user->email }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-smartphone me-1"></i>{{ number_format($stats['total']) }} جهاز
                                </span>
                                @if($stats['trusted'] > 0)
                                    <span class="group-show-chip group-show-chip--sm text-success">
                                        <i class="fe fe-shield me-1"></i>{{ number_format($stats['trusted']) }} موثوق
                                    </span>
                                @endif
                                @if($stats['blocked'] > 0)
                                    <span class="group-show-chip group-show-chip--sm text-danger">
                                        <i class="fe fe-slash me-1"></i>{{ number_format($stats['blocked']) }} محظور
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-devices.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">كل الأجهزة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="userDevicesUserStatsContainer" class="mb-4 ud-page-animate">
            @include('admin.user-devices.partials.stats-user', ['stats' => $stats])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية الأجهزة</h4>
                <p class="fs-12 text-muted mb-0">جميع الفلاتر تعمل فوراً عبر AJAX.</p>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-wrap gap-2 mb-3" id="userDevicesUserQuickFilters">
                    @php
                        $quickStatuses = [
                            '' => 'الكل',
                            'active' => 'نشطة',
                            'trusted' => 'موثوقة',
                            'blocked' => 'محظورة',
                        ];
                    @endphp
                    @foreach($quickStatuses as $value => $label)
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary ud-quick-filter {{ request('status', '') === $value ? 'active' : '' }}"
                                data-status="{{ $value }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.user-devices.user', $user->id) }}" id="userDevicesUserFilterForm" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="userDevicesUserType">نوع الجهاز</label>
                            <select name="device_type" id="userDevicesUserType" class="form-select">
                                <option value="">الكل</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>جوال</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>تابلت</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>سطح مكتب</option>
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="userDevicesUserStatus">الحالة</label>
                            <select name="status" id="userDevicesUserStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="trusted" {{ request('status') == 'trusted' ? 'selected' : '' }}>موثوق</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>محظور</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesUserPerPage">عدد السجلات</label>
                            <select name="per_page" id="userDevicesUserPerPage" class="form-select">
                                @foreach([25, 50, 100, 150, 200] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>
                                        {{ $size }} سجل
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fe fe-search me-1"></i>بحث
                                </button>
                                <button type="button" id="userDevicesUserResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                <span id="userDevicesUserSearchFeedback" class="fs-12 text-muted"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة الأجهزة
                    <span class="group-show-members-card__count" id="userDevicesUserCountBadge">{{ $devices->total() }}</span>
                </h6>
                @if($devices->total() > 0)
                    <span class="fs-12 text-muted" id="userDevicesUserRangeInfo">
                        عرض {{ $devices->firstItem() }}–{{ $devices->lastItem() }} · {{ $devices->perPage() }} لكل صفحة
                    </span>
                @else
                    <span class="fs-12 text-muted d-none" id="userDevicesUserRangeInfo"></span>
                @endif
            </div>
            <div class="card-body pt-3" id="userDevicesUserTableContainer">
                @include('admin.user-devices._user_devices_table', ['devices' => $devices])
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function initUserDevicesUserCountup(root) {
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

initUserDevicesUserCountup();

(function () {
    function syncQuickFilters(status) {
        document.querySelectorAll('.ud-quick-filter').forEach(function (btn) {
            btn.classList.toggle('active', (btn.dataset.status || '') === (status || ''));
        });
    }

    function initUserDevicesUserAjaxFilter() {
        const form = document.getElementById('userDevicesUserFilterForm');
        const tableContainer = document.getElementById('userDevicesUserTableContainer');
        const countBadge = document.getElementById('userDevicesUserCountBadge');
        const rangeInfo = document.getElementById('userDevicesUserRangeInfo');
        const statusSelect = document.getElementById('userDevicesUserStatus');
        const feedback = document.getElementById('userDevicesUserSearchFeedback');
        const resetBtn = document.getElementById('userDevicesUserResetBtn');
        const quickFilters = document.querySelectorAll('.ud-quick-filter');

        if (!form || !tableContainer) return;

        let currentController = null;

        const getQueryString = function () {
            return new URLSearchParams(new FormData(form)).toString();
        };

        const updateBrowserUrl = function (queryString) {
            const nextUrl = queryString ? (form.action + '?' + queryString) : form.action;
            window.history.replaceState({}, '', nextUrl);
        };

        const fetchAndRender = function (url) {
            if (currentController) currentController.abort();
            currentController = new AbortController();
            if (feedback) feedback.textContent = 'جاري التحميل...';

            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('فشل جلب النتائج');
                    return response.json();
                })
                .then(function (data) {
                    if (!data || typeof data.table_html !== 'string') throw new Error('صيغة غير متوقعة');

                    tableContainer.innerHTML = data.table_html;

                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }

                    if (rangeInfo) {
                        if (data.from && data.to && data.per_page) {
                            rangeInfo.textContent = 'عرض ' + data.from + '–' + data.to + ' · ' + data.per_page + ' لكل صفحة';
                            rangeInfo.classList.remove('d-none');
                        } else if (data.count === 0) {
                            rangeInfo.classList.add('d-none');
                        }
                    }

                    updateBrowserUrl(url.includes('?') ? url.split('?')[1] : '');
                    if (feedback) feedback.textContent = 'تم تحديث النتائج';
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') return;
                    if (feedback) feedback.textContent = 'تعذر تحميل النتائج، حاول مرة أخرى.';
                });
        };

        const triggerSearch = function () {
            const queryString = getQueryString();
            fetchAndRender(queryString ? (form.action + '?' + queryString) : form.action);
            syncQuickFilters(statusSelect ? statusSelect.value : '');
        };

        quickFilters.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (statusSelect) statusSelect.value = btn.dataset.status || '';
                triggerSearch();
            });
        });

        form.querySelectorAll('select').forEach(function (field) {
            field.addEventListener('change', triggerSearch);
        });

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                syncQuickFilters(statusSelect.value);
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                syncQuickFilters('');
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
        document.addEventListener('DOMContentLoaded', initUserDevicesUserAjaxFilter);
    } else {
        initUserDevicesUserAjaxFilter();
    }
})();
</script>
@endpush
