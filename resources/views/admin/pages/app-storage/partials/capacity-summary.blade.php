@php
    $formatBytes = fn ($b) => $inventoryService->formatBytes((int) $b);
    $local = $capacitySummary['local'] ?? [];
    $cloud = $capacitySummary['cloud'] ?? [];
    $scannedAt = $capacitySummary['scanned_at'] ?? null;
    $server = $capacitySummary['server'] ?? null;
    $returnUrl = $returnUrl ?? url()->current();
    $query = request()->query();
    if ($query !== []) {
        $returnUrl .= (str_contains($returnUrl, '?') ? '&' : '?').http_build_query($query);
    }
@endphp

<div class="storage-capacity-banner mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h6 class="mb-1 fw-bold">سعة التخزين الكاملة</h6>
            @if($scannedAt)
                <div class="small text-muted">آخر حساب: {{ $scannedAt }} · يُحدَّث تلقائياً كل ساعة</div>
            @endif
        </div>
        <form method="POST" action="{{ route('app-storage.inventory.refresh-capacity') }}">
            @csrf
            <input type="hidden" name="return" value="{{ $returnUrl }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i> إعادة حساب السعة
            </button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="capacity-box capacity-box-local h-100">
                <div class="capacity-icon"><i class="fas fa-hdd"></i></div>
                <div class="capacity-content">
                    <div class="capacity-label">المحلي على السيرفر</div>
                    <div class="capacity-value">{{ $formatBytes($local['bytes'] ?? 0) }}</div>
                    <div class="capacity-meta">
                        {{ number_format($local['files'] ?? 0) }} ملف
                        · {{ $local['root'] ?? 'storage/app/public' }}
                    </div>
                    @if(!empty($local['error']))
                        <div class="small text-danger mt-1">{{ $local['error'] }}</div>
                    @elseif(empty($local['exists']))
                        <div class="small text-muted mt-1">المجلد غير موجود على السيرفر.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="capacity-box capacity-box-cloud h-100">
                <div class="capacity-icon"><i class="fas fa-cloud"></i></div>
                <div class="capacity-content">
                    <div class="capacity-label">السحابة (الإجمالي)</div>
                    <div class="capacity-value">{{ $formatBytes($cloud['bytes'] ?? 0) }}</div>
                    <div class="capacity-meta">{{ number_format($cloud['files'] ?? 0) }} ملف</div>
                    @if(!empty($cloud['error']))
                        <div class="small text-danger mt-1">{{ $cloud['error'] }}</div>
                    @endif
                    @if(!empty($cloud['configs']))
                        <div class="capacity-configs mt-2">
                            @foreach($cloud['configs'] as $configUsage)
                                <div class="small">
                                    <strong>{{ $configUsage['name'] }}</strong>
                                    ({{ $configUsage['driver'] }}) —
                                    {{ $formatBytes($configUsage['bytes'] ?? 0) }}
                                    · {{ number_format($configUsage['files'] ?? 0) }} ملف
                                    @if(!empty($configUsage['error']))
                                        <span class="text-danger">· {{ $configUsage['error'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($server && !empty($server['paths']))
        <div class="capacity-server mt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-server me-1 text-secondary"></i>
                    أكبر مستهلكي المساحة على السيرفر
                </h6>
                @if(!empty($server['disk_free']))
                    <span class="small text-muted">
                        القرص: {{ $formatBytes($server['disk_free']) }} حرة
                        @if(!empty($server['disk_total'])) من {{ $formatBytes($server['disk_total']) }} @endif
                    </span>
                @endif
            </div>

            <p class="small text-muted mb-2">
                ترحيل الملفات للسحابة يخفّض «ملفات عامة (محتوى)» فقط. إن كان الحجم الأكبر في
                السجلات أو أصول القالب فالترحيل لن يصغّر المشروع.
            </p>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 capacity-server-table">
                    <tbody>
                        @foreach($server['paths'] as $row)
                            @php $isBig = $row['bytes'] >= 100 * 1024 * 1024; @endphp
                            <tr class="{{ $isBig ? 'table-warning' : '' }}">
                                <td class="fw-semibold" style="width:16rem">
                                    {{ $row['label'] }}
                                    @if($isBig)
                                        <i class="fas fa-triangle-exclamation text-warning ms-1"></i>
                                    @endif
                                </td>
                                <td style="width:7rem" class="text-nowrap">{{ $formatBytes($row['bytes']) }}</td>
                                <td style="width:7rem" class="text-nowrap text-muted small">
                                    {{ $row['exists'] ? number_format($row['files']).' ملف' : 'غير موجود' }}
                                </td>
                                <td class="small text-muted">{{ $row['hint'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="fw-bold">الإجمالي</td>
                            <td class="fw-bold text-nowrap">{{ $formatBytes($server['total_bytes'] ?? 0) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>

<style>
    .storage-capacity-banner .capacity-server {
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
    }
    .storage-capacity-banner .capacity-server-table td {
        padding-block: .4rem;
    }

    .storage-capacity-banner .capacity-box {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        padding: 1.1rem 1.15rem;
    }
    .storage-capacity-banner .capacity-box-local { border-color: #fed7aa; background: linear-gradient(180deg, #fff7ed 0%, #fff 100%); }
    .storage-capacity-banner .capacity-box-cloud { border-color: #bae6fd; background: linear-gradient(180deg, #f0f9ff 0%, #fff 100%); }
    .storage-capacity-banner .capacity-icon {
        width: 2.75rem; height: 2.75rem; border-radius: .85rem;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 1.15rem;
    }
    .storage-capacity-banner .capacity-box-local .capacity-icon { background: #ffedd5; color: #c2410c; }
    .storage-capacity-banner .capacity-box-cloud .capacity-icon { background: #e0f2fe; color: #0369a1; }
    .storage-capacity-banner .capacity-label { font-size: .82rem; color: #64748b; margin-bottom: .15rem; }
    .storage-capacity-banner .capacity-value { font-size: 1.65rem; font-weight: 700; color: #1e293b; line-height: 1.15; }
    .storage-capacity-banner .capacity-meta { font-size: .78rem; color: #64748b; margin-top: .2rem; }
    .storage-capacity-banner .capacity-configs { border-top: 1px dashed #cbd5e1; padding-top: .55rem; }
</style>
