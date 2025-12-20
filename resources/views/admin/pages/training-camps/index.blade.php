@extends('admin.layouts.master')

@section('page-title')
    المعسكرات التدريبية
@stop

@section('css')
<style>
    .camp-status-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .camp-image {
        width: 90px !important;
        max-width: 90px !important;
        height: 60px !important;
        max-height: 60px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
        flex-shrink: 0 !important;
        display: block !important;
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة المعسكرات التدريبية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">المعسكرات التدريبية</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">قائمة المعسكرات</div>
                            <a href="{{ route('training-camps.create') }}" class="btn btn-primary btn-wave">
                                <i class="fas fa-plus me-2"></i>إضافة معسكر جديد
                            </a>
                        </div>

                        <div class="card-header">
                            <form action="{{ route('training-camps.index') }}" method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="بحث..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">جميع الحالات</option>
                                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>قادم</option>
                                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>جاري</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>منتهي</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="category_id" class="form-select">
                                        <option value="">جميع التصنيفات</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary w-100">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">المعسكر</th>
                                            <th scope="col">التصنيف</th>
                                            <th scope="col">المدرب</th>
                                            <th scope="col">التاريخ</th>
                                            <th scope="col">المدة</th>
                                            <th scope="col">السعر</th>
                                            <th scope="col">المشاركين</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($camps as $camp)
                                            <tr>
                                                <td>{{ $loop->iteration + ($camps->currentPage() - 1) * $camps->perPage() }}</td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($camp->image)
                                                            <img src="{{ asset('storage/' . $camp->image) }}"
                                                                 alt="{{ $camp->name }}"
                                                                 class="camp-image me-3"
                                                                 style="width: 90px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                        @endif
                                                        <div>
                                                            <strong>{{ $camp->name }}</strong>
                                                            @if($camp->is_featured)
                                                                <i class="fas fa-star text-warning ms-1" title="مميز"></i>
                                                            @endif
                                                            <br><small class="text-muted">{{ $camp->location ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    @if($camp->category)
                                                        <span class="badge" style="background-color: {{ $camp->category->color }}">
                                                            {{ $camp->category->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>{{ $camp->instructor_name ?? '-' }}</td>

                                                <td>
                                                    <small>
                                                        <strong>من:</strong> {{ $camp->start_date->format('Y-m-d') }}<br>
                                                        <strong>إلى:</strong> {{ $camp->end_date->format('Y-m-d') }}
                                                    </small>
                                                </td>

                                                <td>
                                                    <span class="badge bg-info">{{ $camp->duration_days }} يوم</span>
                                                </td>

                                                <td>
                                                    <strong>${{ number_format($camp->price, 2) }}</strong>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary me-1">{{ $camp->current_participants }}</span>
                                                        @if($camp->max_participants)
                                                            / {{ $camp->max_participants }}
                                                            @if($camp->isFull())
                                                                <i class="fas fa-exclamation-circle text-danger ms-1" title="ممتلئ"></i>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <div>
                                                        @if($camp->isOngoing())
                                                            <span class="badge bg-success camp-status-badge">
                                                                <i class="fas fa-play me-1"></i>جاري
                                                            </span>
                                                        @elseif($camp->hasEnded())
                                                            <span class="badge bg-secondary camp-status-badge">
                                                                <i class="fas fa-check me-1"></i>منتهي
                                                            </span>
                                                        @else
                                                            <span class="badge bg-info camp-status-badge">
                                                                <i class="fas fa-clock me-1"></i>قادم
                                                            </span>
                                                        @endif
                                                        <br>
                                                        @if($camp->is_active)
                                                            <span class="badge bg-success mt-1">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger mt-1">معطل</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('training-camps.show', $camp->id) }}"
                                                           class="btn btn-sm btn-primary-light"
                                                           title="عرض">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <a href="{{ route('training-camps.edit', $camp->id) }}"
                                                           class="btn btn-sm btn-info-light"
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <button type="button"
                                                                class="btn btn-sm btn-danger-light"
                                                                onclick="deleteCamp({{ $camp->id }}, '{{ e(Str::limit($camp->name, 50)) }}')"
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-campground fa-3x mb-3 d-block"></i>
                                                        <h5>لا توجد معسكرات تدريبية حالياً</h5>
                                                        <p>قم بإنشاء أول معسكر تدريبي</p>
                                                        <a href="{{ route('training-camps.create') }}" class="btn btn-primary mt-2">
                                                            <i class="fas fa-plus me-2"></i>إضافة معسكر
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($camps->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $camps->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Camp Modal -->
    <div class="modal fade" id="deleteCampModal" tabindex="-1" aria-labelledby="deleteCampModalLabel" aria-hidden="true">
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
                        <h5 class="mb-2" id="deleteCampModalLabel">حذف المعسكر</h5>
                        <p class="text-muted mb-0" id="deleteCampMessage">هل أنت متأكد من حذف هذا المعسكر؟</p>
                        <p class="text-danger small mt-2 mb-0">لن يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCamp">
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
    let currentCampId = null;

    // Delete Camp
    function deleteCamp(campId, campName) {
        currentCampId = campId;
        
        const messageEl = document.getElementById('deleteCampMessage');
        if (messageEl) {
            messageEl.innerHTML = `هل أنت متأكد من حذف المعسكر<br><strong>${campName}</strong>؟`;
        }
        
        const modalElement = document.getElementById('deleteCampModal');
        if (!modalElement) {
            console.error('deleteCampModal element not found');
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
        // Confirm Delete Camp
        const confirmBtn = document.getElementById('confirmDeleteCamp');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!currentCampId) return;
                
                const modalElement = document.getElementById('deleteCampModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                
                fetch(`{{ url('/admin/training-camps') }}/${currentCampId}`, {
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
                            showAlert('success', data.message || 'تم حذف المعسكر بنجاح');
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

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@stop
