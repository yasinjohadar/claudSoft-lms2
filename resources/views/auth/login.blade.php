<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>تسجيل الدخول - أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-login.css') }}?v={{ filemtime(public_path('assets/css/auth-login.css')) }}">
</head>
<body>
    <div class="auth-page">
        <aside class="auth-brand">
            <div class="auth-brand__inner">
                <div class="auth-brand__logo-wrap">
                    <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-brand__logo">
                </div>
                <div>
                    <h2 class="auth-brand__name">أكاديمية كلاودسوفت</h2>
                    <p class="auth-brand__tagline">
                        منصة تعليمية متكاملة — كورسات، مشاريع، وشهادات معتمدة لتطوير مهاراتك التقنية.
                    </p>
                </div>
                <ul class="auth-brand__features">
                    <li class="auth-brand__feature">
                        <span class="auth-brand__feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </span>
                        <span>محتوى تعليمي منظم ومسارات واضحة</span>
                    </li>
                    <li class="auth-brand__feature">
                        <span class="auth-brand__feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        </span>
                        <span>تحديات مشاريع وشهادات إنجاز</span>
                    </li>
                    <li class="auth-brand__feature">
                        <span class="auth-brand__feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </span>
                        <span>دعم ومتابعة من فريق كلاودسوفت</span>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-card">
                <div class="auth-card__header">
                    <div class="auth-card__logo-wrap">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-card__logo">
                    </div>
                    <h1>أهلاً بعودتك</h1>
                    <p>سجّل دخولك للوصول إلى كورساتك ومشاريعك</p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert auth-alert--danger" role="alert">
                        <strong>تعذّر تسجيل الدخول</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (request('session_expired'))
                    <div class="auth-alert auth-alert--warning" role="status">
                        تم تحديث الصفحة. انتهت جلستك السابقة — يمكنك تسجيل الدخول الآن.
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert auth-alert--success" role="status">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="auth-alert auth-alert--danger" role="alert">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate data-device-token>
                    @csrf
                    <input type="hidden" name="device_token" value="">

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
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="example@email.com"
                                inputmode="email"
                                dir="ltr"
                                style="text-align: right;"
                            >
                        </div>
                        @error('email')
                            <span class="auth-invalid">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">كلمة المرور</label>
                        <div class="auth-input-group @error('password') is-invalid @enderror">
                            <span class="auth-input-group__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="auth-input"
                                required
                                autocomplete="current-password"
                                placeholder="أدخل كلمة المرور"
                            >
                            <button
                                type="button"
                                id="password-toggle"
                                class="auth-input-action"
                                aria-pressed="false"
                                aria-label="إظهار كلمة المرور"
                                title="إظهار كلمة المرور"
                            >
                                <svg class="icon-password-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="icon-password-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="auth-invalid">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-row">
                        <label class="auth-remember">
                            <input type="checkbox" id="remember" name="remember" @checked(old('remember'))>
                            <span>تذكرني</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="auth-forgot">نسيت كلمة المرور؟</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-btn">تسجيل الدخول</button>
                </form>

                @if(\Illuminate\Support\Facades\Route::has('phone-login'))
                    <div class="auth-divider">أو</div>
                    <a href="{{ route('phone-login') }}" class="auth-otp-btn">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 0 0 .611.611l4.458-1.495A11.953 11.953 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
                        </svg>
                        الدخول برمز OTP عبر واتساب
                    </a>
                @endif

                <div class="auth-footer">
                    ليس لديك حساب؟ <a href="{{ route('register') }}">إنشاء حساب جديد</a>
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            var input = document.getElementById('password');
            var btn = document.getElementById('password-toggle');
            if (!input || !btn) return;

            function applyState(revealed) {
                input.type = revealed ? 'text' : 'password';
                btn.classList.toggle('is-password-revealed', revealed);
                btn.setAttribute('aria-pressed', revealed ? 'true' : 'false');
                btn.setAttribute('aria-label', revealed ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
                btn.setAttribute('title', revealed ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            }

            applyState(false);
            btn.addEventListener('click', function () {
                applyState(input.type === 'password');
            });
        })();
    </script>
    <script src="{{ asset('assets/js/device-token.js') }}?v={{ filemtime(public_path('assets/js/device-token.js')) }}"></script>
</body>
</html>
