@extends('admin.layouts.master')

@section('page-title')
كورسات الواجهة الأمامية
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">كورسات الواجهة الأمامية</h4>
                <p class="mb-0 text-muted">إدارة الكورسات المعروضة في الموقع</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.frontend-courses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    إضافة كورس جديد
                </a>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Filters Card -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">فلترة وبحث</div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.frontend-courses.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">بحث</label>
                            <input type="text" name="search" class="form-control" placeholder="ابحث بالعنوان أو الوصف..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">التصنيف</label>
                            <select name="category" class="form-select">
                                <option value="">الكل</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="">الكل</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">المستوى</label>
                            <select name="level" class="form-select">
                                <option value="">الكل</option>
                                <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i> بحث
                            </button>
                            <a href="{{ route('admin.frontend-courses.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Courses Table -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">قائمة الكورسات ({{ $courses->total() }})</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table text-nowrap table-hover">
                        <thead>
                            <tr>
                                <th width="80">#</th>
                                <th>الكورس</th>
                                <th>التصنيف</th>
                                <th>المدرب</th>
                                <th>المستوى</th>
                                <th>السعر</th>
                                <th>الحالة</th>
                                <th>الطلاب</th>
                                <th>التقييم</th>
                                <th width="150">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($courses as $course)
                            <tr>
                                <td>{{ $course->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($course->thumbnail)
                                        <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                             alt="{{ $course->title }}"
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-book fs-4 text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $course->title }}</div>
                                            <small class="text-muted">{{ $course->lessons_count }} درس</small>
                                            @if($course->is_featured)
                                            <span class="badge bg-warning-transparent ms-1">مميز</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-transparent">
                                        {{ $course->category->name }}
                                    </span>
                                </td>
                                <td>{{ $course->instructor->name }}</td>
                                <td>
                                    @if($course->level == 'beginner')
                                    <span class="badge bg-success">مبتدئ</span>
                                    @elseif($course->level == 'intermediate')
                                    <span class="badge bg-warning">متوسط</span>
                                    @else
                                    <span class="badge bg-danger">متقدم</span>
                                    @endif
                                </td>
                                <td>
                                    @if($course->is_free)
                                    <span class="badge bg-success">مجاني</span>
                                    @else
                                    <div>
                                        @if($course->discount_price)
                                        <div>
                                            <span class="fw-bold text-success">{{ $course->discount_price }} {{ $course->currency }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted text-decoration-line-through">{{ $course->price }} {{ $course->currency }}</small>
                                        </div>
                                        @else
                                        <span class="fw-bold">{{ $course->price }} {{ $course->currency }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($course->status == 'published')
                                    <span class="badge bg-success">منشور</span>
                                    @elseif($course->status == 'draft')
                                    <span class="badge bg-secondary">مسودة</span>
                                    @else
                                    <span class="badge bg-dark">مؤرشف</span>
                                    @endif
                                </td>
                                <td>
                                    <i class="bi bi-people me-1"></i>
                                    {{ number_format($course->students_count) }}
                                </td>
                                <td>
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    {{ $course->rating }}
                                    <small class="text-muted">({{ $course->reviews_count }})</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.frontend-courses.edit', $course->id) }}"
                                           class="btn btn-sm btn-primary"
                                           title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('frontend.courses.show', $course->slug) }}"
                                           class="btn btn-sm btn-info"
                                           title="معاينة"
                                           target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmDelete({{ $course->id }}, '{{ e(Str::limit($course->title, 40)) }}')"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $course->id }}"
                                          action="{{ route('admin.frontend-courses.destroy', $course->id) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">لا توجد كورسات</p>
                                    <a href="{{ route('admin.frontend-courses.create') }}" class="btn btn-primary btn-sm">
                                        إضافة كورس جديد
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($courses->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        عرض {{ $courses->firstItem() }} إلى {{ $courses->lastItem() }} من أصل {{ $courses->total() }} نتيجة
                    </div>
                    <div>
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
<!-- End::app-content -->

<!-- Delete Frontend Course Confirmation Modal -->
<div class="modal fade" id="deleteFrontendCourseModal" tabindex="-1" aria-labelledby="deleteFrontendCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle">
                        <i class="bi bi-trash-fill fs-1"></i>
                    </span>
                </div>
                <h5 id="deleteFrontendCourseModalLabel" class="mb-2">تأكيد حذف كورس الواجهة الأمامية</h5>
                <p class="text-muted mb-4">
                    هل أنت متأكد من حذف الكورس
                    <strong id="frontend-delete-title"></strong>؟
                    <br>
                    سيتم حذف هذا الكورس من الواجهة الأمامية مع جميع البيانات المرتبطة به ولا يمكن التراجع عن هذه العملية.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        إلغاء
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="frontend-confirm-delete">
                        <i class="bi bi-trash me-1"></i> حذف نهائياً
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('script')
<script>
let frontendDeleteCourseId = null;

function confirmDelete(courseId, courseTitle) {
    frontendDeleteCourseId = courseId;

    const modalElement = document.getElementById('deleteFrontendCourseModal');
    if (!modalElement) return;

    const titleSpan = document.getElementById('frontend-delete-title');
    if (titleSpan) {
        titleSpan.textContent = courseTitle || '';
    }

    const deleteModal = new bootstrap.Modal(modalElement);
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const confirmBtn = document.getElementById('frontend-confirm-delete');
    const modalElement = document.getElementById('deleteFrontendCourseModal');
    if (!confirmBtn || !modalElement) return;

    const deleteModal = new bootstrap.Modal(modalElement);

    confirmBtn.addEventListener('click', function () {
        if (!frontendDeleteCourseId) return;

        const form = document.getElementById('delete-form-' + frontendDeleteCourseId);
        if (form) {
            form.submit();
        }
        deleteModal.hide();
    });
});
</script>
@stop

@endsection
