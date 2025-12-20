@extends('admin.layouts.master')

@section('page-title')
    إدارة الواجبات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الواجبات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الواجبات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة واجب جديد
                    </a>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('assignments.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث بعنوان الواجب..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        قائمة الواجبات ({{ $assignments->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان الواجب</th>
                                    <th>الكورس</th>
                                    <th>الدرس</th>
                                    <th>الدرجة القصوى</th>
                                    <th>موعد التسليم</th>
                                    <th>التسليمات</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <a href="{{ route('assignments.show', $assignment->id) }}"
                                                       class="fw-semibold">{{ $assignment->title }}</a>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user fs-10 me-1"></i>
                                                        {{ $assignment->creator->name ?? 'غير محدد' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent">
                                                {{ $assignment->course->title }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($assignment->lesson)
                                                <span class="badge bg-info-transparent">
                                                    {{ $assignment->lesson->title }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $assignment->max_grade }}</span>
                                        </td>
                                        <td>
                                            @if($assignment->due_date)
                                                <small>
                                                    {{ $assignment->due_date->format('Y-m-d H:i') }}
                                                    <br>
                                                    @if($assignment->isPastDue())
                                                        <span class="text-danger">
                                                            <i class="fas fa-clock"></i> منتهي
                                                        </span>
                                                    @else
                                                        <span class="text-success">
                                                            <i class="fas fa-clock"></i> نشط
                                                        </span>
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted">غير محدد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('assignments.show', $assignment->id) }}"
                                               class="badge bg-secondary-transparent">
                                                {{ $assignment->submissions->count() }} تسليم
                                            </a>
                                        </td>
                                        <td>
                                            @if($assignment->is_published)
                                                <span class="badge bg-success">منشور</span>
                                            @else
                                                <span class="badge bg-warning">مسودة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('assignments.show', $assignment->id) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('assignments.edit', $assignment->id) }}"
                                                   class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('assignments.destroy', $assignment->id) }}"
                                                      method="POST" class="d-inline assignment-delete-form"
                                                      id="delete-form-{{ $assignment->id }}"
                                                      data-assignment-title="{{ $assignment->title }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-assignment" title="حذف"
                                                            data-assignment-id="{{ $assignment->id }}"
                                                            data-assignment-title="{{ $assignment->title }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fs-48 text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد واجبات</p>
                                            <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                                                إضافة واجب جديد
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($assignments->hasPages())
                    <div class="card-footer">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Delete Assignment Confirmation Modal -->
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </span>
                    </div>
                    <h5 id="deleteAssignmentModalLabel" class="mb-2">تأكيد حذف الواجب</h5>
                    <p class="text-muted mb-4">
                        هل أنت متأكد من حذف الواجب
                        <strong id="delete-assignment-title"></strong>؟
                        <br>
                        سيتم حذف جميع البيانات المرتبطة بهذا الواجب ولا يمكن التراجع عن هذه العملية.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-assignment">
                            <i class="fas fa-trash me-1"></i> حذف نهائياً
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4" id="alert-icon">
                        <span class="avatar avatar-xl bg-success-transparent text-success rounded-circle">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </span>
                    </div>
                    <h5 id="alert-title" class="mb-2">نجح</h5>
                    <p class="text-muted mb-4" id="alert-message"></p>
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        موافق
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteForm = null;
    const modalElement = document.getElementById('deleteAssignmentModal');
    const alertModalElement = document.getElementById('alertModal');
    
    if (!modalElement) return;

    const deleteModal = new bootstrap.Modal(modalElement);
    const alertModal = new bootstrap.Modal(alertModalElement);
    const titleSpan = document.getElementById('delete-assignment-title');
    const confirmBtn = document.getElementById('confirm-delete-assignment');

    // Handle delete button click
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-assignment');
        if (!btn) return;

        e.preventDefault();

        const assignmentId = btn.getAttribute('data-assignment-id');
        const assignmentTitle = btn.getAttribute('data-assignment-title') || '';

        deleteForm = document.getElementById('delete-form-' + assignmentId);
        if (!deleteForm) return;

        if (titleSpan) {
            titleSpan.textContent = assignmentTitle;
        }

        deleteModal.show();
    });

    // Handle confirm delete
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!deleteForm) return;

            const formAction = deleteForm.getAttribute('action');
            const formData = new FormData(deleteForm);
            const csrfToken = formData.get('_token');

            fetch(formAction, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                deleteModal.hide();
                
                if (data.success) {
                    // Show success message
                    const alertIcon = document.getElementById('alert-icon');
                    const alertTitle = document.getElementById('alert-title');
                    const alertMessage = document.getElementById('alert-message');
                    
                    if (alertIcon) {
                        alertIcon.innerHTML = '<span class="avatar avatar-xl bg-success-transparent text-success rounded-circle"><i class="fas fa-check-circle fa-2x"></i></span>';
                    }
                    if (alertTitle) {
                        alertTitle.textContent = 'نجح';
                    }
                    if (alertMessage) {
                        alertMessage.textContent = data.message || 'تم حذف الواجب بنجاح';
                    }
                    
                    alertModal.show();
                    
                    // Remove the row after a short delay
                    setTimeout(() => {
                        const row = deleteForm.closest('tr');
                        if (row) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                // Reload page if no more rows
                                const tbody = row.closest('tbody');
                                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        } else {
                            location.reload();
                        }
                    }, 1500);
                } else {
                    // Show error message
                    const alertIcon = document.getElementById('alert-icon');
                    const alertTitle = document.getElementById('alert-title');
                    const alertMessage = document.getElementById('alert-message');
                    
                    if (alertIcon) {
                        alertIcon.innerHTML = '<span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle"><i class="fas fa-exclamation-circle fa-2x"></i></span>';
                    }
                    if (alertTitle) {
                        alertTitle.textContent = 'خطأ';
                    }
                    if (alertMessage) {
                        alertMessage.textContent = data.message || 'حدث خطأ أثناء حذف الواجب';
                    }
                    
                    alertModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                deleteModal.hide();
                
                // Show error message
                const alertIcon = document.getElementById('alert-icon');
                const alertTitle = document.getElementById('alert-title');
                const alertMessage = document.getElementById('alert-message');
                
                if (alertIcon) {
                    alertIcon.innerHTML = '<span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle"><i class="fas fa-exclamation-circle fa-2x"></i></span>';
                }
                if (alertTitle) {
                    alertTitle.textContent = 'خطأ';
                }
                if (alertMessage) {
                    alertMessage.textContent = 'حدث خطأ أثناء حذف الواجب';
                }
                
                alertModal.show();
            });
        });
    }
});
</script>
@stop
