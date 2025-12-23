@extends('admin.layouts.master')

@section('page-title')
    المجموعات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة المجموعات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">المجموعات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Add Group Button -->
            <div class="mb-3">
                <a href="{{ route('groups.select-course') }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-plus me-2"></i>إضافة مجموعة جديدة (في نافذة جديدة)
                </a>
                <span class="ms-2 text-muted">أو</span>
                <button type="button" class="btn btn-success ms-2" onclick="console.log('Navigating to:', '{{ route('groups.select-course') }}'); setTimeout(function(){ window.location.replace('{{ route('groups.select-course') }}'); }, 100);">
                    <i class="fas fa-plus me-2"></i>إضافة مجموعة جديدة (نفس النافذة)
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي المجموعات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalGroups }}</h4>
                                    <span class="badge bg-primary-transparent">في جميع الكورسات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">مجموعات نشطة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $activeGroups }}</h4>
                                    <span class="badge bg-success-transparent">نشطة حالياً</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-user-friends fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الأعضاء</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalMembers }}</h4>
                                    <span class="badge bg-info-transparent">في جميع المجموعات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('groups.all') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن مجموعة...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
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
                                <select name="is_active" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('groups.all') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Groups Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة المجموعات</div>
                </div>
                <div class="card-body">
                    @if($groups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>اسم المجموعة</th>
                                        <th>الكورس</th>
                                        <th>عدد الأعضاء</th>
                                        <th>منشئ المجموعة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $group)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                        <i class="fas fa-users"></i>
                                                    </span>
                                                    <div>
                                                        <a href="{{ route('courses.groups.show', [$group->courses->first()->id ?? 1, $group->id]) }}"
                                                           class="text-primary fw-semibold">
                                                            {{ $group->name }}
                                                        </a>
                                                        @if($group->description)
                                                            <small class="d-block text-muted">{{ Str::limit($group->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($group->courses && $group->courses->count() > 0)
                                                    @foreach($group->courses as $course)
                                                        <a href="{{ route('courses.show', $course->id) }}" class="badge bg-primary-transparent mb-1">
                                                            {{ $course->title }}
                                                        </a>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                    @if($group->courses->count() > 1)
                                                        <small class="text-muted d-block">({{ $group->courses->count() }} كورسات)</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">لا توجد كورسات</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    <i class="fas fa-user-friends me-1"></i>{{ $group->members_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($group->createdBy)
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-xs me-2">
                                                            {{ substr($group->createdBy->name, 0, 1) }}
                                                        </span>
                                                        {{ $group->createdBy->name }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $group->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                @if($group->is_active)
                                                    <span class="badge bg-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('courses.groups.show', [$group->courses->first()->id ?? 1, $group->id]) }}"
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('courses.groups.edit', [$group->courses->first()->id ?? 1, $group->id]) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" title="حذف"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal{{ $group->id }}"
                                                            data-group-name="{{ $group->name }}">
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
                            {{ $groups->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد مجموعات</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Modals -->
    @foreach($groups as $group)
        <div class="modal fade" id="deleteModal{{ $group->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $group->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel{{ $group->id }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="avatar avatar-xl bg-danger-transparent mb-3">
                                <i class="fas fa-trash-alt fs-1"></i>
                            </div>
                            <h5 class="mb-3">هل أنت متأكد من حذف هذه المجموعة؟</h5>
                            <p class="text-muted mb-2">
                                المجموعة: <strong class="text-danger">{{ $group->name }}</strong>
                            </p>
                            @if($group->members_count > 0)
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>تحذير:</strong> هذه المجموعة تحتوي على <strong>{{ $group->members_count }}</strong> عضو/أعضاء
                                </div>
                            @endif
                            <p class="text-danger mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                <small>لا يمكن التراجع عن هذا الإجراء!</small>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteGroup({{ $group->id }}, '{{ $group->name }}')">
                            <i class="fas fa-trash me-2"></i>نعم، احذف المجموعة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Success/Error Alert Container -->
    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>
@stop

@section('script')
<script>
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        let alertClass, icon, title;
        
        if (type === 'success') {
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            title = 'نجح!';
        } else if (type === 'error') {
            alertClass = 'alert-danger';
            icon = 'fa-exclamation-circle';
            title = 'خطأ!';
        } else {
            alertClass = 'alert-info';
            icon = 'fa-info-circle';
            title = 'معلومة';
        }
        
        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <strong><i class="fas ${icon} me-2"></i>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        // Auto hide after 5 seconds (except for info which hides after 2 seconds)
        const hideDelay = type === 'info' ? 2000 : 5000;
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 300);
            }
        }, hideDelay);
    }

    function deleteGroup(groupId, groupName) {
        // Close the modal
        const modalElement = document.getElementById('deleteModal' + groupId);
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }

        // Show loading
        showAlert('info', 'جاري حذف المجموعة...');

        // Send AJAX request
        const deleteUrl = `{{ url('/admin/groups') }}/${groupId}/delete`;
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'تم حذف المجموعة بنجاح');
                // Reload page after 1.5 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'حدث خطأ أثناء حذف المجموعة');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'حدث خطأ أثناء حذف المجموعة: ' + error.message);
        });
    }
</script>
@stop
