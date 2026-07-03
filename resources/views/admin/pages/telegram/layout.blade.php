@extends('admin.layouts.master')

@section('page-title', $tgPageTitle ?? 'Telegram')

@section('css')
<style>
    .tg-hero {
        background: linear-gradient(135deg, #229ED9 0%, #0088cc 55%, #006699 100%);
        border-radius: 18px;
        padding: 1.75rem 1.5rem;
        color: #fff;
        box-shadow: 0 12px 32px rgba(34, 158, 217, 0.28);
        margin-bottom: 1.5rem;
    }
    .tg-hero__title { font-size: 1.5rem; font-weight: 800; margin-bottom: .35rem; }
    .tg-hero__desc { opacity: .92; margin: 0; max-width: 720px; }
    .tg-form-section {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        position: relative;
        overflow: hidden;
    }
    .tg-form-section::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #229ED9, #0088cc);
    }
    .tg-form-section__title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.25rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .tg-form-section__icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(34, 158, 217, 0.12);
        color: #0088cc;
        font-size: 1.15rem;
    }
    .tg-guide-box {
        background: linear-gradient(180deg, #f0f9ff 0%, #f1f5f9 100%);
        border: 1px solid #bae6fd;
        border-radius: 14px;
        padding: 1.25rem;
    }
    .tg-guide-box ol, .tg-guide-box ul { margin: 0; padding-right: 1.1rem; }
    .tg-guide-box li { margin-bottom: .45rem; color: #334155; }
    .tg-nav-pills .nav-link.active {
        background: linear-gradient(135deg, #229ED9, #0088cc) !important;
        box-shadow: 0 .25rem .5rem rgba(34, 158, 217, 0.35);
        color: #fff !important;
    }
    .tg-flash-alert { border-right: 4px solid currentColor; animation: tgFlashIn .35s ease; }
    @keyframes tgFlashIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .tg-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: rgba(255,255,255,.2);
        border-radius: 999px;
        padding: .35rem .85rem;
        font-size: .875rem;
    }
</style>
@yield('tg-css')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')
        @include('admin.pages.telegram.partials.flash')
        @include('admin.pages.telegram.partials.header', [
            'title' => $tgTitle ?? 'Telegram Bot',
            'subtitle' => $tgSubtitle ?? 'مراسلة الطلاب والمجموعات عبر Bot API الرسمي',
            'badge' => $tgBadge ?? null,
        ])
        @include('admin.pages.telegram.partials.nav')

        <div class="dashboard-fade-in">
            @yield('tg-content')
        </div>
    </div>
</div>
@endsection

@section('scripts')
@yield('tg-scripts')
@stack('tg-scripts')
@endsection
