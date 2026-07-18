@extends('admin.layouts.master')

@section('page-title')
    جرد الملفات والترحيل
@stop

@section('styles')
<style>
    .inv-page { --inv-ink: #1e293b; --inv-muted: #64748b; --inv-line: #e2e8f0; --inv-soft: #f8fafc; --inv-accent: #0f766e; --inv-accent-soft: #ccfbf1; --inv-warn: #b45309; --inv-danger: #b91c1c; }
    .inv-page .inv-hero {
        background: linear-gradient(135deg, #0f766e 0%, #134e4a 55%, #1e293b 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .inv-page .inv-hero::after {
        content: '';
        position: absolute;
        inset: auto -20% -40% auto;
        width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,.12), transparent 70%);
        pointer-events: none;
    }
    .inv-page .inv-hero h5 { color: #fff; margin: 0 0 .35rem; font-weight: 700; }
    .inv-page .inv-hero p { color: rgba(255,255,255,.82); margin: 0; max-width: 42rem; }
    .inv-page .inv-hero .btn { border-color: rgba(255,255,255,.35); color: #fff; }
    .inv-page .inv-hero .btn:hover { background: rgba(255,255,255,.12); color: #fff; }
    .inv-page .inv-stat {
        border: 1px solid var(--inv-line);
        border-radius: .9rem;
        background: #fff;
        padding: 1rem .85rem;
        height: 100%;
        text-align: center;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .inv-page .inv-stat:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, .06); transform: translateY(-1px); }
    .inv-page .inv-stat .label { font-size: .78rem; color: var(--inv-muted); margin-bottom: .25rem; }
    .inv-page .inv-stat .value { font-size: 1.55rem; font-weight: 700; color: var(--inv-ink); line-height: 1.2; }
    .inv-page .inv-stat .meta { font-size: .75rem; color: var(--inv-muted); margin-top: .2rem; }
    .inv-page .inv-stat.is-cloud .value { color: #15803d; }
    .inv-page .inv-stat.is-local .value { color: #b45309; }
    .inv-page .inv-stat.is-both .value { color: #0369a1; }
    .inv-page .inv-stat.is-missing .value { color: #b91c1c; }
    .inv-page .inv-flow {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 991px) { .inv-page .inv-flow { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 575px) { .inv-page .inv-flow { grid-template-columns: 1fr; } }
    .inv-page .inv-step {
        border: 1px solid var(--inv-line);
        border-radius: .9rem;
        background: #fff;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: .55rem;
    }
    .inv-page .inv-step .step-num {
        width: 1.6rem; height: 1.6rem;
        border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700;
        background: var(--inv-accent-soft); color: var(--inv-accent);
    }
    .inv-page .inv-step h6 { margin: 0; font-size: .95rem; font-weight: 700; color: var(--inv-ink); }
    .inv-page .inv-step p { margin: 0; font-size: .78rem; color: var(--inv-muted); line-height: 1.55; flex: 1; }
    .inv-page .inv-step .step-actions { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .25rem; }
    .inv-page .inv-card {
        border: 1px solid var(--inv-line);
        border-radius: 1rem;
        background: #fff;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .inv-page .inv-card-head {
        padding: .95rem 1.15rem;
        border-bottom: 1px solid var(--inv-line);
        background: var(--inv-soft);
        display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;
    }
    .inv-page .inv-card-head h6 { margin: 0; font-weight: 700; color: var(--inv-ink); }
    .inv-page .inv-card-head .hint { font-size: .78rem; color: var(--inv-muted); }
    .inv-page .inv-card-body { padding: 1.15rem; }
    .inv-page .inv-phase {
        border: 1px solid var(--inv-line);
        border-radius: .85rem;
        padding: .85rem;
        background: var(--inv-soft);
        height: 100%;
    }
    .inv-page .inv-phase .title { font-weight: 700; color: var(--inv-ink); margin-bottom: .2rem; }
    .inv-page .inv-phase .desc { font-size: .75rem; color: var(--inv-muted); margin-bottom: .65rem; min-height: 2.2rem; }
    .inv-page .inv-note {
        border-radius: .9rem;
        border: 1px solid #99f6e4;
        background: linear-gradient(90deg, #f0fdfa, #fff);
        padding: .9rem 1.1rem;
        margin-bottom: 1.25rem;
        font-size: .88rem;
        color: #115e59;
    }
    .inv-page .inv-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--inv-muted);
    }
    .inv-page .inv-empty .icon {
        width: 3.2rem; height: 3.2rem; margin: 0 auto .75rem;
        border-radius: 999px; display: flex; align-items: center; justify-content: center;
        background: var(--inv-accent-soft); color: var(--inv-accent); font-size: 1.25rem;
    }
    .inv-page .inv-selection-bar {
        position: sticky; bottom: 1rem; z-index: 20;
        display: none;
        margin-top: 1rem;
        border: 1px solid var(--inv-line);
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(8px);
        border-radius: 999px;
        box-shadow: 0 10px 30px rgba(15,23,42,.12);
        padding: .55rem .85rem;
        align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;
    }
    .inv-page .inv-selection-bar.is-visible { display: flex; }
    .inv-page .inv-selection-bar .count { font-size: .85rem; font-weight: 600; color: var(--inv-ink); }
    .inv-page .table thead th { white-space: nowrap; font-size: .82rem; }
    .inv-page .badge-soft-success { background: #dcfce7; color: #166534; }
    .inv-page .badge-soft-warning { background: #ffedd5; color: #9a3412; }
    .inv-page .badge-soft-info { background: #e0f2fe; color: #075985; }
    .inv-page .badge-soft-danger { background: #fee2e2; color: #991b1b; }
</style>
@stop

@section('content')
@php
    $formatBytes = fn ($b) => $inventoryService->formatBytes((int) $b);
    $phaseLabels = $phaseLabels ?? config('storage_inventory.phase_labels', []);
    $hasScan = !empty($scannedAt);
@endphp
<div class="main-content app-content inv-page">
    <div class="container-fluid">

        <div class="inv-hero">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h5 class="page-title fs-21">جرد الملفات والترحيل إلى السحابة</h5>
                    <p>
                        @if($hasScan)
                            آخر تحليل: {{ $scannedAt }}
                        @else
                            ابدأ بـ<strong>تحليل</strong> مواقع الملفات لمعرفة ما هو محلي، سحابي، أو مكرر في المكانين.
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('app-storage.inventory.cloud-files') }}" class="btn btn-sm btn-light text-dark fw-semibold">
                        <i class="fas fa-cloud me-1"></i> استعراض السحابة
                    </a>
                    <a href="{{ route('app-storage.inventory.browse-local') }}" class="btn btn-sm btn-light text-dark fw-semibold">
                        <i class="fas fa-folder-open me-1"></i> استعراض المحلي
                    </a>
                    <a href="{{ route('app-storage.inventory.local-files') }}" class="btn btn-sm btn-light text-dark fw-semibold">
                        <i class="fas fa-hdd me-1"></i> إدارة النسخ المحلية
                    </a>
                    <a href="{{ route('app-storage.configs.index') }}" class="btn btn-sm btn-outline-light">إعدادات التخزين</a>
                    <a href="{{ route('storage-disk-mappings.index') }}" class="btn btn-sm btn-outline-light">Disk Mappings</a>
                </div>
            </div>
        </div>

        <div class="inv-note">
            <strong>لماذا تظهر «نسختان»؟</strong>
            غالباً بعد ترحيل للسحابة دون حذف النسخة المحلية، أو رفع قديم بقي محلياً بعد ربط S3.
            المسار الآمن: <strong>تحليل → معاينة → ترحيل → تحقق → تنظيف محلي</strong>.
            التنظيف يحذف المحلي فقط بعد تأكيد وجود الملف على السحابة.
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

        {{-- Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="inv-stat">
                    <div class="label">الإجمالي</div>
                    <div class="value">{{ $summary['total'] }}</div>
                    <div class="meta">{{ $formatBytes($summary['total_bytes'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="inv-stat is-cloud">
                    <div class="label">سحابة فقط</div>
                    <div class="value">{{ $summary['cloud_only'] }}</div>
                    <div class="meta">موجود على S3/السحابة</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('app-storage.inventory.local-files', ['status' => 'local_only']) }}" class="text-decoration-none d-block">
                    <div class="inv-stat is-local">
                        <div class="label">محلي فقط</div>
                        <div class="value">{{ $summary['local_only'] }}</div>
                        <div class="meta">{{ $formatBytes($summary['local_only_bytes'] ?? 0) }} · إدارة ←</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('app-storage.inventory.local-files', ['status' => 'both']) }}" class="text-decoration-none d-block">
                    <div class="inv-stat is-both">
                        <div class="label">نسختان</div>
                        <div class="value">{{ $summary['both'] }}</div>
                        <div class="meta">{{ $formatBytes($summary['both_bytes'] ?? 0) }} · إدارة ←</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="inv-stat is-missing">
                    <div class="label">مفقود</div>
                    <div class="value">{{ $summary['missing'] }}</div>
                    <div class="meta">مسجّل بدون ملف</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="inv-stat">
                    <div class="label">قابل للترحيل</div>
                    <div class="value" style="font-size:1.1rem;">{{ $formatBytes(($summary['local_only_bytes'] ?? 0) + ($summary['both_bytes'] ?? 0)) }}</div>
                    <div class="meta">محلي + مكرر</div>
                </div>
            </div>
        </div>

        {{-- Workflow steps with clear actions --}}
        <div class="inv-card">
            <div class="inv-card-head">
                <div>
                    <h6>مسار العمل</h6>
                    <div class="hint">كل زر موضّح أدناه — نفّذ الخطوات بالترتيب للحصول على نتيجة آمنة</div>
                </div>
            </div>
            <div class="inv-card-body">
                <div class="inv-flow">
                    {{-- 1 Analyze --}}
                    <div class="inv-step">
                        <span class="step-num">1</span>
                        <h6><i class="fas fa-search me-1" style="color:#0f766e"></i> تحليل</h6>
                        <p>يفحص قاعدة البيانات والأقراص ويصنّف كل ملف: محلي / سحابة / نسختان / مفقود. لا ينقل ولا يحذف شيئاً.</p>
                        <div class="step-actions">
                            <form method="POST" action="{{ route('app-storage.inventory.scan') }}">
                                @csrf
                                @foreach($filters as $key => $value)
                                    @if($value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-chart-pie me-1"></i> تحليل الآن
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- 2 Preview --}}
                    <div class="inv-step">
                        <span class="step-num">2</span>
                        <h6><i class="fas fa-eye me-1"></i> معاينة</h6>
                        <p>تجربة جافة (dry-run): تعرض الملفات المؤهلة للترحيل والحجم والتحذيرات دون رفع فعلي.</p>
                        <div class="step-actions">
                            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $filters['status'] ?: 'local_only' }}">
                                <input type="hidden" name="dry_run" value="1">
                                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                                <button type="submit" class="btn btn-sm btn-outline-primary" @disabled(!$hasScan)>
                                    معاينة الترحيل
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- 3 Migrate --}}
                    <div class="inv-step">
                        <span class="step-num">3</span>
                        <h6><i class="fas fa-cloud-upload-alt me-1"></i> ترحيل</h6>
                        <p>يرفع الملفات إلى السحابة عبر قائمة الانتظار. «محلي فقط» للملفات غير الموجودة سحابياً، و«نسختان» لإكمال النسخة السحابية إن لزم.</p>
                        <div class="step-actions">
                            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}"
                                  onsubmit="return confirm('ترحيل الملفات المحلية فقط إلى السحابة عبر Queue؟');">
                                @csrf
                                <input type="hidden" name="status" value="local_only">
                                <input type="hidden" name="use_queue" value="1">
                                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                                <button type="submit" class="btn btn-sm btn-success" @disabled(!$hasScan) title="يرفع الملفات الموجودة محلياً فقط وغير الموجودة على السحابة">
                                    ترحيل محلي
                                </button>
                            </form>
                            <form method="POST" action="{{ route('app-storage.inventory.migrate') }}"
                                  onsubmit="return confirm('ترحيل الملفات بحالة نسختين (إن كانت السحابة ناقصة)؟');">
                                @csrf
                                <input type="hidden" name="status" value="both">
                                <input type="hidden" name="use_queue" value="1">
                                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                                <button type="submit" class="btn btn-sm btn-outline-success" @disabled(!$hasScan) title="للملفات الموجودة في المحلي والسحابة — يرفع إن نقصت النسخة السحابية">
                                    ترحيل مكرر
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- 4 Verify --}}
                    <div class="inv-step">
                        <span class="step-num">4</span>
                        <h6><i class="fas fa-check-double me-1"></i> تحقق</h6>
                        <p>يعيد فحص الملفات (غالباً «نسختان») ويؤكد هل النسخة السحابية موجودة فعلاً قبل أي حذف.</p>
                        <div class="step-actions">
                            <form method="POST" action="{{ route('app-storage.inventory.verify') }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $filters['status'] ?: 'both' }}">
                                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                                <button type="submit" class="btn btn-sm btn-outline-primary" @disabled(!$hasScan)>
                                    تحقق الآن
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- 5 Cleanup --}}
                    <div class="inv-step">
                        <span class="step-num">5</span>
                        <h6><i class="fas fa-broom me-1"></i> تنظيف</h6>
                        <p>لحذف المحلي بدقة (مفرد / جماعي / الكل الآمن) افتح صفحة إدارة النسخ المحلية. السحابة محمية دائماً.</p>
                        <div class="step-actions">
                            <a href="{{ route('app-storage.inventory.local-files', ['status' => 'both']) }}"
                               class="btn btn-sm btn-warning" @class(['disabled' => !$hasScan || ($summary['both'] ?? 0) < 1])>
                                <i class="fas fa-hdd me-1"></i> إدارة المحلي
                            </a>
                            <form method="POST" action="{{ route('app-storage.inventory.cleanup-local') }}"
                                  onsubmit="return confirm('حذف النسخ المحلية للملفات المؤكدة كنسختين فقط؟ لن تُحذف النسخة السحابية.');">
                                @csrf
                                @if($filters['disk'])<input type="hidden" name="disk" value="{{ $filters['disk'] }}">@endif
                                @if($filters['source'])<input type="hidden" name="source" value="{{ $filters['source'] }}">@endif
                                <button type="submit" class="btn btn-sm btn-outline-warning" @disabled(!$hasScan || ($summary['both'] ?? 0) < 1)
                                        title="حذف سريع لكل المكرر في التصفية الحالية">
                                    تنظيف سريع
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <span class="small text-muted align-self-center me-1">تصدير التقرير الحالي:</span>
                    <a href="{{ route('app-storage.inventory.export', array_filter(array_merge($filters, ['format' => 'csv']))) }}"
                       class="btn btn-sm btn-outline-secondary" @class(['disabled' => !$hasScan])>
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </a>
                    <a href="{{ route('app-storage.inventory.export', array_filter(array_merge($filters, ['format' => 'json']))) }}"
                       class="btn btn-sm btn-outline-secondary" @class(['disabled' => !$hasScan])>
                        <i class="fas fa-file-code me-1"></i> JSON
                    </a>
                </div>
            </div>
        </div>

        @if($progress)
            <div class="inv-card" id="migration-progress-card">
                <div class="inv-card-head">
                    <h6>تقدم الترحيل</h6>
                    <span class="hint" id="migration-progress-status">{{ $progress['status'] ?? '' }}</span>
                </div>
                <div class="inv-card-body">
                    @php
                        $pct = ($progress['total'] ?? 0) > 0
                            ? round((($progress['completed'] ?? 0) / $progress['total']) * 100)
                            : 0;
                    @endphp
                    <div class="progress mb-2" style="height: 10px; border-radius: 999px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%" id="migration-progress-bar"></div>
                    </div>
                    <div class="small text-muted" id="migration-progress-text">
                        {{ $progress['completed'] ?? 0 }} / {{ $progress['total'] ?? 0 }}
                        — نُقل: {{ $progress['migrated'] ?? 0 }},
                        تُخطى: {{ $progress['skipped'] ?? 0 }},
                        فشل: {{ $progress['failed'] ?? 0 }}
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($dryRunPreview))
            <div class="inv-card">
                <div class="inv-card-head">
                    <h6>نتيجة المعاينة</h6>
                    <span class="hint">بدون رفع فعلي</span>
                </div>
                <div class="inv-card-body">
                    <div class="row g-2 mb-3 small">
                        <div class="col-auto"><span class="badge badge-soft-success">مؤهل: {{ $dryRunPreview['eligible_count'] }}</span></div>
                        <div class="col-auto"><span class="badge badge-soft-info">متخطى: {{ $dryRunPreview['skipped_count'] }}</span></div>
                        <div class="col-auto"><span class="badge badge-soft-danger">مفقود: {{ $dryRunPreview['missing_count'] }}</span></div>
                        <div class="col-auto"><span class="badge badge-soft-warning">تحذيرات: {{ $dryRunPreview['warning_count'] ?? 0 }}</span></div>
                        <div class="col-auto text-muted">الحجم ≈ {{ $formatBytes($dryRunPreview['total_bytes'] ?? 0) }}</div>
                    </div>
                    @if(!empty($dryRunPreview['warnings']))
                        <ul class="small text-warning mb-3">
                            @foreach(array_slice($dryRunPreview['warnings'], 0, 10) as $warn)
                                <li><code>{{ $warn['path'] ?? '' }}</code> — {{ $warn['message'] ?? '' }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($dryRunPreview['items']))
                        <div class="table-responsive" style="max-height: 220px; overflow:auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>المسار</th><th>الحالة</th><th>الحجم</th><th>الهدف</th></tr></thead>
                                <tbody>
                                    @foreach(array_slice($dryRunPreview['items'], 0, 50) as $row)
                                        <tr>
                                            <td class="text-start"><small>{{ $row['path'] ?? '' }}</small></td>
                                            <td>{{ $statusLabels[$row['status'] ?? ''] ?? ($row['status'] ?? '') }}</td>
                                            <td>{{ $formatBytes($row['size'] ?? 0) }}</td>
                                            <td>{{ $row['cloud_target'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(!empty($migrateReport['details']))
            <div class="inv-card">
                <div class="inv-card-head"><h6>تفاصيل نتيجة الترحيل</h6></div>
                <div class="inv-card-body">
                    <div class="table-responsive" style="max-height: 220px; overflow:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>المسار</th><th>النتيجة</th><th>الرسالة</th></tr></thead>
                            <tbody>
                                @foreach(array_slice($migrateReport['details'], 0, 100) as $detail)
                                    <tr>
                                        <td class="text-start"><small>{{ $detail['path'] ?? '' }}</small></td>
                                        <td>{{ $detail['action'] ?? '' }}</td>
                                        <td class="text-start"><small>{{ $detail['message'] ?? '' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($verifyReport))
            <div class="inv-card">
                <div class="inv-card-head"><h6>تقرير التحقق</h6></div>
                <div class="inv-card-body">
                    <p class="small mb-0 text-muted">
                        فُحص: {{ $verifyReport['checked'] }} —
                        سحابة مؤكدة: {{ $verifyReport['cloud_confirmed'] }} —
                        نسختان: {{ $verifyReport['both'] }} —
                        محلي فقط: {{ $verifyReport['local_only'] }} —
                        سحابة فقط: {{ $verifyReport['cloud_only'] }} —
                        مفقود: {{ $verifyReport['missing'] }}
                    </p>
                </div>
            </div>
        @endif

        @if(!empty($cleanupReport['details']))
            <div class="inv-card">
                <div class="inv-card-head"><h6>تفاصيل التنظيف المحلي</h6></div>
                <div class="inv-card-body">
                    <div class="table-responsive" style="max-height: 200px; overflow:auto;">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>المسار</th><th>النتيجة</th><th>الرسالة</th></tr></thead>
                            <tbody>
                                @foreach(array_slice($cleanupReport['details'], 0, 100) as $detail)
                                    <tr>
                                        <td class="text-start"><small>{{ $detail['path'] ?? '' }}</small></td>
                                        <td>{{ $detail['action'] ?? '' }}</td>
                                        <td class="text-start"><small>{{ $detail['message'] ?? '' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Phases --}}
        <div class="inv-card">
            <div class="inv-card-head">
                <div>
                    <h6>تحليل حسب المرحلة</h6>
                    <div class="hint">حلّل مجموعة محددة أولاً (موصى به للكميات الكبيرة) ثم عاين قبل الترحيل</div>
                </div>
            </div>
            <div class="inv-card-body">
                <div class="row g-3">
                    @foreach($phases as $phaseKey => $phaseSources)
                        @php $label = $phaseLabels[$phaseKey] ?? $phaseKey; @endphp
                        <div class="col-md-6 col-xl-3">
                            <div class="inv-phase">
                                <div class="title">{{ $label }}</div>
                                <div class="desc" title="{{ implode(', ', $phaseSources) }}">
                                    يشمل: {{ implode('، ', array_slice($phaseSources, 0, 3)) }}{{ count($phaseSources) > 3 ? '…' : '' }}
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('app-storage.inventory.scan') }}">
                                        @csrf
                                        <input type="hidden" name="phase" value="{{ $phaseKey }}">
                                        <button type="submit" class="btn btn-sm btn-primary" title="تحليل ملفات هذه المرحلة فقط">
                                            <i class="fas fa-chart-pie me-1"></i> تحليل
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('app-storage.inventory.migrate') }}">
                                        @csrf
                                        <input type="hidden" name="phase" value="{{ $phaseKey }}">
                                        <input type="hidden" name="status" value="local_only">
                                        <input type="hidden" name="dry_run" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="معاينة الترحيل لهذه المرحلة دون رفع">
                                            معاينة
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if(!empty($summary['by_disk']) || !empty($summary['by_source']))
            <div class="row g-3 mb-4">
                @if(!empty($summary['by_disk']))
                    <div class="col-lg-6">
                        <div class="inv-card h-100 mb-0">
                            <div class="inv-card-head"><h6>ملخص حسب القرص</h6></div>
                            <div class="inv-card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>القرص</th><th>العدد</th><th>الحجم</th></tr></thead>
                                        <tbody>
                                            @foreach($summary['by_disk'] as $diskName => $row)
                                                <tr>
                                                    <td><code>{{ $diskName }}</code></td>
                                                    <td>{{ $row['count'] }}</td>
                                                    <td>{{ $formatBytes($row['bytes']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if(!empty($summary['by_source']))
                    <div class="col-lg-6">
                        <div class="inv-card h-100 mb-0">
                            <div class="inv-card-head"><h6>ملخص حسب المصدر</h6></div>
                            <div class="inv-card-body">
                                <div class="table-responsive" style="max-height: 240px; overflow:auto;">
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>المصدر</th><th>العدد</th><th>الحجم</th></tr></thead>
                                        <tbody>
                                            @foreach($summary['by_source'] as $src)
                                                <tr>
                                                    <td>{{ $src['label'] }}</td>
                                                    <td>{{ $src['count'] }}</td>
                                                    <td>{{ $formatBytes($src['bytes']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Filters --}}
        <div class="inv-card">
            <div class="inv-card-head">
                <div>
                    <h6>تصفية النتائج</h6>
                    <div class="hint">تصفية العرض فقط — لا تغيّر التحليل المخزّن</div>
                </div>
            </div>
            <div class="inv-card-body">
                <form method="GET" action="{{ route('app-storage.inventory.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">القرص المنطقي</label>
                        <select name="disk" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($disks as $diskName)
                                <option value="{{ $diskName }}" @selected($filters['disk'] === $diskName)>{{ $diskName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">مصدر الملف</label>
                        <select name="source" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($sources as $source)
                                <option value="{{ $source['key'] }}" @selected($filters['source'] === $source['key'])>{{ $source['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">حالة الموقع</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($statusLabels as $statusOption => $statusLabel)
                                <option value="{{ $statusOption }}" @selected($filters['status'] === $statusOption)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">تطبيق</button>
                        <a href="{{ route('app-storage.inventory.index') }}" class="btn btn-outline-secondary btn-sm">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results table --}}
        <div class="inv-card">
            <div class="inv-card-head">
                <div>
                    <h6>تفاصيل الملفات</h6>
                    <div class="hint">
                        @if($hasScan)
                            {{ count($items) }} نتيجة بعد التصفية — حدّد صفوفاً لتنفيذ إجراء على المحدد فقط
                        @else
                            لا يوجد تحليل بعد — اضغط «تحليل الآن» أعلاه
                        @endif
                    </div>
                </div>
            </div>
            <div class="inv-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-center mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:2.5rem"><input type="checkbox" id="select-all-files" title="تحديد الكل القابل للترحيل"></th>
                                <th>المصدر</th>
                                <th>#</th>
                                <th>القرص</th>
                                <th>المسار</th>
                                <th>الحالة</th>
                                <th>المواقع</th>
                                <th>الحجم</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                                @php $st = $item['status'] ?? 'missing'; @endphp
                                <tr>
                                    <td>
                                        @if(in_array($st, ['local_only', 'both'], true))
                                            <input type="checkbox" class="file-checkbox" value="{{ $item['path'] }}">
                                        @endif
                                    </td>
                                    <td class="text-start ps-3">{{ $item['source_label'] ?? '' }}</td>
                                    <td>{{ $item['entity_id'] ?? '—' }}</td>
                                    <td><code class="small">{{ $item['disk'] ?? '' }}</code></td>
                                    <td class="text-start"><small class="text-break">{{ $item['path'] ?? '' }}</small></td>
                                    <td>
                                        <span class="badge {{ match($st) {
                                            'cloud_only' => 'badge-soft-success',
                                            'local_only' => 'badge-soft-warning',
                                            'both' => 'badge-soft-info',
                                            default => 'badge-soft-danger',
                                        } }}">{{ $item['status_label'] ?? ($statusLabels[$st] ?? $st) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @forelse(($item['locations'] ?? []) as $loc)
                                                {{ $loc['storage_name'] ?? '?' }}
                                                <span class="opacity-75">({{ $loc['kind_label'] ?? (!empty($loc['is_cloud']) ? 'سحابة' : 'محلي') }})</span>
                                                @if(!$loop->last)<br>@endif
                                            @empty
                                                —
                                            @endforelse
                                        </small>
                                    </td>
                                    <td>{{ $item['size_human'] ?? $formatBytes($item['size'] ?? 0) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light border"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#loc-detail-{{ $index }}">
                                            تفاصيل
                                        </button>
                                    </td>
                                    <td>
                                        @if(!empty($item['entity_url']))
                                            <a href="{{ $item['entity_url'] }}" class="btn btn-sm btn-light border" target="_blank">عرض</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr class="collapse" id="loc-detail-{{ $index }}">
                                    <td colspan="10" class="text-start bg-light">
                                        <div class="p-3 small">
                                            <strong class="d-block mb-2">كل مواقع التخزين المكتشفة:</strong>
                                            @if(empty($item['locations']))
                                                <span class="text-muted">لا توجد مواقع</span>
                                            @else
                                                <ul class="mb-0">
                                                    @foreach($item['locations'] as $loc)
                                                        <li class="mb-1">
                                                            <strong>{{ $loc['storage_name'] ?? '—' }}</strong>
                                                            — <code>{{ $loc['driver'] ?? '?' }}</code>
                                                            — {{ $loc['kind_label'] ?? '' }}
                                                            — {{ $loc['size_human'] ?? $formatBytes($loc['size'] ?? 0) }}
                                                            @if(!empty($loc['storage_config_id']))
                                                                — إعداد #{{ $loc['storage_config_id'] }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="inv-empty">
                                            <div class="icon"><i class="fas fa-folder-open"></i></div>
                                            @if(!$hasScan)
                                                <div class="fw-semibold text-dark mb-1">لم يُجرَ تحليل بعد</div>
                                                <p class="mb-3">التحليل يقرأ مسارات الملفات من قاعدة البيانات ويتحقق أين يوجد كل ملف فعلياً.</p>
                                                <form method="POST" action="{{ route('app-storage.inventory.scan') }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-chart-pie me-1"></i> بدء التحليل
                                                    </button>
                                                </form>
                                            @else
                                                <div class="fw-semibold text-dark mb-1">لا نتائج لهذه التصفية</div>
                                                <p class="mb-0">غيّر الفلاتر أو أعد التحليل لمصدر آخر.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sticky selection bar --}}
        <div class="inv-selection-bar" id="selection-bar">
            <div class="count"><span id="selected-count">0</span> ملف محدد</div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('app-storage.inventory.migrate') }}" id="selected-migrate-form" class="d-inline">
                    @csrf
                    <input type="hidden" name="use_queue" value="1">
                    <div id="selected-paths-container"></div>
                    <button type="submit" class="btn btn-success btn-sm" id="migrate-selected-btn" disabled
                        onclick="return confirm('ترحيل الملفات المحددة إلى السحابة؟');"
                        title="يرفع الملفات المحددة عبر Queue">
                        ترحيل المحدد
                    </button>
                </form>
                <form method="POST" action="{{ route('app-storage.inventory.verify') }}" id="selected-verify-form" class="d-inline">
                    @csrf
                    <div id="selected-verify-paths"></div>
                    <button type="submit" class="btn btn-outline-primary btn-sm" id="verify-selected-btn" disabled
                        title="يعيد فحص المحدد ويؤكد وجود السحابة">
                        تحقق من المحدد
                    </button>
                </form>
                <form method="POST" action="{{ route('app-storage.inventory.cleanup-local') }}" id="selected-cleanup-form" class="d-inline"
                      onsubmit="return confirm('حذف النسخ المحلية للمحدد فقط بعد تحقق فوري من السحابة؟');">
                    @csrf
                    <div id="selected-cleanup-paths"></div>
                    <button type="submit" class="btn btn-warning btn-sm" id="cleanup-selected-btn" disabled
                        title="يحذف المحلي فقط إن وُجدت نسخة سحابية مؤكدة">
                        تنظيف المحدد
                    </button>
                </form>
            </div>
        </div>

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
    const checked = document.querySelectorAll('.file-checkbox:checked');
    fillPaths('selected-paths-container', 'migrate-selected-btn', checked);
    fillPaths('selected-verify-paths', 'verify-selected-btn', checked);
    fillPaths('selected-cleanup-paths', 'cleanup-selected-btn', checked);
    const bar = document.getElementById('selection-bar');
    const countEl = document.getElementById('selected-count');
    if (countEl) countEl.textContent = checked.length;
    if (bar) bar.classList.toggle('is-visible', checked.length > 0);
}

function fillPaths(containerId, btnId, checked) {
    const container = document.getElementById(containerId);
    const btn = document.getElementById(btnId);
    if (!container || !btn) return;
    container.innerHTML = '';
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
                text.textContent = `${p.completed} / ${p.total} — نُقل: ${p.migrated}, تُخطى: ${p.skipped}, فشل: ${p.failed}`;
            }
        });
}, 3000);
@endif
</script>
@stop
