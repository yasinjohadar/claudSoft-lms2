@extends('frontend.docs.layout')

@section('title')
    {{ $page->meta_title ?: $page->title . ' — ' . $category->name }}
@endsection

@push('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ e($page->meta_description) }}">
    @endif
@endpush

@section('content')
    <div class="container docs-article-view">
        <header>
            <div class="header-tag">{{ $category->name }}</div>
            <h1>{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="header-desc">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section class="content-section docs-content" style="animation-delay: 0.1s;">
            {!! $page->content !!}
        </section>

        <footer>
            <strong>{{ $category->name }}</strong> — {{ $page->title }}
        </footer>
    </div>
@endsection