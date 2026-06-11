@php
    $division = $division ?? 'bronze';
    $size = $size ?? 'md';
    $showLabel = $showLabel ?? true;
    $layout = $layout ?? 'column';
    $catalog = $catalog ?? app(\App\Services\Gamification\LeaderboardCatalog::class);
@endphp

<span class="student-leaderboard-division-badge student-leaderboard-division-badge--{{ $division }} student-leaderboard-division-badge--{{ $size }} {{ $layout === 'inline' ? 'student-leaderboard-division-badge--inline' : '' }}" title="{{ $catalog->getDivisionLabel($division) }}">
    <span class="student-leaderboard-division-badge__icon" aria-hidden="true">
        <i class="ri {{ $catalog->getDivisionIcon($division) }}"></i>
    </span>
    @if ($showLabel)
        <span class="student-leaderboard-division-badge__label">{{ $catalog->getDivisionLabel($division) }}</span>
    @endif
</span>
