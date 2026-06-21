<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدخول برمز OTP — أكاديمية كلاودسوفت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; background: #f5f7fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; direction: rtl; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.08); max-width: 420px; width: 100%; padding: 32px; }
        h1 { font-size: 1.35rem; margin-bottom: 8px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; margin-top: 12px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .btn { width: 100%; margin-top: 20px; padding: 12px; border: none; border-radius: 8px; background: #4f46e5; color: #fff; font-weight: 700; cursor: pointer; }
        .error { color: #dc3545; font-size: .9rem; margin-top: 6px; }
    </style>
</head>
<body>
<div class="card">
    <h1>تسجيل الدخول برمز OTP</h1>
    <p style="color:#666;margin-bottom:16px;">سنرسل رمزاً إلى واتسابك المسجّل في المنصة.</p>

    <form method="POST" action="{{ route('phone-login.send-otp') }}">
        @csrf
        <label for="country_code">رمز الدولة</label>
        <select name="country_code" id="country_code" class="form-select" required>
            @foreach($countryCodes as $code => $label)
                <option value="{{ $code }}" @selected(old('country_code') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('country_code')<div class="error">{{ $message }}</div>@enderror

        <label for="phone">رقم الجوال</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="بدون صفر في البداية" required>
        @error('phone')<div class="error">{{ $message }}</div>@enderror

        <button type="submit" class="btn">إرسال رمز الدخول</button>
    </form>

    <p style="margin-top:20px;text-align:center;font-size:.9rem;">
        <a href="{{ route('login') }}">الدخول بالبريد وكلمة المرور</a>
    </p>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>$('#country_code').select2({ width: '100%' });</script>
</body>
</html>
