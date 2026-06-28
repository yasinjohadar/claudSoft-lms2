@php
    $subtitle = $subtitle ?? null;
    $action = $action ?? null;
@endphp
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <span class="avatar avatar-sm bg-primary-transparent">
            <i class="{{ $icon ?? 'fe fe-layers' }} text-primary"></i>
        </span>
        <div>
            <h6 class="card-title mb-0">{{ $title }}</h6>
            @if($subtitle)
                <p class="text-muted fs-12 mb-0">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(! empty($action))
        {!! $action !!}
    @endif
</div>
