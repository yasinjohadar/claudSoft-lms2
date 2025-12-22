@extends('frontend.layouts.master')

@php
    $pageTitle = 'الكورسات والدورات التدريبية - ' . config('app.name');
    $pageDescription = 'تصفح جميع الكورسات والدورات التدريبية المتاحة في مختلف المجالات. كورسات احترافية مع شهادات معتمدة';
    $pageKeywords = 'كورسات, دورات تدريبية, تعليم اونلاين, شهادات معتمدة, تدريب';
    $canonicalUrl = route('frontend.courses.index', request()->query());
    $ogImage = asset('frontend/assets/img/default-course.jpg');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)

@push('head')
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="ar_SA">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Robots Meta --}}
    <meta name="robots" content="index, follow">

    {{-- Schema.org JSON-LD --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "{{ $pageTitle }}",
        "description": "{{ $pageDescription }}",
        "url": "{{ $canonicalUrl }}",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": {{ $courses->total() }},
            "itemListElement": [
                @foreach($courses as $index => $course)
                {
                    "@type": "ListItem",
                    "position": {{ ($courses->currentPage() - 1) * $courses->perPage() + $index + 1 }},
                    "item": {
                        "@type": "Course",
                        "name": "{{ $course->title }}",
                        "description": "{{ Str::limit(strip_tags($course->description ?? ''), 150) }}",
                        "url": "{{ route('frontend.courses.show', $course->slug) }}",
                        "provider": {
                            "@type": "Organization",
                            "name": "{{ config('app.name') }}"
                        }
                    }
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        },
        "breadcrumb": {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "الرئيسية",
                    "item": "{{ route('frontend.home') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "الكورسات",
                    "item": "{{ route('frontend.courses.index') }}"
                }
            ]
        }
    }
    </script>
