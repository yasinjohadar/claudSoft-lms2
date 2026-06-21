@extends('admin.layouts.master')

@section('page-title')
    إشعار الدفع — واتساب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4 page-header-breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">المدفوعات</a></li>
                    <li class="breadcrumb-item active">إشعار الدفع — واتساب</li>
                </ol>
            </nav>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card custom-card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h4 class="card-title mb-1">رسالة واتساب عند تسجيل دفعة</h4>
                <p class="text-muted fs-12 mb-0">يُرسل تلقائياً للطالب على رقمه المسجّل عند إتمام دفعة (تسجيل يدوي أو اعتماد طلب دفع).</p>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-4">
                    <strong>المتغيرات:</strong>
                    @foreach($placeholders as $placeholder)
                        <code class="me-1">{ {{ $placeholder }} }</code>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.settings.payment-whatsapp-message.update') }}" id="paymentWhatsAppMessageForm">
                    @csrf
                    @method('PUT')

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="paymentWaEnabled"
                               @checked(old('enabled', $settings['enabled'] ?? true))>
                        <label class="form-check-label fw-semibold" for="paymentWaEnabled">تفعيل الإرسال التلقائي</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="whatsapp_template_id">قالب واتساب (اختياري)</label>
                        <select name="whatsapp_template_id" id="whatsapp_template_id" class="form-select">
                            <option value="">— نص مخصص أدناه —</option>
                            @foreach($whatsappTemplates as $template)
                                <option value="{{ $template->id }}" @selected(old('whatsapp_template_id', $settings['whatsapp_template_id'] ?? '') == $template->id)>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="whatsapp_body">نص الرسالة</label>
                        <textarea name="whatsapp_body" id="whatsapp_body" class="form-control" rows="12">{{ old('whatsapp_body', $settings['whatsapp_body'] ?? '') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> حفظ</button>
                </form>

                <form method="POST" action="{{ route('admin.settings.payment-whatsapp-message.restore-defaults') }}" class="mt-3"
                      onsubmit="return confirm('استعادة النص الافتراضي؟');">
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
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#whatsapp_body',
            height: 320,
            directionality: 'rtl',
            language: 'ar',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
            promotion: false,
            branding: false,
            menubar: false,
            plugins: 'lists link directionality wordcount emoticons',
            toolbar: 'undo redo | bold italic underline | forecolor | alignright aligncenter alignleft | bullist numlist | emoticons | removeformat',
            content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; line-height: 1.6; }',
            entity_encoding: 'raw',
        });
    }
    document.getElementById('paymentWhatsAppMessageForm')?.addEventListener('submit', function () {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
    });
});
</script>
@stop
