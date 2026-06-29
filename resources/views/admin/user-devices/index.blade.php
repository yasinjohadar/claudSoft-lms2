@extends('admin.layouts.master')

@section('page-title')
    أجهزة المستخدمين
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
                    <li class="breadcrumb-item active">أجهزة المستخدمين</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in ud-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-smartphone me-1"></i>
                        إدارة الأجهزة
                    </span>
                    <h2 class="group-show-hero__title mb-2">أجهزة المستخدمين</h2>
                    <p class="group-show-hero__desc mb-0">
                        تتبع أجهزة الطلاب المسجّلة: المتصفح، المنصة، الموقع، وعدد مرات الدخول — مع حظر وثقة وإدارة جماعية.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.user-devices.security-settings') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-shield"></i></span>
                            <span class="group-show-action__text">إعدادات الأمان</span>
                        </a>
                        <button type="button" class="group-show-action group-show-action--primary border-0" data-bs-toggle="modal" data-bs-target="#deleteInactiveModal">
                            <span class="group-show-action__icon"><i class="fe fe-power"></i></span>
                            <span class="group-show-action__text">حذف غير النشطة</span>
                        </button>
                        <button type="button" class="group-show-action group-show-action--warning border-0" data-bs-toggle="modal" data-bs-target="#deleteOldModal">
                            <span class="group-show-action__icon"><i class="fe fe-clock"></i></span>
                            <span class="group-show-action__text">حذف القديمة</span>
                        </button>
                        <button type="button" class="group-show-action group-show-action--danger border-0" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                            <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                            <span class="group-show-action__text">حذف الكل</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="userDevicesStatsContainer" class="mb-4 ud-page-animate">
            @include('admin.user-devices.partials.stats', ['stats' => $stats])
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية الأجهزة</h4>
                <p class="fs-12 text-muted mb-0">جميع الفلاتر تعمل فوراً عبر AJAX.</p>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex flex-wrap gap-2 mb-3" id="userDevicesQuickFilters">
                    @php
                        $quickStatuses = [
                            '' => 'الكل',
                            'pending_trust' => 'بانتظار الموافقة',
                            'active' => 'نشطة',
                            'trusted' => 'موثوقة',
                            'blocked' => 'محظورة',
                        ];
                    @endphp
                    @foreach($quickStatuses as $value => $label)
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary ud-quick-filter {{ request('status', '') === $value ? 'active' : '' }}"
                                data-status="{{ $value }}">
                            @if($value === 'trusted')
                                <i class="fe fe-shield me-1"></i>
                            @elseif($value === 'blocked')
                                <i class="fe fe-slash me-1"></i>
                            @elseif($value === 'pending_trust')
                                <i class="fe fe-clock me-1"></i>
                            @endif
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.user-devices.index') }}" id="userDevicesFilterForm" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="userDevicesSearch">البحث</label>
                            <input type="text" name="search" id="userDevicesSearch" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="اسم المستخدم، البريد، IP...">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesUser">المستخدم</label>
                            <select name="user_id" id="userDevicesUser" class="form-select">
                                <option value="">الكل</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesType">نوع الجهاز</label>
                            <select name="device_type" id="userDevicesType" class="form-select">
                                <option value="">الكل</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>جوال</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>تابلت</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>سطح مكتب</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesStatus">الحالة</label>
                            <select name="status" id="userDevicesStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="trusted" {{ request('status') == 'trusted' ? 'selected' : '' }}>موثوق</option>
                                <option value="pending_trust" {{ request('status') == 'pending_trust' ? 'selected' : '' }}>بانتظار الموافقة</option>
                                <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>محظور</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesDateFrom">من تاريخ</label>
                            <input type="date" name="date_from" id="userDevicesDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="userDevicesPerPage">عدد السجلات</label>
                            <select name="per_page" id="userDevicesPerPage" class="form-select">
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
                                <button type="button" id="userDevicesResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                <span id="userDevicesSearchFeedback" class="fs-12 text-muted"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card ud-bulk-bar dashboard-fade-in ud-page-animate mb-4 d-none" id="bulkActionBar">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="fw-semibold">
                            <i class="fe fe-check-square me-1 text-primary"></i>
                            <span id="selectedCount">0</span> جهاز محدد
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

        <div class="card custom-card group-show-members-card dashboard-fade-in ud-page-animate">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة الأجهزة
                    <span class="group-show-members-card__count" id="userDevicesCountBadge">{{ $devices->total() }}</span>
                </h6>
                @if($devices->total() > 0)
                    <span class="fs-12 text-muted" id="userDevicesRangeInfo">
                        عرض {{ $devices->firstItem() }}–{{ $devices->lastItem() }} · {{ $devices->perPage() }} لكل صفحة
                    </span>
                @else
                    <span class="fs-12 text-muted d-none" id="userDevicesRangeInfo"></span>
                @endif
            </div>
            <div class="card-body pt-3" id="userDevicesTableContainer">
                @include('admin.user-devices._devices_table', ['devices' => $devices])
            </div>
        </div>

    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fe fe-alert-triangle me-2"></i>تحذير: حذف جميع الأجهزة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fe fe-alert-circle me-2"></i>
                        <strong>هذا الإجراء لا يمكن التراجع عنه!</strong>
                    </div>
                    <p>سيتم حذف <strong>جميع الأجهزة</strong> المسجلة في النظام ({{ number_format($stats['total']) }} جهاز).</p>
                    <p class="mb-0">هل أنت متأكد من هذا الإجراء؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد تماماً؟ سيتم حذف جميع الأجهزة نهائياً!')">
                        <i class="fe fe-trash-2 me-1"></i>نعم، حذف الكل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Old Devices Modal -->
