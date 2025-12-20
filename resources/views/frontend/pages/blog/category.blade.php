@extends('frontend.layouts.master')

@section('title', $category->meta_title ?: $category->name . ' - المدونة')
@section('meta_description', $category->meta_description ?: 'تصفح مقالات ' . $category->name)
@section('meta_keywords', $category->meta_keywords)

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">
                    @if($category->icon)
                    <i class="{{ $category->icon }} me-2"></i>
                    @endif
                    {{ $category->name }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog.index') }}">المدونة</a></li>
                        <li class="breadcrumb-item active">{{ $category->name }}</li>
                    </ol>
                </nav>
                @if($category->description)
                <p class="text-muted mt-3">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Posts Section -->
<section class="blog-content py-5">
    <div class="container">
        <div class="row">

            <!-- Main Content -->
            <div class="col-lg-8">

                @if($posts->count() > 0)
                <div class="posts-grid">
                    <div class="row g-4">
                        @foreach($posts as $post)
                        <div class="col-md-6">
                            <article class="blog-card">
                                <a href="{{ $post->url }}" class="card-link">
                                    <div class="card-image">
                                        @if($post->featured_image)
                                            <img src="{{ asset('storage/' . $post->featured_image) }}"
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
                    <div class="mt-5">
                        {{ $posts->links() }}
                    </div>
                    @endif
                </div>
                @else
                <div class="empty-state text-center py-5">
                    <i class="fa-solid fa-folder-open fa-4x text-muted mb-3"></i>
                    <h4>لا توجد مقالات في هذا التصنيف</h4>
                    <p class="text-muted">سيتم نشر المقالات قريباً</p>
                    <a href="{{ route('frontend.blog.index') }}" class="btn btn-primary mt-3">
                        <i class="fa-solid fa-arrow-right me-2"></i>
                        العودة للمدونة
                    </a>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <aside class="blog-sidebar">

                    <!-- All Categories -->
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-folder"></i>
                            جميع التصنيفات
                        </h4>
                        <div class="categories-list">
                            @foreach($categories as $cat)
                            <a href="{{ $cat->url }}"
                               class="category-item {{ $cat->id == $category->id ? 'active' : '' }}"
                               style="border-right-color: {{ $cat->color ?? '#007bff' }}">
                                @if($cat->icon)
                                <i class="{{ $cat->icon }} me-2"></i>
                                @endif
                                {{ $cat->name }}
                                <span class="posts-count">{{ $cat->published_posts_count }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Popular Tags -->
                    @if($popularTags->count() > 0)
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-tags"></i>
                            الوسوم الشائعة
                        </h4>
                        <div class="tags-cloud">
                            @foreach($popularTags as $tag)
                            <a href="{{ $tag->url }}"
                               class="tag-cloud-item"
                               style="background-color: {{ $tag->color ?? '#e9ecef' }}">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Latest Posts -->
                    @if($posts->count() > 0)
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-clock"></i>
                            أحدث المقالات
                        </h4>
                        <div class="latest-posts">
                            @foreach($posts->take(5) as $latest)
                            <a href="{{ $latest->url }}" class="latest-post-item">
                                @if($latest->featured_image)
                                <img src="{{ asset('storage/' . $latest->featured_image) }}"
                                     alt="{{ $latest->title }}">
                                @else
                                <div class="post-placeholder">
                                    <i class="fa-solid fa-file-alt"></i>
                                </div>
                                @endif
                                <div class="post-info">
                                    <h5>{{ Str::limit($latest->title, 50) }}</h5>
                                    <span class="post-date">
                                        <i class="fa-solid fa-calendar"></i>
                                        {{ $latest->published_at->diffForHumans() }}
                                    </span>
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

/* Blog Content */
.blog-content {
    background: #f8f9fa;
    min-height: 70vh;
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
    display: flex;
    align-items: center;
    gap: 10px;
}

.widget-title i {
    color: var(--main-Color);
}

/* Categories List */
.categories-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    border-right: 4px solid;
}

.category-item:hover {
    background: var(--secondary-Color);
    color: white;
    transform: translateX(-5px);
}

.category-item.active {
    background: var(--main-Color);
    color: white;
    border-right-color: var(--secondary-Color) !important;
}

.category-item i {
    margin-left: 8px;
}

.posts-count {
    background: white;
    color: var(--secondary-Color);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.category-item:hover .posts-count,
.category-item.active .posts-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Tags Cloud */
.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag-cloud-item {
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    color: #495057;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.tag-cloud-item:hover {
    background: var(--main-Color) !important;
    color: white !important;
    border-color: var(--main-Color) !important;
    transform: translateY(-2px);
}

/* Latest Posts */
.latest-posts {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.latest-post-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.latest-post-item:hover {
    background: var(--secondary-Color);
    color: white;
    transform: translateX(-5px);
}

.latest-post-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}

.post-placeholder {
    width: 80px;
    height: 80px;
    background: #e9ecef;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #adb5bd;
    flex-shrink: 0;
}

.post-info {
    flex-grow: 1;
}

.post-info h5 {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--secondary-Color);
    margin-bottom: 5px;
    line-height: 1.4;
}

.latest-post-item:hover .post-info h5 {
    color: white;
}

.post-date {
    font-size: 0.85rem;
    color: #868e96;
    display: flex;
    align-items: center;
    gap: 5px;
}

.latest-post-item:hover .post-date {
    color: rgba(255, 255, 255, 0.9);
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

    .blog-title {
        font-size: 1.1rem;
    }

    .blog-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .widget-title {
        font-size: 1.1rem;
    }
}
</style>

@endsection
