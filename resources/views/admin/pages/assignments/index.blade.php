@extends('admin.layouts.master')

@section('page-title')
    إدارة الواجبات
@stop

@section('styles')
    @include('admin.pages.assignments.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb assignments-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الواجبات</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in assignments-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-clipboard me-1"></i>
                            إدارة التقييم والواجبات
                        </span>
                        <h2 class="group-show-hero__title mb-2">إدارة الواجبات</h2>
                        <p class="group-show-hero__desc mb-0">
                            إنشاء الواجبات، ربطها بالكورسات والدروس، متابعة التسليمات وتقييم أعمال الطلاب.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('assignments.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة واجب جديد</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 assignments-page-animate">
                @include('admin.pages.assignments.partials.stats', [
                    'totalAssignments' => $totalAssignments,
                    'publishedAssignments' => $publishedAssignments,
                    'draftAssignments' => $draftAssignments,
                ])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الواجبات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بعنوان الواجب أو فلتر حسب الكورس والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('assignments.index') }}" id="filterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <label class="form-label" for="assignmentsSearch">البحث</label>
                                <input type="text" id="assignmentsSearch" name="search" class="form-control"
                                       placeholder="ابحث بعنوان الواجب..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="assignmentsCourse">الكورس</label>
                                <select name="course_id" id="assignmentsCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="assignmentsStatus">الحالة</label>
                                <select name="status" id="assignmentsStatus" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الواجبات
                        <span class="group-show-members-card__count">{{ $assignments->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.assignments._assignments_table', ['assignments' => $assignments])
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteAssignmentModalLabel">
                        <i class="fe fe-alert-triangle text-danger me-2"></i>تأكيد حذف الواجب
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">هل أنت متأكد من حذف الواجب <strong id="delete-assignment-title"></strong>؟</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fe fe-info me-2"></i>
                        سيتم حذف جميع البيانات المرتبطة بهذا الواجب ولا يمكن التراجع عن هذه العملية.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-assignment">
                        <i class="fe fe-trash-2 me-1"></i>حذف نهائياً
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-3" id="alert-icon">
                        <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-check-circle"></i></span>
                    </div>
                    <h5 id="alert-title" class="mb-2">نجح</h5>
                    <p class="text-muted mb-4" id="alert-message"></p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">موافق</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    function initAssignmentsCountup() {
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();
            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAssignmentsCountup);
    } else {
        initAssignmentsCountup();
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    let deleteForm = null;
    const modalElement = document.getElementById('deleteAssignmentModal');
    const alertModalElement = document.getElementById('alertModal');
    if (!modalElement) return;

    const deleteModal = new bootstrap.Modal(modalElement);
    const alertModal = new bootstrap.Modal(alertModalElement);
    const titleSpan = document.getElementById('delete-assignment-title');
    const confirmBtn = document.getElementById('confirm-delete-assignment');

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-assignment');
        if (!btn) return;
        e.preventDefault();
        const assignmentId = btn.getAttribute('data-assignment-id');
        deleteForm = document.getElementById('delete-form-' + assignmentId);
        if (!deleteForm) return;
        if (titleSpan) titleSpan.textContent = btn.getAttribute('data-assignment-title') || '';
        deleteModal.show();
    });

    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', function () {
        if (!deleteForm) return;
        const formData = new FormData(deleteForm);
        fetch(deleteForm.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            deleteModal.hide();
            const alertIcon = document.getElementById('alert-icon');
            const alertTitle = document.getElementById('alert-title');
            const alertMessage = document.getElementById('alert-message');
            if (data.success) {
                if (alertIcon) alertIcon.innerHTML = '<span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-check-circle"></i></span>';
                if (alertTitle) alertTitle.textContent = 'نجح';
                if (alertMessage) alertMessage.textContent = data.message || 'تم حذف الواجب بنجاح';
                alertModal.show();
                setTimeout(() => {
                    const row = deleteForm.closest('tr');
                    if (row) {
                        row.remove();
                        if (!document.querySelector('.assignments-table-row')) location.reload();
                    } else {
                        location.reload();
                    }
                }, 1200);
            } else {
                if (alertIcon) alertIcon.innerHTML = '<span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-alert-circle"></i></span>';
                if (alertTitle) alertTitle.textContent = 'خطأ';
                if (alertMessage) alertMessage.textContent = data.message || 'حدث خطأ أثناء حذف الواجب';
                alertModal.show();
            }
        })
        .catch(() => {
            deleteModal.hide();
            alertModal.show();
        });
    });
});
</script>
@stop
