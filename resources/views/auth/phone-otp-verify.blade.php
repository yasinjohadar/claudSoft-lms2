<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من الرمز — أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; background: #f5f7fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; direction: rtl; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.08); max-width: 420px; width: 100%; padding: 32px; }
        h1 { font-size: 1.35rem; margin-bottom: 8px; }
        p { color: #666; margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1.1rem; letter-spacing: 4px; text-align: center; }
        .btn { width: 100%; margin-top: 16px; padding: 12px; border: none; border-radius: 8px; background: #4f46e5; color: #fff; font-weight: 700; cursor: pointer; }
        .btn:disabled { opacity: .55; cursor: not-allowed; }
        .btn-outline { background: #fff; color: #4f46e5; border: 1px solid #4f46e5; }
        .error { color: #dc3545; font-size: .9rem; margin-top: 8px; }
        .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; background: #ecfdf5; color: #065f46; }
        .resend-hint { font-size: .85rem; color: #888; text-align: center; margin-top: 8px; }
    </style>
</head>
<body>
<div class="card">
    <h1>التحقق من الرمز</h1>
    <p>أدخل رمز التحقق المرسل إلى واتساب <strong>{{ $phoneDisplay }}</strong></p>

    @if(session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="error" style="padding:10px 12px;border-radius:8px;background:#fef2f2;margin-bottom:16px;">{{ session('error') }}</div>
    @endif

    @php
        $verifyAction = match($purpose) {
            \App\Enums\OtpPurpose::Login => route('phone-login.verify'),
            \App\Enums\OtpPurpose::Register => route('register.otp.complete'),
            \App\Enums\OtpPurpose::ResetPassword => route('password.otp.verify'),
            \App\Enums\OtpPurpose::ChangePhone => route('phone.change.apply'),
            default => route('phone-otp.verify.submit'),
        };
    @endphp

    <form method="POST" action="{{ $verifyAction }}" data-device-token>
        @csrf
        <input type="hidden" name="device_token" value="">
        <input type="hidden" name="phone" value="{{ $phone }}">
        @if($purpose !== \App\Enums\OtpPurpose::ChangePhone)
            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
        @endif

        <label for="code">رمز التحقق</label>
        <input type="text" name="code" id="code" maxlength="8" autocomplete="one-time-code" required autofocus>
        @error('code')<div class="error">{{ $message }}</div>@enderror

        <button type="submit" class="btn">تأكيد</button>
    </form>

    <form method="POST" action="{{ route('phone-otp.send') }}" id="resendForm" style="margin-top:12px;">
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">
        <input type="hidden" name="purpose" value="{{ $purpose->value }}">
        <button type="submit" class="btn btn-outline" id="resendBtn" @if(($resendCooldownRemaining ?? 0) > 0) disabled @endif>
            إعادة إرسال الرمز
        </button>
        <p class="resend-hint" id="resendHint">
            @if(($resendCooldownRemaining ?? 0) > 0)
                يمكنك إعادة الإرسال بعد <span id="resendCountdown">{{ $resendCooldownRemaining }}</span> ثانية
            @else
                لم يصلك الرمز؟ أعد الإرسال
            @endif
        </p>
    </form>

    <p style="margin-top:20px;font-size:.85rem;text-align:center;">
        <a href="{{ route('login') }}">العودة لتسجيل الدخول</a>
    </p>
</div>
<script src="{{ asset('assets/js/device-token.js') }}?v={{ filemtime(public_path('assets/js/device-token.js')) }}"></script>
<script>
(function () {
    let remaining = {{ (int) ($resendCooldownRemaining ?? 0) }};
    const btn = document.getElementById('resendBtn');
    const hint = document.getElementById('resendHint');
    const countdownEl = document.getElementById('resendCountdown');

    function tick() {
        if (remaining <= 0) {
            if (btn) btn.disabled = false;
            if (hint) hint.textContent = 'لم يصلك الرمز؟ أعد الإرسال';
            return;
        }
        if (btn) btn.disabled = true;
        if (countdownEl) countdownEl.textContent = String(remaining);
        else if (hint) hint.innerHTML = 'يمكنك إعادة الإرسال بعد <span id="resendCountdown">' + remaining + '</span> ثانية';
        remaining--;
        setTimeout(tick, 1000);
    }

    if (remaining > 0) tick();
})();
</script>
</body>
</html>
