@extends('frontend.layouts.master')

{{-- Dynamic SEO Meta Tags --}}
@php
    $seoTags = $post->getSeoMetaTags();
    $schemaData = $post->getSchemaJsonLd();
@endphp

@section('title', $seoTags['title'])
@section('meta_description', $seoTags['description'])
@section('meta_keywords', $seoTags['keywords'])

@push('head')
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $seoTags['canonical'] }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $seoTags['og:title'] }}">
    <meta property="og:description" content="{{ $seoTags['og:description'] }}">
    <meta property="og:type" content="{{ $seoTags['og:type'] }}">
    <meta property="og:url" content="{{ $post->url }}">
    @if($seoTags['og:image'])
    <meta property="og:image" content="{{ asset('storage/' . $seoTags['og:image']) }}">
    @endif
    <meta property="og:locale" content="{{ $seoTags['og:locale'] }}">
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    @if($post->author)
    <meta property="article:author" content="{{ $post->author->name }}">
    @endif

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="{{ $seoTags['twitter:card'] }}">
    <meta name="twitter:title" content="{{ $seoTags['twitter:title'] }}">
    <meta name="twitter:description" content="{{ $seoTags['twitter:description'] }}">
    @if($seoTags['twitter:image'])
    <meta name="twitter:image" content="{{ asset('storage/' . $seoTags['twitter:image']) }}">
    @endif
    @if($post->twitter_creator)
    <meta name="twitter:creator" content="{{ $post->twitter_creator }}">
    @endif

    {{-- Robots Meta --}}
    <meta name="robots" content="{{ $seoTags['robots'] }}">

    {{-- Schema.org JSON-LD - Enhanced Article Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ $seoTags['title'] }}",
        "description": "{{ $seoTags['description'] }}",
        "image": "{{ $seoTags['og:image'] ? asset('storage/' . $seoTags['og:image']) : asset('frontend/assets/img/default-blog.jpg') }}",
        "datePublished": "{{ $post->published_at->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at->toIso8601String() }}",
        "author": {
            "@type": "Person",
            "name": "{{ $post->author->name ?? 'المدير' }}",
            @if($post->author && $post->author->avatar)
            "image": "{{ asset('storage/' . $post->author->avatar) }}",
            @endif
            "url": "{{ $post->author ? route('frontend.students.show', $post->author->id) : '#' }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('frontend/assets/images/logo.png') }}"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ $post->url }}"
        },
        @if($post->category)
        "articleSection": "{{ $post->category->name }}",
        @endif
        @if($post->tags->count() > 0)
        "keywords": "{{ $post->tags->pluck('name')->implode(', ') }}",
        @endif
        "inLanguage": "ar",
        "wordCount": {{ str_word_count(strip_tags($post->content ?? '')) }},
        @if($post->reading_time)
        "timeRequired": "PT{{ $post->reading_time }}M",
        @endif
        "articleBody": "{{ Str::limit(strip_tags($post->content ?? ''), 500) }}"
    }
    </script>

    {{-- Breadcrumb Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
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
                "name": "المدونة",
                "item": "{{ route('frontend.blog.index') }}"
            },
            @if($post->category)
            {
                "@type": "ListItem",
                "position": 3,
                "name": "{{ $post->category->name }}",
                "item": "{{ $post->category->url }}"
            },
            @endif
            {
                "@type": "ListItem",
                "position": {{ $post->category ? 4 : 3 }},
                "name": "{{ $post->title }}"
            }
        ]
    }
    </script>
