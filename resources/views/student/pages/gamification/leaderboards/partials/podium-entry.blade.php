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
        'metricLabel' => $catalog->getMetricLabel($catalog->resolveMetric($leaderboard)),
        'profilePublic' => (bool) ($entryUser->is_profile_public ?? false),
        'userId' => (int) $entry->user_id,
    ];
@endphp

<button
    type="button"
    class="student-leaderboard-podium__item {{ $payload['isMe'] ? 'is-me' : '' }} js-leaderboard-entry"
    data-entry='@json($payload)'
    data-user-id="{{ $entry->user_id }}"
    aria-label="عرض تفاصيل {{ $payload['name'] }}"
>
    <span class="student-leaderboard-podium__medal">
        @if ($payload['rank'] === 1) 🥇
        @elseif ($payload['rank'] === 2) 🥈
        @else 🥉
        @endif
    </span>
    @include('student.pages.gamification.leaderboards.partials.user-avatar', ['user' => $entry->user, 'size' => 'lg'])
    <span class="student-leaderboard-podium__name">
        @include('student.pages.gamification.leaderboards.partials.user-name', ['user' => $entry->user])
    </span>
    <span class="student-leaderboard-podium__score">{{ number_format($payload['score']) }}</span>
    @include('student.pages.gamification.leaderboards.partials.division-badge', [
        'division' => $entry->division,
        'catalog' => $catalog,
        'size' => 'md',
        'layout' => 'column',
    ])
    <span class="student-leaderboard-podium__hint">اضغط للتفاصيل</span>
</button>
