@php
    $tier = $tier ?? 'silver';
    $isGold = $tier === 'gold';
@endphp
<span class="group-show-chip group-show-chip--sm group-show-chip--tier-{{ $tier }}">
    @if($isGold)
        <i class="fe fe-award me-1"></i>
    @else
        <i class="fe fe-shield me-1"></i>
    @endif
    {{ $isGold ? 'ذهبي' : 'فضي' }}
</span>
