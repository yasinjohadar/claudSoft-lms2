@extends('admin.layouts.master')

@section('page-title')
    تصنيفات الدورات
@stop

@section('css')
<style>
    .category-color-box {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: inline-block;
        border: 2px solid #dee2e6;
    }
    .category-icon {
        font-size: 1.5rem;
    }
</style>
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
                    <h5 class="page-title fs-21 mb-1">إدارة تصنيفات الدورات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التصنيفات</li>
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
                            <div class="card-title">قائمة التصنيفات</div>
                            <a href="{{ route('course-categories.create') }}" class="btn btn-primary btn-wave">
                                <i class="fas fa-plus me-2"></i>إضافة تصنيف جديد
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">الاسم</th>
                                            <th scope="col">اللون</th>
                                            <th scope="col">الأيقونة</th>
                                            <th scope="col">التصنيف الأب</th>
                                            <th scope="col">التصنيفات الفرعية</th>
                                            <th scope="col">الترتيب</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">تاريخ الإنشاء</th>
                                            <th scope="col" style="min-width: 150px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($categories as $category)
                                            <tr>
                                                <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($category->image)
                                                            <img src="{{ asset('storage/' . $category->image) }}"
                                                                 alt="{{ $category->name }}"
                                                                 class="avatar avatar-sm rounded-circle me-2">
                                                        @endif
                                                        <div>
                                                            <strong>{{ $category->name }}</strong>
                                                            @if($category->slug)
                                                                <br><small class="text-muted">{{ $category->slug }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="category-color-box"
                                                          style="background-color: {{ $category->color }}"
                                                          title="{{ $category->color }}">
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($category->icon)
                                                        <i class="{{ $category->icon }} category-icon"
                                                           style="color: {{ $category->color }}"></i>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($category->parent)
                                                        <span class="badge bg-info-transparent">
                                                            {{ $category->parent->name }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-transparent">تصنيف رئيسي</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($category->children_count > 0)
                                                        <span class="badge bg-primary">{{ $category->children_count }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="badge bg-light text-dark">{{ $category->order }}</span>
                                                </td>

                                                <td>
                                                    @if($category->is_active)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>نشط
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>غير نشط
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <small class="text-muted">
                                                        {{ $category->created_at->format('Y-m-d') }}
                                                    </small>
                                                </td>

                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('course-categories.edit', $category->id) }}"
                                                           class="btn btn-sm btn-info-light"
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <form action="{{ route('course-categories.destroy', $category->id) }}"
                                                              method="POST"
                                                              class="d-inline category-delete-form"
                                                              data-category-name="{{ $category->name }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger-light btn-delete-category"
                                                                    title="حذف"
                                                                    @if($category->children_count > 0) disabled @endif>
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                        <h5>لا توجد تصنيفات حالياً</h5>
                                                        <p>قم بإنشاء أول تصنيف للدورات</p>
                                                        <a href="{{ route('course-categories.create') }}" class="btn btn-primary mt-2">
                                                            <i class="fas fa-plus me-2"></i>إضافة تصنيف
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($categories->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $categories->links() }}
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </span>
                    </div>
                    <h5 id="deleteCategoryModalLabel" class="mb-2">تأكيد حذف التصنيف</h5>
                    <p class="text-muted mb-4">
                        هل أنت متأكد من حذف التصنيف
                        <strong id="delete-category-name"></strong>؟
                        <br>
                        لا يمكن التراجع عن هذه العملية.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-category">
                            <i class="fas fa-trash me-1"></i> حذف نهائياً
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Fancy delete confirmation modal
    (function () {
        let deleteForm = null;
        const modalElement = document.getElementById('deleteCategoryModal');
        if (!modalElement) return;

        const deleteModal = new bootstrap.Modal(modalElement);
        const nameSpan = document.getElementById('delete-category-name');
        const confirmBtn = document.getElementById('confirm-delete-category');

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-delete-category');
            if (!btn) return;

            e.preventDefault();

            deleteForm = btn.closest('form.category-delete-form');
            if (!deleteForm) return;

            const categoryName = deleteForm.getAttribute('data-category-name') || '';
            if (nameSpan) {
                nameSpan.textContent = categoryName;
            }

            deleteModal.show();
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (deleteForm) {
                    deleteForm.submit();
                }
            });
        }
    })();
</script>
@stop
