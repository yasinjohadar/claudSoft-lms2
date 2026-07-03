@extends('auth.layouts.auth-shell')

@section('auth-title', 'الدخول برمز OTP — أكاديمية كلاودسوفت')

@section('auth-heading', 'تسجيل الدخول برمز OTP')

@section('auth-subheading', 'سنرسل رمزاً إلى واتسابك المسجّل في المنصة')

@section('auth-content')
    <form method="POST" action="{{ route('phone-login.send-otp') }}" novalidate>
        @csrf

        <div class="auth-field">
            <label for="country_code">رمز الدولة</label>
            <div class="auth-input-group @error('country_code') is-invalid @enderror">
                <span class="auth-input-group__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </span>
                <select name="country_code" id="country_code" class="auth-input auth-select" required>
                    @foreach($countryCodes as $code => $label)
                        <option value="{{ $code }}" @selected(old('country_code', config('country_codes.default', '+966')) === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @error('country_code')
                <span class="auth-invalid">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="phone">رقم الجوال</label>
            <div class="auth-input-group @error('phone') is-invalid @enderror">
                <span class="auth-input-group__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <input
                    type="text"
                    name="phone"
                    id="phone"
                    class="auth-input"
                    value="{{ old('phone') }}"
                    placeholder="بدون صفر في البداية"
                    required
                    autofocus
                    inputmode="numeric"
                    autocomplete="tel-national"
                    dir="ltr"
                    style="text-align: right;"
                >
            </div>
            @error('phone')
                <span class="auth-invalid">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-btn auth-btn--whatsapp">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="20" height="20">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 0 0 .611.611l4.458-1.495A11.953 11.953 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
            </svg>
            إرسال رمز الدخول
        </button>
    </form>

    <div class="auth-divider">أو</div>
    <a href="{{ route('login') }}" class="auth-link-btn">الدخول بالبريد وكلمة المرور</a>
@endsection
