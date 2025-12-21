@extends('frontend.layouts.master')

@php
    $pageTitle = 'نتائج البحث: ' . $keyword . ' | ' . config('app.name');
    $pageDescription = 'نتائج البحث عن: ' . $keyword . ' في المدونة';
    $pageKeywords = 'بحث, ' . $keyword . ', مدونة, مقالات';
    $canonicalUrl = route('frontend.blog.search', ['q' => $keyword]);
    $ogImage = asset('frontend/assets/img/default-blog.jpg');
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
@endpush

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">نتائج البحث</h1>
                <p class="page-subtitle">البحث عن: <strong>"{{ $keyword }}"</strong></p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog.index') }}">المدونة</a></li>
                        <li class="breadcrumb-item active">نتائج البحث</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="blog-content py-5">
    <div class="container">
        <div class="row">

            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Search Results Section -->
                <div class="search-results">
                    <div class="section-header mb-4">
                        <h2 class="section-title">
                            <i class="fa-solid fa-search"></i>
                            نتائج البحث
                            @if($posts->total() > 0)
                            <span class="results-count">({{ $posts->total() }} نتيجة)</span>
                            @endif
                        </h2>
                    </div>

                    @if($posts->count() > 0)
                        <div class="row g-4">
                            @foreach($posts as $post)
                            <div class="col-md-6">
                                <article class="blog-card">
                                    <a href="{{ $post->url }}" class="card-link">
                                        <div class="card-image">
                                            @if($post->featured_image)
                                                <img src="{{ blog_image_url($post->featured_image) }}"
                                                     alt="{{ $post->featured_image_alt ?: $post->title }}"
                                                     title="{{ $post->title }}"
                                                     loading="lazy"
                                                     width="600"
                                                     height="400">
                                            @else
                                                <div class="image-placeholder">
                                                    <i class="fa-solid fa-file-alt"></i>
                                                </div>
                                            @endif
                                            @if($post->reading_time)
                                            <span class="reading-time">
                                                <i class="fa-solid fa-clock"></i>
                                                {{ $post->reading_time }} دقائق
                                            </span>
                                            @endif
                                        </div>
                                        <div class="card-content">
                                            @if($post->category)
                                            <span class="post-category" style="background: {{ $post->category->color ?? '#0555a2' }}">
                                                {{ $post->category->name }}
                                            </span>
                                            @endif
                                            <h3 class="post-title">{{ $post->title }}</h3>
                                            <p class="post-excerpt">{{ Str::limit($post->excerpt, 120) }}</p>

                                            <div class="post-meta">
                                                <div class="meta-left">
                                                    <span class="meta-item">
                                                        <i class="fa-solid fa-user"></i>
                                                        {{ $post->author?->name ?? 'المدير' }}
                                                    </span>
                                                    <span class="meta-item">
                                                        <i class="fa-solid fa-calendar"></i>
                                                        {{ $post->published_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                                <div class="meta-right">
                                                    <span class="meta-item">
                                                        <i class="fa-solid fa-eye"></i>
                                                        {{ $post->views_count }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if($post->tags->count() > 0)
                                            <div class="post-tags">
                                                @foreach($post->tags->take(3) as $tag)
                                                <span class="tag-badge">
                                                    #{{ $tag->name }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </a>
                                </article>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($posts->hasPages())
                        <div class="pagination-wrapper mt-5">
                            <nav aria-label="Search results pagination">
                                {{ $posts->appends(['q' => $keyword])->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                        @endif

                    @else
                        <div class="empty-state text-center py-5">
                            <i class="fa-solid fa-search fa-4x text-muted mb-3"></i>
                            <h4>لم يتم العثور على نتائج</h4>
                            <p class="text-muted">لم نتمكن من العثور على مقالات تتطابق مع بحثك "<strong>{{ $keyword }}</strong>"</p>
                            <div class="mt-4">
                                <a href="{{ route('frontend.blog.index') }}" class="btn btn-primary">
                                    <i class="fa-solid fa-arrow-right me-2"></i>
                                    العودة إلى المدونة
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <aside class="blog-sidebar">

                    <!-- Search Box -->
                    <div class="sidebar-widget search-widget">
                        <h3 class="widget-title">
                            <i class="fa-solid fa-search"></i>
                            البحث في المدونة
                        </h3>
                        <form action="{{ route('frontend.blog.search') }}" method="GET" class="search-form">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control"
                                       placeholder="ابحث عن مقال..." 
                                       value="{{ $keyword }}"
                                       required>
                                <button type="submit" class="btn btn-search">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    @if($categories->count() > 0)
                    <div class="sidebar-widget categories-widget">
                        <h3 class="widget-title">
                            <i class="fa-solid fa-folder"></i>
                            التصنيفات
                        </h3>
                        <ul class="categories-list">
                            @foreach($categories as $category)
                            <li class="category-item">
                                <a href="{{ $category->url }}" class="category-link">
                                    @if($category->icon)
                                        <i class="{{ $category->icon }}"></i>
                                    @else
                                        <i class="fa-solid fa-folder-open"></i>
                                    @endif
                                    <span class="category-name">{{ $category->name }}</span>
                                    <span class="category-count">{{ $category->published_posts_count }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Popular Tags Widget -->
                    @if($popularTags->count() > 0)
                    <div class="sidebar-widget tags-widget">
                        <h3 class="widget-title">
                            <i class="fa-solid fa-tags"></i>
                            الوسوم الشائعة
                        </h3>
                        <div class="tags-cloud">
                            @foreach($popularTags as $tag)
                            <a href="{{ $tag->url }}" class="tag-item"
                               style="background: {{ $tag->color ?? '#e9ecef' }}">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </aside>
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
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.page-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 20px;
}

.page-subtitle strong {
    color: var(--main-Color);
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

/* Blog Content */
.blog-content {
    background: #f8f9fa;
    min-height: 70vh;
}

.section-header {
    border-right: 4px solid var(--main-Color);
    padding-right: 15px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin: 0;
}

.section-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

.results-count {
    font-size: 1.2rem;
    color: var(--main-Color);
    font-weight: 600;
}

/* Blog Cards */
.blog-card {
    background: white;
    border-radius: 7px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.card-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.card-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card:hover .card-image img {
    transform: scale(1.05);
}

.image-placeholder {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #dee2e6;
}

.reading-time {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.85rem;
}

.card-content {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.post-category {
    display: inline-block;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.post-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 10px;
    line-height: 1.4;
}

.post-excerpt {
    color: #6c757d;
    margin-bottom: 15px;
    line-height: 1.6;
    flex-grow: 1;
}

.post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #868e96;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
    margin-bottom: 10px;
}

.meta-left,
.meta-right {
    display: flex;
    gap: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.meta-item i {
    color: var(--main-Color);
}

.post-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-badge {
    background: #f8f9fa;
    color: #495057;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
}

/* Sidebar */
.blog-sidebar {
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

/* Search Widget */
.search-form .input-group {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.search-form .form-control {
    border: 2px solid #e9ecef;
    padding: 12px 15px;
    border-left: none;
}

.search-form .form-control:focus {
    border-color: var(--main-Color);
    box-shadow: none;
}

.btn-search {
    background: var(--main-Color);
    color: white;
    border: 2px solid var(--main-Color);
    padding: 0 20px;
}

.btn-search:hover {
    background: var(--secondary-Color);
    border-color: var(--secondary-Color);
}

/* Categories Widget */
.categories-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-item {
    margin-bottom: 12px;
}

.category-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
}

.category-link:hover {
    background: var(--secondary-Color);
    color: white;
    transform: translateX(-5px);
}

.category-link i {
    color: var(--main-Color);
    width: 20px;
}

.category-link:hover i {
    color: white;
}

.category-name {
    flex-grow: 1;
    font-weight: 600;
}

.category-count {
    background: white;
    color: var(--secondary-Color);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.category-link:hover .category-count {
    background: var(--main-Color);
    color: white;
}

/* Tags Widget */
.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag-item {
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    color: #495057;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.tag-item:hover {
    background: var(--main-Color) !important;
    color: white !important;
    border-color: var(--main-Color) !important;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    background: white;
    border-radius: 15px;
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-state .btn {
    margin-top: 20px;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .page-link {
    color: var(--secondary-Color);
    border: 1px solid #dee2e6;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 0 3px;
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

/* Responsive */
@media (max-width: 991px) {
    .blog-sidebar {
        position: static;
        margin-top: 40px;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .page-subtitle {
        font-size: 1rem;
    }

    .section-title {
        font-size: 1.5rem;
    }

    .post-title {
        font-size: 1.1rem;
    }

    .post-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

@endsection

