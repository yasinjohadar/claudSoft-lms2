@php
    $topThree = $board->top_three ?? collect();
    $userRank = $board->user_rank ?? null;
    $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
@endphp

<article class="student-leaderboard-index-card h-100">
    <div class="student-leaderboard-index-card__header">
        <div class="student-leaderboard-index-card__header-main">
            <span class="student-leaderboard-index-card__icon">{{ $board->icon ?? '🏆' }}</span>
            <div class="min-w-0">
                <h6 class="student-leaderboard-index-card__title">{{ $board->name }}</h6>
                @if ($board->description)
                    <p class="student-leaderboard-index-card__desc">{{ Str::limit($board->description, 72) }}</p>
                @endif
            </div>
        </div>
        @if ($userRank)
            <span class="student-leaderboard-index-card__my-rank">#{{ $userRank['rank'] }}</span>
        @endif
    </div>

    <div class="student-leaderboard-index-card__body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="student-leaderboard-index-card__pill">{{ $catalog->getTypeLabel($board->type) }}</span>
            <span class="student-leaderboard-index-card__pill">{{ $catalog->getPeriodLabel($board->period) }}</span>
            <span class="student-leaderboard-index-card__pill student-leaderboard-index-card__pill--muted">
                <i class="ri ri-group-line"></i>{{ number_format($board->entries_count ?? 0) }}
            </span>
        </div>

        @if ($topThree->isNotEmpty())
            <div class="student-leaderboard-index-card__podium">
                <span class="student-leaderboard-index-card__podium-label">المتصدرون</span>
                <div class="student-leaderboard-index-card__podium-list">
                    @foreach ($topThree as $entry)
                        <div class="student-leaderboard-index-card__podium-item" title="{{ $entry->user->name ?? '' }}">
                            <span class="student-leaderboard-index-card__podium-medal">{{ $medals[$entry->rank] ?? '#'.$entry->rank }}</span>
                            @include('student.pages.gamification.leaderboards.partials.user-avatar', [
                                'user' => $entry->user,
                                'size' => 'sm',
                            ])
                            <span class="student-leaderboard-index-card__podium-name">
                                @include('student.pages.gamification.leaderboards.partials.user-name', [
                                    'user' => $entry->user,
                                    'compact' => true,
                                ])
                            </span>
                            <span class="student-leaderboard-index-card__podium-score">{{ number_format($entry->score) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="student-leaderboard-index-card__empty-top">
                <i class="ri ri-trophy-line"></i>
                <span>لا يوجد متصدرون بعد — كن الأول!</span>
            </div>
        @endif

        @if ($userRank)
            <div class="student-leaderboard-index-card__my-stats">
                <div class="student-leaderboard-index-card__my-stat">
                    <span class="text-muted">نتيجتك</span>
                    <strong class="text-primary">{{ number_format($userRank['score']) }}</strong>
                </div>
                <div class="student-leaderboard-index-card__my-stat">
                    <span class="text-muted">أفضل من</span>
                    <strong>{{ $userRank['percentile'] }}%</strong>
                </div>
                <div class="student-leaderboard-index-card__my-stat">
                    <span class="text-muted">الفئة</span>
                    @include('student.pages.gamification.leaderboards.partials.division-badge', [
                        'division' => $userRank['division'],
                        'catalog' => $catalog,
                        'size' => 'sm',
                    ])
                </div>
            </div>
            <div class="student-leaderboard-index-card__progress">
                <div class="student-leaderboard-index-card__progress-bar" style="width: {{ min(100, max(4, $userRank['percentile'])) }}%"></div>
            </div>
        @else
            <p class="student-leaderboard-index-card__not-ranked">
                <i class="ri ri-information-line me-1"></i>لم تدخل الترتيب بعد — ابدأ النشاط لكسب نقاط
            </p>
        @endif
    </div>

    <div class="student-leaderboard-index-card__footer">
        <a href="{{ route('gamification.leaderboards.show', $board) }}" class="btn btn-primary btn-sm w-100">
            <i class="ri ri-bar-chart-horizontal-line me-1"></i>عرض اللوحة
        </a>
    </div>
</article>
