@php
    $location = $location ?? null;
    $status = is_array($location) ? ($location['status'] ?? null) : null;
    $labels = [
        'cloud_only' => 'الصورة على السحابة فقط',
        'local_only' => 'الصورة على السيرفر المحلي فقط',
        'both' => 'الصورة موجودة محلياً وعلى السحابة',
        'missing' => 'مسار الصورة مسجّل لكن الملف غير موجود',
    ];
    $title = $labels[$status] ?? 'موقع التخزين غير معروف';
@endphp

@if($status)
    <span class="storage-loc-badge storage-loc-badge--{{ $status }}" title="{{ $title }}">
        @if($status === 'cloud_only')
            <i class="fas fa-cloud"></i>
        @elseif($status === 'local_only')
            <i class="fas fa-hdd"></i>
        @elseif($status === 'both')
            <i class="fas fa-cloud"></i>
            <i class="fas fa-hdd"></i>
        @else
            <i class="fas fa-exclamation-triangle"></i>
        @endif
    </span>
@endif

@once
<style>
    .admin-users-table__avatar { position: relative; }
    .storage-loc-badge {
        position: absolute;
        left: -4px;
        bottom: -4px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 1px;
        min-width: 18px;
        height: 18px;
        padding: 0 3px;
        border-radius: 999px;
        border: 1px solid #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, .18);
        font-size: .58rem;
        line-height: 1;
    }
    .storage-loc-badge--cloud_only { background: #e0f2fe; color: #0369a1; }
    .storage-loc-badge--local_only { background: #ffedd5; color: #c2410c; }
    .storage-loc-badge--both { background: #ede9fe; color: #6d28d9; min-width: 22px; }
    .storage-loc-badge--missing { background: #fee2e2; color: #b91c1c; }
</style>
@endonce
