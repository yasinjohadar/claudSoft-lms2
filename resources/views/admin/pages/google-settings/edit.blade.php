@extends('admin.layouts.master')

@section('page-title')
    إعدادات Google
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/google-settings.css') }}?v={{ filemtime(public_path('assets/css/google-settings.css')) }}">
@endpush

@section('content')
<div class="main-content app-content gs-page">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-3 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.marketing-analytics.index') }}">التسويق</a></li>
                    <li class="breadcrumb-item active">إعدادات Google</li>
                </ol>
            </nav>
        </div>

        <div class="gs-hero dashboard-fade-in">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <i class="fab fa-google fa-lg"></i>
                        <span class="gs-hero__badge">Google Marketing</span>
                        @if($stats['gtm_active'])
                            <span class="gs-hero__badge gs-hero__badge--ok"><i class="fe fe-check me-1"></i>GTM نشط</span>
                        @endif
                    </div>
                    <h1 class="gs-hero__title">إعدادات Google</h1>
                    <p class="gs-hero__desc">
                        Tag Manager للتتبع، Search Console للتحقق، وربط API لعرض الإحصائيات داخل المنصة — بدون تأثير على أداء الموقع.
                    </p>
                    <div class="gs-coverage">
                        <span class="gs-coverage__chip"><i class="fe fe-check"></i> frontend2</span>
                        <span class="gs-coverage__chip"><i class="fe fe-check"></i> تسجيل الدبلوم</span>
                        <span class="gs-coverage__chip"><i class="fe fe-check"></i> المدونة والكورسات</span>
                        <span class="gs-coverage__chip"><i class="fe fe-check"></i> اتصل بنا</span>
                    </div>
                </div>
                <a href="{{ route('admin.marketing-analytics.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fe fe-bar-chart-2 me-1"></i> إحصائيات التسويق
                </a>
            </div>
        </div>

        @php
            $kpiCards = [
                ['variant' => 'blue', 'icon' => 'fe-tag', 'label' => 'Tag Manager', 'value' => $stats['gtm_active'] ? 'مفعّل' : 'معطّل', 'sub' => $settings->gtm_container_id ?: 'لم يُضبط'],
                ['variant' => 'green', 'icon' => 'fe-search', 'label' => 'Search Console', 'value' => $stats['gsc_active'] ? 'مفعّل' : 'معطّل', 'sub' => 'meta verification'],
                ['variant' => 'cyan', 'icon' => 'fe-bar-chart', 'label' => 'Analytics API', 'value' => $stats['api_active'] ? 'مفعّل' : 'معطّل', 'sub' => $settings->ga4_property_id ? 'ID: '.$settings->ga4_property_id : 'GA4 Property'],
                ['variant' => 'orange', 'icon' => 'fe-clock', 'label' => 'آخر مزامنة', 'value' => $settings->last_analytics_sync_at ? $settings->last_analytics_sync_at->diffForHumans() : '—', 'sub' => 'Cache '.$settings->getAnalyticsCacheMinutes().' د'],
            ];
        @endphp

        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card gs-kpi gs-kpi--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="gs-kpi__icon-wrap">
                                <i class="fe {{ $card['icon'] }} gs-kpi__icon"></i>
                            </div>
                            <div class="flex-fill min-w-0">
                                <p class="gs-kpi__label mb-1">{{ $card['label'] }}</p>
                                <h3 class="gs-kpi__value mb-1">{{ $card['value'] }}</h3>
                                <p class="gs-kpi__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('admin.google-settings.update') }}" method="POST" id="gsForm">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-lg-8">
                    {{-- GTM --}}
                    <div class="gs-panel">
                        <div class="gs-panel__head">
                            <h2 class="gs-panel__title">
                                <span class="gs-panel__icon gs-panel__icon--gtm"><i class="fas fa-tags"></i></span>
                                Google Tag Manager
                            </h2>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Container ID</label>
                                <div class="gs-input-wrap">
                                    <i class="gs-input-wrap__icon fe fe-hash"></i>
                                    <input type="text" name="gtm_container_id" id="gtm_container_id"
                                           class="form-control @error('gtm_container_id') is-invalid @enderror"
                                           value="{{ old('gtm_container_id', $settings->gtm_container_id) }}"
                                           placeholder="GTM-XXXXXXX">
                                </div>
                                @error('gtm_container_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">الحالة</label>
                                <div class="gs-switch-card {{ old('gtm_enabled', $settings->gtm_enabled) ? 'on' : '' }}" id="gtmSwitchCard">
                                    <div>
                                        <p class="gs-switch-card__label">تفعيل GTM</p>
                                        <p class="gs-switch-card__hint">تتبع async على الموقع العام</p>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="gtm_enabled" value="1" id="gtm_enabled"
                                               {{ old('gtm_enabled', $settings->gtm_enabled) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="gs-guide small">
                                    أضف <strong>GA4 Configuration Tag</strong> داخل GTM. أحداث
                                    <code>generate_lead</code> و <code>contact</code> تُرسَل عبر dataLayer من Laravel.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Search Console --}}
                    <div class="gs-panel">
                        <div class="gs-panel__head">
                            <h2 class="gs-panel__title">
                                <span class="gs-panel__icon gs-panel__icon--gsc"><i class="fas fa-search"></i></span>
                                Google Search Console
                            </h2>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">كود التحقق</label>
                                <div class="gs-input-wrap">
                                    <i class="gs-input-wrap__icon fe fe-shield"></i>
                                    <input type="text" name="search_console_verification"
                                           class="form-control @error('search_console_verification') is-invalid @enderror"
                                           value="{{ old('search_console_verification', $settings->search_console_verification) }}"
                                           placeholder="content=...">
                                </div>
                                @error('search_console_verification')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">الحالة</label>
                                <div class="gs-switch-card {{ old('search_console_enabled', $settings->search_console_enabled) ? 'on' : '' }}" id="gscSwitchCard">
                                    <div>
                                        <p class="gs-switch-card__label">تفعيل التحقق</p>
                                        <p class="gs-switch-card__hint">meta tag في head</p>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="search_console_enabled" value="1" id="search_console_enabled"
                                               {{ old('search_console_enabled', $settings->search_console_enabled) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="gs-guide small">
                                    Sitemap: <a href="{{ url('/sitemap.xml') }}" target="_blank">{{ url('/sitemap.xml') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- API --}}
                    <div class="gs-panel">
                        <div class="gs-panel__head">
                            <h2 class="gs-panel__title">
                                <span class="gs-panel__icon gs-panel__icon--api"><i class="fas fa-chart-line"></i></span>
                                ربط API — لوحة الإحصائيات
                            </h2>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">GA4 Property ID</label>
                                <input type="text" name="ga4_property_id" class="form-control @error('ga4_property_id') is-invalid @enderror"
                                       value="{{ old('ga4_property_id', $settings->ga4_property_id) }}" placeholder="123456789">
                                @error('ga4_property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search Console Site URL</label>
                                <input type="text" name="gsc_site_url" class="form-control @error('gsc_site_url') is-invalid @enderror"
                                       value="{{ old('gsc_site_url', $settings->gsc_site_url) }}" placeholder="https://example.com/">
                                @error('gsc_site_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">مدة Cache (دقائق)</label>
                                <input type="number" name="analytics_cache_minutes" min="5" max="1440" class="form-control"
                                       value="{{ old('analytics_cache_minutes', $settings->analytics_cache_minutes ?: 60) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">الحالة</label>
                                <div class="gs-switch-card {{ old('analytics_api_enabled', $settings->analytics_api_enabled) ? 'on' : '' }}" id="apiSwitchCard">
                                    <div>
                                        <p class="gs-switch-card__label">تفعيل Analytics API</p>
                                        <p class="gs-switch-card__hint">عرض البيانات في المنصة</p>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="analytics_api_enabled" value="1" id="analytics_api_enabled"
                                               {{ old('analytics_api_enabled', $settings->analytics_api_enabled) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Service Account JSON</label>
                                <textarea name="service_account_json" rows="6" class="form-control font-monospace small @error('service_account_json') is-invalid @enderror"
                                          placeholder='{"type":"service_account",...}'>{{ old('service_account_json') }}</textarea>
                                @error('service_account_json')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($settings->service_account_json)
                                    <small class="text-success mt-1 d-block"><i class="fe fe-check-circle me-1"></i>JSON محفوظ — اترك الحقل فارغاً للإبقاء عليه</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="gs-panel">
                        <div class="gs-panel__head">
                            <h2 class="gs-panel__title">
                                <span class="gs-panel__icon gs-panel__icon--guide"><i class="fas fa-book"></i></span>
                                دليل الإعداد
                            </h2>
                        </div>
                        <div class="gs-guide">
                            <ol class="small">
                                <li>أنشئ <a href="https://tagmanager.google.com/" target="_blank">GTM Container</a></li>
                                <li>أنشئ <a href="https://analytics.google.com/" target="_blank">GA4 Property</a></li>
                                <li>تحقق في <a href="https://search.google.com/search-console" target="_blank">Search Console</a></li>
                                <li>Google Cloud → Analytics Data API + Search Console API</li>
                                <li>Service Account → Viewer في GA4 + User في GSC</li>
                                <li>احفظ ثم افتح <a href="{{ route('admin.marketing-analytics.index') }}">إحصائيات التسويق</a></li>
                            </ol>
                        </div>
                    </div>

                    <div class="gs-panel">
                        <div class="gs-panel__head">
                            <h2 class="gs-panel__title">
                                <span class="gs-panel__icon gs-panel__icon--gtm"><i class="fe fe-zap"></i></span>
                                اختبار سريع
                            </h2>
                        </div>
                        <p class="text-muted small mb-3">بعد حفظ الإعدادات، اختبر اتصال API.</p>
                        <button type="submit" form="gsTestApiForm" class="gs-test-btn w-100">
                            <i class="fas fa-vial me-1"></i> اختبار اتصال API
                        </button>
                    </div>
                </div>
            </div>

            <div class="gs-sticky-bar">
                <span class="text-muted small"><i class="fe fe-info me-1"></i> التغييرات تُطبّق على الموقع العام فقط</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> حفظ الإعدادات
                    </button>
                </div>
            </div>
        </form>

        <form action="{{ route('admin.google-settings.test-api') }}" method="POST" id="gsTestApiForm" class="d-none">
            @csrf
        </form>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindSwitch(checkboxId, cardId) {
        var cb = document.getElementById(checkboxId);
        var card = document.getElementById(cardId);
        if (!cb || !card) return;
        var sync = function () { card.classList.toggle('on', cb.checked); };
        cb.addEventListener('change', sync);
        sync();
    }
    bindSwitch('gtm_enabled', 'gtmSwitchCard');
    bindSwitch('search_console_enabled', 'gscSwitchCard');
    bindSwitch('analytics_api_enabled', 'apiSwitchCard');
});
</script>
@stop
