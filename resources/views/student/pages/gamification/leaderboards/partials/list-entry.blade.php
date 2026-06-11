@php
    $entryUser = $entry->user;
    $isMe = ($currentUser ?? auth()->user()) && $entry->user_id === ($currentUser ?? auth()->user())->id;
    $nameParts = preg_split('/\s+/u', trim($entryUser->name ?? ''), 2);

    $payload = [
        'rank' => (int) $entry->rank,
        'name' => $entryUser->name ?? '',
        'nameAr' => trim($entryUser->name_ar ?? ''),
        'score' => (int) $entry->score,
        'division' => $entry->division,
        'divisionLabel' => $catalog->getDivisionLabel($entry->division),
        'divisionColor' => $catalog->getDivisionColor($entry->division),
        'divisionIcon' => $catalog->getDivisionIcon($entry->division),
        'rankChange' => (int) ($entry->rank_change ?? 0),
        'photoUrl' => student_profile_photo_url($entryUser),
        'initials' => mb_strtoupper(mb_substr($nameParts[0] ?? '?', 0, 1) . mb_substr($nameParts[1] ?? '', 0, 1)),
        'isMe' => $isMe,
        'metricLabel' => isset($leaderboard)
            ? $catalog->getMetricLabel($catalog->resolveMetric($leaderboard))
            : null,
        'profilePublic' => (bool) ($entryUser->is_profile_public ?? false),
        'userId' => (int) $entry->user_id,
    ];
@endphp

<button
    type="button"
    class="student-leaderboard-row {{ $payload['isMe'] ? 'is-me' : '' }} {{ $payload['rank'] <= 3 ? 'is-top' : '' }} {{ !empty($compact) ? 'student-leaderboard-row--compact' : '' }} js-leaderboard-entry {{ empty($compact) ? 'js-leaderboard-filterable' : '' }}"
    data-entry='@json($payload)'
    data-division="{{ $entry->division }}"
    data-user-id="{{ $entry->user_id }}"
    aria-label="عرض تفاصيل {{ $payload['name'] }}"
>
    <span class="student-leaderboard-row__rank">
        @if ($payload['rank'] === 1) 🥇
        @elseif ($payload['rank'] === 2) 🥈
        @elseif ($payload['rank'] === 3) 🥉
        @else #{{ $payload['rank'] }}
        @endif
    </span>
    @include('student.pages.gamification.leaderboards.partials.user-avatar', ['user' => $entry->user, 'size' => !empty($compact) ? 'sm' : 'md'])
    <span class="student-leaderboard-row__body">
        <span class="student-leaderboard-row__name">
            @include('student.pages.gamification.leaderboards.partials.user-name', [
                'user' => $entry->user,
                'compact' => !empty($compact),
            ])
            @if ($payload['isMe'])
                <span class="badge bg-primary ms-1">أنت</span>
            @endif
        </span>
    </span>
    @unless(!empty($compact))
        <span class="student-leaderboard-row__division">
            @include('student.pages.gamification.leaderboards.partials.division-badge', [
                'division' => $entry->division,
                'catalog' => $catalog,
                'size' => 'sm',
                'layout' => 'column',
            ])
        </span>
    @endunless
    <span class="student-leaderboard-row__score">{{ number_format($payload['score']) }}</span>
    <span class="student-leaderboard-row__chevron" aria-hidden="true"><i class="ri ri-arrow-left-s-line"></i></span>
</button>
