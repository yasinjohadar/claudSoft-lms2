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
        .error { color: #dc3545; font-size: .9rem; margin-top: 8px; }
        .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
<div class="card">
    <h1>التحقق من الرمز</h1>
    <p>أدخل رمز التحقق المرسل إلى واتساب <strong>{{ $phoneDisplay }}</strong></p>

    @if(session('status'))
        <div class="alert">{{ session('status') }}</div>
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

    <form method="POST" action="{{ $verifyAction }}">
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">
        @if($purpose !== \App\Enums\OtpPurpose::ChangePhone)
            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
        @endif

        <label for="code">رمز التحقق</label>
        <input type="text" name="code" id="code" maxlength="8" autocomplete="one-time-code" required autofocus>
        @error('code')<div class="error">{{ $message }}</div>@enderror

        <button type="submit" class="btn">تأكيد</button>
    </form>

    <p style="margin-top:20px;font-size:.85rem;text-align:center;">
        <a href="{{ route('login') }}">العودة لتسجيل الدخول</a>
    </p>
</div>
</body>
</html>
