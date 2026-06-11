@php
    $nameEn = trim($user->name ?? '');
    $nameAr = trim($user->name_ar ?? '');
    $compact = !empty($compact);
@endphp

<span class="student-leaderboard-user-name {{ $compact ? 'student-leaderboard-user-name--compact' : '' }}">
    @if ($nameEn !== '')
        <span class="student-leaderboard-user-name__en">{{ $nameEn }}</span>
    @endif
    @if ($nameAr !== '')
        <span class="student-leaderboard-user-name__ar">{{ $nameAr }}</span>
    @elseif ($nameEn === '')
        <span class="student-leaderboard-user-name__en">—</span>
    @endif
</span>
