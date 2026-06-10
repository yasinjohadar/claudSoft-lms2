@props([
    'user',
    'variant' => 'btn',
])

@if($user->is_active && ! $user->hasRole('admin'))
    @php
        $sharedAttributes = $attributes->merge([
            'class' => match ($variant) {
                'group-action' => 'group-show-action group-show-action--success impersonate-btn',
                'dropdown-item' => 'dropdown-item impersonate-btn',
                default => 'btn btn-sm btn-success impersonate-btn',
            },
            'data-user-id' => $user->id,
            'data-user-name' => $user->name,
        ]);
    @endphp

    @if($variant === 'group-action')
        <a href="#" role="button" {{ $sharedAttributes }}>
            @if($slot->isEmpty())
                <span class="group-show-action__icon"><i class="fe fe-log-in"></i></span>
                <span class="group-show-action__text">الدخول كطالب</span>
            @else
                {{ $slot }}
            @endif
        </a>
    @else
        <button type="button" {{ $sharedAttributes }}>
            @if($slot->isEmpty())
                @if($variant === 'dropdown-item')
                    <i class="fe fe-log-in me-2"></i>الدخول كطالب
                @else
                    <i class="fas fa-user-secret"></i>
                @endif
            @else
                {{ $slot }}
            @endif
        </button>
    @endif
@endif
