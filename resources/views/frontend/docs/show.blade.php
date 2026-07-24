@extends('frontend.docs.layout')

@section('title')
    {{ $page->meta_title ?: $page->title . ' — ' . $category->name }}
@endsection

@push('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ e($page->meta_description) }}">
    @endif
@endpush

@push('docs-toolbar')
    @if($page->isPublished())
        <a href="{{ $page->pdfUrl() }}"
           class="docs-export-pdf-btn"
           target="_blank"
           rel="noopener"
           title="تصدير هذه الصفحة كـ PDF">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
            <span>تصدير PDF</span>
        </a>
    @endif
@endpush

@section('content')
    <div class="container docs-article-view">
        <nav class="docs-breadcrumb" aria-label="مسار التنقل">
            <a href="{{ route('frontend.docs.index') }}">التوثيق</a>
            <span class="docs-breadcrumb__sep">/</span>
            <a href="{{ route('frontend.docs.category', ['categorySlug' => $category->slug]) }}">{{ $category->name }}</a>
            <span class="docs-breadcrumb__sep">/</span>
            <span aria-current="page">{{ $page->title }}</span>
        </nav>

        <header>
            <div class="header-tag">{{ $category->name }}</div>
            <h1>{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="header-desc">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section class="content-section docs-content">
            {!! $page->content !!}
        </section>

        @unless($pdfExport ?? false)
            <footer>
                <strong>{{ $category->name }}</strong> — {{ $page->title }}
            </footer>
        @endunless
    </div>
@endsection
