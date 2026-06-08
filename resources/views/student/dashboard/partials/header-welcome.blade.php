@php
    $displayName = auth()->user()->name_ar ?? auth()->user()->name ?? '';
@endphp

<div class="my-4 page-header-breadcrumb admin-dashboard-welcome dashboard-fade-in">
    <h4 class="mb-1 admin-dashboard-welcome__title">
        مرحباً{{ $displayName ? ' ' . $displayName : '' }}! 👋
    </h4>
    <p class="mb-0 text-muted admin-dashboard-welcome__subtitle">
        <i class="fe fe-calendar me-1"></i>{{ now()->locale('ar')->translatedFormat('l، j F Y') }}
        <span class="mx-2">·</span>
        تابع تقدمك التعليمي واختباراتك من لوحة واحدة.
    </p>
</div>