@endpush

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title" itemprop="headline">{{ $post->title }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog.index') }}">المدونة</a></li>
                        @if($post->category)
                        <li class="breadcrumb-item"><a href="{{ $post->category->url }}">{{ $post->category->name }}</a></li>
                        @endif
                        <li class="breadcrumb-item active">{{ Str::limit($post->title, 50) }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<article class="blog-article">
    <!-- Article Content -->
    <div class="article-content-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Featured Image -->
                    @if($post->featured_image)
                    <figure class="featured-image-wrapper mb-4">
                        <img src="{{ asset('storage/' . $post->featured_image) }}"
                             alt="{{ $post->featured_image_alt ?: $post->title }}"
                             title="{{ $post->title }}"
                             class="img-fluid"
                             loading="lazy"
                             width="1200"
                             height="630">
                        @if($post->featured_image_alt)
                        <figcaption class="image-caption">{{ $post->featured_image_alt }}</figcaption>
                        @endif
                    </figure>
                    @endif

                    <div class="article-content">
                        {!! $post->content !!}
                    </div>

                    <!-- Article Tags -->
                    @if($post->tags->count() > 0)
                    <div class="article-tags">
                        <h4 class="tags-title">
                            <i class="fa-solid fa-tags"></i>
                            الوسوم
                        </h4>
                        <div class="tags-list">
                            @foreach($post->tags as $tag)
                            <a href="{{ $tag->url }}" class="tag-item">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Share Buttons -->
                    <div class="article-share">
                        <h4 class="share-title">
                            <i class="fa-solid fa-share-nodes"></i>
                            شارك المقال
                        </h4>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}"
                               target="_blank" class="share-btn facebook" rel="noopener">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($post->url) }}&text={{ urlencode($post->title) }}"
                               target="_blank" class="share-btn twitter" rel="noopener">
                                <i class="fab fa-twitter"></i>
                                Twitter
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($post->url) }}&title={{ urlencode($post->title) }}"
                               target="_blank" class="share-btn linkedin" rel="noopener">
                                <i class="fab fa-linkedin-in"></i>
                                LinkedIn
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $post->url) }}"
                               target="_blank" class="share-btn whatsapp" rel="noopener">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </a>
                        </div>
                    </div>

                    <!-- Navigation (Previous/Next) -->
                    @if($previousPost || $nextPost)
                    <div class="article-navigation">
                        @if($previousPost)
                        <a href="{{ $previousPost->url }}" class="article-nav-item prev">
                            <div class="article-nav-label">
                                <i class="fa-solid fa-arrow-right"></i>
                                المقال السابق
                            </div>
                            <div class="article-nav-title">{{ Str::limit($previousPost->title, 50) }}</div>
                        </a>
                        @endif

                        @if($nextPost)
                        <a href="{{ $nextPost->url }}" class="article-nav-item next">
                            <div class="article-nav-label">
                                المقال التالي
                                <i class="fa-solid fa-arrow-left"></i>
                            </div>
                            <div class="article-nav-title">{{ Str::limit($nextPost->title, 50) }}</div>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <aside class="article-sidebar">
                        <!-- Author Box -->
                        @if($post->author)
                        <div class="sidebar-widget author-widget">
                            <div class="author-box">
                                <div class="author-avatar-large">
                                    @if($post->author->avatar)
                                        <img src="{{ asset('storage/' . $post->author->avatar) }}"
                                             alt="{{ $post->author->name }} - كاتب المقال"
                                             title="{{ $post->author->name }}"
                                             loading="lazy"
                                             width="100"
                                             height="100">
                                    @else
                                        <div class="avatar-placeholder-large">
                                            {{ strtoupper(substr($post->author->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <h4 class="author-name">{{ $post->author->name }}</h4>
                                <p class="author-role">كاتب المقال</p>
                                @if($post->author->bio)
                                <p class="author-bio">{{ $post->author->bio }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Popular Posts from Same Category -->
                        @if($popularPosts->count() > 0)
                        <div class="sidebar-widget popular-widget">
                            <h4 class="widget-title">
                                <i class="fa-solid fa-fire"></i>
                                مقالات شائعة
                            </h4>
                            <div class="popular-posts">
                                @foreach($popularPosts as $popular)
                                <a href="{{ $popular->url }}" class="popular-item">
                                    <div class="popular-image">
                                        @if($popular->featured_image)
                                            <img src="{{ asset('storage/' . $popular->featured_image) }}"
                                                 alt="{{ $popular->title }}"
                                                 title="{{ $popular->title }}"
                                                 loading="lazy"
                                                 width="80"
                                                 height="80">
                                        @else
                                            <div class="image-placeholder">
                                                <i class="fa-solid fa-file-alt"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="popular-content">
                                        <h5 class="popular-title">{{ Str::limit($popular->title, 60) }}</h5>
                                        <div class="popular-meta">
                                            <span><i class="fa-solid fa-eye"></i> {{ $popular->views_count }}</span>
                                            <span><i class="fa-solid fa-calendar"></i>
                                                {{ $popular->published_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Posts -->
    @if($relatedPosts->count() > 0)
    <section class="related-posts">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h3 class="section-title">
                        <i class="fa-solid fa-link"></i>
                        مقالات ذات صلة
                    </h3>
                    <div class="row g-4">
                        @foreach($relatedPosts as $related)
                        <div class="col-md-4">
                            <div class="related-card">
                                <a href="{{ $related->url }}" class="related-link">
                                    <div class="related-image">
                                        @if($related->featured_image)
                                            <img src="{{ asset('storage/' . $related->featured_image) }}"
                                                 alt="{{ $related->title }}"
                                                 title="{{ $related->title }}"
                                                 loading="lazy"
                                                 width="400"
                                                 height="225">
                                        @else
                                            <div class="image-placeholder">
                                                <i class="fa-solid fa-file-alt"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="related-content">
                                        @if($related->category)
                                        <span class="related-category">{{ $related->category->name }}</span>
                                        @endif
                                        <h4 class="related-title">{{ $related->title }}</h4>
                                        <p class="related-excerpt">{{ Str::limit($related->excerpt, 80) }}</p>
                                        <div class="related-meta">
                                            <span><i class="fa-solid fa-calendar"></i>
                                                {{ $related->published_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
</article>

<style>
/* Page Header */
.page-header {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
    margin-bottom: 40px;
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

/* Featured Image */
.featured-image-wrapper {
    border-radius: 7px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.featured-image-wrapper img {
    width: 100%;
    height: auto;
    display: block;
}

.image-caption {
    background: #f8f9fa;
    padding: 15px 20px;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}

/* Article Content */
.article-content-wrapper {
    background: white;
    padding: 60px 0;
}

.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #212529;
}

.article-content h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin: 40px 0 20px;
}

.article-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin: 30px 0 15px;
}

.article-content p {
    margin-bottom: 20px;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 30px 0;
}

.article-content ul,
.article-content ol {
    margin: 20px 0;
    padding-right: 30px;
}

.article-content li {
    margin-bottom: 10px;
}

.article-content blockquote {
    border-right: 4px solid var(--main-Color);
    padding: 20px 25px;
    background: #f8f9fa;
    margin: 30px 0;
    border-radius: 5px;
    font-style: italic;
}

.article-content code {
    background: #f8f9fa;
    padding: 3px 8px;
    border-radius: 4px;
    color: #e83e8c;
    font-size: 0.95em;
}

.article-content pre {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 20px;
    border-radius: 10px;
    overflow-x: auto;
    margin: 30px 0;
}

/* Article Tags */
.article-tags {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 15px;
    margin-top: 50px;
}

.tags-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 20px;
}

.tags-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag-item {
    padding: 8px 18px;
    border-radius: 20px;
    text-decoration: none;
    color: #495057;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: #e9ecef;
}

.tag-item:hover {
    background: var(--main-Color) !important;
    color: white !important;
    transform: translateY(-2px);
}

/* Share Buttons */
.article-share {
    background: white;
    padding: 30px;
    border: 2px solid #e9ecef;
    border-radius: 15px;
    margin-top: 40px;
}

.share-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 20px;
}

.share-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

.share-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.share-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    color: white;
}

.share-btn.facebook {
    background: #1877f2;
}

.share-btn.twitter {
    background: #1da1f2;
}

.share-btn.linkedin {
    background: #0077b5;
}

.share-btn.whatsapp {
    background: #25d366;
}

/* Article Navigation */
.article-navigation {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 50px;
}

.article-nav-item {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 15px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.article-nav-item:hover {
    background: white;
    border-color: var(--main-Color);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.article-nav-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: var(--main-Color);
    font-weight: 600;
    margin-bottom: 10px;
}

.article-nav-item.next .article-nav-label {
    justify-content: flex-end;
}

.article-nav-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--secondary-Color);
    line-height: 1.4;
}

.article-nav-item.next .article-nav-title {
    text-align: left;
}

/* Sidebar */
.article-sidebar {
    position: sticky;
    top: 100px;
}

.sidebar-widget {
    background: white;
    border-radius: 7px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.widget-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.widget-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

/* Author Widget */
.author-box {
    text-align: center;
}

.author-avatar-large {
    width: 100px;
    height: 100px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--main-Color);
}

.author-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-large {
    width: 100%;
    height: 100%;
    background: var(--secondary-Color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
}

.author-box .author-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 5px;
}

.author-box .author-role {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.author-bio {
    font-size: 0.95rem;
    color: #6c757d;
    line-height: 1.6;
}

/* Popular Posts */
.popular-posts {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.popular-item {
    display: flex;
    gap: 15px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    padding: 15px;
    border-radius: 10px;
}

.popular-item:hover {
    background: #f8f9fa;
}

.popular-image {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
}

.popular-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dee2e6;
    font-size: 1.5rem;
}

.popular-content {
    flex-grow: 1;
}

.popular-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 8px;
    line-height: 1.4;
}

.popular-meta {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #6c757d;
}

.popular-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.popular-meta i {
    color: var(--main-Color);
}

/* Related Posts */
.related-posts {
    background: #f8f9fa;
    padding: 60px 0;
}

.related-posts .section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 40px;
    text-align: center;
}

.related-posts .section-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

.related-card {
    background: white;
    border-radius: 7px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.related-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.related-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.related-image {
    height: 180px;
    overflow: hidden;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-card:hover .related-image img {
    transform: scale(1.1);
}

.related-content {
    padding: 20px;
}

.related-category {
    display: inline-block;
    background: var(--secondary-Color);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

.related-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 10px;
    line-height: 1.4;
}

.related-excerpt {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.related-meta {
    font-size: 0.85rem;
    color: #6c757d;
}

.related-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.related-meta i {
    color: var(--main-Color);
}

/* Responsive */
@media (max-width: 991px) {
    .article-sidebar {
        position: static;
        margin-top: 40px;
    }

    .related-posts .row {
        margin-top: 30px;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }
    .article-content {
        font-size: 1rem;
    }

    .share-buttons {
        flex-direction: column;
    }

    .share-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

@endsection
