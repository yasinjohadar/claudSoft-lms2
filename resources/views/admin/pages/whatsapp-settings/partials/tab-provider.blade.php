{{--
    تبويب: المزود — البطاقات الثلاث متبادلة الظهور حسب اختيار المزود في تبويب «عام».
    تبديل الظهور يتم في toggleProviderSettings() ضمن @section('scripts')، ويعتمد
    على المعرّفات: #evolution-hint و #custom-api-settings و #whatsapp-web-settings.
--}}

<div class="mb-3">
    <span class="text-muted small">المزود المختار حالياً:</span>
    <strong id="provider-current-label">
        @switch($activeProvider)
            @case('evolution') Evolution API @break
            @case('whatsapp_web') WhatsApp Web (QR Code) @break
            @case('custom_api') Custom API @break
            @default {{ $activeProvider }}
        @endswitch
    </strong>
    <span class="text-muted small">— لتغييره انتقل إلى تبويب «عام».</span>
</div>

{{-- Evolution --}}
<div class="card border mb-4" id="evolution-hint" style="display: {{ $activeProvider === 'evolution' ? 'block' : 'none' }};">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="ri-plug-line me-2 text-success"></i>Evolution API</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">إعدادات Evolution API في صفحة مستقلة. بعد اختيار هذا المزود، اضبط URL و API Key و Instance من:</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.evolution-api.settings.index') }}" class="btn btn-success btn-sm">
                <i class="ri-settings-3-line me-1"></i>إعدادات Evolution API
            </a>
            <a href="{{ route('admin.evolution-api.instances.index') }}" class="btn btn-outline-success btn-sm">
                <i class="ri-smartphone-line me-1"></i>Instances
            </a>
            <a href="{{ route('admin.evolution-api.webhook.index') }}" class="btn btn-outline-success btn-sm">
                <i class="ri-webhook-line me-1"></i>Webhook والتشخيص
            </a>
        </div>
    </div>
</div>

{{-- Custom API --}}
<div class="card border mb-4" id="custom-api-settings" style="display: {{ $activeProvider === 'custom_api' ? 'block' : 'none' }};">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="ri-code-s-slash-line me-2"></i>إعدادات Custom API</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">API URL <span class="text-danger">*</span></label>
                <input type="url"
                       class="form-control"
                       name="custom_api_url"
                       id="custom_api_url"
                       value="{{ old('custom_api_url', $settings['custom_api_url'] ?? '') }}"
                       placeholder="https://api.example.com/send">
                @error('custom_api_url')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">HTTP Method</label>
                <select class="form-select" name="custom_api_method" id="custom_api_method">
                    <option value="POST" {{ ($settings['custom_api_method'] ?? 'POST') == 'POST' ? 'selected' : '' }}>POST</option>
                    <option value="GET" {{ ($settings['custom_api_method'] ?? '') == 'GET' ? 'selected' : '' }}>GET</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">API Key</label>
                <input type="password"
                       class="form-control"
                       name="custom_api_key"
                       id="custom_api_key"
                       value=""
                       placeholder="اتركه فارغاً للحفاظ على القيمة الحالية">
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Custom Headers (JSON)</label>
                <textarea class="form-control"
                          name="custom_api_headers"
                          id="custom_api_headers"
                          rows="4"
                          placeholder='{"Authorization": "Bearer token", "Content-Type": "application/json"}'>{{ old('custom_api_headers', is_array($settings['custom_api_headers'] ?? []) ? json_encode($settings['custom_api_headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($settings['custom_api_headers'] ?? '{}')) }}</textarea>
                <small class="text-muted">أدخل headers كـ JSON object</small>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox"
                           name="custom_api_preflight_enabled"
                           id="custom_api_preflight_enabled"
                           value="1"
                           {{ ($settings['custom_api_preflight_enabled'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="custom_api_preflight_enabled">
                        تفعيل فحص الرقم قبل الإرسال
                    </label>
                </div>
                <small class="text-muted">يمنع الإرسال إذا الرقم غير موجود على واتساب (عند توفر endpoint).</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Preflight Check URL</label>
                <input type="url"
                       class="form-control"
                       name="custom_api_preflight_url"
                       id="custom_api_preflight_url"
                       value="{{ old('custom_api_preflight_url', $settings['custom_api_preflight_url'] ?? '') }}"
                       placeholder="https://wasenderapi.com/api/check-number">
                @error('custom_api_preflight_url')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- WhatsApp Web --}}
<div class="card border mb-4" id="whatsapp-web-settings" style="display: {{ $activeProvider === 'whatsapp_web' ? 'block' : 'none' }};">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="ri-qr-code-line me-2"></i>WhatsApp Web</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-0">
            <i class="ri-information-line me-2"></i>
            <strong>ملاحظة:</strong> لإعداد WhatsApp Web، يرجى الانتقال إلى صفحة الإعدادات المخصصة.
            <div class="mt-2">
                <a href="{{ route('admin.whatsapp-web-settings.index') }}" class="btn btn-sm btn-primary">
                    <i class="ri-settings-3-line me-1"></i>فتح إعدادات WhatsApp Web
                </a>
                <a href="{{ route('admin.whatsapp-web.connect') }}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-qr-code-line me-1"></i>ربط WhatsApp Web
                </a>
            </div>
        </div>
    </div>
</div>
