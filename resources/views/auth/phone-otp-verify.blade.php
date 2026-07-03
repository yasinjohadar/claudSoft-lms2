@extends('auth.layouts.auth-shell')

@section('auth-title', 'التحقق من الرمز — أكاديمية كلاودسوفت')

@section('auth-heading', 'التحقق من الرمز')

@section('auth-subheading')
    أدخل رمز التحقق المرسل إلى واتساب
    <strong dir="ltr" style="display:inline-block;">{{ $phoneDisplay }}</strong>
@endsection

@section('auth-content')
    @php
        $verifyAction = match($purpose) {
            \App\Enums\OtpPurpose::Login => route('phone-login.verify'),
            \App\Enums\OtpPurpose::Register => route('register.otp.complete'),
            \App\Enums\OtpPurpose::ResetPassword => route('password.otp.verify'),
            \App\Enums\OtpPurpose::ChangePhone => route('phone.change.apply'),
            default => route('phone-otp.verify.submit'),
        };
    @endphp

    <form method="POST" action="{{ $verifyAction }}" data-device-token novalidate>
        @csrf
        <input type="hidden" name="device_token" value="">
        <input type="hidden" name="phone" value="{{ $phone }}">
        @if($purpose !== \App\Enums\OtpPurpose::ChangePhone)
            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
        @endif

        <div class="auth-field">
            <label for="code">رمز التحقق</label>
            <div class="auth-input-group auth-input-group--otp @error('code') is-invalid @enderror">
                <input
                    type="text"
                    name="code"
                    id="code"
                    class="auth-input auth-input--otp"
                    maxlength="8"
                    autocomplete="one-time-code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    placeholder="••••••"
                    required
                    autofocus
                    dir="ltr"
                >
            </div>
            @error('code')
                <span class="auth-invalid">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-btn">تأكيد الرمز</button>
    </form>

    <form method="POST" action="{{ route('phone-otp.send') }}" id="resendForm" class="auth-resend-form">
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">
        <input type="hidden" name="purpose" value="{{ $purpose->value }}">
        <button type="submit" class="auth-btn auth-btn--outline" id="resendBtn" @if(($resendCooldownRemaining ?? 0) > 0) disabled @endif>
            إعادة إرسال الرمز
        </button>
        <p class="auth-resend-hint" id="resendHint">
            @if(($resendCooldownRemaining ?? 0) > 0)
                يمكنك إعادة الإرسال بعد <span id="resendCountdown">{{ $resendCooldownRemaining }}</span> ثانية
            @else
                لم يصلك الرمز؟ أعد الإرسال عبر واتساب
            @endif
        </p>
    </form>
@endsection

@section('auth-footer')
    <a href="{{ route('login') }}">العودة لتسجيل الدخول</a>
@endsection

@push('auth-scripts')
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
            if (hint) hint.textContent = 'لم يصلك الرمز؟ أعد الإرسال عبر واتساب';
            return;
        }
        if (btn) btn.disabled = true;
        if (countdownEl) countdownEl.textContent = String(remaining);
        else if (hint) hint.innerHTML = 'يمكنك إعادة الإرسال بعد <span id="resendCountdown">' + remaining + '</span> ثانية';
        remaining--;
        setTimeout(tick, 1000);
    }

    if (remaining > 0) tick();

    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
        });
    }
})();
</script>
@endpush
