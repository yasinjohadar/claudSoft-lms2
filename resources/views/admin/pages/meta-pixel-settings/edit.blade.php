@extends('admin.layouts.master')

@section('page-title')
    Facebook Pixel
@stop

@section('css')
<style>
    .meta-pixel-hero {
        background: linear-gradient(135deg, #1877f2 0%, #0d5dbf 55%, #004493 100%);
        border-radius: 18px;
        padding: 1.75rem 1.5rem;
        color: #fff;
        box-shadow: 0 12px 32px rgba(24, 119, 242, 0.25);
        margin-bottom: 1.5rem;
    }
    .meta-pixel-hero__title { font-size: 1.5rem; font-weight: 800; margin-bottom: .35rem; }
    .meta-pixel-hero__desc { opacity: .92; margin: 0; max-width: 720px; }
    .meta-form-section {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
        position: relative;
        overflow: hidden;
    }
    .meta-form-section::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1877f2, #42b72a);
    }
    .meta-form-section__title {
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
    .meta-form-section__icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(24, 119, 242, 0.1);
        color: #1877f2;
        font-size: 1.15rem;
    }
    .meta-event-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
        transition: border-color .2s, box-shadow .2s;
        background: #fff;
    }
    .meta-event-card:hover {
        border-color: #1877f2;
        box-shadow: 0 8px 20px rgba(24, 119, 242, 0.08);
    }
    .meta-event-card__name { font-weight: 700; color: #0f172a; }
    .meta-event-card__desc { font-size: .85rem; color: #64748b; margin: .35rem 0 .75rem; min-height: 2.5rem; }
    .meta-guide-box {
        background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 1.25rem;
    }
    .meta-guide-box ol { margin: 0; padding-right: 1.1rem; }
    .meta-guide-box li { margin-bottom: .45rem; color: #334155; }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">الإعدادات</a></li>
                    <li class="breadcrumb-item active">Facebook Pixel</li>
                </ol>
            </nav>
        </div>

        <div class="meta-pixel-hero dashboard-fade-in">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fab fa-facebook fa-lg"></i>
                        <span class="badge bg-light text-primary">Meta Marketing</span>
                    </div>
                    <h1 class="meta-pixel-hero__title">Facebook Pixel & Conversions API</h1>
                    <p class="meta-pixel-hero__desc">
                        تتبع زوار الموقع العام — زيارات الصفحات، عرض الكورسات والمقالات، تسجيل الدبلوم، ونموذج التواصل.
                    </p>
                </div>
                @if($stats['pixel_active'])
                    <span class="badge bg-success fs-6 px-3 py-2"><i class="fe fe-check me-1"></i>Pixel نشط</span>
                @else
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fe fe-alert-circle me-1"></i>Pixel غير مفعّل</span>
                @endif
            </div>
        </div>

        @php
            $kpiCards = [
                ['variant' => 'blue', 'icon' => 'fe-facebook', 'label' => 'حالة Pixel', 'value' => $stats['pixel_active'] ? 'مفعّل' : 'معطّل', 'sub' => $settings->pixel_id ? 'ID: '.$settings->pixel_id : 'لم يُضبط بعد', 'is_text' => true],
                ['variant' => 'cyan', 'icon' => 'fe-server', 'label' => 'Conversions API', 'value' => $stats['capi_active'] ? 'مفعّل' : 'معطّل', 'sub' => $settings->capi_enabled ? 'إرسال من السيرفر' : 'Browser فقط', 'is_text' => true],
                ['variant' => 'green', 'icon' => 'fe-activity', 'label' => 'الأحداث المفعّلة', 'value' => $stats['events_enabled'], 'sub' => 'من '.$stats['events_total'].' أحداث', 'is_text' => false],
                ['variant' => 'orange', 'icon' => 'fe-code', 'label' => 'Test Event', 'value' => $settings->test_event_code ? 'مضبوط' : '—', 'sub' => 'Events Manager', 'is_text' => true],
            ];
        @endphp

        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                <h3 class="admin-stats-card__value mb-1 fs-5">
                                    @if($card['is_text'] ?? false)
                                        {{ $card['value'] }}
                                    @else
                                        <span data-countup="{{ $card['value'] }}">0</span>
                                    @endif
                                </h3>
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('admin.meta-pixel-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="meta-form-section">
                        <div class="meta-form-section__title">
                            <span class="meta-form-section__icon"><i class="fe fe-target"></i></span>
                            إعدادات Pixel الأساسية
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Pixel ID</label>
                                <input type="text" name="pixel_id" class="form-control @error('pixel_id') is-invalid @enderror"
                                       value="{{ old('pixel_id', $settings->pixel_id) }}" placeholder="مثال: 123456789012345" dir="ltr">
                                @error('pixel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled"
                                           @checked(old('enabled', $settings->enabled))>
                                    <label class="form-check-label fw-semibold" for="enabled">تفعيل Pixel</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Test Event Code <span class="text-muted fw-normal">(اختياري)</span></label>
                                <input type="text" name="test_event_code" class="form-control @error('test_event_code') is-invalid @enderror"
                                       value="{{ old('test_event_code', $settings->test_event_code) }}" placeholder="TEST12345" dir="ltr">
                                @error('test_event_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="meta-form-section">
                        <div class="meta-form-section__title">
                            <span class="meta-form-section__icon"><i class="fe fe-shield"></i></span>
                            Conversions API (CAPI)
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="capi_enabled" value="1" id="capi_enabled"
                                           @checked(old('capi_enabled', $settings->capi_enabled))>
                                    <label class="form-check-label fw-semibold" for="capi_enabled">تفعيل إرسال الأحداث من السيرفر</label>
                                </div>
                                <small class="text-muted">موصى به لـ Lead و Contact لتحسين الدقة.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Access Token</label>
                                <input type="password" name="capi_access_token" class="form-control" dir="ltr"
                                       placeholder="{{ $settings->capi_access_token ? '•••••••• (اتركه فارغاً للإبقاء على الحالي)' : 'أدخل CAPI Access Token' }}" autocomplete="new-password">
                                <small class="text-muted">من Meta Events Manager → Settings → Conversions API</small>
                            </div>
                        </div>
                    </div>

                    <div class="meta-form-section">
                        <div class="meta-form-section__title">
                            <span class="meta-form-section__icon"><i class="fe fe-layers"></i></span>
                            الأحداث المتتبّعة
                        </div>
                        <div class="row g-3">
                            @foreach($events as $eventKey => $event)
                                @php $field = $event['setting_key']; @endphp
                                <div class="col-md-6">
                                    <div class="meta-event-card">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="meta-event-card__name">{{ $event['label_ar'] }}</div>
                                                <code class="small text-primary">{{ $eventKey }}</code>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
                                                       id="{{ $field }}" @checked(old($field, $settings->{$field}))>
                                            </div>
                                        </div>
                                        <p class="meta-event-card__desc">{{ $event['description'] }}</p>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <span class="badge bg-primary-transparent text-primary">Browser</span>
                                            @if($event['capi'] ?? false)
                                                <span class="badge bg-success-transparent text-success">CAPI</span>
                                            @endif
                                            @if($event['custom'] ?? false)
                                                <span class="badge bg-info-transparent text-info">Custom</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fe fe-save me-1"></i>حفظ الإعدادات
                        </button>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="meta-guide-box mb-3">
                        <h6 class="fw-bold mb-3"><i class="fe fe-book-open me-1 text-primary"></i>دليل الإعداد السريع</h6>
                        <ol>
                            <li>افتح <strong>Meta Events Manager</strong> وأنشئ Pixel أو استخدم موجوداً.</li>
                            <li>انسخ <strong>Pixel ID</strong> والصقه أعلاه.</li>
                            <li>من Settings → Conversions API أنشئ <strong>Access Token</strong>.</li>
                            <li>للتجربة: انسخ <strong>Test Event Code</strong> من Test Events.</li>
                            <li>فعّل Pixel واحفظ، ثم جرّب زيارة الموقع العام أو إرسال نموذج.</li>
                        </ol>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title mb-0"><i class="fe fe-zap me-1"></i>اختبار CAPI</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">يرسل حدث Lead تجريبي إلى Meta (يتطلب CAPI مفعّلاً و Access Token).</p>
                            <form action="{{ route('admin.meta-pixel-settings.test-capi') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100" @disabled(!$settings->hasValidCapi())>
                                    <i class="fe fe-send me-1"></i>إرسال حدث تجريبي
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card custom-card mt-3">
                        <div class="card-header">
                            <div class="card-title mb-0"><i class="fe fe-map me-1"></i>الصفحات المشمولة</div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li class="mb-2"><i class="fe fe-check text-success me-1"></i>الموقع العام (frontend2)</li>
                                <li class="mb-2"><i class="fe fe-check text-success me-1"></i>تسجيل الدبلوم</li>
                                <li class="mb-2"><i class="fe fe-check text-success me-1"></i>المدونة والكورسات</li>
                                <li class="mb-0"><i class="fe fe-check text-success me-1"></i>اتصل بنا</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop
