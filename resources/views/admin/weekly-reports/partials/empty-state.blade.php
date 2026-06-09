<div class="text-center py-5 px-3">
    <span class="avatar avatar-lg bg-secondary-transparent mb-3">
        <i class="fe {{ $icon ?? 'fe-inbox' }} fs-24 text-secondary"></i>
    </span>
    <h6 class="mb-2">{{ $title }}</h6>
    <p class="text-muted fs-13 mb-3">{{ $description }}</p>
    @if(!empty($actionRoute) && !empty($actionLabel))
        <a href="{{ route($actionRoute) }}" class="btn btn-primary btn-sm">
            <i class="fe fe-plus me-1"></i>{{ $actionLabel }}
        </a>
    @endif
</div>
