@php
    $displayName = auth()->user()->name_ar ?? auth()->user()->name ?? '';
    $tier = $accountTier ?? 'silver';
    $isGoldAccount = $tier === 'gold';
@endphp

<div class="my-4 page-header-breadcrumb admin-dashboard-welcome dashboard-fade-in">
    <h4 class="mb-1 admin-dashboard-welcome__title d-flex flex-wrap align-items-center gap-2">
        <span>مرحباً{{ $displayName ? ' ' . $displayName : '' }}! 👋</span>
        <span class="student-account-tier-badge student-account-tier-badge--{{ $tier }}">
            @if ($isGoldAccount)
                <i class="ri-vip-crown-fill"></i>
                <span>حساب ذهبي</span>
            @else
                <i class="ri-medal-fill"></i>
                <span>حساب فضي</span>
            @endif
        </span>
    </h4>
    <p class="mb-0 text-muted admin-dashboard-welcome__subtitle">
        <i class="fe fe-calendar me-1"></i>{{ now()->locale('ar')->translatedFormat('l، j F Y') }}
        <span class="mx-2">·</span>
        تابع تقدمك التعليمي واختباراتك من لوحة واحدة.
    </p>
</div>
