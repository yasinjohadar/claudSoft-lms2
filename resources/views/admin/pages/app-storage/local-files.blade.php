@extends('admin.layouts.master')

@section('page-title')
    إدارة النسخ المحلية
@stop

@section('styles')
<style>
    .lf-page { --lf-ink:#1e293b; --lf-muted:#64748b; --lf-line:#e2e8f0; --lf-soft:#f8fafc; --lf-accent:#0f766e; }
    .lf-page .lf-hero {
        background: linear-gradient(135deg, #0f766e 0%, #134e4a 60%, #1e293b 100%);
        color: #fff; border-radius: 1rem; padding: 1.35rem 1.5rem; margin-bottom: 1.15rem;
    }
    .lf-page .lf-hero h5 { color:#fff; margin:0 0 .35rem; font-weight:700; }
    .lf-page .lf-hero p { margin:0; color:rgba(255,255,255,.85); max-width:46rem; }
    .lf-page .lf-hero .btn { border-color:rgba(255,255,255,.4); color:#fff; }
    .lf-page .lf-hero .btn:hover { background:rgba(255,255,255,.12); color:#fff; }
    .lf-page .lf-stat {
        border:1px solid var(--lf-line); border-radius:.9rem; background:#fff;
        padding:.9rem; text-align:center; height:100%;
    }
    .lf-page .lf-stat .label { font-size:.78rem; color:var(--lf-muted); }
    .lf-page .lf-stat .value { font-size:1.4rem; font-weight:700; color:var(--lf-ink); }
    .lf-page .lf-card {
        border:1px solid var(--lf-line); border-radius:1rem; background:#fff; margin-bottom:1.15rem; overflow:hidden;
    }
    .lf-page .lf-card-head {
        padding:.9rem 1.1rem; border-bottom:1px solid var(--lf-line); background:var(--lf-soft);
        display:flex; justify-content:space-between; gap:.75rem; flex-wrap:wrap; align-items:center;
    }
    .lf-page .lf-card-head h6 { margin:0; font-weight:700; }
    .lf-page .lf-card-body { padding:1.1rem; }
    .lf-page .lf-note {
        border:1px solid #99f6e4; background:#f0fdfa; color:#115e59;
        border-radius:.85rem; padding:.85rem 1rem; margin-bottom:1.15rem; font-size:.88rem;
    }
    .lf-page .lf-danger-box {
        border:1px solid #fecaca; background:#fef2f2; color:#991b1b;
        border-radius:.85rem; padding:.85rem 1rem; font-size:.85rem;
    }
    .lf-page .badge-soft-warning { background:#ffedd5; color:#9a3412; }
    .lf-page .badge-soft-info { background:#e0f2fe; color:#075985; }
    .lf-page .badge-soft-success { background:#dcfce7; color:#166534; }
    .lf-page .badge-soft-danger { background:#fee2e2; color:#991b1b; }
    .lf-page .lf-bar {
        position:sticky; bottom:1rem; z-index:20; display:none;
        border:1px solid var(--lf-line); background:rgba(255,255,255,.97);
        border-radius:999px; box-shadow:0 10px 28px rgba(15,23,42,.12);
        padding:.55rem .9rem; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap;
    }
    .lf-page .lf-bar.is-visible { display:flex; }
</style>
@stop

@section('content')
@php
    $formatBytes = fn ($b) => $inventoryService->formatBytes((int) $b);
@endphp
<div class="main-content app-content lf-page">
    <div class="container-fluid">

        <div class="lf-hero">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                <div>
                    <h5 class="fs-21">إدارة النسخ المحلية</h5>
                    <p>
                        هنا تظهر الملفات التي لها نسخة على السيرفر المحلي فقط.
                        يمكنك حذف المحلي <strong>مفرداً أو جماعياً</strong> للإبقاء على السحابة.
                        <strong>لا يُحذف أي شيء من السحابة أبداً.</strong>
                        @if($scannedAt)
                            <br><span class="small opacity-75">آخر تحليل: {{ $scannedAt }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('app-storage.inventory.cloud-files') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-cloud me-1"></i> استعراض السحابة
                    </a>
                    <a href="{{ route('app-storage.inventory.index') }}" class="btn btn-sm btn-outline-light">
                        ← العودة للجرد
                    </a>
                    <form method="POST" action="{{ route('app-storage.inventory.scan') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light text-dark">
                            <i class="fas fa-chart-pie me-1"></i> إعادة التحليل
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lf-note">
            <strong>قاعدة الأمان:</strong>
            الحذف الآمن يعمل فقط لملفات <em>نسختان</em> بعد تحقق فوري من وجود السحابة.
            ملفات <em>محلي فقط</em> لا تُحذف إلا إذا فعّلت خياراً صريحاً خطيراً (قد تفقد الملف نهائياً).
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="lf-stat">
                    <div class="label">ملفات لها محلي</div>
                    <div class="value">{{ $summary['total'] }}</div>
                    <div class="small text-muted">{{ $formatBytes($summary['local_bytes']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="lf-stat">
                    <div class="label">نسختان (آمن للحذف)</div>
                    <div class="value text-info">{{ $summary['both'] }}</div>
                    <div class="small text-muted">قابل للحذف الآمن: {{ $summary['safe_deletable'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="lf-stat">
                    <div class="label">محلي فقط</div>
                    <div class="value text-warning">{{ $summary['local_only'] }}</div>
                    <div class="small text-muted">بدون سحابة مؤكدة</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="lf-stat">
                    <div class="label">السحابة</div>
                    <div class="value text-success" style="font-size:1rem;">محمية</div>
                    <div class="small text-muted">لا حذف سحابي من هنا</div>
                </div>
            </div>
        </div>

        {{-- Bulk actions --}}
        <div class="lf-card">
            <div class="lf-card-head">
                <h6>إجراءات جماعية</h6>
                <span class="small text-muted">تعمل على النتائج المصفّاة أدناه</span>
            </div>
            <div class="lf-card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="mb-1">حذف كل المكرر الآمن</h6>
                            <p class="small text-muted mb-3">
                                يحذف المحلي لكل ملفات «نسختان» في التصفية الحالية فقط.
                                السحابة تبقى. الملفات «محلي فقط» تُتجاهل.
                            </p>
                            <form method="POST" action="{{ route('app-storage.inventory.local-files.delete') }}"
                                  onsubmit="return confirm('حذف كل النسخ المحلية المكررة الآمنة (نسختان) في التصفية الحالية؟ السحابة لن تُمس.');">
                                @csrf
                                <input type="hidden" name="mode" value="all_safe">
                                @if($filters['disk'])<input type="hidden" name="filter_disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="filter_source" value="{{ $filters['source'] }}">@endif
                                @if($filters['status'])<input type="hidden" name="filter_status" value="{{ $filters['status'] }}">@endif
                                <button type="submit" class="btn btn-warning btn-sm" @disabled(($summary['safe_deletable'] ?? 0) < 1)>
                                    <i class="fas fa-broom me-1"></i>
                                    حذف كل المكرر الآمن ({{ $summary['safe_deletable'] }})
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="lf-danger-box h-100">
                            <h6 class="mb-1 text-danger">حذف كل المحلي (خطر)</h6>
                            <p class="mb-2">
                                يشمل «محلي فقط» — قد تفقد الملف إن لم يكن على السحابة.
                                اكتب <code>DELETE_LOCAL</code> للتأكيد.
                            </p>
                            <form method="POST" action="{{ route('app-storage.inventory.local-files.delete') }}"
                                  onsubmit="return confirm('تأكيد نهائي: حذف كل النسخ المحلية في التصفية بما فيها محلي فقط؟');">
                                @csrf
                                <input type="hidden" name="mode" value="all_local">
                                <input type="hidden" name="allow_orphan_local" value="1">
                                @if($filters['disk'])<input type="hidden" name="filter_disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="filter_source" value="{{ $filters['source'] }}">@endif
                                @if($filters['status'])<input type="hidden" name="filter_status" value="{{ $filters['status'] }}">@endif
                                <div class="input-group input-group-sm mb-2" style="max-width:280px;">
                                    <input type="text" name="confirm_orphan" class="form-control" placeholder="DELETE_LOCAL" autocomplete="off">
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm" @disabled(($summary['total'] ?? 0) < 1)>
                                    حذف كل المحلي في التصفية
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($deleteReport['details']))
            <div class="lf-card">
                <div class="lf-card-head">
                    <h6>تقرير آخر عملية حذف</h6>
                    <span class="small text-muted">
                        نجح {{ $deleteReport['cleaned'] }} /
                        تُخطى {{ $deleteReport['skipped'] }} /
                        فشل {{ $deleteReport['failed'] }}
                    </span>
                </div>
                <div class="lf-card-body p-0">
                    <div class="table-responsive" style="max-height:240px; overflow:auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>القرص</th><th>المسار</th><th>النتيجة</th><th>الرسالة</th><th>بعد الحذف</th></tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($deleteReport['details'], 0, 150) as $detail)
                                    <tr>
                                        <td><code>{{ $detail['disk'] ?? '' }}</code></td>
                                        <td class="text-start"><small>{{ $detail['path'] ?? '' }}</small></td>
                                        <td>
                                            @php $act = $detail['action'] ?? ''; @endphp
                                            <span class="badge {{ $act === 'cleaned' ? 'badge-soft-success' : ($act === 'skipped' ? 'badge-soft-warning' : 'badge-soft-danger') }}">
                                                {{ $act }}
                                            </span>
                                        </td>
                                        <td class="text-start"><small>{{ $detail['message'] ?? '' }}</small></td>
                                        <td><small>{{ $statusLabels[$detail['after_status'] ?? ''] ?? ($detail['after_status'] ?? '—') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div class="lf-card">
            <div class="lf-card-head"><h6>تصفية</h6></div>
            <div class="lf-card-body">
                <form method="GET" action="{{ route('app-storage.inventory.local-files') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">الحالة</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">الكل (محلي + نسختان)</option>
                            <option value="both" @selected($filters['status'] === 'both')>نسختان فقط (آمن)</option>
                            <option value="local_only" @selected($filters['status'] === 'local_only')>محلي فقط</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">القرص</label>
                        <select name="disk" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($disks as $diskName)
                                <option value="{{ $diskName }}" @selected($filters['disk'] === $diskName)>{{ $diskName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">المصدر</label>
                        <select name="source" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($sources as $source)
                                <option value="{{ $source['key'] }}" @selected($filters['source'] === $source['key'])>{{ $source['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit">تطبيق</button>
                        <a href="{{ route('app-storage.inventory.local-files') }}" class="btn btn-outline-secondary btn-sm">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="lf-card">
            <div class="lf-card-head">
                <div>
                    <h6>قائمة النسخ المحلية</h6>
                    <div class="small text-muted">{{ count($items) }} ملف — حدّد للحذف الجماعي للمحدد</div>
                </div>
            </div>
            <div class="lf-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th style="width:2.2rem"><input type="checkbox" id="lf-select-all"></th>
                                <th>المصدر</th>
                                <th>#</th>
                                <th>القرص</th>
                                <th>المسار</th>
                                <th>الحالة</th>
                                <th>المحلي</th>
                                <th>السحابة</th>
                                <th>الحجم المحلي</th>
                                <th>حذف المحلي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php $st = $item['status'] ?? ''; @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="lf-check"
                                               value="{{ $item['selection_key'] }}"
                                               data-safe="{{ !empty($item['can_safe_delete']) ? '1' : '0' }}">
                                    </td>
                                    <td class="text-start ps-3">{{ $item['source_label'] ?? '' }}</td>
                                    <td>{{ $item['entity_id'] ?? '—' }}</td>
                                    <td><code class="small">{{ $item['disk'] ?? '' }}</code></td>
                                    <td class="text-start"><small class="text-break">{{ $item['path'] ?? '' }}</small></td>
                                    <td>
                                        <span class="badge {{ $st === 'both' ? 'badge-soft-info' : 'badge-soft-warning' }}">
                                            {{ $item['status_label'] ?? ($statusLabels[$st] ?? $st) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            @forelse($item['local_locations'] ?? [] as $loc)
                                                {{ $loc['storage_name'] ?? 'محلي' }}
                                                @if(!$loop->last)<br>@endif
                                            @empty —
                                            @endforelse
                                        </small>
                                    </td>
                                    <td>
                                        @if(!empty($item['cloud_confirmed']))
                                            <span class="badge badge-soft-success">مؤكدة</span>
                                            <div class="small text-muted mt-1">
                                                @foreach($item['cloud_locations'] ?? [] as $loc)
                                                    {{ $loc['storage_name'] ?? 'سحابة' }}@if(!$loop->last)<br>@endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge badge-soft-danger">غير موجودة</span>
                                        @endif
                                    </td>
                                    <td>{{ $formatBytes($item['local_bytes'] ?? 0) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('app-storage.inventory.local-files.delete') }}" class="d-inline"
                                              onsubmit="return confirm(@json(!empty($item['can_safe_delete'])
                                                  ? 'حذف النسخة المحلية فقط؟ السحابة ستبقى.'
                                                  : 'تحذير: لا توجد سحابة مؤكدة. سيُحذف المحلي وقد تفقد الملف. هل أنت متأكد؟'));">
                                            @csrf
                                            <input type="hidden" name="mode" value="single">
                                            <input type="hidden" name="disk" value="{{ $item['disk'] }}">
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            @if($filters['disk'])<input type="hidden" name="filter_disk" value="{{ $filters['disk'] }}">@endif
                                            @if($filters['source'])<input type="hidden" name="filter_source" value="{{ $filters['source'] }}">@endif
                                            @if($filters['status'])<input type="hidden" name="filter_status" value="{{ $filters['status'] }}">@endif
                                            @if(empty($item['can_safe_delete']))
                                                <input type="hidden" name="allow_orphan_local" value="1">
                                            @endif
                                            <button type="submit"
                                                    class="btn btn-sm {{ !empty($item['can_safe_delete']) ? 'btn-outline-warning' : 'btn-outline-danger' }}"
                                                    title="{{ !empty($item['can_safe_delete']) ? 'حذف آمن — السحابة مؤكدة' : 'خطر — محلي فقط' }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        لا توجد نسخ محلية في التصفية الحالية.
                                        @if(($summary['total'] ?? 0) === 0)
                                            إما أن كل شيء سحابة فقط، أو أعد التحليل.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="lf-bar" id="lf-selection-bar">
            <div class="fw-semibold"><span id="lf-count">0</span> محدد</div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" id="lf-allow-orphan" value="1">
                    <label class="form-check-label small" for="lf-allow-orphan">يشمل «محلي فقط»</label>
                </div>
                <form method="POST" action="{{ route('app-storage.inventory.local-files.delete') }}" id="lf-selected-form"
                      onsubmit="return confirm('حذف النسخ المحلية للمحدد؟ السحابة لن تُمس.');">
                    @csrf
                    <input type="hidden" name="mode" value="selected">
                    <div id="lf-keys"></div>
                    <input type="hidden" name="allow_orphan_local" id="lf-allow-orphan-input" value="0">
                    @if($filters['disk'])<input type="hidden" name="filter_disk" value="{{ $filters['disk'] }}">@endif
                    @if($filters['source'])<input type="hidden" name="filter_source" value="{{ $filters['source'] }}">@endif
                    @if($filters['status'])<input type="hidden" name="filter_status" value="{{ $filters['status'] }}">@endif
                    <button type="submit" class="btn btn-warning btn-sm" id="lf-delete-selected" disabled>
                        حذف المحلي للمحدد
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@stop

@section('script')
<script>
const selectAll = document.getElementById('lf-select-all');
const checks = () => document.querySelectorAll('.lf-check');
const bar = document.getElementById('lf-selection-bar');
const countEl = document.getElementById('lf-count');
const keysBox = document.getElementById('lf-keys');
const btn = document.getElementById('lf-delete-selected');
const orphanCb = document.getElementById('lf-allow-orphan');
const orphanInput = document.getElementById('lf-allow-orphan-input');

function refreshSelection() {
    const selected = [...checks()].filter(c => c.checked);
    keysBox.innerHTML = '';
    selected.forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'keys[]';
        input.value = c.value;
        keysBox.appendChild(input);
    });
    countEl.textContent = selected.length;
    btn.disabled = selected.length === 0;
    bar.classList.toggle('is-visible', selected.length > 0);
    orphanInput.value = orphanCb?.checked ? '1' : '0';
}

selectAll?.addEventListener('change', () => {
    checks().forEach(c => c.checked = selectAll.checked);
    refreshSelection();
});
checks().forEach(c => c.addEventListener('change', refreshSelection));
orphanCb?.addEventListener('change', refreshSelection);
</script>
@stop
