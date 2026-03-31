@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المعسكر: {{ $camp->name }}
@stop

@section('css')
<style>
    .info-card {
        border-left: 4px solid #0d6efd;
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .camp-image-large {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.2;
        border-radius: 0.25rem;
    }
    .btn-xs i {
        font-size: 0.875rem;
    }
    .camp-quick-info .kv-item small {
        display: block;
        color: #6c757d;
        font-size: 0.75rem;
    }
    .camp-quick-info .kv-item strong {
        font-size: 0.9rem;
    }
    .compact-side-card .card-body {
        padding: 0.75rem 1rem;
    }
    .compact-side-card .meta-row {
        margin-bottom: 0.55rem;
    }
    .compact-side-card .meta-row small {
        font-size: 0.72rem;
        color: #6c757d;
        display: block;
    }
    .compact-side-card .meta-row p {
        margin-bottom: 0;
        font-size: 0.86rem;
    }
</style>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل المعسكر</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $camp->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('training-camps.edit', $camp->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">المشاركين الحاليين</p>
                                    <h3 class="mb-0">{{ $camp->current_participants }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-primary-transparent">
                                        <i class="fas fa-users fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">الحد الأقصى</p>
                                    <h3 class="mb-0">{{ $camp->max_participants ?? 'غير محدد' }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-success-transparent">
                                        <i class="fas fa-user-check fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">المدة</p>
                                    <h3 class="mb-0">{{ $camp->duration_days }} يوم</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-info-transparent">
                                        <i class="fas fa-calendar fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-fill">
                                    <p class="mb-1 text-muted">السعر</p>
                                    <h3 class="mb-0">${{ number_format($camp->price, 2) }}</h3>
                                </div>
                                <div class="ms-3">
                                    <span class="avatar avatar-lg bg-warning-transparent">
                                        <i class="fas fa-dollar-sign fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-9">
                    <!-- إدارة الأعضاء -->
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                إدارة الأعضاء (<span id="enrollments-count">{{ $camp->enrollments_count }}</span>)
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('training-camps.enrollments.create-individual', $camp->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user me-1"></i>إضافة فردي
                                </a>
                                <a href="{{ route('training-camps.enrollments.create-bulk', $camp->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-users me-1"></i>إضافة من الكروبات
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="mb-3">
                                <form id="enrollments-filter-form" class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" name="search" id="filter-search" class="form-control form-control-sm"
                                               placeholder="بحث بالاسم أو البريد..." value="">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" id="filter-status" class="form-select form-select-sm">
                                            <option value="">جميع الحالات</option>
                                            <option value="pending">قيد الانتظار</option>
                                            <option value="approved">مقبول</option>
                                            <option value="rejected">مرفوض</option>
                                            <option value="cancelled">ملغي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="payment_status" id="filter-payment-status" class="form-select form-select-sm">
                                            <option value="">حالة الدفع</option>
                                            <option value="unpaid">غير مدفوع</option>
                                            <option value="paid">مدفوع</option>
                                            <option value="refunded">مسترد</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="loadEnrollments()">
                                            <i class="fas fa-search me-1"></i>بحث
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Enrollments Table -->
                            <div id="enrollments-table-container">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات المعسكر (مختصرة) -->
                    <div class="card custom-card info-card camp-quick-info mt-3">
                        <div class="card-header py-2">
                            <div class="card-title mb-0">معلومات المعسكر</div>
                        </div>
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="kv-item">
                                    <small>المعسكر</small>
                                    <strong>{{ $camp->name }}</strong>
                                </div>
                                <div class="kv-item">
                                    <small>المدرب</small>
                                    <strong>{{ $camp->instructor_name ?? '-' }}</strong>
                                </div>
                                <div class="kv-item">
                                    <small>البداية</small>
                                    <strong>{{ $camp->start_date->format('Y-m-d') }}</strong>
                                </div>
                                <div class="kv-item">
                                    <small>النهاية</small>
                                    <strong>{{ $camp->end_date->format('Y-m-d') }}</strong>
                                </div>
                                <div class="kv-item">
                                    <small>الحالة</small>
                                    @if($camp->isOngoing())
                                        <span class="badge bg-success">جاري الآن</span>
                                    @elseif($camp->hasEnded())
                                        <span class="badge bg-secondary">منتهي</span>
                                    @else
                                        <span class="badge bg-info">قادم</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- معلومات إضافية -->
                <div class="col-xl-3">
                    <div class="card custom-card compact-side-card">
                        <div class="card-header">
                            <div class="card-title">معلومات إضافية</div>
                        </div>
                        <div class="card-body">
                            <div class="meta-row">
                                <small class="text-muted">المعرف (Slug)</small>
                                <p class="mb-0"><code>{{ $camp->slug }}</code></p>
                            </div>

                            <div class="meta-row">
                                <small class="text-muted">الترتيب</small>
                                <p class="mb-0">{{ $camp->order }}</p>
                            </div>

                            <div class="meta-row">
                                <small class="text-muted">تاريخ الإنشاء</small>
                                <p class="mb-0">{{ $camp->created_at->format('Y-m-d H:i') }}</p>
                            </div>

                            <div class="meta-row">
                                <small class="text-muted">آخر تحديث</small>
                                <p class="mb-0">{{ $camp->updated_at->format('Y-m-d H:i') }}</p>
                            </div>

                            @if($camp->max_participants)
                                <div class="meta-row">
                                    <small class="text-muted">المقاعد المتبقية</small>
                                    <p class="mb-0">
                                        <strong>{{ $camp->availableSeats() }}</strong> من {{ $camp->max_participants }}
                                    </p>
                                    @if($camp->isFull())
                                        <span class="badge bg-danger mt-1">المعسكر ممتلئ</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- إجراءات سريعة -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إجراءات سريعة</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('training-camps.edit', $camp->id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>تعديل المعسكر
                                </a>

                                <a href="{{ route('training-camps.index') }}" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>العودة للقائمة
                                </a>

                                <form action="{{ route('training-camps.destroy', $camp->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المعسكر؟\nسيتم حذف جميع التسجيلات المرتبطة به!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف المعسكر
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    const campId = {{ $camp->id }};
    const baseUrl = '{{ route("training-camps.enrollments.index", $camp->id) }}';
    const storeUrl = '{{ route("training-camps.enrollments.store", $camp->id) }}';
    const showUrlTemplate = '{{ route("training-camps.enrollments.show", [$camp->id, ":id"]) }}';
    const updateStatusUrlTemplate = '{{ route("training-camps.enrollments.update-status", [$camp->id, ":id"]) }}';
    const destroyUrlTemplate = '{{ route("training-camps.enrollments.destroy", [$camp->id, ":id"]) }}';
    let currentPage = 1;
    let currentFilterController = null;

    // Status configuration
    const statusConfig = {
        'pending': { icon: 'fa-clock', label: 'قيد الانتظار', color: 'warning', badgeClass: 'bg-warning text-dark' },
        'approved': { icon: 'fa-check-circle', label: 'مقبول', color: 'success', badgeClass: 'bg-success' },
        'rejected': { icon: 'fa-times-circle', label: 'مرفوض', color: 'danger', badgeClass: 'bg-danger' },
        'cancelled': { icon: 'fa-ban', label: 'ملغي', color: 'secondary', badgeClass: 'bg-secondary' }
    };

    const paymentStatusConfig = {
        'unpaid': { label: 'غير مدفوع', badgeClass: 'bg-warning text-dark' },
        'paid': { label: 'مدفوع', badgeClass: 'bg-success' },
        'refunded': { label: 'مسترد', badgeClass: 'bg-secondary' }
    };

    // Load enrollments
    function loadEnrollments(page = 1) {
        currentPage = page;
        const form = document.getElementById('enrollments-filter-form');
        const formData = new FormData(form);
        formData.append('page', page);

        const url = new URL(baseUrl);
        for (const [key, value] of formData.entries()) {
            if (value) url.searchParams.append(key, value);
        }

        if (currentFilterController) {
            currentFilterController.abort();
        }
        currentFilterController = new AbortController();

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: currentFilterController.signal
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderEnrollmentsTable(data.enrollments);
                if (data.camp) {
                    updateCampStats(data.camp);
                }
            } else {
                toastr.error(data.message || 'حدث خطأ أثناء تحميل البيانات');
            }
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                return;
            }
            console.error('Error:', error);
            toastr.error('حدث خطأ أثناء تحميل البيانات');
        });
    }

    // Render enrollments table
    function renderEnrollmentsTable(enrollments) {
        const container = document.getElementById('enrollments-table-container');
        
        if (enrollments.data.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="fas fa-user-slash fa-3x mb-3 d-block"></i>
                    <h5>لا يوجد أعضاء</h5>
                </div>
            `;
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ التسجيل</th>
                            <th>الحالة</th>
                            <th>حالة الدفع</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        enrollments.data.forEach((enrollment, index) => {
            const status = statusConfig[enrollment.status] || statusConfig.pending;
            const paymentStatus = paymentStatusConfig[enrollment.payment_status] || paymentStatusConfig.unpaid;
            const rowNum = enrollments.from + index;

            html += `
                <tr id="enrollment-row-${enrollment.id}">
                    <td>${rowNum}</td>
                    <td>
                        <div>
                            <strong>${enrollment.student.name}</strong>
                        </div>
                    </td>
                    <td>${enrollment.student.email}</td>
                    <td><small>${new Date(enrollment.enrollment_date).toLocaleDateString('ar-SA')}</small></td>
                    <td>
                        <span class="badge ${status.badgeClass}">${status.label}</span>
                    </td>
                    <td>
                        <span class="badge ${paymentStatus.badgeClass}">${paymentStatus.label}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-xs btn-info" onclick="viewEnrollmentDetails(${enrollment.id})" title="عرض التفاصيل">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${Object.keys(statusConfig).filter(s => s !== enrollment.status).map(status => {
                                const config = statusConfig[status];
                                return `<button type="button" class="btn btn-xs btn-${config.color}" onclick="updateEnrollmentStatus(${enrollment.id}, '${status}')" title="تغيير إلى: ${config.label}">
                                    <i class="fas ${config.icon}"></i>
                                </button>`;
                            }).join('')}
                            <button type="button" class="btn btn-xs btn-danger" onclick="deleteEnrollment(${enrollment.id})" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        // Add pagination
        if (enrollments.last_page > 1) {
            html += `
                <div class="d-flex justify-content-center mt-4">
                    <nav>
                        <ul class="pagination">
                            ${enrollments.current_page > 1 ? `
                                <li class="page-item">
                                    <a class="page-link" href="#" onclick="loadEnrollments(${enrollments.current_page - 1}); return false;">السابق</a>
                                </li>
                            ` : ''}
                            ${Array.from({length: enrollments.last_page}, (_, i) => i + 1).map(pageNum => {
                                if (pageNum === enrollments.current_page) {
                                    return `<li class="page-item active"><span class="page-link">${pageNum}</span></li>`;
                                } else if (pageNum === 1 || pageNum === enrollments.last_page || (pageNum >= enrollments.current_page - 2 && pageNum <= enrollments.current_page + 2)) {
                                    return `<li class="page-item"><a class="page-link" href="#" onclick="loadEnrollments(${pageNum}); return false;">${pageNum}</a></li>`;
                                } else if (pageNum === enrollments.current_page - 3 || pageNum === enrollments.current_page + 3) {
                                    return `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                }
                                return '';
                            }).join('')}
                            ${enrollments.current_page < enrollments.last_page ? `
                                <li class="page-item">
                                    <a class="page-link" href="#" onclick="loadEnrollments(${enrollments.current_page + 1}); return false;">التالي</a>
                                </li>
                            ` : ''}
                        </ul>
                    </nav>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    // Update camp stats
    function updateCampStats(camp) {
        document.getElementById('enrollments-count').textContent = camp.enrollments_count || 0;
        // Update current participants if needed
        const participantsCard = document.querySelector('.stat-card:has(.fa-users) h3');
        if (participantsCard && camp.current_participants !== undefined) {
            participantsCard.textContent = camp.current_participants;
        }
    }

    // View enrollment details
    function viewEnrollmentDetails(enrollmentId) {
        fetch(showUrlTemplate.replace(':id', enrollmentId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const enrollment = data.enrollment;
                const status = statusConfig[enrollment.status] || statusConfig.pending;
                const paymentStatus = paymentStatusConfig[enrollment.payment_status] || paymentStatusConfig.unpaid;

                document.getElementById('enrollment-details-name').textContent = enrollment.student.name;
                document.getElementById('enrollment-details-email').textContent = enrollment.student.email;
                document.getElementById('enrollment-details-status').innerHTML = `<span class="badge ${status.badgeClass}">${status.label}</span>`;
                document.getElementById('enrollment-details-payment-status').innerHTML = `<span class="badge ${paymentStatus.badgeClass}">${paymentStatus.label}</span>`;
                document.getElementById('enrollment-details-date').textContent = new Date(enrollment.enrollment_date).toLocaleDateString('ar-SA');
                document.getElementById('enrollment-details-notes').textContent = enrollment.notes || '-';
                
                const modal = new bootstrap.Modal(document.getElementById('enrollmentDetailsModal'));
                modal.show();
            } else {
                toastr.error(data.message || 'حدث خطأ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('حدث خطأ أثناء تحميل التفاصيل');
        });
    }

    // Update enrollment status
    function updateEnrollmentStatus(enrollmentId, newStatus) {
        const config = statusConfig[newStatus];
        if (!confirm(`هل أنت متأكد من تغيير الحالة إلى: ${config.label}؟`)) {
            return;
        }

        fetch(updateStatusUrlTemplate.replace(':id', enrollmentId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                loadEnrollments(currentPage);
                if (data.camp) {
                    updateCampStats(data.camp);
                }
            } else {
                toastr.error(data.message || 'حدث خطأ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('حدث خطأ أثناء تحديث الحالة');
        });
    }

    let currentDeleteEnrollmentId = null;

    // Delete enrollment - open modal
    function deleteEnrollment(enrollmentId) {
        currentDeleteEnrollmentId = enrollmentId;
        const modal = new bootstrap.Modal(document.getElementById('deleteEnrollmentModal'));
        modal.show();
    }

    // Confirm delete enrollment
    function confirmDeleteEnrollment() {
        if (!currentDeleteEnrollmentId) {
            return;
        }

        const enrollmentId = currentDeleteEnrollmentId;
        currentDeleteEnrollmentId = null;

        fetch(destroyUrlTemplate.replace(':id', enrollmentId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEnrollmentModal'));
            modal.hide();
            
            if (data.success) {
                toastr.success(data.message);
                loadEnrollments(currentPage);
                if (data.camp) {
                    updateCampStats(data.camp);
                }
            } else {
                toastr.error(data.message || 'حدث خطأ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEnrollmentModal'));
            modal.hide();
            toastr.error('حدث خطأ أثناء الحذف');
        });
    }


    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    const debouncedLoadEnrollments = debounce(function() {
        loadEnrollments(1);
    }, 350);

    // Filter event handlers
    document.getElementById('filter-search')?.addEventListener('input', function() {
        debouncedLoadEnrollments();
    });
    document.getElementById('filter-search')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') e.preventDefault();
    });
    document.getElementById('filter-status')?.addEventListener('change', function() {
        loadEnrollments(1);
    });
    document.getElementById('filter-payment-status')?.addEventListener('change', function() {
        loadEnrollments(1);
    });

    // Load enrollments on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadEnrollments(1);
    });
