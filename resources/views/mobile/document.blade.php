{{-- درس مكتوب داخل تطبيق الجوال: نفس قالب وتنسيق صفحات التوثيق العامة (الأكواد، الصناديق، Prism)،
     مع الوضع الليلي/النهاري القادم من التطبيق ودون شريط أدوات الموقع. --}}
@extends('frontend.docs.layout')

@section('title', $title)

@push('styles')
    <style>
        .docs-top-toolbar { display: none !important; }
        .docs-article-view.mobile-doc { padding-top: 20px; padding-bottom: 40px; }
        .mobile-doc header { margin-bottom: 18px; }
        .mobile-doc header h1 { font-size: clamp(1.4rem, 5.5vw, 2rem); }
        @media (max-width: 640px) {
            .docs-article-view.mobile-doc { padding-inline: 14px; }
            .mobile-doc .code-block pre { font-size: 0.82rem; }
        }
    </style>
@endpush

@section('content')
    <div class="container docs-article-view mobile-doc">
        <header>
            @if ($sectionTitle)
                <div class="header-tag">{{ $sectionTitle }}</div>
            @endif
            <h1>{{ $title }}</h1>
        </header>

        <section class="content-section docs-content">
            {!! $content !!}
        </section>
    </div>
@endsection
