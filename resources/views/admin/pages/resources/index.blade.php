@extends('admin.layouts.master')

@section('page-title')
    مكتبة الموارد التعليمية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الموارد التعليمية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الموارد</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('resources.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة مورد جديد
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-folder fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الموارد</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalResources ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">ملف</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-download fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">التحميلات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalDownloads ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">تحميل</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-danger-transparent">
                                        <i class="fas fa-file-pdf fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">ملفات PDF</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $pdfCount ?? 0 }}</h4>
                                    <span class="badge bg-danger-transparent">ملف PDF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-hdd fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">الحجم الإجمالي</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ number_format(($totalSize ?? 0) / 1024 / 1024, 1) }}</h4>
                                    <span class="badge bg-info-transparent">MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('resources.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن مورد...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نوع الملف</label>
                                <select name="type" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="doc" {{ request('type') == 'doc' ? 'selected' : '' }}>DOC/DOCX</option>
                                    <option value="ppt" {{ request('type') == 'ppt' ? 'selected' : '' }}>PPT/PPTX</option>
                                    <option value="xls" {{ request('type') == 'xls' ? 'selected' : '' }}>XLS/XLSX</option>
                                    <option value="zip" {{ request('type') == 'zip' ? 'selected' : '' }}>ZIP/RAR</option>
                                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>أخرى</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses ?? [] as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('resources.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resources Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الموارد</div>
                </div>
                <div class="card-body">
                    @if($resources->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الملف</th>
                                        <th>النوع</th>
                                        <th>الكورس</th>
                                        <th>الحجم</th>
                                        <th>التحميلات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resources as $resource)
                                        @php
                                            $extension = strtolower(pathinfo($resource->file_path, PATHINFO_EXTENSION));
                                            $iconClass = 'fas fa-file';
                                            $iconColor = 'primary';

                                            if (in_array($extension, ['pdf'])) {
                                                $iconClass = 'fas fa-file-pdf';
                                                $iconColor = 'danger';
                                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                                $iconClass = 'fas fa-file-word';
                                                $iconColor = 'primary';
                                            } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                                $iconClass = 'fas fa-file-powerpoint';
                                                $iconColor = 'warning';
                                            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                $iconClass = 'fas fa-file-excel';
                                                $iconColor = 'success';
                                            } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
                                                $iconClass = 'fas fa-file-archive';
                                                $iconColor = 'secondary';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-{{ $iconColor }}-transparent me-2">
                                                        <i class="{{ $iconClass }}"></i>
                                                    </span>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ Str::limit($resource->title, 40) }}</h6>
                                                        @if($resource->description)
                                                            <small class="text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $iconColor }}-transparent text-uppercase">
                                                    {{ $extension }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($resource->course)
                                                    <a href="{{ route('courses.show', $resource->course_id) }}">
                                                        {{ Str::limit($resource->course->title, 30) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    <i class="fas fa-hdd me-1"></i>
                                                    {{ number_format($resource->file_size / 1024, 1) }} KB
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-transparent">
                                                    <i class="fas fa-download me-1"></i>
                                                    {{ $resource->downloads_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ asset('storage/' . $resource->file_path) }}"
                                                       class="btn btn-sm btn-success"
                                                       title="تحميل"
                                                       download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="{{ route('resources.edit', $resource->id) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="deleteResource({{ $resource->id }}, '{{ e(Str::limit($resource->title, 50)) }}')"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $resources->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد موارد</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Resource Modal -->
    <div class="modal fade" id="deleteResourceModal" tabindex="-1" aria-labelledby="deleteResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                            <i class="fas fa-trash-alt fs-24 text-danger"></i>
                        </div>
                        <h5 class="mb-2" id="deleteResourceModalLabel">حذف المورد</h5>
                        <p class="text-muted mb-0" id="deleteResourceMessage">هل أنت متأكد من حذف هذا المورد؟</p>
                        <p class="text-danger small mt-2 mb-0">لن يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteResource">
                        <i class="fas fa-trash me-2"></i>حذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div class="avatar avatar-xl bg-success-transparent mx-auto mb-3" id="alertIconContainer">
                            <i class="fas fa-check-circle fs-24 text-success" id="alertIcon"></i>
                        </div>
                        <h5 class="mb-2" id="alertModalLabel">نجح</h5>
                        <p class="text-muted mb-0" id="alertMessage">تمت العملية بنجاح</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>حسناً
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    let currentResourceId = null;

    // Delete Resource
    function deleteResource(resourceId, resourceTitle) {
        currentResourceId = resourceId;
        
        const messageEl = document.getElementById('deleteResourceMessage');
        if (messageEl) {
            messageEl.innerHTML = `هل أنت متأكد من حذف المورد<br><strong>${resourceTitle}</strong>؟`;
        }
        
        const modalElement = document.getElementById('deleteResourceModal');
        if (!modalElement) {
            console.error('deleteResourceModal element not found');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }

    // Show Alert Modal
    function showAlert(type, message) {
        const modal = new bootstrap.Modal(document.getElementById('alertModal'));
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const label = document.getElementById('alertModalLabel');
        const messageEl = document.getElementById('alertMessage');
        
        if (type === 'success') {
            iconContainer.className = 'avatar avatar-xl bg-success-transparent mx-auto mb-3';
            icon.className = 'fas fa-check-circle fs-24 text-success';
            label.textContent = 'نجح';
        } else {
            iconContainer.className = 'avatar avatar-xl bg-danger-transparent mx-auto mb-3';
            icon.className = 'fas fa-exclamation-circle fs-24 text-danger';
            label.textContent = 'خطأ';
        }
        
        messageEl.textContent = message;
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Confirm Delete Resource
        const confirmBtn = document.getElementById('confirmDeleteResource');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!currentResourceId) return;
                
                const modalElement = document.getElementById('deleteResourceModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                
                fetch(`{{ url('/admin/resources') }}/${currentResourceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        location.reload();
                        return null;
                    }
                })
                .then(data => {
                    if (data) {
                        if (data.success) {
                            showAlert('success', data.message || 'تم حذف المورد بنجاح');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message || 'حدث خطأ أثناء الحذف');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'حدث خطأ أثناء الحذف: ' + error.message);
                });
            });
        }
    });
</script>
@stop
