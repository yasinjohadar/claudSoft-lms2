@extends('admin.layouts.master')

@section('page-title')
    إدارة الكورسات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الكورسات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الكورسات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة كورس جديد
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
                                        <i class="fas fa-graduation-cap fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الكورسات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalCourses }}</h4>
                                    <span class="badge bg-primary-transparent">في جميع التصنيفات</span>
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
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">كورسات منشورة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $publishedCourses }}</h4>
                                    <span class="badge bg-success-transparent">نشطة حالياً</span>
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
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي التسجيلات</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalEnrollments }}</h4>
                                    <span class="badge bg-info-transparent">طالب مسجل</span>
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
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-fire fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">كورسات نشطة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $activeCourses }}</h4>
                                    <span class="badge bg-warning-transparent">يتم التدريس</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('courses.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن كورس...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">التصنيف</label>
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
                                <label class="form-label">المستوى</label>
                                <select name="level" class="form-select">
                                    <option value="">جميع المستويات</option>
                                    <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                    <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                    <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('courses.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الكورسات</div>
                </div>
                <div class="card-body">
                    @if($courses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الكورس</th>
                                        <th>التصنيف</th>
                                        <th>المستوى</th>
                                        <th>المدرب</th>
                                        <th>الدروس</th>
                                        <th>الطلاب</th>
                                        <th>السعر</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $course)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($course->image)
                                                        @php
                                                            // Handle image path - check if it's a full URL or relative path
                                                            $imagePath = $course->image;
                                                            if (strpos($imagePath, 'http') === 0) {
                                                                // External URL
                                                                $imageUrl = $imagePath;
                                                            } else {
                                                                // Local file - use Storage URL helper which works with/without public in URL
                                                                $imageUrl = \Storage::disk('public')->url($imagePath);
                                                            }
                                                        @endphp
                                                        <img src="{{ $imageUrl }}"
                                                             alt="{{ $course->title }}"
                                                             class="avatar avatar-md rounded me-2"
                                                             style="object-fit: cover;"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    @else
                                                        <span class="avatar avatar-md bg-primary-transparent me-2">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </span>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('courses.show', $course->id) }}"
                                                           class="fw-semibold text-primary">
                                                            {{ Str::limit($course->title, 40) }}
                                                        </a>
                                                        @if($course->code)
                                                            <small class="d-block text-muted">{{ $course->code }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($course->category)
                                                    <span class="badge" style="background-color: {{ $course->category->color }}">
                                                        {{ $course->category->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course->level == 'beginner')
                                                    <span class="badge bg-success">مبتدئ</span>
                                                @elseif($course->level == 'intermediate')
                                                    <span class="badge bg-info">متوسط</span>
                                                @elseif($course->level == 'advanced')
                                                    <span class="badge bg-danger">متقدم</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course->instructor)
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-xs me-2">
                                                            {{ substr($course->instructor->name, 0, 1) }}
                                                        </span>
                                                        {{ $course->instructor->name }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-transparent">
                                                    <i class="fas fa-book me-1"></i>{{ $course->modules_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-transparent">
                                                    <i class="fas fa-user me-1"></i>{{ $course->enrollments_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($course->price > 0)
                                                    <strong class="text-primary">${{ number_format($course->price, 2) }}</strong>
                                                @else
                                                    <strong class="text-success">مجاني</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-warning">مسودة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('courses.show', $course->id) }}"
                                                       class="btn btn-sm btn-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('courses.edit', $course->id) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-{{ $course->is_published ? 'warning' : 'success' }}"
                                                            onclick="togglePublish({{ $course->id }}, '{{ e(Str::limit($course->title, 40)) }}', {{ $course->is_published ? 'true' : 'false' }})"
                                                            title="{{ $course->is_published ? 'إلغاء النشر' : 'نشر' }}">
                                                        <i class="fas fa-{{ $course->is_published ? 'eye-slash' : 'paper-plane' }}"></i>
                                                    </button>
                                                    <form action="{{ route('courses.destroy', $course->id) }}"
                                                          method="POST"
                                                          class="d-inline course-delete-form"
                                                          data-course-title="{{ $course->title }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger btn-delete-course" title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $courses->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد كورسات</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Toggle Publish Confirmation Modal -->
    <div class="modal fade" id="publishCourseModal" tabindex="-1" aria-labelledby="publishCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-primary-transparent text-primary rounded-circle">
                            <i class="fas fa-bullhorn fa-2x"></i>
                        </span>
                    </div>
                    <h5 id="publishCourseModalLabel" class="mb-2">تغيير حالة نشر الكورس</h5>
                    <p class="text-muted mb-4" id="publish-course-message">
                        <!-- سيتم تعبئتها عبر الجافاسكربت -->
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="button" class="btn btn-primary px-4" id="confirm-toggle-publish">
                            <i class="fas fa-check me-1"></i> تأكيد
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Course Confirmation Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </span>
                    </div>
                    <h5 id="deleteCourseModalLabel" class="mb-2">تأكيد حذف الكورس</h5>
                    <p class="text-muted mb-4">
                        هل أنت متأكد من حذف الكورس
                        <strong id="delete-course-title"></strong>؟
                        <br>
                        سيتم حذف جميع البيانات المرتبطة بهذا الكورس ولا يمكن التراجع عن هذه العملية.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-course">
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
    // Toggle Publish Status (with modal)
    let toggleCourseId = null;
    let toggleCourseIsPublished = false;

    function togglePublish(courseId, courseTitle, isPublished) {
        toggleCourseId = courseId;
        toggleCourseIsPublished = !!isPublished;

        const modalElement = document.getElementById('publishCourseModal');
        if (!modalElement) return;

        const messageEl = document.getElementById('publish-course-message');
        const actionText = toggleCourseIsPublished ? 'إلغاء نشر' : 'نشر';
        const statusText = toggleCourseIsPublished ? 'سيتم إخفاء هذا الكورس من قائمة الكورسات للطلاب.' : 'سيصبح هذا الكورس متاحاً للطلاب حسب إعدادات التسجيل.';

        if (messageEl) {
            messageEl.innerHTML = `
                هل تريد <strong>${actionText}</strong> الكورس
                <strong>${courseTitle}</strong>؟
                <br>
                ${statusText}
            `;
        }

        const publishModal = new bootstrap.Modal(modalElement);
        publishModal.show();
    }

    // Fade out alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Fancy delete confirmation for courses
    (function () {
        let deleteForm = null;
        const modalElement = document.getElementById('deleteCourseModal');
        if (!modalElement) return;

        const deleteModal = new bootstrap.Modal(modalElement);
        const titleSpan = document.getElementById('delete-course-title');
        const confirmBtn = document.getElementById('confirm-delete-course');

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-delete-course');
            if (!btn) return;

            e.preventDefault();

            deleteForm = btn.closest('form.course-delete-form');
            if (!deleteForm) return;

            const courseTitle = deleteForm.getAttribute('data-course-title') || '';
            if (titleSpan) {
                titleSpan.textContent = courseTitle;
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

    // Handle confirm toggle publish
    (function () {
        const confirmBtn = document.getElementById('confirm-toggle-publish');
        const modalElement = document.getElementById('publishCourseModal');
        if (!confirmBtn || !modalElement) return;

        const publishModal = new bootstrap.Modal(modalElement);

        confirmBtn.addEventListener('click', function () {
            if (!toggleCourseId) return;

            const togglePublishUrl = '{{ route("courses.toggle-publish", 0) }}'.replace('/0/toggle-publish', `/${toggleCourseId}/toggle-publish`);
            fetch(togglePublishUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(async response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'حدث خطأ في الخادم');
                    }
                    return data;
                } else {
                    // If not JSON, it's a redirect - reload the page
                    location.reload();
                    return null;
                }
            })
            .then(data => {
                if (data) {
                    if (data.success) {
                        publishModal.hide();
                        location.reload();
                    } else {
                        alert(data.message || 'حدث خطأ');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال: ' + error.message);
            });
        });
    })();
</script>
@stop
