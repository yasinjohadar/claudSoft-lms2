{{-- تبويب: عامل الطابور — المعرّفات مستخدَمة في IIFE ضمن @section('scripts') --}}
<p class="text-muted small mb-3">مطلوب للرد التلقائي والإرسال الجماعي. يجب أن يعمل على نفس السيرفر الذي يشغّل Laravel.</p>

<div class="alert alert-info small mb-3">
    <div><strong>الطابور المستهدف:</strong> <code>{{ config('whatsapp.queue', 'whatsapp') }}</code>
        — يجب أن يستمع له العامل، وإلا تراكمت وظائف واتساب بلا تنفيذ.</div>
    <div class="mt-1"><strong>اتصال الطابور:</strong> <code>{{ config('queue.default') }}</code></div>
    <div class="mt-2">
        <strong>على Linux أونلاين:</strong> زر التشغيل أدناه قد يتوقف بعد تحديث الصفحة.
        للتشغيل الدائم استخدم <strong>Supervisor</strong> بالأمر:
        <code dir="ltr">queue:work --queue={{ config('whatsapp.queue', 'whatsapp') }},default</code>
    </div>
    <div class="mt-1 text-danger">
        <i class="ri-alert-line me-1"></i>
        بعد كل نشر نفّذ <code>php artisan queue:restart</code> — العمال يبقون على الكود القديم في الذاكرة.
    </div>
</div>

<div class="d-flex flex-wrap align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
        <span id="queue-worker-status-badge" class="badge {{ ($queueWorkerStatus['running'] ?? false) ? 'bg-success' : 'bg-secondary' }} fs-6">
            {{ ($queueWorkerStatus['running'] ?? false) ? 'يعمل' : 'متوقف' }}
        </span>
        @if(!empty($queueWorkerStatus['pid'] ?? null))
            <span class="text-muted small">(PID: {{ $queueWorkerStatus['pid'] }})</span>
        @endif
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-success" id="queue-worker-start-btn" {{ ($queueWorkerStatus['running'] ?? false) ? 'disabled' : '' }}>
            <i class="ri-play-line me-1"></i>تشغيل
        </button>
        <button type="button" class="btn btn-danger" id="queue-worker-stop-btn" {{ ($queueWorkerStatus['running'] ?? false) ? '' : 'disabled' }}>
            <i class="ri-stop-line me-1"></i>إيقاف
        </button>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="queue-worker-refresh-btn">
        <i class="ri-refresh-line me-1"></i>تحديث الحالة
    </button>
</div>
<div id="queue-worker-message" class="mt-2 small text-muted"></div>
