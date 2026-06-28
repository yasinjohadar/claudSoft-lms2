@extends('frontend2.layouts.profile-card')

@php
    $displayName = trim((string) ($user->name_ar ?: $user->name)) ?: ($user->name ?? 'طالب');
    $pageTitle = 'بطاقة '.$displayName.' | '.config('app.name');
    $pageDescription = $card->bio ? Str::limit(strip_tags($card->bio), 160) : 'البطاقة التعريفية لـ '.$displayName;
    $ogImage = student_profile_photo_url($user);
@endphp

@section('title', $pageTitle)

@push('head')
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ $card->public_url }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SY">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $displayName,
        'url' => $card->public_url,
        'image' => $ogImage,
        'jobTitle' => $card->job_title,
        'description' => $card->bio,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    @include('shared.profile-card.card')
@endsection
