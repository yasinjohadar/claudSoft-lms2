{{-- Shared post-card markup for blog grids. Expects: $post, optional $eager (bool, defaults false). --}}
<div class="col-lg-4 col-md-6">
    <div class="glass-panel blog-card animate-on-scroll animate-delay-{{ min($loop->iteration ?? 1, 3) }}">
        <div class="blog-img-wrapper">
            @if($post->featured_image)
                <img src="{{ blog_image_url($post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}" width="400" height="200"
                     @if($eager ?? false) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
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
