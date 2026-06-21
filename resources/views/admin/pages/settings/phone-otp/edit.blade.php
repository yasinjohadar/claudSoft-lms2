@extends('admin.layouts.master')

@section('page-title')
    OTP عبر واتساب (Flaxxa)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h4 class="card-title mb-1">إعدادات OTP عبر Flaxxa WAPI</h4>
                <p class="text-muted fs-12 mb-0">رسائل OTP تُرسل حصرياً عبر قوالب Meta المعتمدة (مسار Flaxxa) — لا تستخدم Evolution.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.phone-otp.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="otpEnabled" @checked(old('enabled', $settings['enabled'] ?? false))>
                        <label class="form-check-label fw-semibold" for="otpEnabled">تفعيل OTP عبر واتساب</label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="wapi_template_id">قالب Flaxxa (Meta)</label>
                            <select name="wapi_template_id" id="wapi_template_id" class="form-select">
                                <option value="">— اختر قالب OTP —</option>
                                @foreach($wapiTemplates as $tpl)
                                    <option value="{{ $tpl->id }}" @selected(old('wapi_template_id', $settings['wapi_template_id'] ?? '') == $tpl->id)>
                                        {{ $tpl->name }} ({{ $tpl->language }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">مزامنة القوالب من <a href="{{ route('admin.flaxxa-wapi.templates.index') }}">قوالب Flaxxa</a></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="template_language">لغة القالب</label>
                            <input type="text" name="template_language" id="template_language" class="form-control" value="{{ old('template_language', $settings['template_language'] ?? 'ar') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="code_length">طول الرمز</label>
                            <input type="number" name="code_length" id="code_length" class="form-control" min="4" max="8" value="{{ old('code_length', $settings['code_length'] ?? 6) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ttl_seconds">صلاحية الرمز (ثانية)</label>
                            <input type="number" name="ttl_seconds" id="ttl_seconds" class="form-control" value="{{ old('ttl_seconds', $settings['ttl_seconds'] ?? 300) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="max_attempts">أقصى محاولات</label>
                            <input type="number" name="max_attempts" id="max_attempts" class="form-control" value="{{ old('max_attempts', $settings['max_attempts'] ?? 5) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="resend_cooldown_seconds">Cooldown إعادة الإرسال</label>
                            <input type="number" name="resend_cooldown_seconds" id="resend_cooldown_seconds" class="form-control" value="{{ old('resend_cooldown_seconds', $settings['resend_cooldown_seconds'] ?? 60) }}">
                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="fw-semibold mb-2">تفعيل حسب السيناريو</p>
                    <div class="row g-2">
                        @foreach([
                            'register_enabled' => 'التسجيل',
                            'login_enabled' => 'تسجيل الدخول',
                            'reset_password_enabled' => 'استعادة كلمة المرور',
                            'change_phone_enabled' => 'تغيير الرقم',
                        ] as $key => $label)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" id="{{ $key }}" @checked(old($key, $settings[$key] ?? false))>
                                    <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary mt-4"><i class="fe fe-save me-1"></i> حفظ</button>
                </form>

                <form method="POST" action="{{ route('admin.settings.phone-otp.restore-defaults') }}" class="mt-2" onsubmit="return confirm('استعادة الافتراضي؟');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">استعادة الافتراضي</button>
                </form>
            </div>
        </div>

        <div class="card custom-card border-0 shadow-sm">
            <div class="card-header bg-transparent"><h5 class="mb-0">اختبار إرسال OTP</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.phone-otp.test-send') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">رمز الدولة</label>
                        <input type="text" name="test_country_code" class="form-control" placeholder="966" value="{{ old('test_country_code') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">رقم الاختبار</label>
                        <input type="text" name="test_phone" class="form-control @error('test_phone') is-invalid @enderror" value="{{ old('test_phone') }}" required>
                        @error('test_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success">إرسال اختبار</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
