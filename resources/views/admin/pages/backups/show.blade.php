@extends('admin.layouts.master')

@section('page-title')
    تفاصيل النسخة الاحتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل النسخة: {{ $backup->name }}</h5>
            </div>
            <div>
                <a href="{{ route('backups.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">معلومات النسخة</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>الاسم:</strong> {{ $backup->name }}</p>
                        <p><strong>النوع:</strong> {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] }}</p>
                        <p><strong>مكان التخزين:</strong> 
                            @if($backup->storageConfig)
                                {{ $backup->storageConfig->name }} ({{ \App\Models\AppStorageConfig::DRIVERS[$backup->storage_driver] ?? $backup->storage_driver }})
                            @else
                                {{ \App\Models\AppStorageConfig::DRIVERS[$backup->storage_driver] ?? $backup->storage_driver }}
                            @endif
                        </p>
                        <p><strong>الحالة:</strong> 
                            <span id="backup-status-badge" class="badge">
                                @if($backup->status === 'completed')
                                    <span class="bg-success">مكتمل</span>
                                @elseif($backup->status === 'failed')
                                    <span class="bg-danger">فشل</span>
                                @elseif($backup->status === 'running')
                                    <span class="bg-warning">
                                        <i class="fas fa-spinner fa-spin me-1"></i>قيد التنفيذ
                                    </span>
                                @else
                                    <span class="bg-secondary">معلق</span>
                                @endif
                            </span>
                            @if(in_array($backup->status, ['pending', 'running']))
                                <button type="button" id="refresh-status-btn" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-sync-alt" id="refresh-icon"></i> تحديث
                                </button>
                            @endif
                            @if(in_array($backup->status, ['pending', 'failed']))
                                <form action="{{ route('backups.run', $backup->id) }}" method="POST" class="d-inline ms-2" id="run-backup-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" id="run-backup-btn">
                                        <i class="fas fa-play me-1"></i> تشغيل الآن
                                    </button>
                                </form>
                            @endif
                        </p>
                        @if(in_array($backup->status, ['pending', 'running']))
                            <div id="progress-message" class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                                    <div>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span id="progress-text">جاري معالجة النسخة الاحتياطية...</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <p><strong>الحجم:</strong> <span id="backup-size">{{ $backup->getFileSize() }}</span></p>
                        <p><strong>تاريخ الإنشاء:</strong> {{ $backup->created_at->format('Y-m-d H:i:s') }}</p>
                        <p id="completed-at-section" style="display: {{ $backup->completed_at ? 'block' : 'none' }};">
                            <strong>تاريخ الاكتمال:</strong> <span id="backup-completed-at">{{ $backup->completed_at?->format('Y-m-d H:i:s') }}</span>
                        </p>
                        @if($backup->duration)
                            <p><strong>المدة:</strong> {{ $backup->duration }} ثانية</p>
                        @endif
                        <div id="error-message-section" style="display: {{ $backup->error_message ? 'block' : 'none' }};">
                            <div class="alert alert-danger">
                                <strong>خطأ:</strong> <span id="backup-error-message">{{ $backup->error_message }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="actions-section" style="display: {{ $backup->status === 'completed' ? 'block' : 'none' }};">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">الإجراءات</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="{{ route('backups.download', $backup->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-1"></i> تحميل
                                </a>
                                <form action="{{ route('backups.restore', $backup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من استعادة هذه النسخة؟ سيتم استبدال البيانات الحالية.');">
                                    @csrf
                                    <input type="hidden" name="confirm" value="1">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-undo me-1"></i> استعادة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">سجل العمليات</h6>
                        @if(in_array($backup->status, ['pending', 'running']))
                            <button type="button" id="refresh-logs-btn" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> تحديث السجل
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div id="logs-container" class="table-responsive">
                            @if($backup->logs->count() > 0)
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>الوقت</th>
                                            <th>المستوى</th>
                                            <th>الرسالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logs-tbody">
                                        @foreach($backup->logs as $log)
                                            <tr>
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
                                <p class="text-muted text-center mb-0">لا توجد سجلات بعد</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const backupId = {{ $backup->id }};
    const statusUrl = '{{ route("backups.status", $backup->id) }}';
    let pollingInterval = null;
    let isPolling = false;

    // التحقق من الحالة تلقائياً إذا كانت النسخة قيد المعالجة
    @if(in_array($backup->status, ['pending', 'running']))
        startPolling();
    @endif

    // زر التحديث اليدوي
    const refreshBtn = document.getElementById('refresh-status-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = document.getElementById('refresh-icon');
            if (icon) {
                icon.classList.add('fa-spin');
            }
            checkStatus(true).finally(() => {
                if (icon) {
                    setTimeout(() => {
                        icon.classList.remove('fa-spin');
                    }, 500);
                }
            });
        });
    }

    // زر تشغيل النسخة يدوياً
    const runBackupForm = document.getElementById('run-backup-form');
    if (runBackupForm) {
        runBackupForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('run-backup-btn');
            if (btn) {
                const originalText = btn.innerHTML;
                
                // تعطيل الزر وإظهار حالة التحميل
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري التشغيل...';
                
                // السماح للنموذج بالإرسال العادي (Laravel سيتعامل معه)
                // سيتم إعادة تحميل الصفحة تلقائياً بعد الإرسال
            }
        });
    }

    // زر تحديث السجل
    const refreshLogsBtn = document.getElementById('refresh-logs-btn');
    if (refreshLogsBtn) {
        refreshLogsBtn.addEventListener('click', function() {
            location.reload();
        });
    }

    function startPolling() {
        if (isPolling) return;
        isPolling = true;
        
        // التحقق كل 3 ثواني
        pollingInterval = setInterval(function() {
            checkStatus(false);
        }, 3000);
        
        // التحقق فوراً
        checkStatus(false);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        isPolling = false;
    }

    function checkStatus(manual = false) {
        return fetch(statusUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            updateStatus(data);
            
            // إذا اكتملت أو فشلت، توقف عن الـ polling
            if (data.status === 'completed' || data.status === 'failed') {
                stopPolling();
                if (data.status === 'completed') {
                    // إظهار رسالة نجاح قبل إعادة التحميل
                    const progressText = document.getElementById('progress-text');
                    if (progressText) {
                        progressText.textContent = '✓ اكتملت عملية النسخ الاحتياطي بنجاح!';
                    }
                    // إعادة تحميل الصفحة بعد اكتمال النسخة لعرض جميع البيانات
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            }
            return data;
        })
        .catch(error => {
            console.error('Error checking backup status:', error);
            if (manual) {
                alert('حدث خطأ أثناء التحقق من حالة النسخة: ' + error.message);
            }
            throw error;
        });
    }

    function updateStatus(data) {
        // تحديث الحالة
        const statusBadge = document.getElementById('backup-status-badge');
        if (statusBadge) {
            const statusLabels = {
                'pending': '<span class="badge bg-secondary">معلق</span>',
                'running': '<span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>قيد التنفيذ</span>',
                'completed': '<span class="badge bg-success">مكتمل</span>',
                'failed': '<span class="badge bg-danger">فشل</span>'
            };
            statusBadge.innerHTML = statusLabels[data.status] || statusLabels['pending'];
        }

        // تحديث الحجم
        if (data.file_size_formatted) {
            const sizeElement = document.getElementById('backup-size');
            if (sizeElement) {
                sizeElement.textContent = data.file_size_formatted;
            }
        }

        // تحديث تاريخ الاكتمال
        if (data.completed_at) {
            const completedAtSection = document.getElementById('completed-at-section');
            const completedAtElement = document.getElementById('backup-completed-at');
            if (completedAtSection) {
                completedAtSection.style.display = 'block';
            }
            if (completedAtElement) {
                completedAtElement.textContent = data.completed_at;
            }
        }

        // تحديث رسالة الخطأ
        if (data.error_message) {
            const errorSection = document.getElementById('error-message-section');
            const errorElement = document.getElementById('backup-error-message');
            if (errorSection) {
                errorSection.style.display = 'block';
            }
            if (errorElement) {
                errorElement.textContent = data.error_message;
            }
        }

        // تحديث رسالة التقدم
        const progressText = document.getElementById('progress-text');
        if (progressText) {
            if (data.status === 'running') {
                const messages = {
                    'database': 'جاري نسخ قاعدة البيانات...',
                    'files': 'جاري نسخ الملفات...',
                    'config': 'جاري نسخ الإعدادات...',
                    'full': 'جاري إنشاء النسخة الكاملة...'
                };
                progressText.textContent = data.latest_log || messages['{{ $backup->backup_type }}'] || 'جاري معالجة النسخة الاحتياطية...';
            } else if (data.status === 'completed') {
                progressText.textContent = 'اكتملت عملية النسخ الاحتياطي بنجاح!';
            } else if (data.status === 'failed') {
                progressText.textContent = 'فشلت عملية النسخ الاحتياطي';
            }
        }

        // إظهار/إخفاء قسم الإجراءات
        const actionsSection = document.getElementById('actions-section');
        if (actionsSection) {
            actionsSection.style.display = data.status === 'completed' ? 'block' : 'none';
        }

        // إخفاء رسالة التقدم عند اكتمال أو فشل
        const progressMessage = document.getElementById('progress-message');
        if (progressMessage && (data.status === 'completed' || data.status === 'failed')) {
            progressMessage.style.display = 'none';
        }
    }
});
</script>
@endpush
@stop

