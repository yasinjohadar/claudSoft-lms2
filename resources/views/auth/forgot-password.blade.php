<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>نسيت كلمة المرور - أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-login.css') }}?v={{ filemtime(public_path('assets/css/auth-login.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-forgot-password.css') }}?v={{ filemtime(public_path('assets/css/auth-forgot-password.css')) }}">
</head>
<body>
    @php
        $defaultChannel = old('channel', !empty($whatsappAvailable) ? 'whatsapp' : 'email');
    @endphp

    <div class="auth-page auth-page--forgot">
        <aside class="auth-brand">
            <div class="auth-brand__inner">
                <div class="auth-brand__logo-wrap">
                    <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-brand__logo">
                </div>
                <div>
                    <h2 class="auth-brand__name">استعادة كلمة المرور</h2>
                    <p class="auth-brand__tagline auth-page--forgot__tagline">
                        اختر الطريقة المناسبة — واتساب أو بريد إلكتروني — وسنرسل لك بيانات الدخول الجديدة عبر الواتساب والبريد.
                    </p>
                </div>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-card auth-card--wide">
                <div class="auth-card__header">
                    <div class="auth-card__logo-wrap">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-card__logo">
                    </div>
                    <h1>نسيت كلمة المرور؟</h1>
                    <p id="header-desc">
                        @if(!empty($whatsappAvailable))
                            الطريقة الافتراضية: <strong>واتساب</strong> — أو اختر البريد الإلكتروني.
                        @else
                            اختر طريقة الاستعادة عبر البريد الإلكتروني.
                        @endif
                    </p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert auth-alert--danger" role="alert">
                        <strong>تعذّر إرسال الطلب</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert auth-alert--success" role="status">
                        <strong>{{ session('status') }}</strong>
                        @if(session('reset_channel') === 'whatsapp')
                            <p class="mb-0 mt-1">تحقق من واتساب الرقم <strong>{{ session('reset_contact') }}</strong>@if(!data_get(session('reset_delivery'), 'whatsapp_sent')) <span class="text-warning">(تعذّر إرسال الواتساب — راجع البريد)</span>@endif وبريدك الإلكتروني.</p>
                        @else
                            <p class="mb-0 mt-1">تحقق من بريد <strong>{{ session('reset_contact', old('email')) }}</strong>@if(data_get(session('reset_delivery'), 'whatsapp_recipient')) وواتساب <strong>{{ data_get(session('reset_delivery'), 'whatsapp_recipient') }}</strong>@endif (ومجلد الرسائل المزعجة).</p>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgot-form" class="auth-forgot-form">
                    @csrf
                    <input type="hidden" name="channel" id="channel-input" value="{{ $defaultChannel }}">

                    <div class="auth-channels">
                        <button type="button"
                                class="auth-channel {{ $defaultChannel === 'whatsapp' ? 'is-active' : '' }}"
                                data-channel="whatsapp"
                                @if(empty($whatsappAvailable)) disabled title="الواتساب غير مفعّل" @endif>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <span>واتساب</span>
                        </button>
                        <button type="button"
                                class="auth-channel {{ $defaultChannel === 'email' ? 'is-active' : '' }}"
                                data-channel="email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>
                            </svg>
                            <span>البريد الإلكتروني</span>
                        </button>
                        @if(!empty($whatsappOtpAvailable))
                            <button type="button"
                                    class="auth-channel auth-channel--full {{ $defaultChannel === 'whatsapp_otp' ? 'is-active' : '' }}"
                                    data-channel="whatsapp_otp">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span>OTP واتساب</span>
                            </button>
                        @endif
                    </div>

                    <div class="auth-steps" id="auth-steps-box" aria-live="polite">
                        @if($defaultChannel === 'email')
                            <strong>عبر البريد:</strong> 1. أدخل بريدك · 2. استلم بيانات الدخول · 3. سجّل الدخول
                        @elseif($defaultChannel === 'whatsapp_otp')
                            <strong>عبر OTP واتساب:</strong> 1. أدخل رقمك · 2. استلم الرمز · 3. أدخل كلمة مرور جديدة
                        @else
                            <strong>عبر الواتساب:</strong> 1. أدخل رقمك · 2. استلم بيانات الدخول · 3. سجّل الدخول
                        @endif
                    </div>

                    <div class="auth-panel {{ $defaultChannel === 'email' ? 'is-active' : '' }}" id="panel-email">
                        <div class="auth-field">
                            <label for="email">البريد الإلكتروني</label>
                            <div class="auth-input-group @error('email') is-invalid @enderror">
                                <span class="auth-input-group__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </span>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    class="auth-input"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    placeholder="example@email.com"
                                    dir="ltr"
                                    style="text-align: right;"
                                >
                            </div>
                            @error('email')
                                <span class="auth-invalid">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="auth-panel {{ ($defaultChannel === 'whatsapp' || $defaultChannel === 'whatsapp_otp') ? 'is-active' : '' }}" id="panel-whatsapp">
                        <x-auth-phone-country-fields
                            country-code-id="forgot_country_code_select"
                            :phone-error="$errors->first('phone')"
                            :country-error="$errors->first('country_code')"
                        />
                    </div>

                    <button type="submit" class="auth-btn" id="submit-btn">
                        إرسال بيانات الدخول
                    </button>
                </form>

                <div class="auth-back">
                    <a href="{{ route('login') }}">← العودة إلى تسجيل الدخول</a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    (function () {
        var channelInput = document.getElementById('channel-input');
        var tabs = document.querySelectorAll('.auth-channel[data-channel]');
        var panelEmail = document.getElementById('panel-email');
        var panelWhatsapp = document.getElementById('panel-whatsapp');
        var emailField = document.getElementById('email');
        var phoneField = document.getElementById('phone');
        var countryCodeField = document.getElementById('forgot_country_code_select');
        var stepsBox = document.getElementById('auth-steps-box');
        var submitBtn = document.getElementById('submit-btn');

        var stepsCopy = {
            email: '<strong>عبر البريد:</strong> 1. أدخل بريدك · 2. استلم بيانات الدخول · 3. سجّل الدخول',
            whatsapp: '<strong>عبر الواتساب:</strong> 1. أدخل رقمك · 2. استلم بيانات الدخول · 3. سجّل الدخول',
            whatsapp_otp: '<strong>عبر OTP واتساب:</strong> 1. أدخل رقمك · 2. استلم الرمز · 3. أدخل كلمة مرور جديدة'
        };

        var flagUrlTemplate = countryCodeField
            ? (countryCodeField.getAttribute('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png')
            : 'https://flagcdn.com/w20/{iso}.png';

        if (window.jQuery && countryCodeField) {
            jQuery(countryCodeField).select2({
                placeholder: 'اختر الدولة',
                allowClear: false,
                dir: 'rtl',
                width: '100%',
                theme: 'bootstrap-5',
                dropdownAutoWidth: false,
                templateResult: function (state) {
                    if (!state.id) return state.text;
                    var iso = jQuery(state.element).data('iso') || 'sa';
                    var url = flagUrlTemplate.replace('{iso}', String(iso).toLowerCase());
                    var $span = jQuery('<span class="d-flex align-items-center gap-2"></span>');
                    $span.append(jQuery('<img src="' + url + '" alt="">'));
                    $span.append(document.createTextNode(state.text));
                    return $span;
                },
                templateSelection: function (state) {
                    if (!state.id) return state.text;
                    var iso = jQuery(state.element).data('iso') || 'sa';
                    var url = flagUrlTemplate.replace('{iso}', String(iso).toLowerCase());
                    var $span = jQuery('<span class="d-flex align-items-center gap-2" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%"></span>');
                    $span.append(jQuery('<img src="' + url + '" alt="">'));
                    $span.append(document.createTextNode(state.text));
                    return $span;
                }
            });
        }

        if (phoneField) {
            phoneField.addEventListener('input', function () {
                phoneField.value = phoneField.value.replace(/\D/g, '');
                if (phoneField.value.charAt(0) === '0') {
                    phoneField.value = phoneField.value.replace(/^0+/, '');
                }
            });
            phoneField.addEventListener('blur', function () {
                phoneField.value = phoneField.value.replace(/^0+/, '');
            });
        }

        function setChannel(channel) {
            channelInput.value = channel;
            tabs.forEach(function (tab) {
                tab.classList.toggle('is-active', tab.dataset.channel === channel);
            });
            panelEmail.classList.toggle('is-active', channel === 'email');
            panelWhatsapp.classList.toggle('is-active', channel === 'whatsapp' || channel === 'whatsapp_otp');
            if (stepsBox) {
                stepsBox.innerHTML = stepsCopy[channel] || stepsCopy.email;
            }
            if (emailField) emailField.required = channel === 'email';
            if (phoneField) phoneField.required = channel === 'whatsapp' || channel === 'whatsapp_otp';
            if (countryCodeField) countryCodeField.required = channel === 'whatsapp' || channel === 'whatsapp_otp';
            submitBtn.textContent = channel === 'whatsapp_otp'
                ? 'إرسال رمز OTP'
                : 'إرسال بيانات الدخول';
            submitBtn.classList.toggle('auth-btn--wa', channel === 'whatsapp' || channel === 'whatsapp_otp');
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                if (tab.disabled) return;
                setChannel(tab.dataset.channel);
            });
        });

        setChannel(channelInput.value || 'whatsapp');
    })();
    </script>
</body>
</html>