@endpush

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">الكورسات</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">الكورسات</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Courses Section -->
<section class="courses-section py-5">
    <div class="container">
        <div class="row">

            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="filters-sidebar">

                    <!-- Filters Header -->
                    <div class="filters-header">
                        <h4>تصفية النتائج</h4>
                        @if(request()->hasAny(['category', 'level', 'price_type', 'sort']))
                        <a href="{{ route('frontend.courses.index') }}" class="clear-filters">مسح الكل</a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('frontend.courses.index') }}" id="filterForm">

                        <!-- Category Filter -->
                        <div class="filter-group">
                            <h5 class="filter-title">التصنيف</h5>
                            <div class="filter-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" value=""
                                           id="cat-all" {{ !request('category') ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="cat-all">
                                        جميع التصنيفات
                                    </label>
                                </div>
                                @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category"
                                           value="{{ $category->id }}" id="cat-{{ $category->id }}"
                                           {{ request('category') == $category->id ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="cat-{{ $category->id }}">
                                        <i class="fa {{ $category->icon }}"></i>
                                        {{ $category->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Level Filter -->
                        <div class="filter-group">
                            <h5 class="filter-title">المستوى</h5>
                            <div class="filter-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="level" value=""
                                           id="level-all" {{ !request('level') ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="level-all">
                                        جميع المستويات
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="level" value="beginner"
                                           id="level-beginner" {{ request('level') == 'beginner' ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="level-beginner">
                                        مبتدئ
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="level" value="intermediate"
                                           id="level-intermediate" {{ request('level') == 'intermediate' ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="level-intermediate">
                                        متوسط
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="level" value="advanced"
                                           id="level-advanced" {{ request('level') == 'advanced' ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="level-advanced">
                                        متقدم
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="filter-group">
                            <h5 class="filter-title">السعر</h5>
                            <div class="filter-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" value=""
                                           id="price-all" {{ !request('price_type') ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="price-all">
                                        الكل
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" value="free"
                                           id="price-free" {{ request('price_type') == 'free' ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="price-free">
                                        مجاني
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_type" value="paid"
                                           id="price-paid" {{ request('price_type') == 'paid' ? 'checked' : '' }}
                                           onchange="document.getElementById('filterForm').submit()">
                                    <label class="form-check-label" for="price-paid">
                                        مدفوع
                                    </label>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="col-lg-9">

                <!-- Results Header -->
                <div class="results-header mb-4">
                    <div class="results-count">
                        <h5>تم العثور على <span class="count-badge">{{ $courses->total() }}</span> كورس</h5>
                    </div>
                    <div class="sort-dropdown">
                        <form method="GET" action="{{ route('frontend.courses.index') }}" id="sortForm">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request('level'))
                                <input type="hidden" name="level" value="{{ request('level') }}">
                            @endif
                            @if(request('price_type'))
                                <input type="hidden" name="price_type" value="{{ request('price_type') }}">
                            @endif
                            <select name="sort" class="form-select" onchange="document.getElementById('sortForm').submit()">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>الأكثر شعبية</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>الأعلى تقييماً</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Courses List -->
                @if($courses->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach($courses as $course)
                    <div class="col">
                        <div class="course-card h-100">
                            <a href="{{ route('frontend.courses.show', $course->slug) }}" class="course-card-link">
                                <div class="course-thumbnail">
                                    <img src="{{ $course->thumbnail_url }}" 
                                         alt="{{ $course->title }} - {{ $course->subtitle ?? '' }}"
                                         title="{{ $course->title }}"
                                         loading="lazy"
                                         width="400"
                                         height="225">
                                    @if($course->is_featured)
                                    <span class="featured-badge">مميز</span>
                                    @endif
                                    @if($course->is_free)
                                    <span class="free-badge">مجاني</span>
                                    @endif
                                </div>
                                <div class="course-card-body">
                                    <div class="course-category-badge">
                                        <i class="fa {{ $course->category->icon }}"></i>
                                        {{ $course->category->name }}
                                    </div>
                                    <h5 class="course-card-title">{{ Str::limit($course->title, 60) }}</h5>
                                    <p class="course-card-subtitle">{{ Str::limit($course->subtitle, 80) }}</p>

                                    <div class="course-meta-info">
                                        <div class="meta-item">
                                            <i class="fa-solid fa-star text-warning"></i>
                                            <span>{{ $course->rating }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fa-solid fa-user-graduate"></i>
                                            <span>{{ number_format($course->students_count) }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fa-solid fa-clock"></i>
                                            <span>{{ $course->duration }}س</span>
                                        </div>
                                    </div>

                                    <div class="course-card-footer">
                                        <div class="course-price">
                                            @if($course->is_free)
                                                <span class="price free">مجاني</span>
                                            @else
                                                @if($course->has_discount)
                                                    <span class="price-old">{{ $course->price }} {{ $course->currency }}</span>
                                                    <span class="price">{{ $course->discount_price }} {{ $course->currency }}</span>
                                                @else
                                                    <span class="price">{{ $course->price }} {{ $course->currency }}</span>
                                                @endif
                                            @endif
                                        </div>
                                        <span class="view-btn">عرض <i class="fa-solid fa-arrow-left"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($courses->hasPages())
                <div class="pagination-wrapper mt-5">
                    <nav aria-label="Page navigation">
                        {{ $courses->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
                @endif

                @else
                <div class="empty-state text-center py-5">
                    <i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i>
                    <h4>لا توجد كورسات متاحة</h4>
                    <p class="text-muted">جرب تغيير معايير البحث</p>
                    <a href="{{ route('frontend.courses.index') }}" class="btn btn-main mt-3">عرض جميع الكورسات</a>
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
    margin-bottom: 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.page-header .breadcrumb {
    background: transparent;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.page-header .breadcrumb-item.active {
    color: #ffffff;
}

.page-header .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
}

/* Filters Sidebar */
.filters-sidebar {
    background: #ffffff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 100px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--main-Color);
}

.filters-header h4 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin: 0;
}

.clear-filters {
    color: #e74c3c;
    font-size: 14px;
    text-decoration: none;
    font-weight: 600;
}

.clear-filters:hover {
    text-decoration: underline;
}

.filter-group {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.filter-group:last-child {
    border-bottom: none;
}

.filter-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-check-label {
    cursor: pointer;
    color: #555;
    font-size: 15px;
}

.form-check-input:checked {
    background-color: var(--main-Color);
    border-color: var(--main-Color);
}

.form-check-label i {
    margin-left: 8px;
    color: var(--main-Color);
}

/* Results Header */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.results-count h5 {
    margin: 0;
    font-size: 1.1rem;
    color: #2c3e50;
}

.count-badge {
    color: var(--main-Color);
    font-weight: 700;
    font-size: 1.2rem;
}

.sort-dropdown {
    position: relative;
}

.sort-dropdown .form-select {
    border: 1px solid #ddd;
    padding: 8px 15px;
    padding-left: 40px;
    border-radius: 8px;
    font-size: 14px;
    min-width: 200px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: left 0.75rem center;
    background-size: 16px 12px;
    appearance: none;
}

.sort-dropdown .form-select:focus {
    border-color: var(--main-Color);
    box-shadow: 0 0 0 0.2rem rgba(242, 145, 37, 0.25);
}

/* Course Cards */
.course-card {
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.course-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.course-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.course-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.course-card:hover .course-thumbnail img {
    transform: scale(1.1);
}

.featured-badge,
.free-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--main-Color);
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.free-badge {
    background: #38c172;
}

.course-card-body {
    padding: 20px;
}

.course-category-badge {
    display: inline-block;
    background: #f0f0f0;
    color: #555;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    margin-bottom: 10px;
}

.course-category-badge i {
    margin-left: 5px;
}

.course-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 10px;
    min-height: 55px;
}

.course-card-subtitle {
    color: #777;
    font-size: 14px;
    margin-bottom: 15px;
    min-height: 40px;
}

.course-meta-info {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
}

.meta-item i {
    font-size: 14px;
}

.course-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.course-price {
    display: flex;
    align-items: center;
    gap: 10px;
}

.price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--main-Color);
}

.price.free {
    color: #38c172;
}

.price-old {
    font-size: 1rem;
    color: #999;
    text-decoration: line-through;
}

.view-btn {
    color: var(--secondary-Color);
    font-weight: 600;
    font-size: 14px;
}

.view-btn i {
    margin-right: 5px;
    transition: margin 0.3s ease;
}

.course-card:hover .view-btn i {
    margin-right: 10px;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    gap: 5px;
}

.pagination-wrapper .page-link {
    color: var(--secondary-Color);
    border: 1px solid #dee2e6;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-wrapper .page-link:hover {
    background: var(--main-Color);
    color: #ffffff;
    border-color: var(--main-Color);
}

.pagination-wrapper .page-item.active .page-link {
    background: var(--secondary-Color);
    border-color: var(--secondary-Color);
    color: #ffffff;
}

.pagination-wrapper .page-item.disabled .page-link {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

/* Empty State */
.empty-state i {
    opacity: 0.3;
}

.btn-main {
    background: var(--main-Color);
    color: #ffffff;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.btn-main:hover {
    background: var(--secondary-Color);
    color: #ffffff;
}

/* Responsive */
@media (max-width: 992px) {
    .filters-sidebar {
        position: static;
    }

    .results-header {
        flex-direction: column;
        gap: 15px;
    }

    .sort-dropdown .form-select {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .course-card-title {
        min-height: auto;
    }

    .course-card-subtitle {
        min-height: auto;
    }
}
</style>

@endsection
