@extends('admin.layouts.master')

@section('page-title')
    جلسات المستخدمين
@stop

@section('styles')
    @include('admin.user-sessions.partials.page-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb us-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">جلسات المستخدمين</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in us-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-monitor me-1"></i>
                        تتبع النشاط
                    </span>
                    <h2 class="group-show-hero__title mb-2">جلسات المستخدمين</h2>
                    <p class="group-show-hero__desc mb-0">
                        مراقبة جلسات الطلاب: الأجهزة، المواقع، المدة، والأنشطة — مع فلاتر AJAX وإدارة جماعية.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-sessions.statistics') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-bar-chart-2"></i></span>
                            <span class="group-show-action__text">الإحصائيات</span>
                        </a>
                        <a href="{{ route('admin.user-sessions.active') }}" class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-radio"></i></span>
                            <span class="group-show-action__text">الجلسات النشطة</span>
                        </a>
                        <button type="button" class="group-show-action group-show-action--danger border-0" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                            <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                            <span class="group-show-action__text">حذف الكل</span>
                        </button>
                        <button type="button" class="group-show-action group-show-action--warning border-0" data-bs-toggle="modal" data-bs-target="#deleteCompletedModal">
                            <span class="group-show-action__icon"><i class="fe fe-check-circle"></i></span>
                            <span class="group-show-action__text">حذف المكتملة</span>
                        </button>
                        <button type="button" class="group-show-action border-0" data-bs-toggle="modal" data-bs-target="#deleteDisconnectedModal">
                            <span class="group-show-action__icon"><i class="fe fe-wifi-off"></i></span>
                            <span class="group-show-action__text">حذف المنفصلة</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="userSessionsStatsContainer" class="mb-4 us-page-animate">
            @include('admin.user-sessions.partials.stats', ['stats' => $stats])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية الجلسات</h4>
                <p class="fs-12 text-muted mb-0">جميع الفلاتر تعمل فوراً عبر AJAX.</p>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-wrap gap-2 mb-3" id="userSessionsQuickFilters">
                    @php
                        $quickStatuses = [
                            '' => 'الكل',
                            'active' => 'نشطة',
                            'completed' => 'مكتملة',
                            'disconnected' => 'منفصلة',
                            'timeout' => 'انتهت',
                        ];
                    @endphp
                    @foreach($quickStatuses as $value => $label)
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary us-quick-filter {{ request('status', '') === $value ? 'active' : '' }}"
                                data-status="{{ $value }}">
                            @if($value === 'active')
                                <i class="fe fe-radio me-1"></i>
                            @endif
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.user-sessions.index') }}" id="userSessionsFilterForm" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="userSessionsSearch">البحث</label>
                            <input type="text" name="search" id="userSessionsSearch" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="اسم المستخدم، البريد، IP...">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userSessionsUser">المستخدم</label>
                            <select name="user_id" id="userSessionsUser" class="form-select">
                                <option value="">الكل</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userSessionsStatus">الحالة</label>
                            <select name="status" id="userSessionsStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="disconnected" {{ request('status') == 'disconnected' ? 'selected' : '' }}>منفصلة</option>
                                <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>انتهت</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userSessionsDevice">نوع الجهاز</label>
                            <select name="device_type" id="userSessionsDevice" class="form-select">
                                <option value="">الكل</option>
                                @foreach($deviceTypes as $type)
                                    <option value="{{ $type }}" {{ request('device_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userSessionsDateFrom">من تاريخ</label>
                            <input type="date" name="date_from" id="userSessionsDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userSessionsPerPage">عدد السجلات</label>
                            <select name="per_page" id="userSessionsPerPage" class="form-select">
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
                                <button type="button" id="userSessionsResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                <span id="userSessionsSearchFeedback" class="fs-12 text-muted"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card us-bulk-bar dashboard-fade-in us-page-animate mb-4 d-none" id="bulkActionBar">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="fw-semibold">
                            <i class="fe fe-check-square me-1 text-primary"></i>
                            <span id="selectedCount">0</span> جلسة محددة
                        </span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                                <i class="fe fe-check me-1"></i>تحديد الكل
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDeleteSelected()">
                        <i class="fe fe-trash-2 me-1"></i>حذف المحدد
                    </button>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in us-page-animate">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة الجلسات
                    <span class="group-show-members-card__count" id="userSessionsCountBadge">{{ $sessions->total() }}</span>
                </h6>
                @if($sessions->total() > 0)
                    <span class="fs-12 text-muted" id="userSessionsRangeInfo">
                        عرض {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} · {{ $sessions->perPage() }} لكل صفحة
                    </span>
                @else
                    <span class="fs-12 text-muted d-none" id="userSessionsRangeInfo"></span>
                @endif
            </div>
            <div class="card-body pt-3" id="userSessionsTableContainer">
                @include('admin.user-sessions._sessions_table', ['sessions' => $sessions])
            </div>
        </div>

    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fe fe-alert-triangle me-2"></i>تحذير: حذف جميع الجلسات</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fe fe-alert-circle me-2"></i>
                        <strong>هذا الإجراء لا يمكن التراجع عنه!</strong>
                    </div>
                    <p>سيتم حذف <strong>جميع الجلسات</strong> المسجلة في النظام ({{ number_format($stats['total']) }} جلسة).</p>
                    <p class="mb-0">هل أنت متأكد من هذا الإجراء؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد تماماً؟ سيتم حذف جميع الجلسات نهائياً!')">
                        <i class="fe fe-trash-2 me-1"></i>نعم، حذف الكل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Completed Sessions Modal -->
<div class="modal fade" id="deleteCompletedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fe fe-check-circle me-2"></i>حذف الجلسات المكتملة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-completed') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الجلسات المكتملة (التي انتهت بشكل طبيعي).</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">حذف الجلسات المكتملة أقدم من (بالأيام):</label>
                        <input type="number" name="days" class="form-control" value="0" min="0" max="365">
                        <small class="text-muted">اتركه 0 لحذف جميع الجلسات المكتملة، أو حدد عدد أيام لحذف الأقدم فقط</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من حذف الجلسات المكتملة؟')">
                        <i class="fe fe-trash-2 me-1"></i>حذف الجلسات المكتملة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Disconnected Sessions Modal -->
<div class="modal fade" id="deleteDisconnectedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fe fe-wifi-off me-2"></i>حذف الجلسات المنفصلة والمنتهية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-disconnected') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>سيتم حذف جميع الجلسات التي حالتها:</p>
                    <ul class="mb-0">
                        <li><span class="us-status-chip us-status-chip--disconnected">منفصلة</span></li>
                        <li><span class="us-status-chip us-status-chip--timeout">انتهت</span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('هل أنت متأكد من حذف الجلسات المنفصلة والمنتهية؟')">
                        <i class="fe fe-trash-2 me-1"></i>حذف الجلسات المنفصلة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function initUserSessionsCountup(root) {
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

initUserSessionsCountup();

function toggleSelectAll(source) {
    document.querySelectorAll('.session-checkbox').forEach(function (checkbox) {
        checkbox.checked = source.checked;
    });
    updateBulkActionBar();
}

function selectAll() {
    document.querySelectorAll('.session-checkbox').forEach(function (checkbox) {
        checkbox.checked = true;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) selectAllCheckbox.checked = true;
    updateBulkActionBar();
}

function deselectAll() {
    document.querySelectorAll('.session-checkbox').forEach(function (checkbox) {
        checkbox.checked = false;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const count = document.querySelectorAll('.session-checkbox:checked').length;
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) selectedCount.textContent = count;
    if (bulkActionBar) bulkActionBar.classList.toggle('d-none', count === 0);
}

function bulkDeleteSelected() {
    const checkboxes = document.querySelectorAll('.session-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('يرجى تحديد جلسة واحدة على الأقل');
        return;
    }
    if (!confirm('هل أنت متأكد من حذف ' + checkboxes.length + ' جلسة؟')) return;

    const form = document.getElementById('bulkDeleteForm');
    if (!form) return;

    form.querySelectorAll('input[name="session_ids[]"]').forEach(function (el) { el.remove(); });
    checkboxes.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'session_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    form.submit();
}

window.initUserSessionsTableHandlers = function () {
    deselectAll();
};

(function () {
    function debounce(fn, delay) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(this, args); }, delay);
        };
    }

    function syncQuickFilters(status) {
        document.querySelectorAll('.us-quick-filter').forEach(function (btn) {
            btn.classList.toggle('active', (btn.dataset.status || '') === (status || ''));
        });
    }

    function initUserSessionsAjaxFilter() {
        const form = document.getElementById('userSessionsFilterForm');
        const tableContainer = document.getElementById('userSessionsTableContainer');
        const countBadge = document.getElementById('userSessionsCountBadge');
        const rangeInfo = document.getElementById('userSessionsRangeInfo');
        const statusSelect = document.getElementById('userSessionsStatus');
        const searchInput = document.getElementById('userSessionsSearch');
        const feedback = document.getElementById('userSessionsSearchFeedback');
        const resetBtn = document.getElementById('userSessionsResetBtn');
        const quickFilters = document.querySelectorAll('.us-quick-filter');

        if (!form || !tableContainer) return;

        let currentController = null;

        const getQueryString = function () {
            const formData = new FormData(form);
            formData.set('search', (formData.get('search') || '').toString().trim());
            return new URLSearchParams(formData).toString();
        };

        const updateBrowserUrl = function (queryString) {
            const nextUrl = queryString ? (form.action + '?' + queryString) : form.action;
            window.history.replaceState({}, '', nextUrl);
        };

        const fetchAndRender = function (url) {
            if (currentController) currentController.abort();
            currentController = new AbortController();
            if (feedback) feedback.textContent = 'جاري البحث...';

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

                    if (typeof window.initUserSessionsTableHandlers === 'function') {
                        window.initUserSessionsTableHandlers();
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

        if (searchInput) searchInput.addEventListener('input', debounce(triggerSearch, 350));

        form.querySelectorAll('select, input[type="date"]').forEach(function (field) {
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
        document.addEventListener('DOMContentLoaded', initUserSessionsAjaxFilter);
    } else {
        initUserSessionsAjaxFilter();
    }
})();
</script>
@endpush
