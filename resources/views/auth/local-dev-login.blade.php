<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>دخول تطوير محلي - أكاديمية كلاودسوفت</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-login.css') }}?v={{ filemtime(public_path('assets/css/auth-login.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/auth-local-dev-login.css') }}?v={{ filemtime(public_path('assets/css/auth-local-dev-login.css')) }}">
</head>
<body>
    <div class="auth-page auth-page--local-dev">
        <main class="auth-main auth-main--local-dev">
            <div class="auth-card auth-card--local-dev">
                <div class="auth-card__header">
                    <div class="auth-card__logo-wrap">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="auth-card__logo">
                    </div>
                    <h1>دخول تطوير محلي</h1>
                    <p>صفحة مخفية للاختبار — تعمل فقط على <strong>local</strong> وعند تفعيلها من الإعدادات.</p>
                </div>

                <div class="local-dev-badge" role="status">
                    <span class="local-dev-badge__dot" aria-hidden="true"></span>
                    بيئة محلية — {{ $accessPath }}
                </div>

                @if (session('error'))
                    <div class="auth-alert auth-alert--danger" role="alert">{{ session('error') }}</div>
                @endif

                <div class="local-dev-actions">
                    <form method="POST" action="{{ url($accessPath) }}" data-device-token>
                        @csrf
                        <input type="hidden" name="device_token" value="">
                        <input type="hidden" name="role" value="admin">
                        <button type="submit" class="local-dev-btn local-dev-btn--admin">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/>
                            </svg>
                            <span>
                                <strong>دخول كأدمن</strong>
                                <small>{{ $adminEmail }}</small>
                            </span>
                        </button>
                    </form>

                    <form method="POST" action="{{ url($accessPath) }}" data-device-token>
                        @csrf
                        <input type="hidden" name="device_token" value="">
                        <input type="hidden" name="role" value="student">
                        <button type="submit" class="local-dev-btn local-dev-btn--student">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>
                                <strong>دخول كطالب</strong>
                                <small>{{ $studentEmail }}</small>
                            </span>
                        </button>
                    </form>
                </div>

                <p class="local-dev-note">
                    لا تظهر هذه الصفحة على السيرفر. يمكن إيقافها نهائياً من
                    <strong>إعدادات الموقع</strong> في لوحة التحكم.
                </p>

                <div class="auth-back">
                    <a href="{{ route('login') }}">← العودة إلى تسجيل الدخول</a>
                </div>
            </div>
        </main>
    </div>
    <script src="{{ asset('assets/js/device-token.js') }}?v={{ filemtime(public_path('assets/js/device-token.js')) }}"></script>
    <script src="{{ asset('assets/js/device-fingerprint.js') }}?v={{ filemtime(public_path('assets/js/device-fingerprint.js')) }}"></script>
</body>
</html>
