@php
    $catalog = app(\App\Services\Gamification\LeaderboardCatalog::class);
@endphp

@switch($leaderboard->type)
    @case('global')
        <span class="badge bg-primary-transparent text-primary">{{ $catalog->getTypeLabel('global') }}</span>
        @break
    @case('weekly')
        <span class="badge bg-info-transparent text-info">{{ $catalog->getTypeLabel('weekly') }}</span>
        @break
    @case('monthly')
        <span class="badge bg-success-transparent text-success">{{ $catalog->getTypeLabel('monthly') }}</span>
        @break
    @case('streak')
        <span class="badge bg-warning-transparent text-warning">{{ $catalog->getTypeLabel('streak') }}</span>
        @break
    @case('course')
        <span class="badge bg-secondary-transparent text-secondary">{{ $catalog->getTypeLabel('course') }}</span>
        @break
    @default
        <span class="badge bg-light text-dark">{{ $catalog->getTypeLabel($leaderboard->type) }}</span>
@endswitch
