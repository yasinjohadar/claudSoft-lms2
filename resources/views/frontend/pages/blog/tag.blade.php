@extends('frontend.layouts.master')

@section('title', $tag->meta_title ?: $tag->name . ' - المدونة')
@section('meta_description', $tag->meta_description ?: 'تصفح مقالات ' . $tag->name)

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">
                    <i class="fa-solid fa-tag me-2"></i>
                    #{{ $tag->name }}
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.blog.index') }}">المدونة</a></li>
                        <li class="breadcrumb-item active">#{{ $tag->name }}</li>
                    </ol>
                </nav>
                @if($tag->description)
                <p class="text-muted mt-3">{{ $tag->description }}</p>
                @endif
                <p class="text-muted">
                    <i class="fa-solid fa-file-alt me-2"></i>
                    {{ $posts->total() }} {{ $posts->total() == 1 ? 'مقال' : 'مقالات' }}
                </p>
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
                                <a href="{{ $post->url }}" class="blog-link">
                                    <!-- Post Image -->
                                    <div class="blog-image">
                                        @if($post->featured_image)
                                            <img src="{{ asset('storage/' . $post->featured_image) }}"
                                                 alt="{{ $post->featured_image_alt ?: $post->title }}">
                                        @else
                                            <div class="blog-image-placeholder">
                                                <i class="fa-solid fa-newspaper"></i>
                                            </div>
                                        @endif

                                        <!-- Reading Time Badge -->
                                        @if($post->reading_time)
                                        <span class="reading-badge">
                                            <i class="fa-solid fa-clock"></i>
                                            {{ $post->reading_time }} دقائق
                                        </span>
                                        @endif

                                        <!-- Category Badge -->
                                        @if($post->category)
                                        <span class="category-badge" style="background: {{ $post->category->color ?? 'var(--secondary-Color)' }}">
                                            {{ $post->category->name }}
                                        </span>
                                        @endif
                                    </div>

                                    <!-- Post Content -->
                                    <div class="blog-content">
                                        <h3 class="blog-title">{{ $post->title }}</h3>
                                        <p class="blog-excerpt">{{ Str::limit($post->excerpt, 100) }}</p>

                                        <!-- Post Meta -->
                                        <div class="blog-meta">
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

                                        <!-- Tags -->
                                        @if($post->tags->count() > 0)
                                        <div class="blog-tags">
                                            @foreach($post->tags->take(3) as $postTag)
                                            <span class="tag-badge {{ $postTag->id == $tag->id ? 'active' : '' }}">#{{ $postTag->name }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        <!-- Read More Button -->
                                        <div class="read-more">
                                            <span class="read-more-text">
                                                اقرأ المزيد
                                                <i class="fa-solid fa-arrow-left"></i>
                                            </span>
                                        </div>
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
                    <i class="fa-solid fa-tag fa-4x text-muted mb-3"></i>
                    <h4>لا توجد مقالات بهذا الوسم</h4>
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

                    <!-- Popular Tags -->
                    @if($popularTags->count() > 0)
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-tags"></i>
                            جميع الوسوم
                        </h4>
                        <div class="tags-cloud">
                            @foreach($popularTags as $popularTag)
                            <a href="{{ $popularTag->url }}"
                               class="tag-cloud-item {{ $popularTag->id == $tag->id ? 'active' : '' }}"
                               style="background-color: {{ $popularTag->color ?? '#e9ecef' }}">
                                #{{ $popularTag->name }}
                                <span class="tag-count">({{ $popularTag->posts_count }})</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- All Categories -->
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-folder"></i>
                            التصنيفات
                        </h4>
                        <div class="categories-list">
                            @foreach($categories as $category)
                            <a href="{{ $category->url }}"
                               class="category-item"
                               style="border-right-color: {{ $category->color ?? '#007bff' }}">
                                @if($category->icon)
                                <i class="{{ $category->icon }} me-2"></i>
                                @endif
                                {{ $category->name }}
                                <span class="posts-count">{{ $category->posts_count }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Latest Posts -->
                    @if($latestPosts->count() > 0)
                    <div class="sidebar-widget">
                        <h4 class="widget-title">
                            <i class="fa-solid fa-clock"></i>
                            أحدث المقالات
                        </h4>
                        <div class="latest-posts">
                            @foreach($latestPosts as $latest)
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
.tag-badge.active {
    background: var(--main-Color) !important;
    color: white !important;
    font-weight: bold;
}

.tag-cloud-item.active {
    background: var(--main-Color) !important;
    color: white !important;
    border: 2px solid var(--main-Color);
    font-weight: bold;
}

.tag-count {
    font-size: 0.85em;
    opacity: 0.8;
}
</style>

@endsection
