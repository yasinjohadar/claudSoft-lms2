@extends('admin.layouts.master')

@section('page-title')
    جرد الملفات والترحيل
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">جرد الملفات والترحيل إلى السحابة</h5>
                @if($scannedAt)
                    <p class="text-muted mb-0 small">آخر مسح: {{ $scannedAt }}</p>
                @else
                    <p class="text-muted mb-0 small">لم يتم المسح بعد — اضغط «مسح الآن».</p>
                @endif
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('app-storage.configs.index') }}" class="btn btn-outline-secondary btn-sm">إعدادات التخزين</a>
                <a href="{{ route('storage-disk-mappings.index') }}" class="btn btn-outline-secondary btn-sm">Disk Mappings</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">الإجمالي</div>
                        <div class="fs-4 fw-bold">{{ $summary['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100 border-success">
                    <div class="card-body text-center">
                        <div class="text-muted small">سحابة فقط</div>
                        <div class="fs-4 fw-bold text-success">{{ $summary['cloud_only'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100 border-warning">
                    <div class="card-body text-center">
                        <div class="text-muted small">محلي فقط</div>
                        <div class="fs-4 fw-bold text-warning">{{ $summary['local_only'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100 border-info">
                    <div class="card-body text-center">
                        <div class="text-muted small">محلي + سحابة</div>
                        <div class="fs-4 fw-bold text-info">{{ $summary['both'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100 border-danger">
                    <div class="card-body text-center">
                        <div class="text-muted small">مفقود</div>
                        <div class="fs-4 fw-bold text-danger">{{ $summary['missing'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small">حجم محلي للترحيل</div>
                        <div class="fs-6 fw-bold">{{ number_format($summary['local_only_bytes'] + $summary['both_bytes']) }} B</div>
                    </div>
                </div>
            </div>
        </div>

        @if($progress)
            <div class="card border-0 shadow-sm mb-4" id="migration-progress-card">
                <div class="card-body">
                    <h6 class="mb-2">تقدم الترحيل</h6>
                    @php
                        $pct = ($progress['total'] ?? 0) > 0
                            ? round((($progress['completed'] ?? 0) / $progress['total']) * 100)
                            : 0;
                    @endphp
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%" id="migration-progress-bar"></div>
                    </div>
                    <div class="small text-muted" id="migration-progress-text">
                        {{ $progress['completed'] ?? 0 }} / {{ $progress['total'] ?? 0 }}
                        — نُقل: {{ $progress['migrated'] ?? 0 }},
                        تُخطى: {{ $progress['skipped'] ?? 0 }},
                        فشل: {{ $progress['failed'] ?? 0 }}
                        ({{ $progress['status'] ?? 'unknown' }})
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">مراحل الترحيل الموصى بها</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($phases as $phaseKey => $phaseSources)
                        <form method="POST" action="{{ route('app-storage.inventory.scan') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="phase" value="{{ $phaseKey }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                مسح: {{ $phaseKey }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('app-storage.inventory.migrate') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="phase" value="{{ $phaseKey }}">
                            <input type="hidden" name="status" value="local_only">
                            <input type="hidden" name="dry_run" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                معاينة {{ $phaseKey }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('app-storage.inventory.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">القرص</label>
                        <select name="disk" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($disks as $diskName)
                                <option value="{{ $diskName }}" @selected($filters['disk'] === $diskName)>{{ $diskName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المصدر</label>
                        <select name="source" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($sources as $source)
                                <option value="{{ $source['key'] }}" @selected($filters['source'] === $source['key'])>{{ $source['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الموقع</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" @selected($filters['status'] === $statusOption)>{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">تصفية</button>
                        <a href="{{ route('app-storage.inventory.index') }}" class="btn btn-outline-secondary btn-sm">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <form method="POST" action="{{ route('app-storage.inventory.scan') }}">
                @csrf
                @foreach($filters as $key => $value)
                    @if($value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync me-1"></i> مسح الآن
                </button>
            </form>

            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}">
                @csrf
                <input type="hidden" name="status" value="local_only">
                <input type="hidden" name="dry_run" value="1">
                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                <button type="submit" class="btn btn-outline-info btn-sm">معاينة الترحيل</button>
            </form>

            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}" onsubmit="return confirm('ترحيل كل الملفات المحلية المطابقة للفلاتر؟');">
                @csrf
                <input type="hidden" name="status" value="{{ $filters['status'] ?: 'local_only' }}">
                <input type="hidden" name="use_queue" value="1">
                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-cloud-upload-alt me-1"></i> ترحيل (Queue)
                </button>
            </form>

            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}" onsubmit="return confirm('ترحيل وحذف النسخ المحلية؟');">
                @csrf
                <input type="hidden" name="status" value="{{ $filters['status'] ?: 'local_only' }}">
                <input type="hidden" name="delete_local" value="1">
                <input type="hidden" name="use_queue" value="1">
                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                <button type="submit" class="btn btn-warning btn-sm">ترحيل + حذف محلي</button>
            </form>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="select-all-files"></th>
                                <th>المصدر</th>
                                <th>القرص</th>
                                <th>المسار</th>
                                <th>الموقع</th>
                                <th>التخزين</th>
                                <th>الحجم</th>
                                <th>رابط</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        @if(in_array($item['status'], ['local_only', 'both']))
                                            <input type="checkbox" class="file-checkbox" value="{{ $item['path'] }}">
                                        @endif
                                    </td>
                                    <td>{{ $item['source_label'] ?? '' }}</td>
                                    <td><code>{{ $item['disk'] ?? '' }}</code></td>
                                    <td class="text-start"><small>{{ $item['path'] ?? '' }}</small></td>
                                    <td>
                                        @php $st = $item['status'] ?? 'missing'; @endphp
                                        <span class="badge bg-{{ match($st) {
                                            'cloud_only' => 'success',
                                            'local_only' => 'warning',
                                            'both' => 'info',
                                            default => 'danger',
                                        } }}">{{ $st }}</span>
                                    </td>
                                    <td><small>{{ $item['storage_name'] ?? '—' }}</small></td>
                                    <td>{{ number_format($item['size'] ?? 0) }}</td>
                                    <td>
                                        @if(!empty($item['entity_url']))
                                            <a href="{{ $item['entity_url'] }}" class="btn btn-sm btn-outline-primary" target="_blank">عرض</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">لا توجد نتائج — نفّذ مسحاً أو غيّر الفلاتر.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('app-storage.inventory.migrate') }}" id="selected-migrate-form" class="mt-3">
            @csrf
            <input type="hidden" name="use_queue" value="1">
            <div id="selected-paths-container"></div>
            <button type="submit" class="btn btn-success btn-sm" id="migrate-selected-btn" disabled
                onclick="return confirm('ترحيل الملفات المحددة؟');">
                ترحيل المحدد (Queue)
            </button>
        </form>
    </div>
</div>
@stop

@section('script')
<script>
document.getElementById('select-all-files')?.addEventListener('change', function () {
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = this.checked);
    updateSelectedForm();
});

document.querySelectorAll('.file-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedForm);
});

function updateSelectedForm() {
    const container = document.getElementById('selected-paths-container');
    const btn = document.getElementById('migrate-selected-btn');
    container.innerHTML = '';
    const checked = document.querySelectorAll('.file-checkbox:checked');
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'paths[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    btn.disabled = checked.length === 0;
}

@if($progress && ($progress['status'] ?? '') === 'running')
setInterval(function () {
    fetch('{{ route('app-storage.inventory.progress') }}')
        .then(r => r.json())
        .then(data => {
            const p = data.progress;
            if (!p) return;
            const pct = p.total > 0 ? Math.round((p.completed / p.total) * 100) : 0;
            const bar = document.getElementById('migration-progress-bar');
            const text = document.getElementById('migration-progress-text');
            if (bar) bar.style.width = pct + '%';
            if (text) {
                text.textContent = `${p.completed} / ${p.total} — نُقل: ${p.migrated}, تُخطى: ${p.skipped}, فشل: ${p.failed} (${p.status})`;
            }
        });
}, 3000);
@endif
</script>
@stop
