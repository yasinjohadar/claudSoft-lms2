@php
    $photoUrl = student_profile_photo_url($user ?? null);
    $initials = '';
    if ($user ?? null) {
        $parts = preg_split('/\s+/u', trim($user->name ?? ''), 2);
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }
    $sizeClass = $size ?? 'md';
@endphp

<span class="student-leaderboard-avatar student-leaderboard-avatar--{{ $sizeClass }}">
    <img
        src="{{ $photoUrl }}"
        alt="{{ $user->name ?? '' }}"
        loading="lazy"
        onerror="this.hidden=true;this.nextElementSibling.hidden=false;"
    >
    <span class="student-leaderboard-avatar__fallback" hidden>{{ $initials ?: '?' }}</span>
</span>
