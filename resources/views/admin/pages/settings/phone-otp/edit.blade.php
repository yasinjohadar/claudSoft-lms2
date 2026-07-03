@extends('admin.layouts.master')

@section('page-title')
    OTP عبر واتساب
@stop

@section('css')
<style>
    .otp-settings-page .otp-channel-card {
        border: 2px solid var(--default-border, #e9edf4);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        cursor: pointer;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        height: 100%;
        background: var(--custom-white, #fff);
    }
    .otp-settings-page .otp-channel-card:hover {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), .35);
        box-shadow: 0 .35rem 1rem rgba(0,0,0,.06);
        transform: translateY(-1px);
    }
    .otp-settings-page .otp-channel-card.is-active {
        border-color: rgb(var(--primary-rgb, 132, 90, 223));
        box-shadow: 0 .35rem 1rem rgba(var(--primary-rgb, 132, 90, 223), .12);
        background: rgba(var(--primary-rgb, 132, 90, 223), .04);
    }
    .otp-settings-page .otp-channel-card__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .otp-settings-page .otp-channel-card__icon--flaxxa { background: rgba(25, 135, 84, .12); color: #198754; }
    .otp-settings-page .otp-channel-card__icon--evo { background: rgba(13, 110, 253, .12); color: #0d6efd; }
    .otp-settings-page .otp-section-title {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--text-muted, #6c757d);
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px dashed var(--default-border, #e9edf4);
    }
    .otp-settings-page .otp-scenario-card {
        border: 1px solid var(--default-border, #e9edf4);
        border-radius: 12px;
        padding: .85rem 1rem;
        background: var(--custom-white, #fff);
        transition: border-color .2s ease, background .2s ease;
    }
    .otp-settings-page .otp-scenario-card:has(input:checked) {
        border-color: rgba(var(--primary-rgb, 132, 90, 223), .45);
        background: rgba(var(--primary-rgb, 132, 90, 223), .04);
    }
    .otp-settings-page .otp-preview-box {
        border-radius: 12px;
        border: 1px dashed var(--default-border, #dee2e6);
        background: var(--body-bg-rgb, #f8f9fa);
        padding: 1rem;
        font-size: .9rem;
        line-height: 1.6;
        min-height: 72px;
    }
    .otp-settings-page .otp-master-switch {
        border-radius: 14px;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb, 132, 90, 223), .06), rgba(25, 135, 84, .05));
        border: 1px solid rgba(var(--primary-rgb, 132, 90, 223), .12);
    }
</style>
@stop

@section('content')
@php
    $health = $health ?? [];
    $isEvolution = ($health['delivery_channel'] ?? 'flaxxa') === 'evolution';
    $isReady = $health['ready'] ?? false;
    $kpiCards = [
        [
            'variant' => $isReady ? 'green' : 'orange',
            'icon' => $isReady ? 'ri-checkbox-circle-line' : 'ri-error-warning-line',
            'label' => 'الجاهزية',
            'value' => $isReady ? 'جاهز' : 'يتطلب إعداد',
            'sub' => $isReady ? 'يمكن إرسال OTP الآن' : 'أكمل الإعدادات أدناه',
        ],
        [
            'variant' => $isEvolution ? 'blue' : 'green',
            'icon' => $isEvolution ? 'ri-shuffle-line' : 'ri-cloud-line',
            'label' => 'قناة الإرسال',
            'value' => $isEvolution ? 'Evolution' : 'Flaxxa WAPI',
            'sub' => $isEvolution ? 'تبديل تلقائي بين الأرقام' : 'قوالب Meta المعتمدة',
        ],
        [
            'variant' => ($health['otp_enabled'] ?? false) ? 'cyan' : 'orange',
            'icon' => 'ri-shield-keyhole-line',
            'label' => 'OTP',
            'value' => ($health['otp_enabled'] ?? false) ? 'مفعّل' : 'معطّل',
            'sub' => 'طول الرمز: '.($settings['code_length'] ?? 6).' أرقام',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'ri-timer-line',
            'label' => 'الصلاحية والحدود',
            'value' => ($settings['ttl_seconds'] ?? 300).' ث',
            'sub' => ($settings['rate_limit_max_per_phone'] ?? 3).' طلب / '.($settings['rate_limit_window_minutes'] ?? 15).' د',
        ],
    ];
    $scenarios = [
        'register_enabled' => ['label' => 'التسجيل', 'icon' => 'ri-user-add-line', 'desc' => 'رمز عند إنشاء حساب جديد'],
        'login_enabled' => ['label' => 'تسجيل الدخول', 'icon' => 'ri-login-box-line', 'desc' => 'دخول برقم الهاتف'],
        'reset_password_enabled' => ['label' => 'استعادة كلمة المرور', 'icon' => 'ri-lock-password-line', 'desc' => 'إعادة تعيين كلمة المرور'],
        'change_phone_enabled' => ['label' => 'تغيير الرقم', 'icon' => 'ri-phone-line', 'desc' => 'تأكيد رقم جديد'],
    ];
@endphp

<div class="main-content app-content otp-settings-page">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">OTP عبر واتساب</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <span class="admin-group-form-page__icon">
                            <i class="ri-whatsapp-line"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="group-show-hero__eyebrow">
                                <i class="ri-settings-3-line me-1"></i>إعدادات النظام
                            </span>
                            <h2 class="group-show-hero__title mb-2">OTP عبر واتساب</h2>
                            <p class="group-show-hero__desc mb-0">
                                إعداد قناة الإرسال، قوالب الرسائل، حدود المعدل، والسيناريوهات المفعّلة — مع دعم Flaxxa أو Evolution.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.evolution-api.instances.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="ri-smartphone-line"></i></span>
                            <span class="group-show-action__text">إدارة أرقام Evolution</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="{{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                <h3 class="admin-stats-card__value mb-1 fs-5">{{ $card['value'] }}</h3>
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(!empty($health['template_issues']) || ($health['has_media_header'] ?? false))
            <div class="alert alert-warning border-0 shadow-sm dashboard-fade-in mb-4" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="ri-alert-line fs-18 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">تنبيهات الإعداد</strong>
                        @if($health['has_media_header'] ?? false)
                            <div>القالب يحتوي على header وسائط ولا يدعمه مسار OTP.</div>
                        @endif
                        @if(!empty($health['template_issues']))
                            <div>{{ implode(' ', $health['template_issues']) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <form method="POST" action="{{ route('admin.settings.phone-otp.update') }}" id="otp-settings-form">
                    @csrf
                    @method('PUT')

                    <div class="card custom-card group-show-members-card border-0 shadow-sm mb-4 dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                <i class="ri-toggle-line me-2 text-primary"></i>التفعيل العام
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="otp-master-switch d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h6 class="mb-1 fw-semibold">تفعيل OTP عبر واتساب</h6>
                                    <p class="text-muted small mb-0">عند التعطيل لن يُرسل أي رمز تحقق عبر واتساب في المنصة.</p>
                                </div>
                                <div class="form-check form-switch form-switch-lg mb-0">
                                    <input class="form-check-input" type="checkbox" name="enabled" value="1" id="otpEnabled"
                                           @checked(old('enabled', $settings['enabled'] ?? false))>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card border-0 shadow-sm mb-4 dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                <i class="ri-route-line me-2 text-success"></i>قناة الإرسال
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <input type="hidden" name="delivery_channel" id="delivery_channel" value="{{ old('delivery_channel', $settings['delivery_channel'] ?? 'flaxxa') }}">

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="otp-channel-card d-block mb-0 {{ old('delivery_channel', $settings['delivery_channel'] ?? 'flaxxa') === 'flaxxa' ? 'is-active' : '' }}" data-channel="flaxxa">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="otp-channel-card__icon otp-channel-card__icon--flaxxa"><i class="ri-cloud-line"></i></span>
                                            <div>
                                                <div class="fw-semibold mb-1">Flaxxa WAPI</div>
                                                <div class="text-muted small mb-2">قوالب Meta المعتمدة — مناسب للإنتاج الرسمي.</div>
                                                <span class="badge bg-success-transparent text-success">قالب Meta</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="otp-channel-card d-block mb-0 {{ old('delivery_channel', $settings['delivery_channel'] ?? 'flaxxa') === 'evolution' ? 'is-active' : '' }}" data-channel="evolution">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="otp-channel-card__icon otp-channel-card__icon--evo"><i class="ri-shuffle-line"></i></span>
                                            <div>
                                                <div class="fw-semibold mb-1">Evolution API</div>
                                                <div class="text-muted small mb-2">رسالة نصية مع تبديل تلقائي بين {{ $evolutionPoolCount ?? 0 }} رقم/جلسة.</div>
                                                <span class="badge bg-primary-transparent text-primary">تبديل الأرقام</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="flaxxa-otp-fields">
                                <p class="otp-section-title">إعدادات Flaxxa</p>
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold" for="wapi_template_id">قالب Flaxxa (Meta)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-file-list-3-line"></i></span>
                                            <select name="wapi_template_id" id="wapi_template_id" class="form-select">
                                                <option value="">— اختر قالب OTP —</option>
                                                @foreach($wapiTemplates as $tpl)
                                                    <option value="{{ $tpl->id }}"
                                                            data-language="{{ $tpl->language }}"
                                                            @selected(old('wapi_template_id', $settings['wapi_template_id'] ?? '') == $tpl->id)>
                                                        {{ $tpl->name }} ({{ $tpl->language }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-text">
                                            <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="text-primary">مزامنة القوالب من Flaxxa</a>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold" for="template_language">لغة القالب</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-translate-2"></i></span>
                                            <input type="text" name="template_language" id="template_language" class="form-control"
                                                   value="{{ old('template_language', $settings['template_language'] ?? 'ar') }}">
                                        </div>
                                    </div>
                                    @if(!empty($health['template_name']))
                                        <div class="col-12 flaxxa-health-field">
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-light text-dark border">القالب: {{ $health['template_name'] }}</span>
                                                @if(!empty($health['template_status']))
                                                    <span class="badge bg-{{ strtoupper($health['template_status']) === 'APPROVED' ? 'success' : 'warning' }}-transparent text-{{ strtoupper($health['template_status']) === 'APPROVED' ? 'success' : 'warning' }}">
                                                        {{ $health['template_status'] }}
                                                    </span>
                                                @endif
                                                <span class="badge bg-light text-dark border">
                                                    placeholders: {{ $health['header_placeholders'] ?? 0 }} / {{ $health['body_placeholders'] ?? 0 }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="evolution-otp-fields">
                                <p class="otp-section-title">إعدادات Evolution</p>
                                <label class="form-label fw-semibold" for="evolution_message_template">نص رسالة OTP</label>
                                <textarea name="evolution_message_template" id="evolution_message_template" class="form-control mb-2" rows="3">{{ old('evolution_message_template', $settings['evolution_message_template'] ?? 'رمز التحقق الخاص بك هو: {code}') }}</textarea>
                                <div class="form-text mb-3">يجب تضمين <code>{code}</code> — يُستبدل برمز التحقق عند الإرسال.</div>
                                <p class="small text-muted mb-1">معاينة:</p>
                                <div class="otp-preview-box" id="evolution-preview">—</div>
                                @if(($evolutionPoolCount ?? 0) === 0)
                                    <div class="alert alert-info border-0 mt-3 mb-0 py-2 small">
                                        <i class="ri-information-line me-1"></i>
                                        لا توجد جلسات Evolution نشطة. <a href="{{ route('admin.evolution-api.instances.index') }}">اربط رقماً واحداً على الأقل</a>.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card border-0 shadow-sm mb-4 dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                <i class="ri-shield-check-line me-2 text-info"></i>الرمز والأمان
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="code_length">طول الرمز</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-hashtag"></i></span>
                                        <input type="number" name="code_length" id="code_length" class="form-control" min="4" max="8"
                                               value="{{ old('code_length', $settings['code_length'] ?? 6) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="ttl_seconds">صلاحية الرمز (ثانية)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-timer-line"></i></span>
                                        <input type="number" name="ttl_seconds" id="ttl_seconds" class="form-control"
                                               value="{{ old('ttl_seconds', $settings['ttl_seconds'] ?? 300) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="max_attempts">أقصى محاولات</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                        <input type="number" name="max_attempts" id="max_attempts" class="form-control"
                                               value="{{ old('max_attempts', $settings['max_attempts'] ?? 5) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="resend_cooldown_seconds">Cooldown إعادة الإرسال</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-time-line"></i></span>
                                        <input type="number" name="resend_cooldown_seconds" id="resend_cooldown_seconds" class="form-control"
                                               value="{{ old('resend_cooldown_seconds', $settings['resend_cooldown_seconds'] ?? 60) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="rate_limit_max_per_phone">حد المعدل (لكل رقم)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-bar-chart-line"></i></span>
                                        <input type="number" name="rate_limit_max_per_phone" id="rate_limit_max_per_phone" class="form-control" min="1" max="50"
                                               value="{{ old('rate_limit_max_per_phone', $settings['rate_limit_max_per_phone'] ?? 3) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" for="rate_limit_window_minutes">نافذة الحد (دقيقة)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                        <input type="number" name="rate_limit_window_minutes" id="rate_limit_window_minutes" class="form-control" min="1" max="1440"
                                               value="{{ old('rate_limit_window_minutes', $settings['rate_limit_window_minutes'] ?? 15) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card border-0 shadow-sm mb-4 dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                <i class="ri-apps-2-line me-2 text-warning"></i>السيناريوهات المفعّلة
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                @foreach($scenarios as $key => $scenario)
                                    <div class="col-md-6">
                                        <label class="otp-scenario-card d-flex align-items-start gap-3 mb-0" for="{{ $key }}">
                                            <input class="form-check-input mt-1 flex-shrink-0" type="checkbox" name="{{ $key }}" value="1" id="{{ $key }}"
                                                   @checked(old($key, $settings[$key] ?? false))>
                                            <span class="avatar avatar-sm bg-primary-transparent rounded flex-shrink-0">
                                                <i class="{{ $scenario['icon'] }} text-primary"></i>
                                            </span>
                                            <span>
                                                <span class="fw-semibold d-block">{{ $scenario['label'] }}</span>
                                                <span class="text-muted small">{{ $scenario['desc'] }}</span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="ri-save-line me-1"></i> حفظ الإعدادات
                        </button>
                        <button type="submit" form="otp-restore-form" class="btn btn-light border rounded-pill"
                                onclick="return confirm('استعادة الإعدادات الافتراضية؟');">
                            <i class="ri-refresh-line me-1"></i> استعادة الافتراضي
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.settings.phone-otp.restore-defaults') }}" id="otp-restore-form" class="d-none">
                    @csrf
                </form>
            </div>

            <div class="col-xl-4">
                <div class="card custom-card border-0 shadow-sm mb-4 dashboard-fade-in bg-info-transparent">
                    <div class="card-body">
                        <h6 class="fw-semibold text-info mb-3"><i class="ri-lightbulb-line me-1"></i> نصائح سريعة</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-2"><strong>Flaxxa:</strong> يتطلب قالب Meta بحالة APPROVED وتوكن WAPI.</li>
                            <li class="mb-2"><strong>Evolution:</strong> يوزّع OTP على عدة أرقام لتقليل الحظر.</li>
                            <li class="mb-2">فعّل فقط السيناريوهات التي تحتاجها فعلاً.</li>
                            @if($health['queue_async'] ?? false)
                                <li>الإرسال عبر الطابور — شغّل <code>queue:work</code> لـ Flaxxa.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card border-0 shadow-sm dashboard-fade-in">
                    <div class="card-header border-0 pb-0">
                        <h6 class="group-show-members-card__title mb-0">
                            <i class="ri-send-plane-line me-2 text-success"></i>اختبار الإرسال
                        </h6>
                    </div>
                    <div class="card-body pt-3">
                        <p class="text-muted small mb-3">أرسل رمزاً تجريبياً للتأكد من صحة الإعدادات والقناة المختارة.</p>
                        <form method="POST" action="{{ route('admin.settings.phone-otp.test-send') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">رمز الدولة</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-global-line"></i></span>
                                    <input type="text" name="test_country_code" class="form-control" placeholder="966" value="{{ old('test_country_code') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">رقم الاختبار</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                    <input type="text" name="test_phone" class="form-control @error('test_phone') is-invalid @enderror"
                                           value="{{ old('test_phone') }}" placeholder="5xxxxxxxx" required>
                                </div>
                                @error('test_phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-success w-100 rounded-pill">
                                <i class="ri-whatsapp-line me-1"></i> إرسال اختبار OTP
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card custom-card border-0 shadow-sm mt-4 dashboard-fade-in flaxxa-health-field">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2"><i class="ri-key-2-line me-1 text-success"></i> Flaxxa</h6>
                        <p class="small mb-2">
                            التوكن:
                            <span class="badge bg-{{ ($health['token_configured'] ?? false) ? 'success' : 'danger' }}-transparent text-{{ ($health['token_configured'] ?? false) ? 'success' : 'danger' }}">
                                {{ ($health['token_configured'] ?? false) ? 'مُعدّ' : 'غير مُعدّ' }}
                            </span>
                        </p>
                        <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="btn btn-sm btn-outline-success w-100 rounded-pill">قوالب Flaxxa</a>
                    </div>
                </div>

                <div class="card custom-card border-0 shadow-sm mt-4 dashboard-fade-in evolution-health-sidebar">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-2"><i class="ri-smartphone-line me-1 text-primary"></i> Evolution</h6>
                        <p class="small mb-2">
                            أرقام نشطة في التبديل:
                            <strong>{{ $evolutionPoolCount ?? 0 }}</strong>
                        </p>
                        <a href="{{ route('admin.evolution-api.instances.index') }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">إدارة Instances</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    const channelInput = document.getElementById('delivery_channel');
    const channelCards = document.querySelectorAll('.otp-channel-card[data-channel]');
    const templateSelect = document.getElementById('wapi_template_id');
    const langInput = document.getElementById('template_language');
    const evoTemplate = document.getElementById('evolution_message_template');
    const evoPreview = document.getElementById('evolution-preview');

    function syncLanguage() {
        if (!templateSelect || !langInput) return;
        const opt = templateSelect.options[templateSelect.selectedIndex];
        const lang = opt && opt.dataset.language ? opt.dataset.language : '';
        if (lang) langInput.value = lang;
    }

    function syncChannelFields() {
        if (!channelInput) return;
        const isEvolution = channelInput.value === 'evolution';

        document.querySelectorAll('.flaxxa-otp-fields, .flaxxa-health-field').forEach(function (el) {
            el.style.display = isEvolution ? 'none' : '';
        });
        document.querySelectorAll('.evolution-otp-fields').forEach(function (el) {
            el.style.display = isEvolution ? '' : 'none';
        });
        document.querySelectorAll('.evolution-health-sidebar').forEach(function (el) {
            el.style.display = isEvolution ? '' : 'none';
        });

        channelCards.forEach(function (card) {
            card.classList.toggle('is-active', card.dataset.channel === channelInput.value);
        });
    }

    function syncEvoPreview() {
        if (!evoTemplate || !evoPreview) return;
        const sample = evoTemplate.value.replace(/\{code\}/g, '••••••');
        evoPreview.textContent = sample || '—';
    }

    channelCards.forEach(function (card) {
        card.addEventListener('click', function () {
            channelInput.value = card.dataset.channel || 'flaxxa';
            syncChannelFields();
        });
    });

    if (templateSelect) templateSelect.addEventListener('change', syncLanguage);
    if (evoTemplate) evoTemplate.addEventListener('input', syncEvoPreview);

    syncChannelFields();
    syncEvoPreview();
})();
</script>
@endpush
