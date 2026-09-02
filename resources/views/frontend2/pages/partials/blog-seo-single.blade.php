{{-- Shared SEO block for a single blog post page. Expects: $post (BlogPost, with author/category/tags loaded). --}}
@php
    $seoTags = $post->getSeoMetaTags();
    $ogImageUrl = !empty($seoTags['og:image']) ? blog_image_url($seoTags['og:image']) : asset('frontend2/assets/images/logo.png');
    $twitterImageUrl = !empty($seoTags['twitter:image']) ? blog_image_url($seoTags['twitter:image']) : $ogImageUrl;
@endphp

<link rel="canonical" href="{{ $seoTags['canonical'] ?? $post->url }}">

@if(!empty($seoTags['keywords']))
<meta name="keywords" content="{{ $seoTags['keywords'] }}">
@endif

<meta property="og:title" content="{{ $seoTags['og:title'] }}">
<meta property="og:description" content="{{ $seoTags['og:description'] }}">
<meta property="og:type" content="{{ $seoTags['og:type'] ?? 'article' }}">
<meta property="og:url" content="{{ $post->url }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
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

{{-- json_encode(), not addslashes() — addslashes leaves raw newlines/control
     characters that break the JSON block if a title/excerpt ever contains one. --}}
<script type="application/ld+json">{!! json_encode($post->getSchemaJsonLd(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($post->getBreadcrumbJsonLd(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
