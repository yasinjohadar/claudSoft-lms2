@php
    /*
     * بطاقة معسكر بنمط ودجات الإحصاء (Hr-System): خلفية متدرّجة + الطبقات
     * الزخرفية نفسها، لكن بمحتوى خاص (عدّاد أيام + تواريخ) عبر .camp-card__*
     *
     * الثيم يتبع حالة المعسكر: جارٍ = أخضر، قادم = أزرق، منتهٍ = فضّي.
     */
    $group = $membership->group;
    $hasEndDate = $group->end_date !== null;
    $daysRemaining = $hasEndDate
        ? max(0, (int) now()->startOfDay()->diffInDays($group->end_date->copy()->startOfDay(), false))
        : null;

    $theme = 'blue';
    $statusLabel = 'قادم';
    $countdownLabel = 'يوم متبقي';

    if ($group->hasEnded()) {
        $theme = 'silver';
        $statusLabel = 'منتهي';
        $countdownLabel = 'انتهى';
    } elseif ($group->isOngoing()) {
        $theme = 'green';
        $statusLabel = 'جاري';
        $countdownLabel = $daysRemaining === null ? 'مستمر' : ($daysRemaining === 0 ? 'ينتهي اليوم' : 'يوم متبقي');
    } elseif ($daysRemaining === 0) {
        $countdownLabel = 'ينتهي اليوم';
    }

    $formatDate = fn ($date) => $date?->locale('ar')->translatedFormat('j F Y') ?? '—';
@endphp

<div class="{{ $columnClass ?? 'col-xl-6 col-lg-6 col-md-6 col-sm-12' }}">
    <a href="{{ route('student.training-camps.show', $group->id) }}"
       class="dashboard-stat-link"
       style="--card-delay: {{ ($staggerDelay ?? 0) / 1000 }}s">
        <div class="dashboard-stat-card dashboard-stat-{{ $theme }}">
            <div class="stat-card-shine"></div>
            <div class="stat-card-mesh"></div>
            <div class="stat-card-bubble stat-card-bubble-1"></div>
            <div class="stat-card-bubble stat-card-bubble-2"></div>
            <div class="stat-card-bubble stat-card-bubble-3"></div>
            <div class="stat-card-glow"></div>

            <div class="camp-card__body">
                <div class="camp-card__head">
                    <span class="camp-card__name">{{ $group->name }}</span>
                    <span class="camp-card__status">{{ $statusLabel }}</span>
                    <span class="camp-card__arrow"><i class="ri-arrow-left-line"></i></span>
                </div>

                <div class="camp-card__main">
                    <div class="camp-card__countdown">
                        <span class="camp-card__days">{{ $daysRemaining !== null ? $daysRemaining : '—' }}</span>
                        <span class="camp-card__days-label">{{ $countdownLabel }}</span>
                    </div>

                    <div class="camp-card__dates">
                        <span class="camp-card__date">
                            <i class="ri-play-circle-line"></i>
                            البداية: <strong>{{ $formatDate($group->start_date) }}</strong>
                        </span>
                        <span class="camp-card__date">
                            <i class="ri-flag-line"></i>
                            النهاية: <strong>{{ $hasEndDate ? $formatDate($group->end_date) : 'بلا تاريخ نهاية' }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
