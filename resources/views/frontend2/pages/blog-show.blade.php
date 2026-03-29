@extends('frontend2.layouts.master')

@php
    $seoTags = $post->getSeoMetaTags();
    $ogImageUrl = !empty($seoTags['og:image']) ? blog_image_url($seoTags['og:image']) : asset('frontend2/assets/images/logo.png');
    $twitterImageUrl = !empty($seoTags['twitter:image']) ? blog_image_url($seoTags['twitter:image']) : $ogImageUrl;
@endphp

@section('title', $seoTags['title'])
@section('meta_description', $seoTags['description'] ?? Str::limit(strip_tags($post->excerpt ?? ''), 160))

@push('head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <link rel="canonical" href="{{ $seoTags['canonical'] ?? $post->url }}">

    <meta property="og:title" content="{{ $seoTags['og:title'] }}">
    <meta property="og:description" content="{{ $seoTags['og:description'] }}">
    <meta property="og:type" content="{{ $seoTags['og:type'] ?? 'article' }}">
    <meta property="og:url" content="{{ $post->url }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:locale" content="{{ $seoTags['og:locale'] ?? 'ar_SA' }}">
    @if($post->published_at)
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endif
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    @if($post->author)
    <meta property="article:author" content="{{ $post->author->name }}">
    @endif

    <meta name="twitter:card" content="{{ $seoTags['twitter:card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seoTags['twitter:title'] }}">
    <meta name="twitter:description" content="{{ $seoTags['twitter:description'] }}">
    <meta name="twitter:image" content="{{ $twitterImageUrl }}">
    @if(!empty($post->twitter_creator))
    <meta name="twitter:creator" content="{{ $post->twitter_creator }}">
    @endif

    @if(!empty($seoTags['robots']))
    <meta name="robots" content="{{ $seoTags['robots'] }}">
    @endif

    {{-- Schema.org Article --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "{{ addslashes($seoTags['title']) }}",
        "description": "{{ addslashes(Str::limit(strip_tags($post->excerpt ?? ''), 200)) }}",
        "image": "{{ $ogImageUrl }}",
        "datePublished": "{{ $post->published_at?->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at->toIso8601String() }}",
        "author": {
            "@@type": "Person",
            "name": "{{ addslashes($post->author->name ?? 'المدير') }}"
            @if($post->author && $post->author->avatar)
            , "image": "{{ asset('storage/' . $post->author->avatar) }}"
            @endif
        },
        "publisher": {
            "@@type": "Organization",
            "name": "{{ addslashes(config('app.name')) }}",
            "logo": { "@@type": "ImageObject", "url": "{{ asset('frontend2/assets/images/logo.png') }}" }
        },
        "mainEntityOfPage": { "@@type": "WebPage", "@@id": "{{ $post->url }}" }
        @if($post->category)
        , "articleSection": "{{ addslashes($post->category->name) }}"
        @endif
        @if($post->tags->count() > 0)
        , "keywords": "{{ addslashes($post->tags->pluck('name')->implode(', ')) }}"
        @endif
        , "inLanguage": "ar"
        , "wordCount": {{ str_word_count(strip_tags($post->content ?? '')) }}
        @if($post->reading_time)
        , "timeRequired": "PT{{ $post->reading_time }}M"
        @endif
    }
    </script>

    {{-- Breadcrumb Schema --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            { "@@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ route('frontend.home') }}" },
            { "@@type": "ListItem", "position": 2, "name": "المدونة", "item": "{{ route('frontend.blog.index') }}" }
            @if($post->category)
            , { "@@type": "ListItem", "position": 3, "name": "{{ addslashes($post->category->name) }}", "item": "{{ $post->category->url }}" }
            , { "@@type": "ListItem", "position": 4, "name": "{{ addslashes(Str::limit($post->title, 60)) }}" }
            @else
            , { "@@type": "ListItem", "position": 3, "name": "{{ addslashes(Str::limit($post->title, 60)) }}" }
            @endif
        ]
    }
    </script>
@endpush

@section('content')

    {{-- Page Banner --}}
    <section class="page-banner page-banner-blog">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-blog"></i></div>
                <h1 class="page-banner-title" itemprop="headline">{{ $post->title }}</h1>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.blog.index') }}">المدونة</a>
                    @if($post->category)
                    <span class="page-banner-sep">/</span>
                    <a href="{{ $post->category->url }}">{{ $post->category->name }}</a>
                    @endif
                    <span class="page-banner-sep">/</span>
                    <span>{{ Str::limit($post->title, 50) }}</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <article class="blog-article section-padding">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-lg-8">
                    {{-- Meta line: ألوان تتبع الوضع الليلي --}}
                    <div class="article-meta-line d-flex flex-wrap gap-3 mb-3 small animate-on-scroll">
                        @if($post->published_at)
                        <span><i class="fas fa-calendar-alt me-1"></i>{{ $post->published_at->format('d F Y') }}</span>
                        @endif
                        @if($post->author)
                        <span><i class="fas fa-user me-1"></i>{{ $post->author->name }}</span>
                        @endif
                        <span><i class="fas fa-eye me-1"></i>{{ number_format($post->views_count ?? 0) }} مشاهدة</span>
                        @if($post->reading_time)
                        <span><i class="fas fa-clock me-1"></i>{{ $post->reading_time }} دقيقة قراءة</span>
                        @endif
                        @if($post->category)
                        <a href="{{ $post->category->url }}" class="text-decoration-none article-meta-link"><i class="fas fa-folder me-1"></i>{{ $post->category->name }}</a>
                        @endif
                    </div>

                    @if($post->featured_image)
                    <figure class="glass-panel overflow-hidden rounded-3 mb-4 animate-on-scroll">
                        <img src="{{ blog_image_url($post->featured_image) }}"
                             alt="{{ $post->featured_image_alt ?: $post->title }}"
                             class="img-fluid w-100"
                             loading="lazy"
                             width="1200"
                             height="630">
                        @if($post->featured_image_alt)
                        <figcaption class="p-3 text-center small article-meta-line border-top">{{ $post->featured_image_alt }}</figcaption>
                        @endif
                    </figure>
                    @endif

                    <div class="article-content glass-panel p-4 p-lg-5 rounded-3">
                        {!! $post->content !!}
                    </div>

                    @if($post->tags->count() > 0)
                    <div class="glass-panel p-4 rounded-3 mt-4 animate-on-scroll">
                        <h5 class="mb-3"><i class="fas fa-tags me-2" style="color: var(--clr-primary);"></i> الوسوم</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                            <a href="{{ $tag->url }}" class="filter-btn">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="glass-panel p-4 rounded-3 mt-4 animate-on-scroll">
                        <h5 class="mb-3"><i class="fas fa-share-nodes me-2" style="color: var(--clr-primary);"></i> شارك المقال</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}" target="_blank" rel="noopener" class="btn btn-sm rounded-pill" style="background:#1877f2; color:#fff;"><i class="fab fa-facebook-f me-1"></i> فيسبوك</a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($post->url) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="btn btn-sm rounded-pill" style="background:#1da1f2; color:#fff;"><i class="fab fa-twitter me-1"></i> تويتر</a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($post->url) }}&title={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="btn btn-sm rounded-pill" style="background:#0077b5; color:#fff;"><i class="fab fa-linkedin-in me-1"></i> لينكد إن</a>
                            <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $post->url) }}" target="_blank" rel="noopener" class="btn btn-sm rounded-pill" style="background:#25d366; color:#fff;"><i class="fab fa-whatsapp me-1"></i> واتساب</a>
                        </div>
                    </div>

                    @if($previousPost || $nextPost)
                    <div class="row g-3 mt-4 animate-on-scroll">
                        @if($previousPost)
                        <div class="col-md-6">
                            <a href="{{ $previousPost->url }}" class="glass-panel d-block p-4 rounded-3 text-decoration-none h-100" style="color: inherit;">
                                <span class="small d-block mb-2" style="color: var(--clr-primary);"><i class="fas fa-arrow-right me-1"></i> المقال السابق</span>
                                <strong>{{ Str::limit($previousPost->title, 55) }}</strong>
                            </a>
                        </div>
                        @endif
                        @if($nextPost)
                        <div class="col-md-6">
                            <a href="{{ $nextPost->url }}" class="glass-panel d-block p-4 rounded-3 text-decoration-none h-100 text-start" style="color: inherit;">
                                <span class="small d-block mb-2" style="color: var(--clr-primary);">المقال التالي <i class="fas fa-arrow-left ms-1"></i></span>
                                <strong>{{ Str::limit($nextPost->title, 55) }}</strong>
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if($post->author)
                    <div class="glass-panel p-4 rounded-3 mb-4 animate-on-scroll">
                        <div class="text-center">
                            @if($post->author->avatar)
                            <img src="{{ asset('storage/' . $post->author->avatar) }}" alt="{{ $post->author->name }}" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover; border: 3px solid var(--clr-primary);">
                            @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:80px;height:80px;background:var(--clr-primary);color:#fff;font-size:1.8rem;font-weight:700;">{{ strtoupper(mb_substr($post->author->name, 0, 1)) }}</div>
                            @endif
                            <h5 class="mb-1">{{ $post->author->name }}</h5>
                            <p class="small article-meta-line mb-2">كاتب المقال</p>
                            @auth
                                @if(method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.blog.posts.edit', $post->id) }}" class="btn btn-outline-warning btn-sm w-100">تعديل (لوحة التحكم)</a>
                                @endif
                            @endauth
                            @if($post->author->bio ?? null)
                            <p class="small article-meta-line mt-2 mb-0">{{ $post->author->bio }}</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($popularPosts->count() > 0)
                    <div class="glass-panel p-4 rounded-3 animate-on-scroll">
                        <h5 class="mb-3"><i class="fas fa-fire me-2" style="color: var(--clr-primary);"></i> مقالات شائعة</h5>
                        <div class="d-flex flex-column gap-3">
                            @foreach($popularPosts as $popular)
                            <a href="{{ $popular->url }}" class="d-flex gap-3 align-items-start text-decoration-none w-100 min-w-0" style="color: inherit;">
                                @if($popular->featured_image)
                                <img src="{{ blog_image_url($popular->featured_image) }}" alt="" class="rounded flex-shrink-0" width="70" height="70" style="object-fit: cover;">
                                @else
                                <div class="rounded d-flex align-items-center justify-content-center bg-secondary bg-opacity-25 flex-shrink-0" style="width:70px;height:70px;min-width:70px;"><i class="fas fa-newspaper text-muted"></i></div>
                                @endif
                                <div class="flex-grow-1 min-w-0 overflow-hidden">
                                    <strong class="d-block text-truncate">{{ Str::limit($popular->title, 45) }}</strong>
                                    <span class="small text-muted"><i class="fas fa-eye me-1"></i>{{ $popular->views_count }}</span>
                                    <span class="small text-muted me-2"><i class="fas fa-calendar me-1"></i>{{ $popular->published_at?->diffForHumans() }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($relatedPosts->count() > 0)
        <section class="blog-related-section">
            <div class="container">
                <h4 class="blog-related-heading mb-4 animate-on-scroll"><i class="fas fa-link me-2" style="color: var(--clr-primary);"></i> مقالات ذات صلة</h4>
                <div class="row g-4">
                    @foreach($relatedPosts as $related)
                    <div class="col-lg-4 col-md-6 animate-on-scroll">
                        <a href="{{ $related->url }}" class="text-decoration-none d-block h-100" style="color: inherit;">
                            <div class="glass-panel blog-card h-100 animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                                <div class="blog-img-wrapper">
                                    @if($related->featured_image)
                                    <img src="{{ blog_image_url($related->featured_image) }}" alt="{{ $related->featured_image_alt ?: $related->title }}" width="400" height="180" loading="lazy">
                                    @else
                                    <img src="{{ asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $related->title }}" width="400" height="180" loading="lazy">
                                    @endif
                                    @if($related->category)
                                    <div style="position: absolute; top: 12px; right: 12px; background: var(--clr-primary); color: #fff; padding: 3px 12px; border-radius: 50px; font-size: 0.72rem; font-weight: 600;">{{ $related->category->name }}</div>
                                    @endif
                                </div>
                                <div class="blog-body">
                                    <div class="blog-meta">
                                        <span><i class="fas fa-calendar-alt"></i> {{ $related->published_at ? $related->published_at->format('d F Y') : '—' }}</span>
                                        @if($related->reading_time)
                                        <span><i class="fas fa-clock"></i> {{ $related->reading_time }} دقائق</span>
                                        @endif
                                    </div>
                                    <h5 class="blog-related-card-title">{{ Str::limit($related->title, 70) }}</h5>
                                    <p>{{ Str::limit(strip_tags($related->excerpt ?? ''), 100) }}</p>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--clr-border);">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="" width="30" height="30" loading="lazy" style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--clr-primary); object-fit: cover;">
                                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $related->author?->name ?? 'المدير' }}</span>
                                        </div>
                                        <span class="read-more" style="margin-top: 0;">المزيد <i class="fas fa-arrow-left"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </article>

    <style>
    /* مقالات ذات صلة: فاصل وعناوين متعددة الأسطر */
    .blog-related-section {
        margin-top: 0.5rem;
        padding-top: 2.5rem;
        padding-bottom: 0.5rem;
        border-top: 1px solid var(--clr-border);
    }
    .blog-related-card-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
        line-height: 1.45;
    }

    /* خلفية المحتوى: في الوضع النهاري لون رمادي-أزرق خفيف بدل الأبيض الصريح */
    .blog-article .article-content { font-size: 1.05rem; line-height: 1.85; color: var(--clr-text); background: rgba(248, 250, 252, 0.95); opacity: 1; visibility: visible; min-height: 1px; }
    :root:not([data-theme="dark"]) .blog-article .article-content { background: rgba(248, 250, 252, 0.95); border: 1px solid rgba(0,0,0,0.06); }
    .blog-article .article-content h2 { font-size: 1.6rem; font-weight: 700; margin: 2rem 0 1rem; color: var(--clr-primary); }
    .blog-article .article-content h3 { font-size: 1.3rem; font-weight: 700; margin: 1.5rem 0 0.75rem; color: var(--clr-text); }
    .blog-article .article-content p { margin-bottom: 1.25rem; color: var(--clr-text); }
    .blog-article .article-content img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 1rem 0; }
    .blog-article .article-content ul, .blog-article .article-content ol { margin: 1rem 0; padding-right: 1.5rem; color: var(--clr-text); }
    .blog-article .article-content li { margin-bottom: 0.5rem; color: var(--clr-text); }
    .blog-article .article-content blockquote { border-right: 4px solid var(--clr-primary); padding: 1rem 1.25rem; background: var(--clr-surface); margin: 1.5rem 0; border-radius: var(--radius-sm); font-style: italic; color: var(--clr-text); }
    /* كود مضمّن (أي code ليس داخل pre — يشمل span>code من المحرّر): لون محايد + LTR */
    .blog-article .article-content code {
        background: rgba(15, 23, 42, 0.07) !important;
        color: #0f172a !important;
        border: 1px solid rgba(15, 23, 42, 0.12);
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.88em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: left;
        white-space: pre-wrap;
        word-break: break-word;
        display: inline-block;
        max-width: 100%;
        vertical-align: middle;
    }
    .blog-article .article-content pre { background: #2d2d2d; color: #f8f8f2; padding: 1.25rem; border-radius: var(--radius-md); overflow-x: auto; margin: 1.5rem 0; direction: ltr; text-align: left; }
    .blog-article .article-content pre code {
        background: transparent !important;
        padding: 0 !important;
        color: inherit !important;
        border: none !important;
        white-space: pre !important;
        word-break: normal !important;
        font-size: inherit !important;
        display: block !important;
        max-width: none !important;
        vertical-align: baseline !important;
    }
    .blog-article .article-content a { color: var(--clr-primary); }
    .blog-article .article-content a:hover { color: var(--clr-primary-light); }

    /* في كلا الوضعين: إزالة خلفيات العناصر الداخلية من المحرر حتى لا تظهر صناديق بيضاء متكدسة */
    .blog-article .article-content * { background: transparent !important; color: inherit !important; }
    .blog-article .article-content pre,
    .blog-article .article-content pre * { background: #2d2d2d !important; color: #f8f8f2 !important; }
    .blog-article .article-content pre code { background: transparent !important; color: #f8f8f2 !important; border: none !important; }
    .blog-article .article-content code {
        background: rgba(15, 23, 42, 0.07) !important;
        color: #0f172a !important;
        border: 1px solid rgba(15, 23, 42, 0.12) !important;
    }
    /* إلغاء الخلفيات البيضاء المضمنة (من محرر النص) في الوضعين */
    .blog-article .article-content [style*="background-color: white"],
    .blog-article .article-content [style*="background: white"],
    .blog-article .article-content [style*="background-color:#fff"],
    .blog-article .article-content [style*="background:#fff"],
    .blog-article .article-content [style*="background-color:#ffffff"],
    .blog-article .article-content [style*="background:#ffffff"],
    .blog-article .article-content [style*="rgb(255, 255, 255)"],
    .blog-article .article-content [style*="rgb(255,255,255)"] { background: transparent !important; color: inherit !important; }

    /* الوضع الليلي: خلفية المحتوى ولون النص */
    [data-theme="dark"] .blog-article .article-content { background: var(--glass-bg) !important; color: var(--clr-text) !important; border-color: var(--clr-border); }
    [data-theme="dark"] .blog-article .article-content a { color: var(--clr-primary-light) !important; }
    [data-theme="dark"] .blog-article .text-muted { color: var(--clr-text-muted) !important; }
    [data-theme="dark"] .blog-article .article-content code {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #e2e8f0 !important;
        border-color: var(--clr-border) !important;
    }
    [data-theme="dark"] .blog-article .article-content pre code {
        background: transparent !important;
        color: #f8f8f2 !important;
        border: none !important;
    }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var articleContent = document.querySelector('.article-content');
        if (!articleContent) return;
        var codeBlocks = articleContent.querySelectorAll('pre code[class*="language-"]');
        codeBlocks.forEach(function(codeBlock) {
            var pre = codeBlock.parentElement;
            if (!pre || pre.classList.contains('processed')) return;
            pre.classList.add('processed');
            if (typeof Prism !== 'undefined') Prism.highlightElement(codeBlock);
        });
        if (typeof Prism !== 'undefined') Prism.highlightAll();
    });
    </script>
@endsection
