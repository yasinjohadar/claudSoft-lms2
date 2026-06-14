@extends('admin.layouts.master')

@section('page-title')
    جلسات المستخدمين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">جلسات المستخدمين</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">جلسات المستخدمين</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('admin.user-sessions.statistics') }}" class="btn btn-primary btn-wave">
                    <i class="fas fa-chart-bar me-1"></i>الإحصائيات
                </a>
                <a href="{{ route('admin.user-sessions.active') }}" class="btn btn-success btn-wave">
                    <i class="fas fa-circle me-1"></i>الجلسات النشطة
                </a>
                <button type="button" class="btn btn-danger btn-wave" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                    <i class="fas fa-trash-alt me-1"></i>حذف الكل
                </button>
                <button type="button" class="btn btn-warning btn-wave" data-bs-toggle="modal" data-bs-target="#deleteCompletedModal">
                    <i class="fas fa-check-double me-1"></i>حذف المكتملة
                </button>
                <button type="button" class="btn btn-secondary btn-wave" data-bs-toggle="modal" data-bs-target="#deleteDisconnectedModal">
                    <i class="fas fa-plug me-1"></i>حذف المنفصلة
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">إجمالي الجلسات</p>
                                <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fas fa-list fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الجلسات النشطة</p>
                                <h4 class="mb-0 text-success">{{ number_format($stats['active']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-success-transparent rounded-circle">
                                    <i class="fas fa-circle fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">الجلسات المكتملة</p>
                                <h4 class="mb-0 text-info">{{ number_format($stats['completed']) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-info-transparent rounded-circle">
                                    <i class="fas fa-check-circle fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">متوسط المدة</p>
                                <h4 class="mb-0">
                                    @if($stats['avg_duration'])
                                        {{ gmdate('H:i:s', (int)$stats['avg_duration']) }}
                                    @else
                                        -
                                    @endif
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-md bg-warning-transparent rounded-circle">
                                    <i class="fas fa-clock fs-18"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-filter me-2"></i>الفلاتر
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.user-sessions.index') }}" id="userSessionsFilterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label" for="userSessionsSearch">البحث</label>
                            <input type="text" name="search" id="userSessionsSearch" class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="اسم المستخدم، البريد، IP...">
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <label class="form-label" for="userSessionsStatus">الحالة</label>
                            <select name="status" id="userSessionsStatus" class="form-select">
                                <option value="">الكل</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشطة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="disconnected" {{ request('status') == 'disconnected' ? 'selected' : '' }}>منفصلة</option>
                                <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>انتهت</option>
                            </select>
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <label class="form-label" for="userSessionsDateFrom">من تاريخ</label>
                            <input type="date" name="date_from" id="userSessionsDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i>بحث
                                </button>
                                <button type="button" id="userSessionsResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-rotate-right me-1"></i>إعادة تعيين
                                </button>
                                <span id="userSessionsSearchFeedback" class="fs-12 text-muted"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div class="card d-none" id="bulkActionBar">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="me-3 fw-semibold">
                            <span id="selectedCount">0</span> جلسة محددة
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-wave" onclick="selectAll()">
                                <i class="fas fa-check-double me-1"></i>تحديد الكل
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-wave" onclick="deselectAll()">
                                <i class="fas fa-times me-1"></i>إلغاء التحديد
                            </button>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-danger btn-wave" onclick="bulkDeleteSelected()">
                            <i class="fas fa-trash-alt me-1"></i>حذف المحدد
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    قائمة الجلسات
                    <span class="badge bg-primary-transparent text-primary ms-1" id="userSessionsCountBadge">{{ $sessions->total() }}</span>
                </h5>
            </div>
            <div class="card-body" id="userSessionsTableContainer">
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
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>تحذير: حذف جميع الجلسات</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>هذا الإجراء لا يمكن التراجع عنه!</strong>
                    </div>
                    <p>سيتم حذف <strong>جميع الجلسات</strong> المسجلة في النظام ({{ number_format($stats['total']) }} جلسة).</p>
                    <p class="mb-0">هل أنت متأكد من هذا الإجراء؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد تماماً؟ سيتم حذف جميع الجلسات نهائياً!')">
                        <i class="fas fa-trash-alt me-1"></i>نعم، حذف الكل
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
                <h5 class="modal-title"><i class="fas fa-check-double me-2"></i>حذف الجلسات المكتملة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-completed') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>حذف الجلسات المكتملة (التي انتهت بشكل طبيعي).</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">حذف الجلسات المكتملة أقدم من (بالأيام):</label>
                        <input type="number" name="days" class="form-control" value="0" min="0" max="365">
                        <small class="text-muted">اتركه 0 لحذف جميع الجلسات المكتملة، أو حدد عدد أيام لحذف الأقدم فقط</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('هل أنت متأكد من حذف الجلسات المكتملة؟')">
                        <i class="fas fa-trash-alt me-1"></i>حذف الجلسات المكتملة
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
                <h5 class="modal-title"><i class="fas fa-plug me-2"></i>حذف الجلسات المنفصلة والمنتهية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user-sessions.delete-disconnected') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>سيتم حذف جميع الجلسات التي حالتها:</p>
                    <ul>
                        <li><span class="badge bg-warning">منفصلة</span> (disconnected)</li>
                        <li><span class="badge bg-secondary">انتهت</span> (timeout)</li>
                    </ul>
                    <p class="mb-0">هل أنت متأكد من هذا الإجراء؟</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('هل أنت متأكد من حذف الجلسات المنفصلة والمنتهية؟')">
                        <i class="fas fa-trash-alt me-1"></i>حذف الجلسات المنفصلة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.session-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActionBar();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.session-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = true;
    }
    updateBulkActionBar();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.session-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const checkboxes = document.querySelectorAll('.session-checkbox:checked');
    const count = checkboxes.length;
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');

    if (selectedCount) {
        selectedCount.textContent = count;
    }

    if (bulkActionBar) {
        if (count > 0) {
            bulkActionBar.classList.remove('d-none');
        } else {
            bulkActionBar.classList.add('d-none');
        }
    }
}

function bulkDeleteSelected() {
    const checkboxes = document.querySelectorAll('.session-checkbox:checked');
    const count = checkboxes.length;

    if (count === 0) {
        alert('يرجى تحديد جلسة واحدة على الأقل');
        return;
    }

    if (!confirm(`هل أنت متأكد من حذف ${count} جلسة؟`)) {
        return;
    }

    const form = document.getElementById('bulkDeleteForm');
    if (!form) {
        return;
    }

    checkboxes.forEach(checkbox => {
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
            timer = setTimeout(function () {
                fn.apply(this, args);
            }, delay);
        };
    }

    function initUserSessionsAjaxFilter() {
        const form = document.getElementById('userSessionsFilterForm');
        const tableContainer = document.getElementById('userSessionsTableContainer');
        const countBadge = document.getElementById('userSessionsCountBadge');
        const searchInput = document.getElementById('userSessionsSearch');
        const feedback = document.getElementById('userSessionsSearchFeedback');
        const resetBtn = document.getElementById('userSessionsResetBtn');

        if (!form || !tableContainer) {
            return;
        }

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
            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();

            if (feedback) {
                feedback.textContent = 'جاري البحث...';
            }

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
                    if (!response.ok) {
                        throw new Error('فشل جلب النتائج');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!data || typeof data.table_html !== 'string') {
                        throw new Error('صيغة استجابة غير متوقعة');
                    }

                    tableContainer.innerHTML = data.table_html;

                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }

                    if (typeof window.initUserSessionsTableHandlers === 'function') {
                        window.initUserSessionsTableHandlers();
                    }

                    const queryString = url.includes('?') ? url.split('?')[1] : '';
                    updateBrowserUrl(queryString);

                    if (feedback) {
                        feedback.textContent = 'تم تحديث النتائج';
                    }
                })
                .catch(function (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    if (feedback) {
                        feedback.textContent = 'تعذر تحميل النتائج، حاول مرة أخرى.';
                    }
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

        form.querySelectorAll('select, input[type="date"]').forEach(function (field) {
            field.addEventListener('change', triggerSearch);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                if (feedback) {
                    feedback.textContent = '';
                }
                triggerSearch();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerSearch();
        });

        tableContainer.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('.pagination a');
            if (!paginationLink) {
                return;
            }

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
