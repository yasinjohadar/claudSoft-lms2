@extends('admin.pages.evolution-api.layout')

@php
    $evoPageTitle = 'Webhook Evolution';
    $evoTitle = 'Webhook';
    $evoSubtitle = 'ربط Evolution API بمنصة LMS لاستقبال الرسائل والأحداث';
    $evoBreadcrumb = 'Webhook';
    $webhookPathSuffix = $instance ? '/api/webhooks/evolution/' . urlencode($instance) : '/api/webhooks/evolution/{instance}';
@endphp

@section('evo-content')
@if($error)
    <div class="alert alert-warning border-0 shadow-sm mb-3"><i class="ri-alert-line me-2"></i>{{ $error }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card custom-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent">
                <div class="card-title mb-0"><i class="ri-webhook-line me-2 text-success"></i>تفعيل Webhook</div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Instance</label>
                    <div class="fw-semibold">{{ $instance ?: '—' }}</div>
                </div>

                <form method="POST" action="{{ route('admin.evolution-api.webhook.save-url') }}" class="mb-4">
                    @csrf
                    <label class="form-label fw-semibold" for="evolution_webhook_base_url">
                        رابط المنصة العام (Webhook)
                    </label>
                    <input type="url"
                           name="evolution_webhook_base_url"
                           id="evolution_webhook_base_url"
                           class="form-control @error('evolution_webhook_base_url') is-invalid @enderror"
                           value="{{ old('evolution_webhook_base_url', $webhookBaseUrl ?? '') }}"
                           placeholder="{{ $appUrl }}"
                           dir="ltr">
                    <small class="text-muted d-block mt-2">
                        أدخل الرابط العام الذي يصل إليه Evolution (الإنتاج، ngrok، Cloudflare Tunnel…).
                        اتركه فارغاً لاستخدام <code>APP_URL</code> الحالي: <code>{{ $appUrl }}</code>
                    </small>
                    @error('evolution_webhook_base_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="mt-3">
                        <label class="form-label text-muted small">معاينة رابط Webhook الكامل</label>
                        <code id="webhookUrlPreview" class="d-block small text-break bg-light rounded p-2 border">{{ $webhookUrl }}</code>
                    </div>
                    <button type="submit" class="btn btn-outline-primary mt-3">
                        <i class="ri-save-line me-1"></i> حفظ الرابط
                    </button>
                </form>

                <div class="mb-4">
                    <label class="form-label text-muted small">Events</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($events as $ev)
                            <span class="badge bg-light text-dark border">{{ $ev }}</span>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.evolution-api.webhook.activate') }}" id="webhookActivateForm">
                    @csrf
                    <input type="hidden" name="evolution_webhook_base_url" id="evolution_webhook_base_url_activate" value="">
                    @if(!empty($autoReplyInstance) && $autoReplyInstance !== $instance)
                        <input type="hidden" name="instance" id="webhookActivateInstance" value="{{ $instance }}">
                    @endif
                    <button class="btn btn-success w-100" @if(!$instance) disabled @endif>
                        <i class="ri-play-circle-line me-1"></i> تفعيل Webhook تلقائياً
                    </button>
                </form>

                @if(!empty($autoReplyInstance) && $autoReplyInstance !== $instance)
                    <div class="alert alert-warning small mt-3 mb-0">
                        <i class="ri-alert-line me-1"></i>
                        <strong>Instance الرد التلقائي مختلف عن النشط.</strong>
                        الرد التلقائي يستمع لـ «{{ $autoReplyInstance }}» بينما النشط «{{ $instance }}».
                        فعّل الـ Webhook لـ instance الرد التلقائي أيضاً، وإلا لن تصله أي رسالة.
                        <form method="POST" action="{{ route('admin.evolution-api.webhook.activate') }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="instance" value="{{ $autoReplyInstance }}">
                            <button class="btn btn-sm btn-warning">
                                <i class="ri-play-circle-line me-1"></i> تفعيل Webhook لـ «{{ $autoReplyInstance }}»
                            </button>
                        </form>
                    </div>
                @endif

                {{-- تشخيص المسار: يكشف أعطال التوجيه والإعداد الصامتة في طلب واحد --}}
                <button type="button" class="btn btn-outline-primary w-100 mt-3" id="btnDiagnoseWebhook">
                    <i class="ri-stethoscope-line me-1"></i> تشخيص مسار الرد التلقائي
                </button>
                <div id="diagnoseResult" class="mt-3 d-none"></div>

                @if($isLocalWebhookUrl ?? false)
                    <div class="alert alert-danger small mt-3 mb-0">
                        <i class="ri-error-warning-line me-1"></i>
                        <strong>الرابط الحالي يشير إلى localhost</strong> — خادم Evolution لا يستطيع الوصول إليه.
                        <ul class="mb-0 mt-2">
                            <li>ضع الرابط العام في الحقل أعلاه (مثل <code>https://lms.yourdomain.com</code> أو رابط ngrok)</li>
                            <li>اضغط «حفظ الرابط» ثم «تفعيل Webhook تلقائياً»</li>
                            <li>شغّل <code>php artisan queue:work</code> باستمرار</li>
                        </ul>
                    </div>
                @elseif($usesCustomWebhookBaseUrl ?? false)
                    <p class="text-success small mt-3 mb-0">
                        <i class="ri-check-line"></i> يُستخدم رابط مخصّص للـ Webhook (ليس APP_URL المحلي).
                    </p>
                @endif

                @if(($webhookEventsCount ?? 0) === 0)
                    <p class="text-muted small mt-2 mb-0">لم يُستقبل أي Webhook بعد في قاعدة البيانات — تأكد أن Evolution يصل فعلاً إلى Laravel.</p>
                @else
                    <p class="text-success small mt-2 mb-0"><i class="ri-check-line"></i> تم استقبال {{ $webhookEventsCount }} حدث Webhook.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card custom-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent">
                <div class="card-title mb-0"><i class="ri-code-box-line me-2"></i>الإعداد الحالي على Evolution</div>
            </div>
            <div class="card-body">
                @if($webhook)
                    <pre class="small mb-0 bg-light rounded p-3 border overflow-auto" style="max-height:320px">{{ json_encode($webhook, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="ri-webhook-line fs-48 opacity-25 d-block mb-2"></i>
                        لم يُفعَّل Webhook بعد
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('evo-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var baseInput = document.getElementById('evolution_webhook_base_url');
    var preview = document.getElementById('webhookUrlPreview');
    var activateHidden = document.getElementById('evolution_webhook_base_url_activate');
    var activateForm = document.getElementById('webhookActivateForm');
    var appUrl = @json($appUrl);
    var pathSuffix = @json($webhookPathSuffix);

    function effectiveBase() {
        var val = (baseInput && baseInput.value || '').trim().replace(/\/+$/, '');
        return val || appUrl;
    }

    function updatePreview() {
        if (!preview) return;
        preview.textContent = effectiveBase() + pathSuffix;
    }

    if (baseInput) {
        baseInput.addEventListener('input', updatePreview);
    }

    if (activateForm && activateHidden && baseInput) {
        activateForm.addEventListener('submit', function() {
            activateHidden.value = baseInput.value.trim();
        });
    }

    updatePreview();

    // ==== تشخيص مسار الرد التلقائي ====
    var diagBtn = document.getElementById('btnDiagnoseWebhook');
    var diagBox = document.getElementById('diagnoseResult');

    if (diagBtn && diagBox) {
        diagBtn.addEventListener('click', function () {
            var original = diagBtn.innerHTML;
            diagBtn.disabled = true;
            diagBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جارٍ الفحص...';
            diagBox.classList.remove('d-none');
            diagBox.innerHTML = '<div class="text-muted small">يُجري فحصاً حقيقياً للرابط من الخادم — قد يستغرق ثوانٍ.</div>';

            fetch(@json(route('admin.evolution-api.webhook.diagnose')), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var icons = { ok: 'ri-checkbox-circle-fill text-success',
                              fail: 'ri-close-circle-fill text-danger',
                              warning: 'ri-error-warning-fill text-warning' };
                var html = '';

                if (data.failed_count > 0) {
                    html += '<div class="alert alert-danger small py-2 mb-2">'
                          + '<i class="ri-error-warning-line me-1"></i>'
                          + '<strong>' + data.failed_count + '</strong> عطل يمنع وصول الرسائل.</div>';
                } else {
                    html += '<div class="alert alert-success small py-2 mb-2">'
                          + '<i class="ri-check-line me-1"></i> كل الفحوص سليمة.</div>';
                }

                html += '<ul class="list-group list-group-flush small border rounded">';
                (data.steps || []).forEach(function (s) {
                    html += '<li class="list-group-item py-2">'
                          + '<i class="' + (icons[s.status] || icons.warning) + ' me-2"></i>'
                          + '<span class="fw-semibold">' + s.label + '</span>';
                    if (s.detail) {
                        html += '<div class="text-muted mt-1" style="word-break:break-all">' + s.detail + '</div>';
                    }
                    if (s.hint) {
                        html += '<div class="text-danger mt-1"><i class="ri-arrow-right-s-line"></i>' + s.hint + '</div>';
                    }
                    html += '</li>';
                });
                html += '</ul>';

                diagBox.innerHTML = html;
            })
            .catch(function (e) {
                diagBox.innerHTML = '<div class="alert alert-danger small mb-0">تعذّر إجراء الفحص: ' + e.message + '</div>';
            })
            .finally(function () {
                diagBtn.disabled = false;
                diagBtn.innerHTML = original;
            });
        });
    }
});
</script>
@endsection
