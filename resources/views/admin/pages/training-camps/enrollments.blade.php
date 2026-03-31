@extends('admin.layouts.master')

@section('page-title')
    طلبات التسجيل في المعسكرات
@stop

@section('css')
<style>
    .enrollment-card {
        transition: all 0.3s ease;
    }
    .enrollment-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">طلبات التسجيل في المعسكرات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">طلبات التسجيل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form id="enrollmentsFilterForm" action="{{ route('training-camps.enrollments') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input id="enrollmentsSearchInput" type="text" name="search" class="form-control"
                                   placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">حالة الدفع</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="camp_id" class="form-select">
                                <option value="">جميع المعسكرات</option>
                                @foreach($camps as $camp)
                                    <option value="{{ $camp->id }}" {{ request('camp_id') == $camp->id ? 'selected' : '' }}>
                                        {{ $camp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>بحث
                            </button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="clearEnrollmentsFilters" class="btn btn-light w-100">
                                <i class="fas fa-eraser me-1"></i>مسح
                            </button>
                        </div>
                    </form>
                    <small id="enrollmentsFilterFeedback" class="text-muted d-block mt-2"></small>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الطلبات</div>
                </div>
                <div class="card-body">
                    <div id="enrollmentsTableContainer">
                        @include('admin.pages.training-camps.partials.enrollments-table', ['enrollments' => $enrollments])
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4 px-5">
                    <div class="mb-4" id="statusIconContainer">
                        <div class="avatar avatar-xl bg-warning-transparent mx-auto mb-3" id="statusIconWrapper">
                            <i class="fas fa-clock fs-24 text-warning" id="statusIcon"></i>
                        </div>
                    </div>
                    <h5 class="mb-3" id="changeStatusModalLabel">تغيير حالة التسجيل</h5>
                    <p class="text-muted mb-4" id="statusMessage">
                        هل أنت متأكد من تغيير الحالة إلى <strong id="statusLabelText">قيد الانتظار</strong>؟
                    </p>
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>يمكنك تغيير الحالة في أي وقت</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn" id="confirmStatusChange" style="min-width: 120px;">
                        <i class="fas fa-check me-2"></i>تأكيد التغيير
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.25rem;
    }
    .btn-xs i {
        font-size: 0.875rem;
    }
    #statusIconWrapper {
        transition: all 0.3s ease;
    }
