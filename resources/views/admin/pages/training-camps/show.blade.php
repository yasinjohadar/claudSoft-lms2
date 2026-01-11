@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المعسكر: {{ $camp->name }}
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-right: 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
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
                <!-- معلومات المعسكر -->
                <div class="col-xl-8">
                    <div class="card custom-card info-card">
                        <div class="card-header">
                            <div class="card-title">معلومات المعسكر</div>
                        </div>
                        <div class="card-body">
                            @if($camp->image)
                                <img src="{{ asset('storage/' . $camp->image) }}"
                                     alt="{{ $camp->name }}"
                                     class="camp-image-large mb-4">
                            @endif

                            <h4 class="mb-3">{{ $camp->name }}</h4>

                            @if($camp->description)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">الوصف:</h6>
                                    <p class="text-muted">{{ $camp->description }}</p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">التصنيف:</h6>
                                    @if($camp->category)
                                        <span class="badge" style="background-color: {{ $camp->category->color }}">
                                            {{ $camp->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">المدرب:</h6>
                                    <p>{{ $camp->instructor_name ?? '-' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">الموقع:</h6>
                                    <p>{{ $camp->location ?? '-' }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">تاريخ البداية:</h6>
                                    <p>{{ $camp->start_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">تاريخ النهاية:</h6>
                                    <p>{{ $camp->end_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">الحالة:</h6>
                                    @if($camp->isOngoing())
                                        <span class="badge bg-success">جاري الآن</span>
                                    @elseif($camp->hasEnded())
                                        <span class="badge bg-secondary">منتهي</span>
                                    @else
                                        <span class="badge bg-info">قادم</span>
                                    @endif

                                    @if($camp->is_active)
                                        <span class="badge bg-success ms-1">نشط</span>
                                    @else
                                        <span class="badge bg-danger ms-1">معطل</span>
                                    @endif

                                    @if($camp->is_featured)
                                        <span class="badge bg-warning ms-1">
                                            <i class="fas fa-star me-1"></i>مميز
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- إدارة الأعضاء -->
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                إدارة الأعضاء (<span id="enrollments-count">{{ $camp->enrollments_count }}</span>)
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                                <i class="fas fa-plus me-1"></i>إضافة عضو جديد
                            </button>
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
                </div>

                <!-- معلومات إضافية -->
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات إضافية</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">المعرف (Slug)</small>
                                <p class="mb-0"><code>{{ $camp->slug }}</code></p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">الترتيب</small>
                                <p class="mb-0">{{ $camp->order }}</p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">تاريخ الإنشاء</small>
                                <p class="mb-0">{{ $camp->created_at->format('Y-m-d H:i') }}</p>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">آخر تحديث</small>
                                <p class="mb-0">{{ $camp->updated_at->format('Y-m-d H:i') }}</p>
                            </div>

                            @if($camp->max_participants)
                                <div class="mb-3">
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

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
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

    // Add enrollment
    function addEnrollment() {
        const form = document.getElementById('add-enrollment-form');
        const formData = new FormData(form);

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('addEnrollmentModal'));
                modal.hide();
                form.reset();
                loadEnrollments(1);
                if (data.camp) {
                    updateCampStats(data.camp);
                }
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        toastr.error(data.errors[key][0]);
                    });
                } else {
                    toastr.error(data.message || 'حدث خطأ');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('حدث خطأ أثناء الإضافة');
        });
    }

    // Filter event handlers
    document.getElementById('filter-search')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadEnrollments(1);
        }
    });

    // Load enrollments on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadEnrollments(1);
        initializeSelect2();
    });

    // Initialize Select2 for student search
    function initializeSelect2() {
        // Wait for DOM and jQuery to be ready
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Check if Select2 is loaded, if not load it
                if (typeof $.fn.select2 === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                    script.onload = function() {
                        setupSelect2($);
                    };
                    document.head.appendChild(script);
                } else {
                    setupSelect2($);
                }
            });
        } else {
            // Retry after a short delay
            setTimeout(initializeSelect2, 100);
        }
    }

    function setupSelect2($) {
        $('#student_id').select2({
                placeholder: 'ابحث عن طالب...',
                allowClear: true,
                dir: 'rtl',
                language: {
                    noResults: function() {
                        return 'لا توجد نتائج';
                    },
                    searching: function() {
                        return 'جاري البحث...';
                    }
                },
                ajax: {
                    url: `{{ route('training-camps.enrollments.search-students', $camp->id) }}`,
                    dataType: 'json',
                    delay: 300,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results || []
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });
    }

    // Load group students
    function loadGroupStudents(groupId) {
        if (!groupId) {
            document.getElementById('group-students-container').style.display = 'none';
            return;
        }

        const container = document.getElementById('group-students-container');
        const table = document.getElementById('group-students-table');
        const empty = document.getElementById('group-students-empty');
        
        container.style.display = 'block';
        table.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> جاري التحميل...</td></tr>';
        empty.style.display = 'none';

        fetch(`{{ route('training-camps.enrollments.group-students', [$camp->id, ':id']) }}`.replace(':id', groupId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.students.length > 0) {
                let html = '';
                data.students.forEach(student => {
                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" name="student_ids[]" value="${student.id}" class="group-student-checkbox">
                            </td>
                            <td>${student.name}</td>
                            <td>${student.email}</td>
                            <td>${student.phone}</td>
                        </tr>
                    `;
                });
                table.innerHTML = html;
                empty.style.display = 'none';
            } else {
                table.innerHTML = '';
                empty.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            table.innerHTML = '<tr><td colspan="4" class="text-center text-danger">حدث خطأ أثناء تحميل الطلاب</td></tr>';
            toastr.error('حدث خطأ أثناء تحميل طلاب الكروب');
        });
    }

    // Select all group students
    function selectAllGroupStudents() {
        document.querySelectorAll('.group-student-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.getElementById('select-all-students').checked = true;
    }

    // Deselect all group students
    function deselectAllGroupStudents() {
        document.querySelectorAll('.group-student-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('select-all-students').checked = false;
    }

    // Toggle all students
    function toggleAllStudents(checkbox) {
        document.querySelectorAll('.group-student-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    // Bulk add enrollments
    function bulkAddEnrollments() {
        const form = document.getElementById('bulk-enrollment-form');
        const formData = new FormData(form);
        
        // Get selected student IDs
        const selectedStudents = Array.from(document.querySelectorAll('.group-student-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedStudents.length === 0) {
            toastr.warning('يرجى اختيار طالب واحد على الأقل');
            return;
        }

        // Add student_ids to form data
        selectedStudents.forEach(id => {
            formData.append('student_ids[]', id);
        });

        fetch(`{{ route('training-camps.enrollments.bulk-store', $camp->id) }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = data.message;
                if (data.skipped_count > 0) {
                    message += ` (تم تخطي ${data.skipped_count} طالب مسجل مسبقاً)`;
                }
                toastr.success(message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('addEnrollmentModal'));
                modal.hide();
                form.reset();
                document.getElementById('group-students-container').style.display = 'none';
                document.getElementById('group_id').value = '';
                loadEnrollments(1);
                if (data.camp) {
                    updateCampStats(data.camp);
                }
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        toastr.error(data.errors[key][0]);
                    });
                } else {
                    toastr.error(data.message || 'حدث خطأ');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('حدث خطأ أثناء الإضافة');
        });
    }
</script>

<!-- Add Enrollment Modal -->
<div class="modal fade" id="addEnrollmentModal" tabindex="-1" aria-labelledby="addEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEnrollmentModalLabel">إضافة عضو جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="enrollmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual-pane" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>إضافة فردي
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups-pane" type="button" role="tab">
                            <i class="fas fa-users me-1"></i>إضافة من الكروبات
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="enrollmentTabContent">
                    <!-- Individual Tab -->
                    <div class="tab-pane fade show active" id="individual-pane" role="tabpanel">
                        <form id="add-enrollment-form">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id" class="form-select" required>
                                    <option value="">ابحث عن طالب...</option>
                                </select>
                                <small class="text-muted">ابحث بالاسم، البريد الإلكتروني، أو الهاتف</small>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">الحالة</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="approved">مقبول</option>
                                    <option value="rejected">مرفوض</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="payment_status" class="form-label">حالة الدفع</label>
                                <select name="payment_status" id="payment_status" class="form-select">
                                    <option value="unpaid">غير مدفوع</option>
                                    <option value="paid">مدفوع</option>
                                    <option value="refunded">مسترد</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">ملاحظات</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="button" class="btn btn-primary" onclick="addEnrollment()">إضافة</button>
                            </div>
                        </form>
                    </div>

                    <!-- Groups Tab -->
                    <div class="tab-pane fade" id="groups-pane" role="tabpanel">
                        <form id="bulk-enrollment-form">
                            <div class="mb-3">
                                <label for="group_id" class="form-label">اختر الكروب <span class="text-danger">*</span></label>
                                <select name="group_id" id="group_id" class="form-select" onchange="loadGroupStudents(this.value)">
                                    <option value="">اختر الكروب</option>
                                    @foreach($courseGroups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="group-students-container" style="display: none;">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0">الطلاب في الكروب</label>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllGroupStudents()">
                                            <i class="fas fa-check-double me-1"></i>تحديد الكل
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllGroupStudents()">
                                            <i class="fas fa-times me-1"></i>إلغاء التحديد
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="select-all-students" onchange="toggleAllStudents(this)">
                                                </th>
                                                <th>الاسم</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الهاتف</th>
                                            </tr>
                                        </thead>
                                        <tbody id="group-students-table">
                                            <!-- Students will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="group-students-empty" class="text-center text-muted py-4" style="display: none;">
                                    <i class="fas fa-users-slash fa-2x mb-2"></i>
                                    <p>لا يوجد طلاب متاحين في هذا الكروب</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bulk_status" class="form-label">الحالة</label>
                                <select name="status" id="bulk_status" class="form-select">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="approved">مقبول</option>
                                    <option value="rejected">مرفوض</option>
                                    <option value="cancelled">ملغي</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="bulk_payment_status" class="form-label">حالة الدفع</label>
                                <select name="payment_status" id="bulk_payment_status" class="form-select">
                                    <option value="unpaid">غير مدفوع</option>
                                    <option value="paid">مدفوع</option>
                                    <option value="refunded">مسترد</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="bulk_notes" class="form-label">ملاحظات</label>
                                <textarea name="notes" id="bulk_notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="button" class="btn btn-primary" onclick="bulkAddEnrollments()">
                                    <i class="fas fa-users me-1"></i>إضافة المحددين
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
