@extends('frontend2.layouts.master')

@php
    $pageTitle = 'المدونة | ' . config('app.name');
    $pageDescription = 'مقالات وتدوينات في البرمجة، تطوير الويب، الموبايل والذكاء الاصطناعي. مدونة أكاديمية كلاودسوفت.';
    $canonicalUrl = route('frontend.blog.index', request()->query());
    $ogImage = asset('frontend2/assets/images/logo.png');
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
@endpush

@section('content')

    <!-- Page Banner -->
    <section class="page-banner page-banner-blog">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-blog"></i></div>
                <h1 class="page-banner-title">المدونة <span>التقنية</span></h1>
                <p class="page-banner-desc">مقالات وتدوينات في البرمجة، تطوير الويب، الموبايل والذكاء الاصطناعي</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>المدونة</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding" style="padding-top: 30px;">
        <div class="container">
            <!-- Search & Filter Bar -->
            <div class="glass-panel animate-on-scroll" style="padding: 25px 30px; margin-bottom: 40px;">
                <form action="{{ route('frontend.blog.search') }}" method="GET" class="row align-items-center g-3">
                    <div class="col-lg-5">
                        <div style="position: relative;">
                            <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                                placeholder="ابحث في المدونة..."
                                style="background: var(--clr-surface); border: 1px solid var(--clr-border); color: var(--clr-text); padding: 12px 18px 12px 45px; border-radius: var(--radius-md); font-family: var(--font-family);">
                            <i class="fas fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--clr-text-muted);"></i>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="courses-filter" style="margin-bottom: 0; justify-content: flex-start;">
                            <a href="{{ route('frontend.blog.index') }}" class="filter-btn {{ !request()->has('category') && !request()->has('q') ? 'active' : '' }}">الكل</a>
                            @foreach($categories as $cat)
                                <a href="{{ route('frontend.blog.index', ['category' => $cat->slug]) }}" class="filter-btn {{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            @php $firstFeatured = $featuredPosts->first(); @endphp

            <!-- Featured Post -->
            @if($firstFeatured)
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <div class="glass-panel animate-on-scroll" style="overflow: hidden; border-radius: var(--radius-xl);">
                        <div class="row g-0 align-items-stretch">
                            <div class="col-lg-6">
                                <div style="height: 100%; min-height: 350px; position: relative; overflow: hidden;">
                                    @if($firstFeatured->featured_image)
                                        <img src="{{ blog_image_url($firstFeatured->featured_image) }}" alt="{{ $firstFeatured->featured_image_alt ?: $firstFeatured->title }}" width="400" height="200" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $firstFeatured->title }}" width="400" height="200" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                    @endif
                                    <div style="position: absolute; top: 20px; right: 20px; background: var(--clr-primary); color: #fff; padding: 5px 18px; border-radius: 50px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fas fa-star"></i> مقال مميز
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div style="padding: 40px; display: flex; flex-direction: column; justify-content: center; height: 100%;">
                                    <div style="display: flex; gap: 15px; font-size: 0.82rem; color: var(--clr-text-muted); margin-bottom: 15px; flex-wrap: wrap;">
                                        <span><i class="fas fa-calendar-alt"></i> {{ $firstFeatured->published_at ? $firstFeatured->published_at->format('d F Y') : '—' }}</span>
                                        <span><i class="fas fa-user"></i> {{ $firstFeatured->author?->name ?? 'المدير' }}</span>
                                        <span><i class="fas fa-eye"></i> {{ number_format($firstFeatured->views_count ?? 0) }} مشاهدة</span>
                                        @if(($firstFeatured->comments_count ?? 0) > 0)
                                            <span><i class="fas fa-comments"></i> {{ $firstFeatured->comments_count }} تعليق</span>
                                        @endif
                                    </div>
                                    @if($firstFeatured->category)
                                        <span style="display: inline-block; background: var(--clr-surface); padding: 3px 14px; border-radius: 50px; font-size: 0.78rem; color: var(--clr-primary); font-weight: 600; width: fit-content; margin-bottom: 12px;">{{ $firstFeatured->category->name }}</span>
                                    @endif
                                    <h3 style="font-weight: 800; margin-bottom: 15px; font-size: 1.5rem; line-height: 1.6;">{{ $firstFeatured->title }}</h3>
                                    <p style="color: var(--clr-text-secondary); font-size: 0.95rem; line-height: 1.9; margin-bottom: 20px;">{{ Str::limit(strip_tags($firstFeatured->excerpt ?? ''), 180) }}</p>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <a href="{{ $firstFeatured->url }}" class="btn-primary-custom" style="padding: 10px 25px; font-size: 0.9rem;">اقرأ المقال <i class="fas fa-arrow-left"></i></a>
                                        @if($firstFeatured->reading_time)
                                            <span style="font-size: 0.82rem; color: var(--clr-text-muted);"><i class="fas fa-clock"></i> {{ $firstFeatured->reading_time }} دقيقة قراءة</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Blog Posts Grid -->
            <div class="row g-4">
                @forelse($posts as $post)
                    @if($firstFeatured && $post->id === $firstFeatured->id)
                        @continue
                    @endif
                    <div class="col-lg-4 col-md-6">
                        <div class="glass-panel blog-card animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                            <div class="blog-img-wrapper">
                                @if($post->featured_image)
                                    <img src="{{ blog_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}" width="400" height="200" loading="lazy">
                                @else
                                    <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $post->title }}" width="400" height="200" loading="lazy">
                                @endif
                                @if($post->category)
                                    <div style="position: absolute; top: 12px; right: 12px; background: var(--clr-primary); color: #fff; padding: 3px 12px; border-radius: 50px; font-size: 0.72rem; font-weight: 600;">{{ $post->category->name }}</div>
                                @endif
                            </div>
                            <div class="blog-body">
                                <div class="blog-meta">
                                    <span><i class="fas fa-calendar-alt"></i> {{ $post->published_at ? $post->published_at->format('d F Y') : '—' }}</span>
                                    @if($post->reading_time)
                                        <span><i class="fas fa-clock"></i> {{ $post->reading_time }} دقائق</span>
                                    @endif
                                </div>
                                <h5>{{ $post->title }}</h5>
                                <p>{{ Str::limit(strip_tags($post->excerpt ?? ''), 100) }}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--clr-border);">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="الكاتب" width="30" height="30" loading="lazy" style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--clr-primary); object-fit: cover;">
                                        <span style="font-size: 0.8rem; font-weight: 600;">{{ $post->author?->name ?? 'المدير' }}</span>
                                    </div>
                                    <a href="{{ $post->url }}" class="read-more" style="margin-top: 0;">المزيد <i class="fas fa-arrow-left"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="glass-panel text-center py-5 animate-on-scroll">
                            <i class="fas fa-newspaper fa-4x mb-3" style="color: var(--clr-text-muted);"></i>
                            <h4>لا توجد مقالات متاحة حالياً</h4>
                            <p class="text-muted mb-0">سيتم نشر المقالات قريباً. تصفح <a href="{{ route('frontend.blog.index') }}">الكل</a> أو جرب البحث.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
            <div class="animate-on-scroll d-flex justify-content-center gap-2 flex-wrap mt-5">
                {{ $posts->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </section>

    <!-- Sidebar: Categories & Tags (optional strip) -->
    @if($categories->count() > 0 || $popularTags->count() > 0)
    <section class="section-padding" style="padding-top: 0;">
        <div class="container">
            <div class="row g-4">
                @if($categories->count() > 0)
                <div class="col-lg-6">
                    <div class="glass-panel animate-on-scroll" style="padding: 25px;">
                        <h5 class="mb-3"><i class="fas fa-folder-open me-2"></i> التصنيفات</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($categories as $cat)
                                <a href="{{ $cat->url }}" class="filter-btn {{ request('category') === $cat->slug ? 'active' : '' }}">{{ $cat->name }} ({{ $cat->published_posts_count ?? 0 }})</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @if($popularTags->count() > 0)
                <div class="col-lg-6">
                    <div class="glass-panel animate-on-scroll" style="padding: 25px;">
                        <h5 class="mb-3"><i class="fas fa-tags me-2"></i> الوسوم الشائعة</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($popularTags as $tag)
                                <a href="{{ $tag->url }}" class="filter-btn">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- Newsletter CTA -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2><i class="fas fa-envelope-open-text" style="margin-left: 10px;"></i> اشترك في النشرة البريدية</h2>
            <p>كن أول من يعرف عن المقالات والدورات الجديدة</p>
            <div style="max-width: 500px; margin: 0 auto;">
                <div style="display: flex; gap: 10px; background: rgba(255,255,255,0.15); padding: 6px; border-radius: 50px;">
                    <input type="email" placeholder="بريدك الإلكتروني..." class="newsletter-input"
                        style="flex: 1; padding: 12px 20px; border: none; border-radius: 50px; background: transparent; color: #fff; font-family: var(--font-family); font-size: 0.95rem; outline: none;">
                    <button type="button" class="btn-light-custom" style="padding: 10px 25px; font-size: 0.9rem; border-radius: 50px;">اشتراك <i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </section>

@endsection
