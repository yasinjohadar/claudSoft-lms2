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
            <div class="cfilter animate-on-scroll">
                <div class="cfilter__head">
                    <div class="cfilter__count">
                        <span class="cfilter__count-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div>
                            <p class="cfilter__count-label">نتائج البحث</p>
                            <h2 class="cfilter__count-value">تم العثور على <strong>{{ $courses->total() }}</strong> كورس</h2>
                        </div>
                    </div>
                    @if(request()->hasAny(['category', 'level', 'price_type', 'sort']))
                        <a href="{{ route('frontend.courses.index') }}" class="cfilter__reset">
                            <i class="fas fa-times"></i> مسح الفلاتر
                        </a>
                    @endif
                </div>

                <div class="cfilter__section">
                    <div class="cfilter__label"><i class="fas fa-tags"></i> التصنيف</div>
                    <div class="cfilter__chips">
                        <a href="{{ route('frontend.courses.index', request()->only(['level', 'price_type', 'sort'])) }}" class="cfilter__chip {{ !request('category') ? 'is-active' : '' }}">الكل</a>
                        @foreach($categories as $cat)
                            <a href="{{ route('frontend.courses.index', array_merge($queryParams, ['category' => $cat->id])) }}" class="cfilter__chip {{ request('category') == $cat->id ? 'is-active' : '' }}">
                                @if(!empty($cat->icon))<i class="fa {{ $cat->icon }}"></i>@endif
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <form method="GET" action="{{ route('frontend.courses.index') }}" id="coursesFilterForm" class="cfilter__form">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="cfilter__fields">
                        <div class="cfilter__field">
                            <label for="filterLevel"><i class="fas fa-layer-group"></i> المستوى</label>
                            <div class="cfilter__select-wrap">
                                <select id="filterLevel" name="level" class="cfilter__select" onchange="this.form.submit()">
                                    <option value="" {{ !request('level') ? 'selected' : '' }}>الكل</option>
                                    <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                    <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                    <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                </select>
                            </div>
                        </div>
                        <div class="cfilter__field">
                            <label for="filterPrice"><i class="fas fa-tag"></i> السعر</label>
                            <div class="cfilter__select-wrap">
                                <select id="filterPrice" name="price_type" class="cfilter__select" onchange="this.form.submit()">
                                    <option value="" {{ !request('price_type') ? 'selected' : '' }}>الكل</option>
                                    <option value="free" {{ request('price_type') == 'free' ? 'selected' : '' }}>مجاني</option>
                                    <option value="paid" {{ request('price_type') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                </select>
                            </div>
                        </div>
                        <div class="cfilter__field cfilter__field--wide">
                            <label for="filterSort"><i class="fas fa-sort-amount-down"></i> ترتيب النتائج</label>
                            <div class="cfilter__select-wrap">
                                <select id="filterSort" name="sort" class="cfilter__select" onchange="this.form.submit()">
                                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>الأكثر شعبية</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>الأعلى تقييماً</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>السعر: من الأقل للأعلى</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>السعر: من الأعلى للأقل</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Courses Grid -->
            <div class="ccards-grid">
                @forelse($courses as $course)
                <a href="{{ route('frontend.courses.show', $course->slug) }}" class="ccard animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                    <div class="ccard__media">
                        <img
                            src="{{ $course->thumbnail ? course_image_url($course->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}"
                            alt="{{ $course->title }}"
                            width="800"
                            height="450"
                            loading="lazy"
                            decoding="async"
                        >
                        @if($course->is_featured)
                            <span class="ccard__badge">مميز</span>
                        @endif
                        @if($course->is_free)
                            <span class="ccard__badge ccard__badge--free">مجاني</span>
                        @elseif($course->hasDiscount())
                            <span class="ccard__badge ccard__badge--sale">خصم {{ $course->discount_percentage }}%</span>
                        @endif
                    </div>
                    <div class="ccard__body">
                        @if($course->category)
                            <span class="ccard__cat"><i class="fa {{ $course->category->icon ?? 'fa-folder' }}"></i> {{ $course->category->name }}</span>
                        @endif
                        <h3 class="ccard__title">{{ $course->title }}</h3>
                        <p class="ccard__excerpt">{{ Str::limit(strip_tags($course->subtitle ?? $course->description ?? ''), 100) }}</p>
                        <div class="ccard__stats">
                            @if($course->rating > 0)
                                <span><i class="fas fa-star"></i> {{ $course->rating }}</span>
                            @endif
                            <span><i class="fas fa-user-graduate"></i> {{ number_format($course->students_count ?? 0) }}</span>
                            <span><i class="fas fa-clock"></i> {{ $course->duration ? number_format((float)$course->duration, 0) . ' ساعة' : '—' }}</span>
                        </div>
                    </div>
                    <div class="ccard__footer">
                        @if($course->is_free)
                            <span class="ccard__price ccard__price--free">مجاني</span>
                        @elseif($course->hasDiscount())
                            <span class="ccard__price-wrap">
                                <span class="ccard__old">{{ number_format((float)$course->price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                                <span class="ccard__price">{{ number_format((float)$course->discount_price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                            </span>
                        @else
                            <span class="ccard__price">{{ number_format((float)$course->price, 0) }} {{ $course->currency ?? 'ر.س' }}</span>
                        @endif
                        <span class="ccard__more">عرض <i class="fas fa-arrow-left"></i></span>
                    </div>
                </a>
                @empty
                <div class="ccards-empty">
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
            <div class="f2-pagination-wrap mt-5 animate-on-scroll">
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
