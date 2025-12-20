{{-- SEO Meta Tags Component --}}

@php
    $courseUrl = $course->canonical_url ?? route('frontend.courses.show', $course->slug);
    $courseImage = $course->og_image ?? ($course->thumbnail ? $course->thumbnail_url : asset('frontend/assets/img/default-course.jpg'));
    $courseTitle = $course->og_title ?? $course->meta_title ?? $course->title;
    $courseDescription = $course->og_description ?? $course->meta_description ?? Str::limit(strip_tags($course->description ?? ''), 160);
    $courseKeywords = $course->meta_keywords ?? $course->focus_keyword ?? '';
    $courseAuthor = $course->author ?? ($course->instructor->name ?? config('app.name'));
    $robotsMeta = $course->robots ?? 'index, follow';
@endphp

{{-- Basic Meta Tags --}}
<meta name="description" content="{{ $courseDescription }}">
@if($courseKeywords)
<meta name="keywords" content="{{ $courseKeywords }}">
@endif
<meta name="author" content="{{ $courseAuthor }}">
<meta name="robots" content="{{ $robotsMeta }}">
<link rel="canonical" href="{{ $courseUrl }}">

{{-- Open Graph (Facebook, LinkedIn) --}}
<meta property="og:title" content="{{ $courseTitle }}">
<meta property="og:description" content="{{ $courseDescription }}">
<meta property="og:image" content="{{ $courseImage }}">
<meta property="og:type" content="{{ $course->og_type ?? 'website' }}">
<meta property="og:url" content="{{ $courseUrl }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="ar_SA">
@if($course->is_free)
<meta property="product:price:amount" content="0">
<meta property="product:price:currency" content="{{ $course->currency ?? 'SAR' }}">
@else
<meta property="product:price:amount" content="{{ $course->discount_price ?? $course->price ?? 0 }}">
<meta property="product:price:currency" content="{{ $course->currency ?? 'SAR' }}">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $course->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $course->twitter_title ?? $courseTitle }}">
<meta name="twitter:description" content="{{ $course->twitter_description ?? $courseDescription }}">
<meta name="twitter:image" content="{{ $course->twitter_image ?? $courseImage }}">
@if(config('services.twitter.username'))
<meta name="twitter:site" content="@{{ config('services.twitter.username') }}">
@endif

{{-- Additional SEO --}}
@if($course->reading_time)
<meta name="twitter:label1" content="وقت القراءة">
<meta name="twitter:data1" content="{{ $course->reading_time }} دقيقة">
@endif
@if($course->students_count > 0)
<meta name="twitter:label2" content="عدد الطلاب">
<meta name="twitter:data2" content="{{ number_format($course->students_count) }} طالب">
@endif

{{-- Schema.org JSON-LD Structured Data --}}
@php
    $schemaData = $course->schema_markup ?? (method_exists($course, 'generateSchemaMarkup') ? $course->generateSchemaMarkup() : []);
    if (empty($schemaData) && method_exists($course, 'generateSchemaMarkup')) {
        $schemaData = $course->generateSchemaMarkup();
    }
@endphp
@if(!empty($schemaData))
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Aggregate Rating Schema --}}
@if($course->reviews_count > 0 && $course->rating > 0)
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AggregateRating",
  "itemReviewed": {
    "@type": "Course",
    "name": "{{ $course->title }}"
  },
  "ratingValue": "{{ number_format($course->rating, 1) }}",
  "reviewCount": "{{ $course->reviews_count }}",
  "bestRating": "5",
  "worstRating": "1"
}
</script>
@endif

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
      "name": "الكورسات",
      "item": "{{ route('frontend.courses.index') }}"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "{{ $course->category->name ?? 'التصنيف' }}",
      "item": "{{ route('frontend.courses.index', ['category' => $course->category_id]) }}"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "{{ $course->title }}",
      "item": "{{ $courseUrl }}"
    }
  ]
}
</script>
