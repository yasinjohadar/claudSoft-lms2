{{--
    Shared SEO block for blog archive/collection pages (index, category, tag, search).
    Expects:
      $type            'index'|'category'|'tag'|'search'
      $model           BlogCategory|BlogTag|null (null for index/search)
      $posts           the paginator
      $canonicalUrl    pre-computed, already page-aware
      $pageTitle       FINAL title (caller must already apply any page-N suffix —
                        see below for why: this partial renders inside @push('head'),
                        which is too late to retroactively change the <title> tag
                        set via @section('title', ...) at the top of the view)
      $pageDescription FINAL description, same rule as $pageTitle
      $pageKeywords    optional plain string
      $ogImage         optional, defaults to the site logo
      $forceNoindex    optional bool, default false — used by search results
--}}
@php
    $currentPage = method_exists($posts, 'currentPage') ? $posts->currentPage() : 1;
    $isPaged = $currentPage > 1;
    $effectiveTitle = $pageTitle;
    $effectiveDescription = $pageDescription;
    $robots = (($forceNoindex ?? false) || $isPaged) ? 'noindex,follow' : 'index,follow';
    $ogImageUrl = $ogImage ?? asset('frontend2/assets/images/logo.png');
@endphp

<link rel="canonical" href="{{ $canonicalUrl }}">

@if(!empty($pageKeywords))
<meta name="keywords" content="{{ $pageKeywords }}">
@endif

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $effectiveTitle }}">
<meta property="og:description" content="{{ $effectiveDescription }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="ar_SA">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $effectiveTitle }}">
<meta name="twitter:description" content="{{ $effectiveDescription }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">

<meta name="robots" content="{{ $robots }}">

@php
    $breadcrumbItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('frontend.home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'المدونة', 'item' => route('frontend.blog.index')],
    ];
    if ($model) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $model->name, 'item' => $model->url];
    } elseif (($type ?? null) === 'search') {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => 'نتائج البحث'];
    }
    $breadcrumbJson = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];

    $itemListElements = collect($posts->items())->take(10)->values()->map(fn ($post, $i) => [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => ['@type' => 'Article', 'name' => $post->title, 'url' => $post->url],
    ])->all();

    $collectionJson = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $effectiveTitle,
        'description' => $effectiveDescription,
        'url' => $canonicalUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => method_exists($posts, 'total') ? $posts->total() : count($itemListElements),
            'itemListElement' => $itemListElements,
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($collectionJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
