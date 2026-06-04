@php
    $isEarned = $isEarned ?? false;
    $progressData = $progress ?? ['progress' => $isEarned ? 100 : 0];
    $progressPct = (float) ($progressData['progress'] ?? 0);
    $points = $badge->points_value ?? $badge->points_reward ?? 0;
    $icon = $badge->icon ?? '🏅';
    $isIconClass = is_string($icon) && preg_match('/\b(fa[srb]?|fe|bi|ri)-/', $icon);

    $rarityMap = [
        'common' => ['class' => 'secondary', 'label' => 'عادية'],
        'rare' => ['class' => 'info', 'label' => 'نادرة'],
        'epic' => ['class' => 'primary', 'label' => 'ملحمية'],
        'legendary' => ['class' => 'warning', 'label' => 'أسطورية'],
        'mythic' => ['class' => 'danger', 'label' => 'خرافية'],
    ];
    $rarity = $rarityMap[$badge->rarity ?? ''] ?? null;
@endphp

<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 40 }}ms">
    <article class="student-badge-card {{ $isEarned ? 'is-earned' : 'is-locked' }} student-badge-card--{{ $badge->rarity ?? 'common' }}">
        @if($rarity)
            <span class="student-badge-card__rarity badge bg-{{ $rarity['class'] }}-transparent">{{ $rarity['label'] }}</span>
        @endif

        <div class="student-badge-card__icon-wrap">
            @if($isIconClass)
                <i class="{{ $icon }}"></i>
            @else
                <span class="student-badge-card__emoji">{{ $icon }}</span>
            @endif
        </div>

        <h6 class="student-badge-card__title">{{ $badge->name ?? 'شارة' }}</h6>
        @if(!empty($badge->description))
            <p class="student-badge-card__desc">{{ $badge->description }}</p>
        @endif

        <span class="badge bg-primary-transparent student-badge-card__points">+{{ $points }} نقطة</span>

        @if(!$isEarned && $progressPct > 0 && $progressPct < 100)
            <div class="student-badge-card__progress mt-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">التقدم</small>
                    <small class="fw-semibold">{{ number_format($progressPct, 0) }}%</small>
                </div>
                <div class="student-course-card__progress-track">
                    <div class="student-course-card__progress-bar" style="width: {{ max(0, min(100, $progressPct)) }}%"></div>
                </div>
            </div>
        @endif

        <div class="student-badge-card__status mt-3">
            @if($isEarned)
                <span class="badge bg-success-transparent">
                    <i class="fe fe-check-circle me-1"></i>
                    @if(!empty($awardedAt))
                        {{ $awardedAt instanceof \Carbon\Carbon ? $awardedAt->format('Y/m/d') : \Carbon\Carbon::parse($awardedAt)->format('Y/m/d') }}
                    @else
                        تم الحصول عليها
                    @endif
                </span>
            @else
                <span class="badge bg-secondary-transparent">
                    <i class="fe fe-lock me-1"></i>غير مكتسب
                </span>
            @endif
        </div>
    </article>
</div>