</style>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Status configuration
    const statusConfig = {
        'pending': {
            icon: 'fa-clock',
            label: 'قيد الانتظار',
            color: 'warning',
            bgClass: 'bg-warning-transparent',
            textClass: 'text-warning',
            btnClass: 'btn-warning'
        },
        'approved': {
            icon: 'fa-check-circle',
            label: 'مقبول',
            color: 'success',
            bgClass: 'bg-success-transparent',
            textClass: 'text-success',
            btnClass: 'btn-success'
        },
        'rejected': {
            icon: 'fa-times-circle',
            label: 'مرفوض',
            color: 'danger',
            bgClass: 'bg-danger-transparent',
            textClass: 'text-danger',
            btnClass: 'btn-danger'
        },
        'cancelled': {
            icon: 'fa-ban',
            label: 'ملغي',
            color: 'secondary',
            bgClass: 'bg-secondary-transparent',
            textClass: 'text-secondary',
            btnClass: 'btn-secondary'
        }
    };

    let currentEnrollmentId = null;
    let currentNewStatus = null;

    // Handle modal show
    const changeStatusModal = document.getElementById('changeStatusModal');
    if (changeStatusModal) {
        changeStatusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            currentEnrollmentId = button.getAttribute('data-enrollment-id');
            currentNewStatus = button.getAttribute('data-new-status');
            const statusLabel = button.getAttribute('data-status-label');
            const statusIcon = button.getAttribute('data-status-icon');
            const statusColor = button.getAttribute('data-status-color');

            // Update modal content
            const config = statusConfig[currentNewStatus];
            if (config) {
                // Update icon
                const iconWrapper = document.getElementById('statusIconWrapper');
                const icon = document.getElementById('statusIcon');
                iconWrapper.className = `avatar avatar-xl ${config.bgClass} mx-auto mb-3`;
                icon.className = `fas ${config.icon} fs-24 ${config.textClass}`;

                // Update label
                document.getElementById('statusLabelText').textContent = config.label;

                // Update confirm button
                const confirmBtn = document.getElementById('confirmStatusChange');
                confirmBtn.className = `btn ${config.btnClass}`;
            }
        });
    }

    // Handle confirm button click
    document.getElementById('confirmStatusChange')?.addEventListener('click', function() {
        if (!currentEnrollmentId || !currentNewStatus) {
            return;
        }

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/training-camps-enrollments') }}/${currentEnrollmentId}/update-status`;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Add status
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = currentNewStatus;
        form.appendChild(statusInput);

        document.body.appendChild(form);
        form.submit();
    });

    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initEnrollmentsAjaxFilters() {
        const form = document.getElementById('enrollmentsFilterForm');
        const searchInput = document.getElementById('enrollmentsSearchInput');
        const clearFiltersBtn = document.getElementById('clearEnrollmentsFilters');
        const tableContainer = document.getElementById('enrollmentsTableContainer');
        const feedback = document.getElementById('enrollmentsFilterFeedback');

        if (!form || !tableContainer) {
            return;
        }

        let currentController = null;
        let lastRequestUrl = null;

        const serializeForm = function() {
            const formData = new FormData(form);
            const searchValue = (formData.get('search') || '').toString().trim();
            formData.set('search', searchValue);
            return new URLSearchParams(formData).toString();
        };

        const setFeedback = function(message) {
            if (feedback) {
                feedback.textContent = message;
            }
        };

        const renderLoading = function(isLoading) {
            tableContainer.style.opacity = isLoading ? '0.6' : '1';
            tableContainer.style.pointerEvents = isLoading ? 'none' : 'auto';
        };

        const fetchAndRender = function(url) {
            if (url === lastRequestUrl) {
                return;
            }
            lastRequestUrl = url;

            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();
            renderLoading(true);
            setFeedback('جاري تحديث النتائج...');

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to fetch enrollments');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (!data || typeof data.table_html !== 'string') {
                        throw new Error('Invalid AJAX response');
                    }

                    tableContainer.innerHTML = data.table_html;
                    initCopyStudentEmailButtons();
                    setFeedback('تم تحديث النتائج');
                })
                .catch(function(error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    console.error(error);
                    setFeedback('تعذر تحديث النتائج، حاول مرة أخرى.');
                })
                .finally(function() {
                    renderLoading(false);
                });
        };

        const requestFromForm = function() {
            const queryString = serializeForm();
            const baseUrl = form.getAttribute('action');
            const requestUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            fetchAndRender(requestUrl);
        };

        const debouncedSearch = debounce(requestFromForm, 350);

        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }

        form.querySelectorAll('select[name="status"], select[name="payment_status"], select[name="camp_id"]').forEach(function(selectEl) {
            selectEl.addEventListener('change', requestFromForm);
        });

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            requestFromForm();
        });

        tableContainer.addEventListener('click', function(event) {
            const paginationLink = event.target.closest('.pagination a');
            if (!paginationLink) {
                return;
            }

            event.preventDefault();
            fetchAndRender(paginationLink.href);
        });

        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                form.reset();
                if (searchInput) {
                    searchInput.value = '';
                }
                lastRequestUrl = null;
                requestFromForm();
            });
        }
    }

    function initCopyStudentEmailButtons() {
        document.querySelectorAll('.copy-student-email-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const email = btn.getAttribute('data-email');
                if (!email) {
                    return;
                }

                navigator.clipboard.writeText(email).then(function() {
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check text-success"></i>';
                    setTimeout(function() {
                        btn.innerHTML = originalHtml;
                    }, 1500);
                }).catch(function() {
                    console.error('Failed to copy student email');
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initEnrollmentsAjaxFilters();
            initCopyStudentEmailButtons();
        });
    } else {
        initEnrollmentsAjaxFilters();
        initCopyStudentEmailButtons();
    }
</script>
@stop
