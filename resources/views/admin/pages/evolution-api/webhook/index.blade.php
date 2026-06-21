@extends('admin.pages.evolution-api.layout')

@php
    $evoPageTitle = 'Webhook Evolution';
    $evoTitle = 'Webhook';
    $evoSubtitle = 'ربط Evolution API بمنصة LMS لاستقبال الرسائل والأحداث';
    $evoBreadcrumb = 'Webhook';
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
                <div class="mb-3">
                    <label class="form-label text-muted small">URL</label>
                    <code class="d-block small text-break bg-light rounded p-2 border">{{ $webhookUrl }}</code>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small">Events</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($events as $ev)
                            <span class="badge bg-light text-dark border">{{ $ev }}</span>
                        @endforeach
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.evolution-api.webhook.activate') }}">
                    @csrf
                    <button class="btn btn-success w-100" @if(!$instance) disabled @endif>
                        <i class="ri-play-circle-line me-1"></i> تفعيل Webhook تلقائياً
                    </button>
                </form>
                @if($isLocalWebhookUrl ?? false)
                    <div class="alert alert-danger small mt-3 mb-0">
                        <i class="ri-error-warning-line me-1"></i>
                        <strong>سبب شائع لعدم الرد التلقائي:</strong> الرابط يشير إلى <code>localhost</code>.
                        خادم Evolution (غالباً على سيرفر/دوcker) <strong>لا يستطيع</strong> الوصول إلى جهازك المحلي.
                        <ul class="mb-0 mt-2">
                            <li>على التطوير: استخدم <strong>ngrok</strong> أو Cloudflare Tunnel وحدّث <code>APP_URL</code> في <code>.env</code></li>
                            <li>ثم اضغط «تفعيل Webhook تلقائياً» مرة أخرى</li>
                            <li>شغّل <code>php artisan queue:work</code> باستمرار</li>
                        </ul>
                    </div>
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
