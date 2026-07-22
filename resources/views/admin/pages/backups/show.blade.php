@extends('admin.layouts.master')

@section('page-title')
    تفاصيل النسخة الاحتياطية
@stop

@section('content')
@php
    $backupLogs = $backup->logs->filter(function ($log) {
        $message = strtolower($log->message);
        return !str_contains($message, 'استعادة')
            && !str_contains($message, 'restore')
            && !str_contains($message, 'استرجاع');
    });

    $restoreLogs = $backup->logs->filter(function ($log) {
        $message = strtolower($log->message);
        return str_contains($message, 'استعادة')
            || str_contains($message, 'restore')
            || str_contains($message, 'استرجاع');
    });

    $isActive = in_array($backup->status, ['pending', 'running'], true);
    $storageLabel = $backup->storageConfig
        ? $backup->storageConfig->name . ' (' . (\App\Models\AppStorageConfig::DRIVERS[$backup->storage_driver] ?? $backup->storage_driver) . ')'
        : (\App\Models\AppStorageConfig::DRIVERS[$backup->storage_driver] ?? $backup->storage_driver);

    $statusChip = match ($backup->status) {
        'completed' => ['class' => 'text-success', 'icon' => 'fe-check-circle', 'label' => 'مكتمل'],
        'failed' => ['class' => 'text-danger', 'icon' => 'fe-slash', 'label' => 'فشل'],
        'running' => ['class' => 'text-warning', 'icon' => 'fe-loader', 'label' => 'قيد التنفيذ'],
        default => ['class' => 'text-muted', 'icon' => 'fe-clock', 'label' => 'معلق'],
    };

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-activity',
            'label' => 'التقدم',
            'value' => (int) ($backup->progress ?? 0) . '%',
            'sub' => $backup->stage ?: 'بانتظار البدء',
            'id' => 'kpi-progress',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-hard-drive',
            'label' => 'الحجم',
            'value' => $backup->getFileSize(),
            'sub' => 'حجم ملف النسخة',
            'id' => 'kpi-size',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-database',
            'label' => 'النوع',
            'value' => \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type,
            'sub' => \App\Models\Backup::COMPRESSION_TYPES[$backup->compression_type] ?? ($backup->compression_type ?: 'بدون ضغط'),
            'id' => 'kpi-type',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'المدة',
            'value' => $backup->duration ? $backup->duration . ' ث' : '—',
            'sub' => $backup->created_at->format('Y-m-d H:i'),
            'id' => 'kpi-duration',
        ],
    ];
