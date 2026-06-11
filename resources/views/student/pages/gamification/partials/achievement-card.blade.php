@php
    $achievement = $userAchievement->achievement;
@endphp
@if(!$achievement)
    {{-- سجل يشير لإنجاز محذوف أو غير متاح --}}
@else
@php
    $isLocked = $isLocked ?? false;
    $isCompleted = $isCompleted ?? in_array($userAchievement->status, ['completed', 'claimed'], true);
    $progress = (float) ($userAchievement->progress_percentage ?? 0);
    $current = (int) ($userAchievement->current_value ?? 0);
    $target = (int) ($achievement->target_value ?? 1);
    $points = (int) ($achievement->points_reward ?? 0);
    $tier = $achievement->tier ?? 'bronze';
    $icon = $achievement->icon ?? '🏆';

    $statusKey = $isCompleted ? 'completed' : ($isLocked || $progress <= 0 ? 'not_started' : 'in_progress');
    $isNearComplete = !$isCompleted && $progress >= 70;

    $tierLabels = [
        'bronze' => 'برونزي',
        'silver' => 'فضي',
        'gold' => 'ذهبي',
        'platinum' => 'بلاتيني',
        'diamond' => 'ماسي',
    ];

    $requirementText = \App\Support\Gamification\AchievementCriteriaMapper::formatForDisplay(
        $achievement->criteria,
        $achievement->target_value
    );

    $completedAt = $userAchievement->completed_at
        ? $userAchievement->completed_at->format('Y/m/d')
        : '';
@endphp

<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 student-my-courses-stagger achievement-grid-item"
     style="--stagger-delay: {{ ($index ?? 0) * 45 }}ms"
     data-achievement-status="{{ $statusKey }}"
     data-achievement-tier="{{ $tier }}">
    <article class="student-achievement-card student-achievement-card--{{ $tier }} {{ $isCompleted ? 'is-completed' : ($isLocked ? 'is-locked' : 'is-active') }} {{ $isNearComplete ? 'is-near-complete' : '' }}"
        role="button"
        tabindex="0"
        data-achievement-open
        data-name="{{ $achievement->name }}"
        data-description="{{ $achievement->description ?? '' }}"
        data-tier="{{ $tierLabels[$tier] ?? $tier }}"
        data-tier-key="{{ $tier }}"
        data-icon="{{ $icon }}"
        data-progress="{{ $progress }}"
        data-current="{{ $current }}"
        data-target="{{ $target }}"
        data-points="{{ $points }}"
        data-status="{{ $statusKey }}"
        data-requirement="{{ $requirementText }}"
        data-completed-at="{{ $completedAt }}"
        data-show-url="{{ route('gamification.achievements.show', $achievement) }}"
        data-claim-url="{{ $userAchievement->status === 'completed' && $points > 0 ? route('gamification.achievements.claim', $userAchievement) : '' }}">
        <span class="student-achievement-card__tier badge">{{ $tierLabels[$tier] ?? $tier }}</span>

        @if($isNearComplete)
            <span class="student-achievement-card__pulse" aria-hidden="true"></span>
        @endif

        <div class="student-achievement-card__glow" aria-hidden="true"></div>

        <div class="student-achievement-card__icon-wrap">
            <span class="student-achievement-card__emoji">{{ $icon }}</span>
            @if($isCompleted)
                <span class="student-achievement-card__check"><i class="fe fe-check"></i></span>
            @endif
        </div>

        <h6 class="student-achievement-card__title">{{ $achievement->name }}</h6>

        @if($achievement->description)
            <p class="student-achievement-card__desc">{{ $achievement->description }}</p>
        @endif

        <span class="student-achievement-card__requirement">{{ $requirementText }}</span>

        @if($isCompleted)
            <div class="student-achievement-card__footer">
                @if($points > 0)
                    <span class="student-achievement-card__reward">+{{ number_format($points) }} نقطة</span>
                @endif
                @if($completedAt)
                    <span class="student-achievement-card__date"><i class="fe fe-calendar me-1"></i>{{ $completedAt }}</span>
                @endif
            </div>
        @else
            <div class="student-achievement-card__progress">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">التقدم</small>
                    <small class="fw-semibold student-achievement-card__pct">{{ number_format($progress, 0) }}%</small>
                </div>
                <div class="student-achievement-card__track">
                    <div class="student-achievement-card__bar" style="--progress: {{ max(0, min(100, $progress)) }}%"></div>
                </div>
                <small class="student-achievement-card__ratio">{{ $current }} / {{ $target }}</small>
            </div>
            @if($points > 0)
                <span class="student-achievement-card__reward student-achievement-card__reward--muted">مكافأة: {{ number_format($points) }} نقطة</span>
            @endif
        @endif

        <span class="student-achievement-card__hint"><i class="fe fe-maximize-2 me-1"></i>اضغط للتفاصيل</span>
    </article>
</div>
@endif
