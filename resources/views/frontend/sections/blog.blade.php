<!-- Start Blog Section -->
<section class="blog-section">
    <div class="container">
        <!-- Section Header -->
        <div class="d-flex justify-content-between">
            <h2 class="mb-4">المدونة:</h2>
            <a href="{{ route('frontend.blog.index') }}">عرض المزيد</a>
        </div>

        <!-- Blog Posts Grid -->
        <div class="row g-4">
            @forelse($latestPosts as $post)
            <div class="col-md-6 col-lg-4">
                <article class="blog-card">
                    <a href="{{ $post->url }}" class="blog-link">
                        <!-- Post Image -->
                        <div class="blog-image">
                            @if($post->featured_image)
                                <img src="{{ blog_image_url($post->featured_image) }}"
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
                                @foreach($post->tags->take(2) as $tag)
                                <span class="tag-badge">#{{ $tag->name }}</span>
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
            @empty
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="fa-solid fa-newspaper fa-3x text-muted mb-3"></i>
                    <h4>لا توجد مقالات حالياً</h4>
                    <p class="text-muted">سيتم نشر المقالات قريباً</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>
<!-- End Blog Section -->
