{{-- تبويب: Webhook — مسار الاستقبال والتحقق من التوقيع (مسار Meta القديم) --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Webhook Path</label>
        <input type="text"
               class="form-control"
               name="webhook_path"
               id="webhook_path"
               value="{{ old('webhook_path', $settings['webhook_path'] ?? '/api/webhooks/whatsapp') }}"
               placeholder="/api/webhooks/whatsapp">
        <small class="text-muted">مسار Webhook في تطبيقك</small>
        @error('webhook_path')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Default From</label>
        <input type="text"
               class="form-control"
               name="default_from"
               id="default_from"
               value="{{ old('default_from', $settings['default_from'] ?? '') }}"
               placeholder="رقم الهاتف الافتراضي">
    </div>

    <div class="col-md-12 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="strict_signature"
                   id="strict_signature"
                   value="1"
                   {{ ($settings['strict_signature'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="strict_signature">
                <strong>تفعيل التحقق الصارم من التوقيع الرقمي</strong>
            </label>
        </div>
        <small class="text-muted">يُنصح بتركه مفعّل للأمان</small>
    </div>

    <div class="col-md-12 mb-3">
        <div class="alert alert-info mb-0">
            <i class="ri-information-line me-2"></i>
            <strong>Webhook URL:</strong>
            <code>{{ url($settings['webhook_path'] ?? '/api/webhooks/whatsapp') }}</code>
            <br>
            استخدم هذا الرابط عند إعداد Webhook في Meta Developer Console
        </div>
    </div>

    @if($activeProvider === 'evolution')
        <div class="col-md-12">
            <div class="alert alert-warning small mb-0">
                <i class="ri-alert-line me-1"></i>
                <strong>مزودك الحالي Evolution</strong> — وله نقطة استقبال ورابط مختلفان تماماً عن الحقول أعلاه
                (هذه خاصة بمزوّد Meta). اضبطه من
                <a href="{{ route('admin.evolution-api.webhook.index') }}">لوحة Evolution → Webhook</a>،
                وهناك أيضاً زر <strong>تشخيص مسار الرد التلقائي</strong>.
            </div>
        </div>
    @endif
</div>
