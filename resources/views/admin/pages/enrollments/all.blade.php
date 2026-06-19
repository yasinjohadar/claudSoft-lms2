@extends('admin.layouts.master')

@section('page-title')
    جميع الانضمامات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الانضمامات</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-user-check me-1"></i>
                            إدارة الانضمامات
                        </span>
                        <h2 class="group-show-hero__title mb-2">كافة الانضمامات</h2>
                        <p class="group-show-hero__desc mb-0">
                            متابعة تسجيلات الطلاب في الكورسات، الحالات، نسب الإنجاز، وطلبات الانتظار من لوحة واحدة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <button type="button"
                                    class="group-show-action group-show-action--primary border-0 bg-transparent w-100 text-start"
                                    data-bs-toggle="modal" data-bs-target="#selectCourseModal">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة انضمام جديد</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-users', 'label' => 'إجمالي الانضمامات', 'value' => $totalEnrollments, 'sub' => 'في جميع الكورسات'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'انضمامات نشطة', 'value' => $activeCount, 'sub' => 'نشطة حالياً'],
                    ['variant' => 'cyan', 'icon' => 'fe-award', 'label' => 'انضمامات مكتملة', 'value' => $completedCount, 'sub' => 'أنهى الطلاب الكورس'],
                    ['variant' => 'orange', 'icon' => 'fe-clock', 'label' => 'طلبات معلقة', 'value' => $pendingCount, 'sub' => 'في انتظار الموافقة'],
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

            @if($pendingCount > 0)
                <div class="alert alert-warning d-flex align-items-center dashboard-fade-in mb-4" role="alert">
                    <i class="fe fe-alert-triangle me-2 fs-18 flex-shrink-0"></i>
                    <div>
                        <strong>تنبيه:</strong> يوجد <strong>{{ $pendingCount }}</strong> طلب تسجيل في انتظار الموافقة.
                        <a href="{{ route('enrollments.all', ['status' => 'pending']) }}" class="alert-link ms-1">عرض الطلبات المعلقة</a>
                    </div>
                </div>
            @endif

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الانضمامات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث عن طالب أو كورس، أو فلتر حسب الكورس والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('enrollments.all') }}" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-5 col-md-6">
                                <label class="form-label" for="enrollmentsSearchInput">البحث</label>
                                <input type="text" name="search" id="enrollmentsSearchInput" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن طالب أو كورس...">
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="enrollmentsCourse">الكورس</label>
                                <select name="course_id" id="enrollmentsCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="enrollmentsStatus">الحالة</label>
                                <select name="status" id="enrollmentsStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>متوقف</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('enrollments.all') }}" class="btn btn-outline-secondary btn-sm">
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
                        قائمة الانضمامات
                        <span class="group-show-members-card__count">{{ $enrollments->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.enrollments._enrollments_table', ['enrollments' => $enrollments])
                </div>
            </div>

        </div>
    </div>

    @include('admin.pages.enrollments._enrollments_modals', ['enrollments' => $enrollments])

    <div class="modal fade" id="selectCourseModal" tabindex="-1" aria-labelledby="selectCourseModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="selectCourseModalTitle">
                        <i class="fe fe-plus me-2 text-primary"></i>إضافة انضمام جديد
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-3">
                    <label class="form-label fw-semibold" for="courseSelectEnrollment">اختر الكورس</label>
                    <select id="courseSelectEnrollment" class="form-select">
                        <option value="">— اختر كورس —</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-12 d-block mt-2">سيتم توجيهك لصفحة إضافة طالب للكورس المختار.</small>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="redirectToCreateEnrollment()">
                        <i class="fe fe-arrow-left me-1"></i>متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    function redirectToCreateEnrollment() {
        const courseId = document.getElementById('courseSelectEnrollment').value;
        if (!courseId) {
            alert('الرجاء اختيار كورس أولاً');
            return;
        }
        window.location.href = '/admin/courses/' + courseId + '/enrollments/create';
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var raw = el.getAttribute('data-countup');
        var target = parseInt(raw, 10);
        if (!target) {
            el.textContent = raw || '0';
            return;
        }
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 20));
        var timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('ar-EG');
        }, 30);
    });
</script>
@stop
