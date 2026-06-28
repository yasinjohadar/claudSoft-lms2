@extends('frontend.docs.layout')

@section('title')
    {{ $category->name }} — التوثيق
@endsection

@push('meta')
    @if ($category->description)
        <meta name="description" content="{{ e(strip_tags($category->description)) }}">
    @endif
@endpush

@section('content')
    <div class="container docs-category-view">
        <nav class="docs-breadcrumb" aria-label="مسار التنقل">
            <a href="{{ route('frontend.docs.index') }}">التوثيق</a>
            <span class="docs-breadcrumb__sep">/</span>
            <span aria-current="page">{{ $category->name }}</span>
        </nav>

        <header class="docs-category-hero @if($category->isTechnology()) docs-category-hero--tech @else docs-category-hero--section @endif">
            <div class="docs-category-hero__icon">
                @if ($category->icon)
                    <i class="{{ $category->icon }}"></i>
                @elseif($category->isTechnology())
                    <i class="bi bi-code-slash"></i>
                @else
                    <i class="bi bi-journal-bookmark"></i>
                @endif
            </div>
            <div class="docs-category-hero__content">
                <div class="header-tag">
                    @if($category->isTechnology())
                        لغة / تقنية
                    @else
                        قسم توثيق
                    @endif
                </div>
                <h1>{{ $category->name }}</h1>
                @if ($category->description)
                    <p class="header-desc">{{ $category->description }}</p>
                @endif
                <div class="docs-category-hero__meta">
                    <span class="docs-category-stat">
                        <strong>{{ $pagesCount }}</strong>
                        {{ $pagesCount === 1 ? 'مقال' : 'مقالات' }}
                    </span>
                </div>
            </div>
        </header>

        <section class="content-section">
            @if (empty($pageTree))
                <div class="docs-category-empty">
                    <div class="docs-category-empty__icon"><i class="bi bi-inbox"></i></div>
                    <h2 class="subsection-title">لا توجد مقالات منشورة</h2>
                    <p class="text-block mb-0">لم يُنشر أي محتوى في هذا القسم بعد. عد لاحقاً.</p>
                </div>
            @else
                <div class="section-title">دليل {{ $category->name }}</div>
                <p class="text-block">اختر مقالاً للبدء — مرتبة حسب التسلسل المنطقي للتعلّم.</p>

                <div class="docs-page-tree">
                    @include('frontend.docs.partials.page-tree', [
                        'nodes' => $pageTree,
                        'category' => $category,
                        'depth' => 0,
                    ])
                </div>
            @endif
        </section>

        <footer>
            <a href="{{ route('frontend.docs.index') }}" class="docs-back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>العودة لكل الأقسام</span>
            </a>
        </footer>
    </div>
@endsection
