@extends('frontend2.layouts.master')

@php
    $baseTitle = ($tag->meta_title ?: '#'.$tag->name).' - المدونة | '.config('app.name');
    $baseDescription = $tag->meta_description ?: 'تصفح مقالات موسومة بـ '.$tag->name.' في مدونة '.config('app.name');
    $canonicalUrl = route('frontend.blog.tag', array_merge(['slug' => $tag->slug], request()->query()));
    // BlogTag has no meta_keywords column — synthesize one for parity with the category page.
    $tagKeywords = $tag->name.', مدونة, مقالات, وسوم';
    $currentPage = $posts->currentPage();
    $pageTitle = $currentPage > 1 ? $baseTitle.' — صفحة '.$currentPage : $baseTitle;
    $pageDescription = $currentPage > 1 ? $baseDescription.' (صفحة '.$currentPage.')' : $baseDescription;
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    @include('frontend2.pages.partials.blog-seo-collection', [
        'type' => 'tag',
        'model' => $tag,
        'posts' => $posts,
        'canonicalUrl' => $canonicalUrl,
        'pageTitle' => $pageTitle,
        'pageDescription' => $pageDescription,
        'pageKeywords' => $tagKeywords,
    ])
@endpush

@section('content')

    <!-- Page Banner -->
    <section class="page-banner page-banner-blog">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-tag"></i></div>
                <h1 class="page-banner-title">#{{ $tag->name }}</h1>
                @if($tag->description)
                <p class="page-banner-desc">{{ $tag->description }}</p>
                @endif
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.blog.index') }}">المدونة</a>
                    <span class="page-banner-sep">/</span>
                    <span>#{{ $tag->name }}</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding" style="padding-top: 30px;">
        <div class="container">
            <div class="row g-4">
                @forelse($posts as $post)
                    @include('frontend2.pages.partials.blog-card', ['post' => $post, 'eager' => $loop->first])
                @empty
                    <div class="col-12">
                        <div class="glass-panel text-center py-5 animate-on-scroll">
                            <i class="fas fa-newspaper fa-4x mb-3" style="color: var(--clr-text-muted);"></i>
                            <h4>لا توجد مقالات بهذا الوسم حالياً</h4>
                            <p class="text-muted mb-0">تصفح <a href="{{ route('frontend.blog.index') }}">كل المقالات</a> بدلاً من ذلك.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($posts->hasPages())
            <div class="f2-pagination-wrap mt-5 animate-on-scroll">
                {{ $posts->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </section>

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
                                <a href="{{ $cat->url }}" class="filter-btn">{{ $cat->name }} ({{ $cat->published_posts_count ?? 0 }})</a>
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
                            @foreach($popularTags as $t)
                                <a href="{{ $t->url }}" class="filter-btn {{ $t->id === $tag->id ? 'active' : '' }}">#{{ $t->name }}</a>
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
