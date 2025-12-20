@extends('admin.layouts.master')

@section('page-title')
    الأسئلة الشائعة
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الأسئلة الشائعة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الأسئلة الشائعة</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">قائمة الأسئلة الشائعة</div>
                            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-wave">
                                <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">السؤال</th>
                                            <th scope="col">الترتيب</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">تاريخ الإنشاء</th>
                                            <th scope="col" style="min-width: 150px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($faqs as $faq)
                                            <tr>
                                                <td>{{ $loop->iteration + ($faqs->currentPage() - 1) * $faqs->perPage() }}</td>
                                                <td>
                                                    <strong>{{ Str::limit($faq->question, 80) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $faq->order }}</span>
                                                </td>
                                                <td>
                                                    @if($faq->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>{{ $faq->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <a href="{{ route('admin.faqs.edit', $faq) }}" 
                                                           class="btn btn-sm btn-info btn-wave">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-{{ $faq->is_active ? 'warning' : 'success' }} btn-wave toggle-active"
                                                                data-id="{{ $faq->id }}"
                                                                data-active="{{ $faq->is_active }}">
                                                            <i class="fas fa-{{ $faq->is_active ? 'eye-slash' : 'eye' }}"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger btn-wave"
                                                                onclick="deleteFaq({{ $faq->id }}, '{{ e(Str::limit($faq->question, 50)) }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">لا توجد أسئلة شائعة حالياً</p>
                                                        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($faqs->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $faqs->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- Delete FAQ Modal -->
    <div class="modal fade" id="deleteFaqModal" tabindex="-1" aria-labelledby="deleteFaqModalLabel" aria-hidden="true">
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
                        <h5 class="mb-2" id="deleteFaqModalLabel">حذف السؤال</h5>
                        <p class="text-muted mb-0" id="deleteFaqMessage">هل أنت متأكد من حذف هذا السؤال؟</p>
                        <p class="text-danger small mt-2 mb-0">لن يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteFaq">
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
@endsection

@section('script')
<script>
    let currentFaqId = null;

    // Delete FAQ
    function deleteFaq(faqId, faqQuestion) {
        currentFaqId = faqId;
        
        const messageEl = document.getElementById('deleteFaqMessage');
        if (messageEl) {
            messageEl.innerHTML = `هل أنت متأكد من حذف السؤال<br><strong>${faqQuestion}</strong>؟`;
        }
        
        const modalElement = document.getElementById('deleteFaqModal');
        if (!modalElement) {
            console.error('deleteFaqModal element not found');
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
        // Toggle Active Status
        document.querySelectorAll('.toggle-active').forEach(button => {
            button.addEventListener('click', function() {
                const faqId = this.getAttribute('data-id');
                const isActive = this.getAttribute('data-active') === '1';
                const button = this;
                
                // Disable button during request
                button.disabled = true;

                fetch(`{{ url('/admin/faqs') }}/${faqId}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    if (!response.ok) {
                        if (contentType && contentType.includes('application/json')) {
                            const err = await response.json();
                            return Promise.reject(err);
                        } else {
                            return Promise.reject({ message: `HTTP error! status: ${response.status}` });
                        }
                    }
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return Promise.reject({ message: 'Expected JSON response but got ' + contentType });
                    }
                })
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message || 'تم تحديث الحالة بنجاح');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showAlert('error', data.message || 'حدث خطأ أثناء تحديث الحالة');
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error.message || error.error || 'حدث خطأ أثناء تحديث الحالة';
                    showAlert('error', errorMessage);
                    button.disabled = false;
                });
            });
        });

        // Confirm Delete FAQ
        const confirmBtn = document.getElementById('confirmDeleteFaq');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!currentFaqId) return;
                
                const modalElement = document.getElementById('deleteFaqModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                
                fetch(`{{ url('/admin/faqs') }}/${currentFaqId}`, {
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
                            showAlert('success', data.message || 'تم حذف السؤال بنجاح');
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


