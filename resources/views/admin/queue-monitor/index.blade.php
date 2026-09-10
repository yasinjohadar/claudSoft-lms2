@extends('admin.layouts.master')

@section('page-title')
    قائمة المهام (الطابور)
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-1">قائمة المهام (الطابور)</h4>
                    <p class="mb-0 text-muted">مراقبة المهام قيد الانتظار والمكتملة والفاشلة، وإعادة تشغيل الفاشلة منها</p>
                </div>
                <div class="btn-list mt-3 mt-md-0">
                    <a href="{{ route('admin.queue-monitor.index') }}" class="btn btn-primary btn-wave">
                        <i class="ri-refresh-line me-2"></i>تحديث البيانات
                    </a>
                </div>
            </div>

            @include('admin.components.alerts')

            {{-- حالة عامل الطابور --}}
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">حالة عامل الطابور</div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <div>اتصال الطابور الحالي: <code>{{ config('queue.default') }}</code></div>
                        <div class="mt-1">
                            <strong>على Linux أونلاين:</strong> إن كان الإنتاج يعتمد Horizon عبر Supervisor، تجاهل زر
                            التشغيل/الإيقاف أدناه — فهو يتحكم بعملية <code>queue:work</code> فقط، بينما Supervisor هو من
                            يدير Horizon (راجع <code>HORIZON_SETUP_LINUX.md</code>).
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span id="queue-worker-status-badge" class="badge {{ ($workerStatus['running'] ?? false) ? 'bg-success' : 'bg-secondary' }} fs-6">
                                {{ ($workerStatus['running'] ?? false) ? 'يعمل' : 'متوقف' }}
                            </span>
                            @if(!empty($workerStatus['type'] ?? null))
                                <span class="badge bg-light text-dark">{{ $workerStatus['type'] }}</span>
                            @endif
                            @if(!empty($workerStatus['pid'] ?? null))
                                <span class="text-muted small">(PID: {{ $workerStatus['pid'] }})</span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" id="queue-worker-start-btn" {{ ($workerStatus['running'] ?? false) ? 'disabled' : '' }}>
                                <i class="ri-play-line me-1"></i>تشغيل
                            </button>
                            <button type="button" class="btn btn-danger" id="queue-worker-stop-btn" {{ ($workerStatus['running'] ?? false) ? '' : 'disabled' }}>
                                <i class="ri-stop-line me-1"></i>إيقاف
                            </button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="queue-worker-refresh-btn">
                            <i class="ri-refresh-line me-1"></i>تحديث الحالة
                        </button>
                    </div>
                    <div id="queue-worker-message" class="mt-2 small text-muted"></div>
                </div>
            </div>

            {{-- بطاقات الإحصائيات --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-warning-transparent">
                                    <i class="ri-time-line fs-20 text-warning"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">قيد الانتظار الآن</p>
                                    <h4 class="mb-0 fw-semibold" id="stat-pending-total">{{ number_format($stats['pending_total']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-success-transparent">
                                    <i class="ri-checkbox-circle-line fs-20 text-success"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">مكتملة اليوم</p>
                                    <h4 class="mb-0 fw-semibold" id="stat-completed-today">{{ number_format($stats['completed_today']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-info-transparent">
                                    <i class="ri-checkbox-multiple-line fs-20 text-info"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">مكتملة آخر ٧ أيام</p>
                                    <h4 class="mb-0 fw-semibold">{{ number_format($stats['completed_week']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-danger-transparent">
                                    <i class="ri-error-warning-line fs-20 text-danger"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted mb-0 fs-13">مهام فاشلة</p>
                                    <h4 class="mb-0 fw-semibold" id="stat-failed-total">{{ number_format($stats['failed_total']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- توزيع الطوابير --}}
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title"><i class="ri-stack-line me-1"></i>الطوابير</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="queues-breakdown-table">
                            <thead>
                                <tr>
                                    <th>الطابور</th>
                                    <th>قيد الانتظار</th>
                                    <th>أقدم مهمة منتظرة منذ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($queues as $queue)
                                    @php
                                        $isStale = $queue['oldest_pending_at'] && \Carbon\Carbon::parse($queue['oldest_pending_at'])->diffInMinutes(now()) > 15;
                                    @endphp
                                    <tr>
                                        <td><code>{{ $queue['name'] }}</code></td>
                                        <td>
                                            <span class="badge {{ $queue['pending'] > 0 ? 'bg-warning' : 'bg-light text-dark' }}">
                                                {{ number_format($queue['pending']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($queue['oldest_pending_at'])
                                                <span class="{{ $isStale ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                    {{ \Carbon\Carbon::parse($queue['oldest_pending_at'])->diffForHumans() }}
                                                </span>
                                                @if($isStale)
                                                    <i class="ri-alert-line text-danger ms-1" title="قد لا يوجد عامل يعالج هذا الطابور حالياً"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- تبويبات المهام --}}
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-pending" role="tab">قيد الانتظار</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-completed" role="tab">مكتملة مؤخراً</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-failed" role="tab">
                                فاشلة
                                @if($stats['failed_total'] > 0)
                                    <span class="badge bg-danger ms-1">{{ $stats['failed_total'] }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        {{-- قيد الانتظار --}}
                        <div class="tab-pane show active" id="tab-pending" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>المهمة</th>
                                            <th>الطابور</th>
                                            <th>الحالة</th>
                                            <th>وقت الإدخال للطابور</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pending as $row)
                                            <tr>
                                                <td><span class="fw-semibold">{{ class_basename($row->job_class) }}</span></td>
                                                <td><code>{{ $row->queue }}</code></td>
                                                <td>
                                                    @if($row->status === 'processing')
                                                        <span class="badge bg-primary">قيد التنفيذ</span>
                                                    @else
                                                        <span class="badge bg-warning">قيد الانتظار</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $row->queued_at ? \Carbon\Carbon::parse($row->queued_at)->diffForHumans() : '-' }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">لا توجد مهام قيد الانتظار حالياً</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $pending->onEachSide(1)->links() }}</div>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="ri-information-line me-1"></i>
                                هذه القائمة مبنية على سجل مراقبة الطابور الخاص باللوحة، وتبدأ بالتجمّع من لحظة تفعيل هذه
                                الميزة — المهام المُرسَلة قبل ذلك لن تظهر هنا.
                            </p>
                        </div>

                        {{-- مكتملة مؤخراً --}}
                        <div class="tab-pane" id="tab-completed" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>المهمة</th>
                                            <th>الطابور</th>
                                            <th>المدة</th>
                                            <th>وقت الاكتمال</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($completed as $row)
                                            <tr>
                                                <td><span class="fw-semibold">{{ class_basename($row->job_class) }}</span></td>
                                                <td><code>{{ $row->queue }}</code></td>
                                                <td>
                                                    <small>{{ $row->duration_ms !== null ? number_format($row->duration_ms) . ' ms' : '-' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $row->finished_at ? \Carbon\Carbon::parse($row->finished_at)->diffForHumans() : '-' }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">لا توجد مهام مكتملة بعد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $completed->onEachSide(1)->links() }}</div>
                        </div>

                        {{-- فاشلة --}}
                        <div class="tab-pane" id="tab-failed" role="tabpanel">
                            @if($stats['failed_total'] > 0)
                                <form action="{{ route('admin.queue-monitor.retry-all') }}" method="POST" class="d-inline" onsubmit="return confirm('إعادة محاولة تشغيل جميع المهام الفاشلة؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success mb-3">
                                        <i class="ri-restart-line me-1"></i>إعادة محاولة الكل
                                    </button>
                                </form>
                                <form action="{{ route('admin.queue-monitor.flush') }}" method="POST" class="d-inline" onsubmit="return confirm('حذف جميع المهام الفاشلة نهائياً؟ لا يمكن التراجع عن هذا الإجراء.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger mb-3">
                                        <i class="ri-delete-bin-line me-1"></i>حذف الكل
                                    </button>
                                </form>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>المهمة</th>
                                            <th>الطابور</th>
                                            <th>وقت الفشل</th>
                                            <th>السبب</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($failed as $row)
                                            <tr>
                                                <td><span class="fw-semibold">{{ class_basename($row->job_class) }}</span></td>
                                                <td><code>{{ $row->queue }}</code></td>
                                                <td><small>{{ \Carbon\Carbon::parse($row->failed_at)->diffForHumans() }}</small></td>
                                                <td>
                                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 text-wrap text-start"
                                                            data-bs-toggle="modal" data-bs-target="#failed-job-modal-{{ $row->id }}">
                                                        {{ $row->exception_summary }}
                                                    </button>

                                                    <div class="modal fade" id="failed-job-modal-{{ $row->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h6 class="modal-title">{{ class_basename($row->job_class) }} — تفاصيل الفشل</h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="small text-wrap">{{ $row->exception }}</pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <form action="{{ route('admin.queue-monitor.retry', $row->uuid) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success btn-wave" title="إعادة المحاولة">
                                                                <i class="ri-restart-line"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.queue-monitor.destroy', $row->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه المهمة نهائياً؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-wave" title="حذف">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">لا توجد مهام فاشلة 🎉</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $failed->onEachSide(1)->links() }}</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        const statusBadge = document.getElementById('queue-worker-status-badge');
        const startBtn = document.getElementById('queue-worker-start-btn');
        const stopBtn = document.getElementById('queue-worker-stop-btn');
        const refreshBtn = document.getElementById('queue-worker-refresh-btn');
        const messageEl = document.getElementById('queue-worker-message');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        function setRunning(running, pid) {
            if (statusBadge) {
                statusBadge.textContent = running ? 'يعمل' : 'متوقف';
                statusBadge.className = 'badge fs-6 ' + (running ? 'bg-success' : 'bg-secondary');
            }
            if (startBtn) startBtn.disabled = !!running;
            if (stopBtn) stopBtn.disabled = !running;
        }

        function showMessage(msg, isError) {
            if (!messageEl) return;
            messageEl.textContent = msg || '';
            messageEl.className = 'mt-2 small ' + (isError ? 'text-danger' : 'text-muted');
        }

        function fetchStatus() {
            fetch('{{ route("admin.queue-monitor.worker.status") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                setRunning(data.running, data.pid);
                showMessage(data.message || '');
            })
            .catch(function() { showMessage('فشل جلب الحالة.', true); });
        }

        if (startBtn) {
            startBtn.addEventListener('click', function() {
                startBtn.disabled = true;
                showMessage('جاري التشغيل...');
                fetch('{{ route("admin.queue-monitor.worker.start") }}', {
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
        }

        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                stopBtn.disabled = true;
                showMessage('جاري الإيقاف...');
                fetch('{{ route("admin.queue-monitor.worker.stop") }}', {
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
                    setRunning(false, null);
                    if (!data.success) stopBtn.disabled = false;
                })
                .catch(function() {
                    showMessage('حدث خطأ أثناء الإيقاف.', true);
                    stopBtn.disabled = false;
                });
            });
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', fetchStatus);
        }

        // تحديث حي للبطاقات كل ٢٠ ثانية
        function refreshLiveStats() {
            fetch('{{ route("admin.queue-monitor.data") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                setRunning(data.workerStatus.running, data.workerStatus.pid);
                const pendingEl = document.getElementById('stat-pending-total');
                const completedEl = document.getElementById('stat-completed-today');
                const failedEl = document.getElementById('stat-failed-total');
                if (pendingEl) pendingEl.textContent = data.stats.pending_total.toLocaleString('ar');
                if (completedEl) completedEl.textContent = data.stats.completed_today.toLocaleString('ar');
                if (failedEl) failedEl.textContent = data.stats.failed_total.toLocaleString('ar');
            })
            .catch(function() {});
        }

        setInterval(refreshLiveStats, 20000);
    })();
</script>
@endpush
