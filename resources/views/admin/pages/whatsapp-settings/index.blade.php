@extends('admin.layouts.master')

@section('page-title', 'إعدادات WhatsApp')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="page-title fw-semibold fs-18 mb-0">إعدادات WhatsApp</h4>
                <p class="fw-normal text-muted fs-14 mb-0">إدارة إعدادات تكامل WhatsApp</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Settings Form -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-whatsapp-line me-2"></i>إعدادات WhatsApp
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.whatsapp-settings.update') }}" method="POST" id="whatsapp-settings-form">
                            @csrf
                            @method('POST')

                            <!-- General Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-settings-3-line me-2"></i>الإعدادات العامة
                                    </h5>
                                </div>
                                <div class="card-body">
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
                                            <label class="form-label">المزود <span class="text-danger">*</span></label>
                                            <select class="form-select" name="whatsapp_provider" id="whatsapp_provider" required>
                                                <option value="meta" {{ ($settings['whatsapp_provider'] ?? 'meta') == 'meta' ? 'selected' : '' }}>Meta (WhatsApp Cloud API)</option>
                                                <option value="custom_api" {{ ($settings['whatsapp_provider'] ?? '') == 'custom_api' ? 'selected' : '' }}>Custom API</option>
                                            </select>
                                            @error('whatsapp_provider')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta Provider Settings -->
                            <div class="card border mb-4" id="meta-settings">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-facebook-box-line me-2"></i>إعدادات Meta (WhatsApp Cloud API)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">إصدار API <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="api_version" 
                                                   id="api_version"
                                                   value="{{ old('api_version', $settings['api_version'] ?? 'v20.0') }}"
                                                   placeholder="v20.0"
                                                   required>
                                            @error('api_version')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="phone_number_id" 
                                                   id="phone_number_id"
                                                   value="{{ old('phone_number_id', $settings['phone_number_id'] ?? '') }}"
                                                   placeholder="رقم معرف رقم الهاتف">
                                            @error('phone_number_id')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">WABA ID</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="waba_id" 
                                                   id="waba_id"
                                                   value="{{ old('waba_id', $settings['waba_id'] ?? '') }}"
                                                   placeholder="معرف WhatsApp Business Account">
                                            @error('waba_id')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Access Token</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   name="access_token" 
                                                   id="access_token"
                                                   value=""
                                                   placeholder="اتركه فارغاً للحفاظ على القيمة الحالية">
                                            <small class="text-muted">اتركه فارغاً إذا كنت لا تريد تغييره</small>
                                            @error('access_token')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Verify Token <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="verify_token" 
                                                   id="verify_token"
                                                   value="{{ old('verify_token', $settings['verify_token'] ?? '') }}"
                                                   placeholder="رمز التحقق للـ Webhook"
                                                   required>
                                            @error('verify_token')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">App Secret</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   name="app_secret" 
                                                   id="app_secret"
                                                   value=""
                                                   placeholder="اتركه فارغاً للحفاظ على القيمة الحالية">
                                            <small class="text-muted">للتوقيع الرقمي للـ Webhook</small>
                                            @error('app_secret')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom API Settings -->
                            <div class="card border mb-4" id="custom-api-settings" style="display: none;">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-code-s-slash-line me-2"></i>إعدادات Custom API
                                    </h5>
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
                                    </div>
                                </div>
                            </div>

                            <!-- Webhook Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-webhook-line me-2"></i>إعدادات Webhook
                                    </h5>
                                </div>
                                <div class="card-body">
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
                                    </div>
                                </div>
                            </div>

                            <!-- Auto Reply Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-reply-line me-2"></i>إعدادات الرد التلقائي
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="auto_reply" 
                                                       id="auto_reply"
                                                       value="1"
                                                       {{ ($settings['auto_reply'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="auto_reply">
                                                    <strong>تفعيل الرد التلقائي</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">رسالة الرد التلقائي</label>
                                            <textarea class="form-control" 
                                                      name="auto_reply_message" 
                                                      id="auto_reply_message"
                                                      rows="3"
                                                      placeholder="شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.">{{ old('auto_reply_message', $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.') }}</textarea>
                                            @error('auto_reply_message')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Settings -->
                            <div class="card border mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="ri-settings-4-line me-2"></i>إعدادات متقدمة
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Timeout (بالثواني)</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="timeout" 
                                                   id="timeout"
                                                   value="{{ old('timeout', $settings['timeout'] ?? 30) }}"
                                                   min="1"
                                                   max="300"
                                                   placeholder="30">
                                            <small class="text-muted">المهلة الزمنية لانتظار استجابة API</small>
                                            @error('timeout')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary" id="test-connection-btn">
                                    <i class="ri-plug-line me-1"></i>اختبار الاتصال
                                </button>
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="ri-save-line me-1"></i>حفظ الإعدادات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختبار الاتصال</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="test-connection-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('whatsapp_provider');
    const metaSettings = document.getElementById('meta-settings');
    const customApiSettings = document.getElementById('custom-api-settings');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const testConnectionModal = new bootstrap.Modal(document.getElementById('testConnectionModal'));

    // Toggle provider settings
    function toggleProviderSettings() {
        const provider = providerSelect.value;
        if (provider === 'meta') {
            metaSettings.style.display = 'block';
            customApiSettings.style.display = 'none';
            // Make Meta fields required
            document.getElementById('api_version').required = true;
            document.getElementById('phone_number_id').required = true;
            document.getElementById('verify_token').required = true;
            document.getElementById('custom_api_url').required = false;
        } else if (provider === 'custom_api') {
            metaSettings.style.display = 'none';
            customApiSettings.style.display = 'block';
            // Make Custom API fields required
            document.getElementById('api_version').required = false;
            document.getElementById('phone_number_id').required = false;
            document.getElementById('verify_token').required = false;
            document.getElementById('custom_api_url').required = true;
        }
    }

    providerSelect.addEventListener('change', toggleProviderSettings);
    toggleProviderSettings(); // Initial call

    // Test connection
    testConnectionBtn.addEventListener('click', function() {
        const form = document.getElementById('whatsapp-settings-form');
        const formData = new FormData(form);
        formData.append('_token', '{{ csrf_token() }}');

        // Show loading
        document.getElementById('test-connection-result').innerHTML = 
            '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري الاختبار...</span></div><p class="mt-2">جاري اختبار الاتصال...</p></div>';
        testConnectionModal.show();

        fetch('{{ route("admin.whatsapp-settings.test-connection") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('test-connection-result').innerHTML = 
                    '<div class="alert alert-success"><i class="ri-check-line me-2"></i>' + (data.message || 'تم الاتصال بنجاح!') + '</div>';
            } else {
                document.getElementById('test-connection-result').innerHTML = 
                    '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>' + (data.message || 'فشل الاتصال') + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('test-connection-result').innerHTML = 
                '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>حدث خطأ أثناء الاختبار: ' + error.message + '</div>';
        });
    });
});
</script>
@endpush

