<div class="card shadow-sm border-0 mb-4">
    <div class="card-header">عامل الطابور (Queue Worker)</div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            <strong>لماذا التشغيل؟</strong> جدولة «تقارير الدراسة» للمجموعات تضع المهام في طابور Laravel؛ لا يُنفَّذ توليد الـ AI ولا الإشعار/البريد حتى يعمل عامل الطابور (<code>queue:work</code>) على هذا السيرفر أو الجهاز.
        </p>
        <p class="text-muted small mb-3">يُستخدم نفس عامل الطابور كإعدادات واتساب؛ التشغيل/الإيقاف من هنا يؤثر على كل مهام الطابور.</p>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span id="sp-queue-worker-status-badge" class="badge bg-secondary fs-6">—</span>
                <span id="sp-queue-worker-pid" class="text-muted small"></span>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success btn-sm" id="sp-queue-worker-start-btn">
                    <i class="ri-play-line me-1"></i>تشغيل
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="sp-queue-worker-stop-btn" disabled>
                    <i class="ri-stop-line me-1"></i>إيقاف
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="sp-queue-worker-refresh-btn">
                <i class="ri-refresh-line me-1"></i>تحديث
            </button>
        </div>
        <div id="sp-queue-worker-message" class="mt-2 small text-muted"></div>
    </div>
</div>

@once
    @push('scripts')
    <script>
    (function() {
        const statusBadge = document.getElementById('sp-queue-worker-status-badge');
        const pidEl = document.getElementById('sp-queue-worker-pid');
        const startBtn = document.getElementById('sp-queue-worker-start-btn');
        const stopBtn = document.getElementById('sp-queue-worker-stop-btn');
        const refreshBtn = document.getElementById('sp-queue-worker-refresh-btn');
        const messageEl = document.getElementById('sp-queue-worker-message');
        if (!statusBadge || !startBtn) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
        const statusUrl = @json(route('admin.whatsapp-settings.queue-worker.status'));
        const startUrl = @json(route('admin.whatsapp-settings.queue-worker.start'));
        const stopUrl = @json(route('admin.whatsapp-settings.queue-worker.stop'));

        function setRunning(running, pid) {
            statusBadge.textContent = running ? 'يعمل' : 'متوقف';
            statusBadge.className = 'badge fs-6 ' + (running ? 'bg-success' : 'bg-secondary');
            if (pidEl) pidEl.textContent = pid ? '(PID: ' + pid + ')' : '';
            startBtn.disabled = !!running;
            if (stopBtn) stopBtn.disabled = !running;
        }

        function showMessage(msg, isError) {
            if (!messageEl) return;
            messageEl.textContent = msg || '';
            messageEl.className = 'mt-2 small ' + (isError ? 'text-danger' : 'text-muted');
        }

        function fetchStatus() {
            fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    setRunning(!!data.running, data.pid || null);
                    showMessage(data.message || '');
                })
                .catch(function() { showMessage('فشل جلب الحالة.', true); });
        }

        startBtn.addEventListener('click', function() {
            startBtn.disabled = true;
            showMessage('جاري التشغيل...');
            fetch(startUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(function(data) {
                showMessage(data.message || '', !data.success);
                if (data.success) setRunning(true, data.pid);
                else startBtn.disabled = false;
                if (stopBtn) stopBtn.disabled = !data.success;
            })
            .catch(function() {
                showMessage('حدث خطأ أثناء التشغيل.', true);
                startBtn.disabled = false;
            });
        });

        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                stopBtn.disabled = true;
                showMessage('جاري الإيقاف...');
                fetch(stopUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(r => r.json())
                .then(function(data) {
                    showMessage(data.message || '');
                    setRunning(false, null);
                    startBtn.disabled = false;
                })
                .catch(function() {
                    showMessage('حدث خطأ أثناء الإيقاف.', true);
                    stopBtn.disabled = false;
                });
            });
        }

        if (refreshBtn) refreshBtn.addEventListener('click', fetchStatus);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fetchStatus);
        } else {
            fetchStatus();
        }
        setInterval(fetchStatus, 15000);
    })();
    </script>
    @endpush
@endonce