<div class="modal fade" id="deleteOldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fe fe-clock me-2"></i>حذف الأجهزة القديمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-old') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الأجهزة التي لم تُستخدم منذ فترة محددة.</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">حذف الأجهزة غير المستخدمة منذ (بالأيام):</label>
                        <input type="number" name="days" class="form-control" value="90" min="1" max="365" required>
                        <small class="text-muted">مثال: 90 يوم = حذف الأجهزة التي لم تُستخدم منذ 3 أشهر</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من حذف الأجهزة القديمة؟')">
                        <i class="fe fe-trash-2 me-1"></i>حذف الأجهزة القديمة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Inactive Devices Modal -->
<div class="modal fade" id="deleteInactiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fe fe-power me-2"></i>حذف الأجهزة غير النشطة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-devices.delete-inactive') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الأجهزة ذات النشاط المنخفض جداً.</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">الحد الأقصى لعدد تسجيلات الدخول:</label>
                        <input type="number" name="max_logins" class="form-control" value="1" min="0" max="100" required>
                        <small class="text-muted">سيتم حذف الأجهزة التي سجلت دخولها هذا العدد أو أقل</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('هل أنت متأكد من حذف الأجهزة غير النشطة؟')">
                        <i class="fe fe-trash-2 me-1"></i>حذف الأجهزة غير النشطة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function initUserDevicesCountup(root) {
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

initUserDevicesCountup();

function toggleSelectAll(source) {
    document.querySelectorAll('.device-checkbox').forEach(function (checkbox) {
        checkbox.checked = source.checked;
    });
    updateBulkActionBar();
}

function selectAll() {
    document.querySelectorAll('.device-checkbox').forEach(function (checkbox) {
        checkbox.checked = true;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) selectAllCheckbox.checked = true;
    updateBulkActionBar();
}

function deselectAll() {
    document.querySelectorAll('.device-checkbox').forEach(function (checkbox) {
        checkbox.checked = false;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const count = document.querySelectorAll('.device-checkbox:checked').length;
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) selectedCount.textContent = count;
    if (bulkActionBar) bulkActionBar.classList.toggle('d-none', count === 0);
}

function bulkDeleteSelected() {
    const checkboxes = document.querySelectorAll('.device-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('يرجى تحديد جهاز واحد على الأقل');
        return;
    }
    if (!confirm('هل أنت متأكد من حذف ' + checkboxes.length + ' جهاز؟')) return;

    const form = document.getElementById('bulkDeleteForm');
    if (!form) return;

    form.querySelectorAll('input[name="device_ids[]"]').forEach(function (el) { el.remove(); });
    checkboxes.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'device_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    form.submit();
}

window.initUserDevicesTableHandlers = function () {
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
        document.querySelectorAll('.ud-quick-filter').forEach(function (btn) {
            btn.classList.toggle('active', (btn.dataset.status || '') === (status || ''));
        });
    }

    function initUserDevicesAjaxFilter() {
        const form = document.getElementById('userDevicesFilterForm');
        const tableContainer = document.getElementById('userDevicesTableContainer');
        const countBadge = document.getElementById('userDevicesCountBadge');
        const rangeInfo = document.getElementById('userDevicesRangeInfo');
        const statusSelect = document.getElementById('userDevicesStatus');
        const searchInput = document.getElementById('userDevicesSearch');
        const feedback = document.getElementById('userDevicesSearchFeedback');
        const resetBtn = document.getElementById('userDevicesResetBtn');
        const quickFilters = document.querySelectorAll('.ud-quick-filter');

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

                    if (typeof window.initUserDevicesTableHandlers === 'function') {
                        window.initUserDevicesTableHandlers();
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
        document.addEventListener('DOMContentLoaded', initUserDevicesAjaxFilter);
    } else {
        initUserDevicesAjaxFilter();
    }
})();
</script>
@endpush
