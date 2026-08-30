{{-- تبويب: عام — التفعيل واختيار المزود ووجهة تقارير الدراسة --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">تفعيل WhatsApp <span class="text-danger">*</span></label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="whatsapp_enabled"
                   id="whatsapp_enabled"
                   value="1"
                   {{ ($settings['whatsapp_enabled'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="whatsapp_enabled">
                تفعيل خدمة WhatsApp
            </label>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">المرسل الافتراضي للرسائل (المزود) <span class="text-danger">*</span></label>
        {{-- بلا onchange: المعالج مسجَّل عبر addEventListener في @section('scripts') --}}
        <select class="form-select" name="whatsapp_provider" id="whatsapp_provider" required>
            <option value="evolution" {{ $activeProvider === 'evolution' ? 'selected' : '' }}>Evolution API</option>
            <option value="whatsapp_web" {{ $activeProvider === 'whatsapp_web' ? 'selected' : '' }}>WhatsApp Web (QR Code)</option>
            <option value="custom_api" {{ $activeProvider === 'custom_api' ? 'selected' : '' }}>Custom API</option>
        </select>
        <small class="text-muted d-block mt-1">
            يُستخدم هذا المزود لإرسال جميع رسائل واتساب (ترحيب، إشعارات، إرسال جماعي، إلخ).
            إعداداته التفصيلية في تبويب <strong>المزود</strong>.
        </small>
        @error('whatsapp_provider')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <hr class="my-2">
        <label class="form-label fw-semibold">إشعارات تقارير الدراسة (AI)</label>
        <select name="study_report_delivery" class="form-select" style="max-width: 28rem;">
            <option value="both" {{ ($settings['study_report_delivery'] ?? 'both') === 'both' ? 'selected' : '' }}>البريد وواتساب معاً (افتراضي)</option>
            <option value="email" {{ ($settings['study_report_delivery'] ?? '') === 'email' ? 'selected' : '' }}>البريد الإلكتروني فقط</option>
            <option value="whatsapp" {{ ($settings['study_report_delivery'] ?? '') === 'whatsapp' ? 'selected' : '' }}>واتساب فقط — يُضاف البريد تلقائياً إن لم يتوفر رقم للطالب</option>
        </select>
        <small class="text-muted d-block mt-1">تُرسل عند إصدار تقرير دراسة جديد للطالب (قاعدة الإشعارات + البريد و/أو واتساب حسب الاختيار).</small>
        <div class="alert alert-info small mt-2 mb-0">
            <strong>Evolution API</strong> هو المزود الموصى به حالياً. للنص الحر والإرسال الجماعي استخدم Evolution أو WhatsApp Web.
            <br><strong>رقم الطالب:</strong> يُشتق من كود الدولة والهاتف في الملف الشخصي. <strong>عامل الطابور</strong> مطلوب لتسليم رسائل واتساب.
        </div>
    </div>
</div>
