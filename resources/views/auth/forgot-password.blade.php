<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>نسيت كلمة المرور - أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            direction: rtl;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-width: 120px;
            height: auto;
            margin-bottom: 8px;
        }
        
        .logo-container .logo-title {
            color: #0555a2;
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 0;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0555a2;
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 85, 162, 0.1);
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #0555a2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .btn-submit:hover {
            background: #044080;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(5, 85, 162, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .back-to-login {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .back-to-login a {
            color: #0555a2;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #044080;
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }
        
        .alert-success {
            background-color: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }
        
        .alert ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        .channel-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .channel-tab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: #f8f9fa;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #555;
        }

        .channel-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .channel-tab.is-active {
            border-color: #0555a2;
            background: #eef5fc;
            color: #0555a2;
        }

        .channel-tab.is-active[data-channel="whatsapp"] {
            border-color: #25D366;
            background: #ecfdf3;
            color: #128C7E;
        }

        .channel-tab.is-active[data-channel="whatsapp"] .channel-icon--wa {
            color: #25D366;
        }

        .channel-tab:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .channel-panel { display: none; }
        .channel-panel.is-active { display: block; }

        .btn-submit.btn-submit--wa {
            background: #25D366;
        }

        .btn-submit.btn-submit--wa:hover {
            background: #1da851;
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.35);
        }

        .form-hint--warn {
            background: #fff8e6;
            border: 1px solid #ffe08a;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 10px;
            color: #7a5b00;
        }

        .phone-country-label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .phone-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.35fr);
            gap: 10px;
            align-items: start;
        }

        .phone-row .form-control,
        .phone-row .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 46px;
            border: 2px solid #e0e0e0 !important;
            border-radius: 10px !important;
            background: #f8f9fa !important;
            padding: 4px 8px;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #25D366 !important;
            background: #fff !important;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.12);
        }

        .select2-container--bootstrap-5 .select2-results__option img,
        .select2-container--bootstrap-5 .select2-selection__rendered img {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 0;
        }

        @media (max-width: 576px) {
            .phone-row {
                grid-template-columns: 1fr;
            }
        }

        .form-hint {
            font-size: 13px;
            color: #888;
            margin-top: 6px;
            line-height: 1.5;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo">
            <div class="logo-title">أكاديمية كلاودسوفت</div>
        </div>
        
        <div class="login-header">
            <h1>نسيت كلمة المرور؟</h1>
            <p id="header-desc">@if(!empty($whatsappAvailable))الطريقة الافتراضية: <strong>واتساب</strong> — أو اختر البريد الإلكتروني.@elseاختر طريقة الاستعادة: البريد الإلكتروني.@endif</p>
            <p id="steps-email" style="font-size: 14px; color: #888; margin-top: 10px; display: {{ !empty($whatsappAvailable) ? 'none' : '' }};">
                <strong>عبر البريد:</strong><br>
                1️⃣ افتح بريدك · 2️⃣ اضغط الرابط · 3️⃣ أدخل كلمة مرور جديدة
            </p>
            <p id="steps-whatsapp" style="font-size: 14px; color: #888; margin-top: 10px; display: {{ !empty($whatsappAvailable) ? '' : 'none' }};">
                <strong>عبر الواتساب:</strong><br>
                1️⃣ افتح محادثة الواتساب · 2️⃣ اضغط الرابط · 3️⃣ أدخل كلمة مرور جديدة
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>خطأ!</strong>
                <ul style="margin-top: 8px; margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success" style="line-height: 1.8;">
                <strong style="display: block; margin-bottom: 10px; font-size: 16px;">✅ {{ session('status') }}</strong>
                @if(session('reset_channel') === 'whatsapp')
                    <p style="margin-bottom: 0; font-size: 14px;">تحقق من واتساب الرقم <strong>{{ session('reset_contact') }}</strong> واضغط الرابط المرسل.</p>
                @else
                    <p style="margin-bottom: 0; font-size: 14px;">تحقق من بريد <strong>{{ session('reset_contact', old('email')) }}</strong> (ومجلد الرسائل المزعجة).</p>
                @endif
            </div>
        @endif

        @php
            $defaultChannel = old('channel', !empty($whatsappAvailable) ? 'whatsapp' : 'email');
        @endphp

        <form method="POST" action="{{ route('password.email') }}" id="forgot-form">
            @csrf
            <input type="hidden" name="channel" id="channel-input" value="{{ $defaultChannel }}">

            <div class="channel-tabs">
                <button type="button"
                        class="channel-tab {{ $defaultChannel === 'whatsapp' ? 'is-active' : '' }}"
                        data-channel="whatsapp"
                        @if(empty($whatsappAvailable)) disabled title="الواتساب غير مفعّل" @endif>
                    <svg class="channel-icon channel-icon--wa" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>واتساب</span>
                </button>
                <button type="button" class="channel-tab {{ $defaultChannel === 'email' ? 'is-active' : '' }}" data-channel="email">
                    <svg class="channel-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M22 6l-10 7L2 6"/>
                    </svg>
                    <span>البريد الإلكتروني</span>
                </button>
            </div>

            <div class="channel-panel {{ $defaultChannel === 'email' ? 'is-active' : '' }}" id="panel-email">
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        placeholder="أدخل بريدك الإلكتروني"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="channel-panel {{ $defaultChannel === 'whatsapp' ? 'is-active' : '' }}" id="panel-whatsapp">
                <x-auth-phone-country-fields
                    country-code-id="forgot_country_code_select"
                    :phone-error="$errors->first('phone')"
                    :country-error="$errors->first('country_code')"
                />
            </div>

            <button type="submit" class="btn-submit" id="submit-btn">
                إرسال رابط إعادة التعيين
            </button>
        </form>

        <div class="back-to-login">
            <a href="{{ route('login') }}">← العودة إلى تسجيل الدخول</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    (function () {
        const channelInput = document.getElementById('channel-input');
        const tabs = document.querySelectorAll('.channel-tab[data-channel]');
        const panelEmail = document.getElementById('panel-email');
        const panelWhatsapp = document.getElementById('panel-whatsapp');
        const emailField = document.getElementById('email');
        const phoneField = document.getElementById('phone');
        const countryCodeField = document.getElementById('forgot_country_code_select');
        const stepsEmail = document.getElementById('steps-email');
        const stepsWhatsapp = document.getElementById('steps-whatsapp');
        const submitBtn = document.getElementById('submit-btn');

        const flagUrlTemplate = countryCodeField
            ? (countryCodeField.getAttribute('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png')
            : 'https://flagcdn.com/w20/{iso}.png';

        if (window.jQuery && countryCodeField) {
            jQuery(countryCodeField).select2({
                placeholder: 'اختر الدولة',
                allowClear: false,
                dir: 'rtl',
                width: '100%',
                theme: 'bootstrap-5',
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
                    var $span = jQuery('<span class="d-flex align-items-center gap-2"></span>');
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
            panelWhatsapp.classList.toggle('is-active', channel === 'whatsapp');
            stepsEmail.style.display = channel === 'email' ? '' : 'none';
            stepsWhatsapp.style.display = channel === 'whatsapp' ? '' : 'none';
            if (emailField) emailField.required = channel === 'email';
            if (phoneField) phoneField.required = channel === 'whatsapp';
            if (countryCodeField) countryCodeField.required = channel === 'whatsapp';
            submitBtn.textContent = channel === 'whatsapp'
                ? 'إرسال الرابط عبر الواتساب'
                : 'إرسال الرابط عبر البريد';
            submitBtn.classList.toggle('btn-submit--wa', channel === 'whatsapp');
            if (channel === 'email' && emailField) {
                emailField.focus();
            } else if (phoneField) {
                phoneField.focus();
            }
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
