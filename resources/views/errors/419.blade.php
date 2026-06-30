@php
    $redirect = $redirect ?? \App\Support\SessionExpiredRedirect::resolve(request());
    $autoRedirectSeconds = 12;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    <title>انتهت الجلسة — أكاديمية كلاودسوفت</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/session-expired.css') }}?v={{ @filemtime(public_path('assets/css/session-expired.css')) ?: 1 }}">
</head>
<body>
    <div class="session-expired-page">
        <div class="session-expired-card" role="alertdialog" aria-labelledby="session-expired-title" aria-describedby="session-expired-desc">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="كلاودسوفت" class="session-expired-card__logo">

            <div class="session-expired-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>

            <h1 id="session-expired-title">انتهت صلاحية الجلسة</h1>
            <p id="session-expired-desc" class="session-expired-card__lead">
                بقيت الصفحة مفتوحة لفترة طويلة دون نشاط، لذلك انتهت الجلسة لحماية حسابك.
                لا تقلق — بياناتك آمنة. فقط حدّث الصفحة وحاول مرة أخرى.
            </p>

            <ul class="session-expired-card__tips">
                <li>اضغط الزر أدناه للعودة بصفحة جديدة وجلسة محدّثة</li>
                <li>إذا كنت تملأ نموذجاً، قد تحتاج لإعادة إدخال البيانات</li>
                <li>تجنّب ترك صفحة تسجيل الدخول مفتوحة لساعات طويلة</li>
            </ul>

            <div class="session-expired-card__actions">
                <a href="{{ $redirect['url'] }}" class="session-expired-btn session-expired-btn--primary" id="session-expired-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                    </svg>
                    {{ $redirect['label'] }}
                </a>
                @if (! empty($redirect['secondary_url']) && ! empty($redirect['secondary_label']))
                    <a href="{{ $redirect['secondary_url'] }}" class="session-expired-btn session-expired-btn--ghost">
                        {{ $redirect['secondary_label'] }}
                    </a>
                @endif
            </div>

            <p class="session-expired-card__countdown" id="session-expired-countdown">
                سيتم توجيهك تلقائياً خلال <strong>{{ $autoRedirectSeconds }}</strong> ثانية…
            </p>

            <p class="session-expired-card__code">419</p>
        </div>
    </div>

    <script>
        (function () {
            var redirectUrl = @json($redirect['url']);
            var seconds = {{ $autoRedirectSeconds }};
            var countdownEl = document.getElementById('session-expired-countdown');
            var primaryBtn = document.getElementById('session-expired-primary');

            function go() {
                window.location.replace(redirectUrl);
            }

            primaryBtn.addEventListener('click', function (e) {
                e.preventDefault();
                go();
            });

            var timer = setInterval(function () {
                seconds -= 1;
                if (seconds <= 0) {
                    clearInterval(timer);
                    go();
                    return;
                }
                countdownEl.innerHTML = 'سيتم توجيهك تلقائياً خلال <strong>' + seconds + '</strong> ثانية…';
            }, 1000);
        })();
    </script>
</body>
</html>
