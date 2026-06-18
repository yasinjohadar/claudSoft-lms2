@extends('admin.layouts.master')

@section('page-title')
    إدارة الكورسات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الكورسات</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-book-open me-1"></i>
                            إدارة المحتوى التعليمي
                        </span>
                        <h2 class="group-show-hero__title mb-2">قائمة الكورسات</h2>
                        <p class="group-show-hero__desc mb-0">
                            إدارة الكورسات، التصنيفات، النشر، والتسجيلات من لوحة واحدة منظّمة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('courses.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة كورس جديد</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-book-open', 'label' => 'إجمالي الكورسات', 'value' => $totalCourses, 'sub' => 'في جميع التصنيفات'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'كورسات منشورة', 'value' => $publishedCourses, 'sub' => 'نشطة حالياً'],
                    ['variant' => 'cyan', 'icon' => 'fe-users', 'label' => 'إجمالي التسجيلات', 'value' => $totalEnrollments, 'sub' => 'طالب مسجّل'],
                    ['variant' => 'orange', 'icon' => 'fe-zap', 'label' => 'كورسات نشطة', 'value' => $activeCourses, 'sub' => 'منشورة ومرئية'],
                ];
            @endphp

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الكورسات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو الكود، أو فلتر حسب التصنيف والمستوى والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('courses.index') }}" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="coursesSearchInput">البحث</label>
                                <input type="text" name="search" id="coursesSearchInput" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن كورس...">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="coursesCategory">التصنيف</label>
                                <select name="category_id" id="coursesCategory" class="form-select">
                                    <option value="">جميع التصنيفات</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="coursesLevel">المستوى</label>
                                <select name="level" id="coursesLevel" class="form-select">
                                    <option value="">جميع المستويات</option>
                                    <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                    <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                    <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="coursesStatus">الحالة</label>
                                <select name="status" id="coursesStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الكورسات
                        <span class="group-show-members-card__count">{{ $courses->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.courses._courses_table', ['courses' => $courses])
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="publishCourseModal" tabindex="-1" aria-labelledby="publishCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-primary-transparent text-primary rounded-circle">
                            <i class="fe fe-send" style="font-size:1.75rem;"></i>
                        </span>
                    </div>
                    <h5 id="publishCourseModalLabel" class="mb-2">تغيير حالة نشر الكورس</h5>
                    <p class="text-muted mb-4" id="publish-course-message"></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-primary px-4" id="confirm-toggle-publish">
                            <i class="fe fe-check me-1"></i>تأكيد
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle">
                            <i class="fe fe-trash-2" style="font-size:1.75rem;"></i>
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
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-course">
                            <i class="fe fe-trash-2 me-1"></i>حذف نهائياً
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        if (!target) {
            el.textContent = '0';
            return;
        }
        var duration = 700;
        var start = performance.now();
        function tick(now) {
            var progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(progress * target).toLocaleString('ar-EG');
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    let toggleCourseId = null;
    let toggleCourseIsPublished = false;

    function togglePublish(courseId, courseTitle, isPublished) {
        toggleCourseId = courseId;
        toggleCourseIsPublished = !!isPublished;

        const modalElement = document.getElementById('publishCourseModal');
        if (!modalElement) return;

        const messageEl = document.getElementById('publish-course-message');
        const actionText = toggleCourseIsPublished ? 'إلغاء نشر' : 'نشر';
        const statusText = toggleCourseIsPublished
            ? 'سيتم إخفاء هذا الكورس من قائمة الكورسات للطلاب.'
            : 'سيصبح هذا الكورس متاحاً للطلاب حسب إعدادات التسجيل.';

        if (messageEl) {
            messageEl.innerHTML = 'هل تريد <strong>' + actionText + '</strong> الكورس <strong>' + courseTitle + '</strong>؟<br>' + statusText;
        }

        new bootstrap.Modal(modalElement).show();
    }

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
            e.stopPropagation();

            deleteForm = btn.closest('form.course-delete-form');
            if (!deleteForm) return;

            if (titleSpan) {
                titleSpan.textContent = deleteForm.getAttribute('data-course-title') || '';
            }

            deleteModal.show();
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (deleteForm) deleteForm.submit();
            });
        }
    })();

    (function () {
        const confirmBtn = document.getElementById('confirm-toggle-publish');
        const modalElement = document.getElementById('publishCourseModal');
        if (!confirmBtn || !modalElement) return;

        const publishModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

        confirmBtn.addEventListener('click', function () {
            if (!toggleCourseId) return;

            const togglePublishUrl = '{{ route("courses.toggle-publish", 0) }}'.replace('/0/toggle-publish', '/' + toggleCourseId + '/toggle-publish');

            fetch(togglePublishUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
                .then(async function (response) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'حدث خطأ في الخادم');
                        return data;
                    }
                    location.reload();
                    return null;
                })
                .then(function (data) {
                    if (!data) return;
                    if (data.success) {
                        publishModal.hide();
                        location.reload();
                    } else {
                        alert(data.message || 'حدث خطأ');
                    }
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    alert('حدث خطأ في الاتصال: ' + error.message);
                });
        });
    })();
</script>
@stop
