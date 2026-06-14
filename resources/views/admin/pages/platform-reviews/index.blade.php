@extends('admin.layouts.master')

@section('page-title')
    تقييمات المنصة
@stop

@section('styles')
    @include('admin.pages.platform-reviews.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb platform-reviews-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">تقييمات المنصة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in platform-reviews-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-star me-1"></i>آراء الطلاب</span>
                        <h2 class="group-show-hero__title mb-2">تقييمات المنصة</h2>
                        <p class="group-show-hero__desc mb-0">مراجعة تقييمات الطلاب، الموافقة عليها، تمييز الأفضل، وعرضها في الواجهة الأمامية.</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 platform-reviews-page-animate dashboard-fade-in">
                @include('admin.pages.platform-reviews.partials.stats', ['stats' => $stats])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in platform-reviews-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية التقييمات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو البريد أو نص التقييم، وفلتر حسب الحالة والنجوم والتميز.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('admin.platform-reviews.index') }}" id="filterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-5 col-md-6">
                                <label class="form-label" for="reviewSearch">البحث</label>
                                <input type="text" id="reviewSearch" name="search" class="form-control"
                                       placeholder="ابحث بالاسم، البريد أو النص..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="reviewStatus">الحالة</label>
                                <select name="status" id="reviewStatus" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مقبولة</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>في الانتظار</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="reviewRating">التقييم</label>
                                <select name="rating" id="reviewRating" class="form-select">
                                    <option value="">الكل</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} نجوم</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="reviewFeatured">التميز</label>
                                <select name="featured" id="reviewFeatured" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="yes" {{ request('featured') == 'yes' ? 'selected' : '' }}>مميزة</option>
                                    <option value="no" {{ request('featured') == 'no' ? 'selected' : '' }}>غير مميزة</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    @if(request()->hasAny(['search', 'status', 'rating', 'featured']))
                                        <a href="{{ route('admin.platform-reviews.index') }}" class="btn btn-light" title="مسح الفلاتر">
                                            <i class="fe fe-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in platform-reviews-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة التقييمات
                        <span class="group-show-members-card__count">{{ $reviews->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3 px-0 pb-0">
                    @include('admin.pages.platform-reviews._reviews_table', ['reviews' => $reviews])
                </div>
                @if($reviews->hasPages())
                    <div class="card-footer">{{ $reviews->withQueryString()->links() }}</div>
                @endif
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    ['reviewStatus', 'reviewRating', 'reviewFeatured'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });
        }
    });
})();
</script>
@stop
