@extends('frontend.layouts.master')

@php
    $pageTitle = config('app.name') . ' - منصة تعليمية شاملة';
    $pageDescription = 'منصة تعليمية متكاملة تقدم كورسات احترافية ومقالات تعليمية في مختلف المجالات. تعلم من الخبراء واحصل على شهادات معتمدة';
    $pageKeywords = 'تعليم اونلاين, كورسات, دورات تدريبية, منصة تعليمية, شهادات معتمدة';
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)

@push('head')
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ route('frontend.home') }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('frontend.home') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="ar_SA">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    {{-- Robots Meta --}}
    <meta name="robots" content="index, follow">

    {{-- Organization Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ route('frontend.home') }}",
        "logo": "{{ asset('frontend/assets/images/logo.png') }}",
        "description": "{{ $pageDescription }}",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "SA"
        },
        "sameAs": []
    }
    </script>

    {{-- WebSite Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{{ config('app.name') }}",
        "url": "{{ route('frontend.home') }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ route('frontend.blog.search') }}?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
@endpush

@section('content')
    @include("frontend.sections.hero")
    @include("frontend.sections.services-section")
    @include("frontend.sections.courses-section")
    @include("frontend.sections.blog")
    @include("frontend.sections.testimonials")
    @include("frontend.sections.faqs")
@stop
