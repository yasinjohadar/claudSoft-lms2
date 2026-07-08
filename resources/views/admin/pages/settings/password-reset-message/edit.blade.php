@extends('admin.layouts.master')

@section('page-title')
    رسالة إعادة تعيين كلمة المرور
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4 page-header-breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.email.index') }}">الإعدادات</a></li>
                    <li class="breadcrumb-item active">رسالة استعادة كلمة المرور</li>
                </ol>
            </nav>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card custom-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h4 class="card-title mb-1">تخصيص رسالة بيانات الدخول</h4>
                <p class="text-muted fs-12 mb-0">
                    عدّل نصوص البريد والواتساب المرسلة عند إعادة تعيين كلمة المرور. تُرسل الرسائل على القناتين معاً وتتضمن بيانات الدخول الكاملة لكل طالب.
                </p>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-4">
                    <strong>المتغيرات المتاحة:</strong>
                    @foreach($placeholders as $placeholder)
                        <code class="me-1">{ {{ $placeholder }} }</code>
                    @endforeach
                    <div class="small text-muted mt-2">
                        ضع كل بيانات الدخول داخل القالب أدناه — لن يُلحق أي نص تلقائياً بالرسالة.
                        استخدم <code>{password}</code> و<code>{login_url}</code> و<code>{admin_instructions}</code> حسب تنسيقك.
                        <code>{reset_url}</code> و<code>{reset_link}</code> يعادلان <code>{login_url}</code> للتوافق مع قوالب قديمة فقط.
                    </div>
                </div>

                @if($usesLegacyLinkTemplate)
                    <div class="alert alert-warning border-warning mb-4">
                        <strong>قالب قديم (رابط مؤقت):</strong>
                        يبدو أن نص الواتساب ما زال مبنياً على رابط إعادة التعيين وصلاحية زمنية.
                        النظام يُعيّن كلمة المرور فوراً ولا يوجد رابط ينتهي — حدّث القالب ليشمل
                        <code>{password}</code> و<code>{login_url}</code> أو اضغط «استعادة الافتراضي».
                    </div>
                @endif

                <div class="alert alert-info border-info mb-4">
                    <strong>مثال جاهز لنص الواتساب:</strong>
                    <pre class="mb-0 mt-2 small" dir="rtl" style="white-space: pre-wrap;">{{ \App\Services\Auth\PasswordResetMessageRenderer::defaultWhatsAppBody() }}</pre>
                </div>

                <form method="POST" action="{{ route('admin.settings.password-reset-message.update') }}" id="passwordResetMessageForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label" for="admin_instructions">إرشادات الإدارة</label>
                            <textarea name="admin_instructions" id="admin_instructions" class="form-control" rows="4">{{ old('admin_instructions', $settings['admin_instructions'] ?? '') }}</textarea>
                            <small class="text-muted">يُعرض في الرسالة عبر المتغير <code>{admin_instructions}</code> — مثل: تغيير كلمة المرور بعد أول دخول.</small>
                            @error('admin_instructions')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr></div>
                        <div class="col-12">
                            <h5 class="fw-semibold mb-3"><i class="ri-whatsapp-line text-success me-1"></i> رسالة الواتساب</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="whatsapp_template_id">قالب واتساب (اختياري)</label>
                            <select name="whatsapp_template_id" id="whatsapp_template_id" class="form-select">
                                <option value="">— نص مخصص أدناه —</option>
                                @foreach($whatsappTemplates as $template)
                                    <option value="{{ $template->id }}" @selected(old('whatsapp_template_id', $settings['whatsapp_template_id'] ?? '') == $template->id)>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">إذا اخترت قالباً يُستخدم بدلاً من النص المخصص.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="whatsapp_body">نص الواتساب المخصص</label>
                            <textarea name="whatsapp_body" id="whatsapp_body" class="form-control" rows="8">{{ old('whatsapp_body', $settings['whatsapp_body'] ?? '') }}</textarea>
                            @error('whatsapp_body')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr></div>

                        <div class="col-12">
                            <h5 class="fw-semibold mb-3"><i class="fe fe-mail text-primary me-1"></i> رسالة البريد الإلكتروني</h5>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="email_subject">موضوع البريد <span class="text-danger">*</span></label>
                            <input type="text" name="email_subject" id="email_subject" class="form-control @error('email_subject') is-invalid @enderror"
                                   value="{{ old('email_subject', $settings['email_subject'] ?? '') }}" required>
                            @error('email_subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email_body">محتوى البريد (HTML)</label>
                            <textarea name="email_body" id="email_body" class="form-control" rows="12">{{ old('email_body', $settings['email_body'] ?? '') }}</textarea>
                            @error('email_body')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-1"></i> حفظ
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.settings.password-reset-message.restore-defaults') }}" class="mt-3"
                      onsubmit="return confirm('استعادة النصوص الافتراضية؟');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fe fe-rotate-cw me-1"></i> استعادة الافتراضي
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var commonConfig = {
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
        promotion: false,
        branding: false,
        menubar: false,
        statusbar: true,
        plugins: 'lists link directionality wordcount emoticons',
        toolbar: 'undo redo | bold italic underline strikethrough | forecolor | alignright aligncenter alignleft | bullist numlist | emoticons | ltr rtl | removeformat',
        content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; line-height: 1.6; }',
        entity_encoding: 'raw',
    };

    if (typeof tinymce !== 'undefined') {
        tinymce.init(Object.assign({}, commonConfig, {
            selector: '#whatsapp_body',
            height: 260,
        }));
        tinymce.init(Object.assign({}, commonConfig, {
            selector: '#email_body',
            height: 360,
            plugins: 'lists link directionality wordcount emoticons code',
            toolbar: 'undo redo | bold italic underline strikethrough | forecolor | alignright aligncenter alignleft | bullist numlist | link | emoticons | code | removeformat',
        }));
    }

    document.getElementById('passwordResetMessageForm')?.addEventListener('submit', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
    });
});
</script>
@stop
