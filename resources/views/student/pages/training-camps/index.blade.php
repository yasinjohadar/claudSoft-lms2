@extends('student.layouts.master')

@section('page-title')
    المعسكرات التدريبية
@stop

@section('content')
<div class="main-content app-content student-training-camps-page">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb admin-dashboard-welcome dashboard-fade-in">
            <nav class="mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المعسكرات التدريبية</li>
                </ol>
            </nav>
            <h4 class="mb-1 admin-dashboard-welcome__title">المعسكرات التدريبية المتاحة</h4>
            <p class="mb-0 text-muted admin-dashboard-welcome__subtitle">
                استكشف المعسكرات النشطة وسجّل في البرنامج المناسب لك.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fe fe-check-circle me-2"></i>{!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fe fe-alert-circle me-2"></i>{!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @include('student.pages.training-camps.partials.index-stats', ['stats' => $stats ?? []])

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية المعسكرات</h4>
                <p class="fs-12 text-muted mb-0">ابحث بالاسم أو المدرب، أو فلتر حسب التخصص والحالة.</p>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('student.training-camps.index') }}" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-lg-5 col-md-6">
                            <label class="form-label" for="campSearch">بحث</label>
                            <input type="text" name="search" id="campSearch" class="form-control"
                                   placeholder="البحث بالاسم أو المدرب..." value="{{ request('search') }}">
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <label class="form-label" for="campCategory">التخصص</label>
                            <select name="category_id" id="campCategory" class="form-select">
                                <option value="">جميع التخصصات</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id', request('category')) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-2 col-md-6">
                            <label class="form-label" for="campStatus">الحالة</label>
                            <select name="status" id="campStatus" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>قادم</option>
                                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>جاري</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-12">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fe fe-search me-1"></i>بحث
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'category_id', 'category', 'status']))
                            <div class="col-12">
                                <a href="{{ route('student.training-camps.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة المعسكرات
                    <span class="group-show-members-card__count">{{ $camps->total() }}</span>
                </h6>
                <a href="{{ route('student.training-camps.my-enrollments') }}" class="btn btn-sm btn-primary-light">
                    <i class="fe fe-layers me-1"></i>تسجيلاتي
                </a>
            </div>
            <div class="card-body pt-3">
                <div class="row g-4">
                    @forelse($camps as $index => $camp)
                        @include('student.pages.training-camps.partials.camp-card', [
                            'camp' => $camp,
                            'index' => $index,
                            'userEnrollments' => $userEnrollments ?? [],
                        ])
                    @empty
                        <div class="col-12">
                            <div class="group-show-empty py-5">
                                <i class="fe fe-flag group-show-empty__icon"></i>
                                <h5 class="group-show-empty__title">لا توجد معسكرات متاحة</h5>
                                <p class="group-show-empty__desc mb-3">جرّب تعديل الفلاتر أو عد لاحقاً.</p>
                                <a href="{{ route('student.training-camps.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($camps->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $camps->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function initCampCountup() {
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            if (el.dataset.countupAnimated === 'true') return;

            const target = parseFloat(el.dataset.countup || '0');
            const duration = 900;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            }

            el.dataset.countupAnimated = 'true';
            requestAnimationFrame(step);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCampCountup);
    } else {
        initCampCountup();
    }
})();
</script>
@endpush