@endphp
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backups.index') }}">النسخ الاحتياطية</a></li>
                    <li class="breadcrumb-item active">#{{ $backup->id }}</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        {{-- Hero (نفس أسلوب صفحة المستخدمين) --}}
        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-shield me-1"></i>
                        تفاصيل النسخة الاحتياطية
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $backup->name }}</h2>
                    <p class="group-show-hero__desc mb-2">
                        التخزين: {{ $storageLabel }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span id="backup-status-chip" class="group-show-chip group-show-chip--sm {{ $statusChip['class'] }}">
                            <i class="fe {{ $statusChip['icon'] }} me-1"></i>{{ $statusChip['label'] }}
                        </span>
                        <span class="group-show-chip group-show-chip--sm">
                            {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}
                        </span>
                        <span class="group-show-chip group-show-chip--sm" id="backup-stage-chip">
                            المرحلة: {{ $backup->stage ?: '—' }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('backups.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للنسخ</span>
                        </a>
                        @if($isActive)
                            <button type="button" id="refresh-status-btn" class="group-show-action group-show-action--primary border-0 bg-transparent w-100 text-start">
                                <span class="group-show-action__icon"><i class="fe fe-refresh-cw" id="refresh-icon"></i></span>
                                <span class="group-show-action__text">تحديث الحالة</span>
                            </button>
                        @endif
                        @if(in_array($backup->status, ['pending', 'failed'], true))
                            <form action="{{ route('backups.run', $backup->id) }}" method="POST" id="run-backup-form" class="w-100">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--success border-0 bg-transparent w-100 text-start" id="run-backup-btn">
                                    <span class="group-show-action__icon"><i class="fe fe-play"></i></span>
                                    <span class="group-show-action__text">تشغيل الآن</span>
                                </button>
                            </form>
                        @endif
                        @if($isActive)
                            <form action="{{ route('backups.mark-failed', $backup->id) }}" method="POST" class="w-100"
                                  onsubmit="return confirm('تعليم هذه النسخة كمفشل؟');">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--danger border-0 bg-transparent w-100 text-start">
                                    <span class="group-show-action__icon"><i class="fe fe-x-circle"></i></span>
                                    <span class="group-show-action__text">تعليم كمفشل</span>
                                </button>
                            </form>
                        @endif
                        @if($backup->status === 'completed')
                            <a href="{{ route('backups.download', $backup->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-download"></i></span>
                                <span class="group-show-action__text">تحميل النسخة</span>
                            </a>
                            <form id="restore-form" action="{{ route('backups.restore', $backup->id) }}" method="POST" class="w-100">
                                @csrf
                                <input type="hidden" name="confirm" value="1">
                                <button type="submit" class="group-show-action group-show-action--warning border-0 bg-transparent w-100 text-start" id="restore-btn">
                                    <span class="group-show-action__icon"><i class="fe fe-rotate-ccw"></i></span>
                                    <span class="group-show-action__text">استعادة</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1" id="{{ $card['id'] }}">{{ $card['value'] }}</h3>
                                <p class="admin-stats-card__sub mb-0" id="{{ $card['id'] }}-sub">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Progress --}}
        <div id="progress-message" class="card custom-card group-show-members-card dashboard-fade-in mb-4" style="display: {{ $isActive ? 'block' : 'none' }};">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="group-show-members-card__title mb-1">تقدم النسخ الاحتياطي</h6>
                    <p class="fs-12 text-muted mb-0" id="progress-text">جاري معالجة النسخة الاحتياطية...</p>
                </div>
                <span class="badge bg-primary-transparent text-primary" id="live-indicator">
                    <span class="spinner-border spinner-border-sm me-1" style="width: .7rem; height: .7rem;"></span>
                    مباشر
                </span>
            </div>
            <div class="card-body pt-3">
                <div class="progress mb-2" style="height: 22px;">
                    <div id="backup-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"
                         style="width: {{ (int) ($backup->progress ?? 0) }}%"
                         aria-valuenow="{{ (int) ($backup->progress ?? 0) }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        <span id="backup-progress-label">{{ (int) ($backup->progress ?? 0) }}%</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between flex-wrap gap-2 fs-12 text-muted">
                    <span>المرحلة: <strong id="backup-stage">{{ $backup->stage ?? '—' }}</strong></span>
                    <span id="backup-bytes">
                        @if($backup->bytes_processed || $backup->bytes_total)
                            {{ number_format((int) $backup->bytes_processed) }} / {{ number_format((int) ($backup->bytes_total ?: 0)) }} بايت
                        @else
                            —
                        @endif
                    </span>
                </div>
                @if($backup->status === 'pending')
                    <div class="alert alert-warning mb-0 mt-3 py-2">
                        <i class="fe fe-alert-triangle me-1"></i>
                        بانتظار عامل الطابور. شغّل في طرفية منفصلة:
                        <code dir="ltr">php artisan queue:work --timeout=3600</code>
                    </div>
                @endif
            </div>
        </div>

        <div id="error-message-section" class="mb-4" style="display: {{ $backup->error_message ? 'block' : 'none' }};">
            <div class="alert alert-danger mb-0">
                <strong>خطأ:</strong> <span id="backup-error-message">{{ $backup->error_message }}</span>
            </div>
        </div>

        <div id="actions-section" class="card custom-card group-show-members-card dashboard-fade-in mb-4" style="display: {{ $backup->status === 'completed' ? 'block' : 'none' }};">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">إجراءات الاستعادة</h6>
                <p class="fs-12 text-muted mb-0">تحميل الملف أو استعادة النسخة إلى النظام.</p>
            </div>
            <div class="card-body pt-3">
                <div id="restore-progress" style="display: none;">
                    <div class="progress" style="height: 25px;">
                        <div id="restore-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                             role="progressbar" style="width: 0%">
                            <span id="restore-progress-text">0%</span>
                        </div>
                    </div>
                    <div id="restore-status" class="mt-2 text-muted small"></div>
                </div>
            </div>
        </div>

        {{-- Info strip --}}
        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">مكان التخزين</small>
                        <strong>{{ $storageLabel }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">تاريخ الإنشاء</small>
                        <strong>{{ $backup->created_at->format('Y-m-d H:i:s') }}</strong>
                    </div>
                    <div class="col-md-4" id="completed-at-section" style="display: {{ $backup->completed_at ? 'block' : 'none' }};">
                        <small class="text-muted d-block">تاريخ الاكتمال</small>
                        <strong id="backup-completed-at">{{ $backup->completed_at?->format('Y-m-d H:i:s') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live logs --}}
        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="group-show-members-card__title mb-0">
                        سجل العمليات
                        <span class="group-show-members-card__count" id="logs-count">{{ $backupLogs->count() }}</span>
                    </h6>
                    <p class="fs-12 text-muted mb-0 mt-1">
                        @if($isActive)
                            يتحدث تلقائياً كل ثانيتين أثناء التنفيذ
                        @else
                            آخر أحداث عملية النسخ
                        @endif
                    </p>
                </div>
                @if($isActive)
                    <span class="badge bg-success-transparent text-success" id="logs-live-badge">
                        <span class="spinner-border spinner-border-sm me-1" style="width: .65rem; height: .65rem;"></span>
                        تحديث تلقائي
                    </span>
                @endif
            </div>
            <div class="card-body pt-3">
                <div id="logs-container" class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                    @if($backupLogs->count() > 0)
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 100px;">الوقت</th>
                                    <th style="width: 110px;">المستوى</th>
                                    <th>الرسالة</th>
                                </tr>
                            </thead>
                            <tbody id="logs-tbody">
                                @foreach($backupLogs as $log)
                                    <tr data-log-id="{{ $log->id }}">
                                        <td>{{ $log->created_at->format('H:i:s') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info') }}">
                                                {{ \App\Models\BackupLog::LEVELS[$log->level] ?? $log->level }}
                                            </span>
                                        </td>
                                        <td>{{ $log->message }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center mb-0 py-4" id="logs-empty">لا توجد سجلات بعد — ستظهر هنا تلقائياً عند بدء التنفيذ</p>
                    @endif
                </div>
            </div>
        </div>

        @if($restoreLogs->count() > 0)
        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 border-warning">
            <div class="card-header border-0 pb-0" style="background-color: #fff9e6;">
                <h6 class="group-show-members-card__title mb-0">
                    تقرير الاستعادة
                    <span class="group-show-members-card__count">{{ $restoreLogs->count() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                <div id="restore-logs-container" class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">الوقت</th>
                                <th style="width: 110px;">المستوى</th>
                                <th>الرسالة</th>
                            </tr>
                        </thead>
                        <tbody id="restore-logs-tbody">
                            @foreach($restoreLogs as $log)
                                <tr data-log-id="{{ $log->id }}">
                                    <td>{{ $log->created_at->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'warning' ? 'warning' : 'info') }}">
                                            {{ \App\Models\BackupLog::LEVELS[$log->level] ?? $log->level }}
                                        </span>
                                    </td>
                                    <td>{{ $log->message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusUrl = '{{ route("backups.status", $backup->id) }}';
    const backupType = '{{ $backup->backup_type }}';
    let pollingInterval = null;
    let isPolling = false;

    @if($isActive)
        startPolling();
    @endif

    const refreshBtn = document.getElementById('refresh-status-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('fa-spin');
            checkStatus(true).finally(() => {
                if (icon) setTimeout(() => icon.classList.remove('fa-spin'), 400);
            });
        });
    }

    const runBackupForm = document.getElementById('run-backup-form');
    if (runBackupForm) {
        runBackupForm.addEventListener('submit', function() {
            const btn = document.getElementById('run-backup-btn');
            if (btn) {
                btn.disabled = true;
                const text = btn.querySelector('.group-show-action__text');
                if (text) text.textContent = 'جاري التشغيل...';
            }
        });
    }

    function startPolling() {
        if (isPolling) return;
        isPolling = true;
        pollingInterval = setInterval(function() { checkStatus(false); }, 2000);
        checkStatus(false);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        isPolling = false;
        const liveBadge = document.getElementById('logs-live-badge');
        if (liveBadge) liveBadge.style.display = 'none';
        const liveIndicator = document.getElementById('live-indicator');
        if (liveIndicator) liveIndicator.style.display = 'none';
    }

    function checkStatus(manual = false) {
        return fetch(statusUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            updateStatus(data);
            if (data.status === 'completed' || data.status === 'failed') {
                stopPolling();
                if (data.status === 'completed') {
                    setTimeout(() => location.reload(), 1500);
                }
            }
            return data;
        })
        .catch(error => {
            console.error('Error checking backup status:', error);
            if (manual) alert('حدث خطأ أثناء التحقق من حالة النسخة: ' + error.message);
            throw error;
        });
    }

    function updateStatus(data) {
        const chip = document.getElementById('backup-status-chip');
        if (chip) {
            const map = {
                pending: { cls: 'text-muted', icon: 'fe-clock', label: 'معلق' },
                running: { cls: 'text-warning', icon: 'fe-loader', label: 'قيد التنفيذ' },
                completed: { cls: 'text-success', icon: 'fe-check-circle', label: 'مكتمل' },
                failed: { cls: 'text-danger', icon: 'fe-slash', label: 'فشل' }
            };
            const s = map[data.status] || map.pending;
            chip.className = 'group-show-chip group-show-chip--sm ' + s.cls;
            chip.innerHTML = '<i class="fe ' + s.icon + ' me-1"></i>' + s.label;
        }

        const kpiProgress = document.getElementById('kpi-progress');
        const kpiProgressSub = document.getElementById('kpi-progress-sub');
        if (typeof data.progress === 'number' && kpiProgress) {
            kpiProgress.textContent = Math.max(0, Math.min(100, data.progress)) + '%';
        }
        if (kpiProgressSub && data.stage) kpiProgressSub.textContent = data.stage;

        if (data.file_size_formatted) {
            const kpiSize = document.getElementById('kpi-size');
            if (kpiSize) kpiSize.textContent = data.file_size_formatted;
        }

        if (data.duration != null) {
            const kpiDuration = document.getElementById('kpi-duration');
            if (kpiDuration) kpiDuration.textContent = data.duration + ' ث';
        }

        const stageChip = document.getElementById('backup-stage-chip');
        if (stageChip && data.stage) stageChip.textContent = 'المرحلة: ' + data.stage;

        const stageEl = document.getElementById('backup-stage');
        if (stageEl && data.stage) stageEl.textContent = data.stage;

        const progressBar = document.getElementById('backup-progress-bar');
        const progressLabel = document.getElementById('backup-progress-label');
        if (typeof data.progress === 'number') {
            const pct = Math.max(0, Math.min(100, data.progress));
            if (progressBar) {
                progressBar.style.width = pct + '%';
                progressBar.setAttribute('aria-valuenow', String(pct));
            }
            if (progressLabel) progressLabel.textContent = pct + '%';
        }

        const bytesEl = document.getElementById('backup-bytes');
        if (bytesEl && (data.bytes_processed != null || data.bytes_total != null)) {
            const a = Number(data.bytes_processed || 0).toLocaleString();
            const b = Number(data.bytes_total || 0).toLocaleString();
            bytesEl.textContent = a + ' / ' + b + ' بايت';
        }

        if (data.completed_at) {
            const completedAtSection = document.getElementById('completed-at-section');
            const completedAtElement = document.getElementById('backup-completed-at');
            if (completedAtSection) completedAtSection.style.display = 'block';
            if (completedAtElement) completedAtElement.textContent = data.completed_at;
        }

        if (data.error_message) {
            const errorSection = document.getElementById('error-message-section');
            const errorElement = document.getElementById('backup-error-message');
            if (errorSection) errorSection.style.display = 'block';
            if (errorElement) errorElement.textContent = data.error_message;
        }

        const progressText = document.getElementById('progress-text');
        if (progressText) {
            const messages = {
                database: 'جاري نسخ قاعدة البيانات...',
                files: 'جاري نسخ الملفات...',
                config: 'جاري نسخ الإعدادات...',
                full: 'جاري إنشاء النسخة الكاملة...'
            };
            if (data.status === 'running' || data.status === 'pending') {
                progressText.textContent = data.latest_log || messages[backupType] || 'جاري معالجة النسخة الاحتياطية...';
            } else if (data.status === 'completed') {
                progressText.textContent = 'اكتملت عملية النسخ الاحتياطي بنجاح!';
            } else if (data.status === 'failed') {
                progressText.textContent = 'فشلت عملية النسخ الاحتياطي';
            }
        }

        const actionsSection = document.getElementById('actions-section');
        if (actionsSection) actionsSection.style.display = data.status === 'completed' ? 'block' : 'none';

        const progressMessage = document.getElementById('progress-message');
        if (progressMessage && (data.status === 'completed' || data.status === 'failed')) {
            progressMessage.style.display = 'none';
        }

        if (data.logs && Array.isArray(data.logs)) {
            updateLogs(data.logs);
            updateRestoreLogs(data.logs);
        }
    }

    function ensureLogsTable() {
        const logsContainer = document.getElementById('logs-container');
        if (!logsContainer) return null;
        let tbody = document.getElementById('logs-tbody');
        if (!tbody) {
            logsContainer.innerHTML = `
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width:100px">الوقت</th>
                            <th style="width:110px">المستوى</th>
                            <th>الرسالة</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody"></tbody>
                </table>`;
            tbody = document.getElementById('logs-tbody');
        }
        return tbody;
    }

    function updateLogs(logs) {
        const backupLogs = logs.filter(log => {
            const message = (log.message || '').toLowerCase();
            return !message.includes('استعادة') && !message.includes('restore') && !message.includes('استرجاع');
        });

        const countEl = document.getElementById('logs-count');
        if (countEl) countEl.textContent = String(backupLogs.length);

        if (backupLogs.length === 0) return;

        const tbody = ensureLogsTable();
        if (!tbody) return;

        const existing = new Set();
        Array.from(tbody.querySelectorAll('tr[data-log-id]')).forEach(row => {
            existing.add(parseInt(row.getAttribute('data-log-id'), 10));
        });

        let added = false;
        const levelColors = { error: 'danger', warning: 'warning', info: 'info' };
        const levelLabels = { error: 'خطأ', warning: 'تحذير', info: 'معلومات' };

        backupLogs.forEach(log => {
            if (existing.has(log.id)) return;
            const row = document.createElement('tr');
            row.setAttribute('data-log-id', log.id);
            row.innerHTML = `
                <td>${escapeHtml(log.created_at)}</td>
                <td><span class="badge bg-${levelColors[log.level] || 'info'}">${levelLabels[log.level] || escapeHtml(log.level)}</span></td>
                <td>${escapeHtml(log.message)}</td>`;
            tbody.appendChild(row);
            added = true;
        });

        if (added) {
            const container = document.getElementById('logs-container');
            if (container) container.scrollTop = container.scrollHeight;
        }
    }

    function updateRestoreLogs(logs) {
        const restoreTbody = document.getElementById('restore-logs-tbody');
        if (!restoreTbody) return;
        const restoreLogs = logs.filter(log => {
            const message = (log.message || '').toLowerCase();
            return message.includes('استعادة') || message.includes('restore') || message.includes('استرجاع');
        });
        const existing = new Set();
        Array.from(restoreTbody.querySelectorAll('tr[data-log-id]')).forEach(row => {
            existing.add(parseInt(row.getAttribute('data-log-id'), 10));
        });
        const levelColors = { error: 'danger', warning: 'warning', info: 'info' };
        const levelLabels = { error: 'خطأ', warning: 'تحذير', info: 'معلومات' };
        restoreLogs.forEach(log => {
            if (existing.has(log.id)) return;
            const row = document.createElement('tr');
            row.setAttribute('data-log-id', log.id);
            row.innerHTML = `
                <td>${escapeHtml(log.created_at)}</td>
                <td><span class="badge bg-${levelColors[log.level] || 'info'}">${levelLabels[log.level] || escapeHtml(log.level)}</span></td>
                <td>${escapeHtml(log.message)}</td>`;
            restoreTbody.appendChild(row);
        });
    }

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text ?? '').replace(/[&<>"']/g, m => map[m]);
    }

    // Restore AJAX (completed backups)
    const restoreForm = document.getElementById('restore-form');
    let restorePollingInterval = null;
    if (restoreForm) {
        restoreForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('هل أنت متأكد من استعادة هذه النسخة؟ سيتم استبدال البيانات الحالية.')) return;
            const restoreBtn = document.getElementById('restore-btn');
            const progressDiv = document.getElementById('restore-progress');
            const statusDiv = document.getElementById('restore-status');
            const actionsSection = document.getElementById('actions-section');
            if (actionsSection) actionsSection.style.display = 'block';
            if (restoreBtn) restoreBtn.disabled = true;
            if (progressDiv) progressDiv.style.display = 'block';

            fetch(restoreForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ confirm: true })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => { throw new Error(data.message || 'HTTP error'); });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'فشل بدء الاستعادة');
                if (statusDiv) statusDiv.textContent = data.message || 'تم بدء الاستعادة...';
                if (restorePollingInterval) clearInterval(restorePollingInterval);
                restorePollingInterval = setInterval(() => {
                    fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
                        .then(r => r.json())
                        .then(d => {
                            if (d.logs) updateLogs(d.logs);
                            if (d.status === 'completed' || d.status === 'failed') {
                                clearInterval(restorePollingInterval);
                                setTimeout(() => location.reload(), 1500);
                            }
                        });
                }, 2000);
            })
            .catch(error => {
                alert(error.message || 'خطأ في الاستعادة');
                if (restoreBtn) restoreBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush
@stop
