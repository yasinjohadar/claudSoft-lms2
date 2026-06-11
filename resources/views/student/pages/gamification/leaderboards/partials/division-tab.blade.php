@php
    $catalog = $catalog ?? app(\App\Services\Gamification\LeaderboardCatalog::class);
    $tag = $tag ?? 'button';
    $isActive = !empty($isActive);
    $filterValue = $filterValue ?? $division;
@endphp

@if ($tag === 'a')
    <a href="{{ $href ?? '#' }}"
        class="student-leaderboard-division-tab student-leaderboard-division-tab--{{ $division }} {{ $isActive ? 'is-active' : '' }}">
        <span class="student-leaderboard-division-tab__icon student-leaderboard-division-badge__icon" aria-hidden="true">
            <i class="ri {{ $catalog->getDivisionIcon($division) }}"></i>
        </span>
        <span>{{ $catalog->getDivisionLabel($division) }}</span>
    </a>
@else
    <button type="button"
        class="student-leaderboard-division-tab student-leaderboard-division-tab--{{ $division }} {{ $isActive ? 'is-active' : '' }}"
        data-division-filter="{{ $filterValue }}">
        <span class="student-leaderboard-division-tab__icon student-leaderboard-division-badge__icon" aria-hidden="true">
            <i class="ri {{ $catalog->getDivisionIcon($division) }}"></i>
        </span>
        <span>{{ $catalog->getDivisionLabel($division) }}</span>
    </button>
@endif
