@extends('frontend2.layouts.master')

@php
    $pageTitle = 'نتائج البحث: '.$keyword.' | '.config('app.name');
    $pageDescription = 'نتائج البحث عن "'.$keyword.'" في مدونة '.config('app.name');
    $canonicalUrl = route('frontend.blog.search', array_merge(['q' => $keyword], request()->query()));
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    @include('frontend2.pages.partials.blog-seo-collection', [
        'type' => 'search',
        'model' => null,
        'posts' => $posts,
        'canonicalUrl' => $canonicalUrl,
        'pageTitle' => $pageTitle,
        'pageDescription' => $pageDescription,
        // Internal search-result pages are thin/duplicate content by nature —
        // always noindex, regardless of page number (unlike category/tag/index).
        'forceNoindex' => true,
    ])
@endpush

@section('content')

    <!-- Page Banner -->
    <section class="page-banner page-banner-blog">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-search"></i></div>
                <h1 class="page-banner-title">نتائج البحث</h1>
                <p class="page-banner-desc">{{ $posts->total() }} نتيجة عن "{{ $keyword }}"</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.blog.index') }}">المدونة</a>
                    <span class="page-banner-sep">/</span>
                    <span>نتائج البحث</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding" style="padding-top: 30px;">
        <div class="container">
            <div class="glass-panel animate-on-scroll" style="padding: 25px 30px; margin-bottom: 40px;">
                <form action="{{ route('frontend.blog.search') }}" method="GET" class="row align-items-center g-3">
                    <div class="col-12">
                        <div style="position: relative;">
                            <input type="text" name="q" class="form-control" value="{{ $keyword }}"
                                placeholder="ابحث في المدونة..."
                                style="background: var(--clr-surface); border: 1px solid var(--clr-border); color: var(--clr-text); padding: 12px 18px 12px 45px; border-radius: var(--radius-md); font-family: var(--font-family);">
                            <i class="fas fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--clr-text-muted);"></i>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                @forelse($posts as $post)
                    @include('frontend2.pages.partials.blog-card', ['post' => $post, 'eager' => $loop->first])
                @empty
                    <div class="col-12">
                        <div class="glass-panel text-center py-5 animate-on-scroll">
                            <i class="fas fa-search fa-4x mb-3" style="color: var(--clr-text-muted);"></i>
                            <h4>لا توجد نتائج مطابقة</h4>
                            <p class="text-muted mb-0">جرّب كلمات مختلفة، أو تصفح <a href="{{ route('frontend.blog.index') }}">كل المقالات</a>.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($posts->hasPages())
            <div class="f2-pagination-wrap mt-5 animate-on-scroll">
                {{ $posts->appends(['q' => $keyword])->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </section>

@endsection
