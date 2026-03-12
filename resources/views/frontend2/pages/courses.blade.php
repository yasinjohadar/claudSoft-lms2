@extends('frontend2.layouts.master')

@php
    $pageTitle = 'الكورسات والدورات التدريبية | ' . config('app.name');
    $pageDescription = 'تصفح جميع الكورسات والدورات التدريبية المتاحة. كورسات احترافية مع شهادات معتمدة - أكاديمية كلاودسوفت.';
    $canonicalUrl = route('frontend.courses.index', request()->query());
    $ogImage = asset('frontend2/assets/images/logo.png');
    $queryParams = request()->only(['category', 'level', 'price_type', 'sort']);
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SA">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "CollectionPage",
        "name": "{{ addslashes($pageTitle) }}",
        "description": "{{ addslashes($pageDescription) }}",
        "url": "{{ $canonicalUrl }}",
        "mainEntity": {
            "@@type": "ItemList",
            "numberOfItems": {{ $courses->total() }},
            "itemListElement": [
                @foreach($courses as $index => $course)
                {
                    "@@type": "ListItem",
                    "position": {{ ($courses->currentPage() - 1) * $courses->perPage() + $index + 1 }},
                    "item": {
                        "@@type": "Course",
                        "name": "{{ addslashes($course->title) }}",
                        "description": "{{ addslashes(Str::limit(strip_tags($course->description ?? ''), 150)) }}",
                        "url": "{{ route('frontend.courses.show', $course->slug) }}",
                        "provider": { "@@type": "Organization", "name": "{{ addslashes(config('app.name')) }}" }
                    }
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        },
        "breadcrumb": {
            "@@type": "BreadcrumbList",
            "itemListElement": [
                { "@@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ route('frontend.home') }}" },
                { "@@type": "ListItem", "position": 2, "name": "الكورسات", "item": "{{ route('frontend.courses.index') }}" }
            ]
        }
    }
    </script>
@endpush

@section('content')

    <!-- Page Banner -->
    <section class="page-banner page-banner-courses">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-graduation-cap"></i></div>
                <h1 class="page-banner-title">جميع <span>الكورسات</span></h1>
                <p class="page-banner-desc">دورات تدريبية عملية من الصفر إلى الاحتراف في تطوير الويب، البرمجة والموبايل</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>الكورسات</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding" style="padding-top: 30px;">
        <div class="container">
            <!-- Filters Bar -->
            <div class="glass-panel animate-on-scroll mb-4" style="padding: 25px 30px;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <h5 class="mb-0" style="color: var(--clr-text);">
                        تم العثور على <span style="color: var(--clr-primary); font-weight: 700;">{{ $courses->total() }}</span> كورس
                    </h5>
                    @if(request()->hasAny(['category', 'level', 'price_type', 'sort']))
                    <a href="{{ route('frontend.courses.index') }}" class="btn btn-sm" style="background: var(--clr-surface); color: var(--clr-text); border: 1px solid var(--clr-border);">مسح الفلاتر</a>
                    @endif
                </div>

                <!-- Row 1: Categories -->
                <div class="mb-3">
                    <span class="d-block small mb-2" style="color: var(--clr-text-muted);">التصنيف:</span>
                    <div class="courses-filter" style="margin-bottom: 0; justify-content: flex-start;">
                        <a href="{{ route('frontend.courses.index', request()->only(['level', 'price_type', 'sort'])) }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">الكل</a>
                        @foreach($categories as $cat)
                        <a href="{{ route('frontend.courses.index', array_merge($queryParams, ['category' => $cat->id])) }}" class="filter-btn {{ request('category') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>

                <!-- Row 2: Level, Price, Sort -->
                <form method="GET" action="{{ route('frontend.courses.index') }}" id="coursesFilterForm" class="row g-3 align-items-end">
                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    <div class="col-md-3">
                        <label class="form-label small" style="color: var(--clr-text-muted);">المستوى</label>
                        <select name="level" class="form-select form-select-sm" style="background: var(--clr-surface); border-color: var(--clr-border); color: var(--clr-text);" onchange="this.form.submit()">
                            <option value="" {{ !request('level') ? 'selected' : '' }}>الكل</option>
                            <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                            <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                            <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small" style="color: var(--clr-text-muted);">السعر</label>
                        <select name="price_type" class="form-select form-select-sm" style="background: var(--clr-surface); border-color: var(--clr-border); color: var(--clr-text);" onchange="this.form.submit()">
                            <option value="" {{ !request('price_type') ? 'selected' : '' }}>الكل</option>
                            <option value="free" {{ request('price_type') == 'free' ? 'selected' : '' }}>مجاني</option>
                            <option value="paid" {{ request('price_type') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small" style="color: var(--clr-text-muted);">ترتيب النتائج</label>
                        <select name="sort" class="form-select form-select-sm" style="background: var(--clr-surface); border-color: var(--clr-border); color: var(--clr-text);" onchange="this.form.submit()">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>الأكثر شعبية</option>
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>الأعلى تقييماً</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Courses Grid -->
            <div class="row g-4">
                @forelse($courses as $course)
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('frontend.courses.show', $course->slug) }}" class="glass-panel course-card animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}" style="text-decoration: none; color: inherit; display: block; height: 100%;">
                        <div class="course-img-wrapper">
                            <img src="{{ $course->thumbnail ? course_image_url($course->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $course->title }}" width="400" height="200" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                            @if($course->is_featured)<span class="course-badge">مميز</span>@endif
                            @if($course->is_free)<span class="course-badge" style="top: auto; right: auto; bottom: 10px; left: 10px; background: var(--clr-secondary);">مجاني</span>@endif
                            @if($course->hasDiscount())<span class="course-badge" style="top: 10px; left: 10px; right: auto; background: #e74c3c;">خصم {{ $course->discount_percentage }}%</span>@endif
                        </div>
                        <div class="course-body">
                            @if($course->category)
                            <span class="d-inline-block small mb-2" style="color: var(--clr-primary);">
                                <i class="fa {{ $course->category->icon ?? 'fa-folder' }} me-1"></i>{{ $course->category->name }}
                            </span>
                            @endif
                            <h5 class="course-card-title">{{ Str::limit($course->title, 55) }}</h5>
                            <p class="course-card-subtitle">{{ Str::limit(strip_tags($course->subtitle ?? $course->description ?? ''), 80) }}</p>
                            <div class="course-meta d-flex flex-wrap gap-2 small" style="color: var(--clr-text-muted);">
                                @if($course->rating > 0)<span><i class="fas fa-star text-warning"></i> {{ $course->rating }}</span>@endif
                                <span><i class="fas fa-user-graduate"></i> {{ number_format($course->students_count ?? 0) }}</span>
                                <span><i class="fas fa-clock"></i> {{ $course->duration ? number_format((float)$course->duration, 1) . ' ساعة' : '—' }}</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            @if($course->is_free)
                                <span class="price" style="color: var(--clr-secondary); font-weight: 700;">مجاني</span>
                            @else
                                @if($course->hasDiscount())
                                    <span class="text-decoration-line-through small me-1" style="color: var(--clr-text-muted);">{{ number_format((float)$course->price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                                    <span class="price" style="color: var(--clr-primary); font-weight: 700;">{{ number_format((float)$course->discount_price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                                @else
                                    <span class="price" style="color: var(--clr-primary); font-weight: 700;">{{ number_format((float)$course->price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                                @endif
                            @endif
                            <span class="view-link" style="color: var(--clr-primary); font-weight: 600;">عرض <i class="fas fa-arrow-left me-1"></i></span>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-12">
                    <div class="glass-panel text-center py-5 animate-on-scroll">
                        <i class="fas fa-inbox fa-4x mb-3" style="color: var(--clr-text-muted);"></i>
                        <h4>لا توجد كورسات متاحة</h4>
                        <p class="mb-0" style="color: var(--clr-text-muted);">جرب تغيير معايير البحث أو مسح الفلاتر</p>
                        <a href="{{ route('frontend.courses.index') }}" class="btn-primary-custom mt-3">عرض جميع الكورسات</a>
                    </div>
                </div>
                @endforelse
            </div>

            @if($courses->hasPages())
            <div class="d-flex justify-content-center mt-5 animate-on-scroll">
                {{ $courses->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2>لم تجد ما تبحث عنه؟</h2>
            <p>تواصل معنا واخبرنا عن المجال الذي تريد تعلمه وسنساعدك</p>
            <a href="{{ route('frontend.contact') }}" class="btn-light-custom">
                <i class="fas fa-envelope"></i> تواصل معنا
            </a>
        </div>
    </section>

@endsection
