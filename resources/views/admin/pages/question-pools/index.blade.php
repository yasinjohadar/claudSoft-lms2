@extends('admin.layouts.master')

@section('page-title')
    مجموعات الأسئلة
@stop

@section('styles')
    @include('admin.pages.question-pools.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb qp-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">مجموعات الأسئلة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in qp-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-layers me-1"></i>
                            تنظيم الأسئلة
                        </span>
                        <h2 class="group-show-hero__title mb-2">مجموعات الأسئلة</h2>
                        <p class="group-show-hero__desc mb-0">
                            تجميع الأسئلة في مجموعات مرتبطة بالكورسات لاستخدامها في الاختبارات العشوائية وبنك الأسئلة.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('question-pools.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة مجموعة جديدة</span>
                            </a>
                            <a href="{{ route('question-bank.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-database"></i></span>
                                <span class="group-show-action__text">بنك الأسئلة</span>
                            </a>
                            <a href="{{ route('random-pool-quizzes.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-shuffle"></i></span>
                                <span class="group-show-action__text">اختبارات بنك عشوائي</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 qp-page-animate">
                @include('admin.pages.question-pools.partials.stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in qp-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية المجموعات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث باسم المجموعة أو فلتر حسب الكورس والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('question-pools.index') }}" id="qpFilterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="qpSearch">البحث</label>
                                <input type="text" id="qpSearch" name="search" class="form-control"
                                       placeholder="ابحث عن مجموعة..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qpCourse">الكورس</label>
                                <select name="course_id" id="qpCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qpStatus">الحالة</label>
                                <select name="status" id="qpStatus" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="filled" {{ in_array(request('status'), ['filled', 'active']) ? 'selected' : '' }}>جاهزة (تحتوي أسئلة)</option>
                                    <option value="empty" {{ in_array(request('status'), ['empty', 'inactive']) ? 'selected' : '' }}>فارغة</option>
                                </select>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <button type="button" id="qpResetBtn" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in qp-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة المجموعات
                        <span class="group-show-members-card__count">{{ $pools->total() }}</span>
                    </h6>
                    @if($pools->total() > 0)
                        <span class="fs-12 text-muted">
                            عرض {{ $pools->firstItem() }}–{{ $pools->lastItem() }} من {{ $pools->total() }}
                        </span>
                    @endif
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.question-pools._table', ['pools' => $pools])
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    function initQpCountup(root) {
        (root || document).querySelectorAll('[data-countup]').forEach(function (el) {
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

    initQpCountup();

    const resetBtn = document.getElementById('qpResetBtn');
    const form = document.getElementById('qpFilterForm');
    if (resetBtn && form) {
        resetBtn.addEventListener('click', function () {
            window.location.href = form.getAttribute('action');
        });
    }
})();
</script>
@endsection