</script>

<!-- Enrollment Details Modal -->
<div class="modal fade" id="enrollmentDetailsModal" tabindex="-1" aria-labelledby="enrollmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollmentDetailsModalLabel">تفاصيل العضو</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>الاسم:</strong>
                    <p id="enrollment-details-name">-</p>
                </div>
                <div class="mb-3">
                    <strong>البريد الإلكتروني:</strong>
                    <p id="enrollment-details-email">-</p>
                </div>
                <div class="mb-3">
                    <strong>الحالة:</strong>
                    <p id="enrollment-details-status">-</p>
                </div>
                <div class="mb-3">
                    <strong>حالة الدفع:</strong>
                    <p id="enrollment-details-payment-status">-</p>
                </div>
                <div class="mb-3">
                    <strong>تاريخ التسجيل:</strong>
                    <p id="enrollment-details-date">-</p>
                </div>
                <div class="mb-3">
                    <strong>ملاحظات:</strong>
                    <p id="enrollment-details-notes">-</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Enrollment Modal -->
<div class="modal fade" id="deleteEnrollmentModal" tabindex="-1" aria-labelledby="deleteEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-5">
                <div class="mb-4">
                    <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                        <i class="fas fa-exclamation-triangle fs-24 text-danger"></i>
                    </div>
                </div>
                <h5 class="mb-3">حذف العضو</h5>
                <p class="text-muted mb-4">
                    هل أنت متأكد من حذف هذا العضو من المعسكر؟
                    <br>
                    <small class="text-muted">لا يمكن التراجع عن هذا الإجراء</small>
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>إلغاء
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteEnrollment()">
                    <i class="fas fa-trash me-2"></i>حذف
                </button>
            </div>
        </div>
    </div>
</div>
@stop
