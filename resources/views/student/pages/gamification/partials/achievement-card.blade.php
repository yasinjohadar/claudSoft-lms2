@php
    $isLocked = $isLocked ?? false;
    $progress = (float) ($userAchievement->progress_percentage ?? 0);
    $current = (int) ($userAchievement->current_value ?? 0);
    $target = (int) ($achievement->target_value ?? 1);
    $tierLabels = [
        'bronze' => 'برونزي',
        'silver' => 'فضي',
        'gold' => 'ذهبي',
        'platinum' => 'بلاتيني',
        'diamond' => 'ماسي',
    ];
@endphp
<div class="col-lg-4 col-md-6">
    <div class="card border-0 shadow-sm h-100 {{ $isLocked ? 'opacity-75' : '' }}">
        <div class="card-body text-center d-flex flex-column">
            <div class="fs-1 mb-2" @if($isLocked) style="filter: grayscale(100%);" @endif>
                {{ $achievement->icon ?? '🏆' }}
            </div>
            <h5 class="fw-bold mb-1">
                <a href="{{ route('gamification.achievements.show', $achievement) }}" class="text-decoration-none text-dark">
                    {{ $achievement->name }}
                </a>
            </h5>
            <span class="badge bg-secondary mb-2 align-self-center">
                {{ $tierLabels[$achievement->tier] ?? $achievement->tier }}
            </span>
            @if($achievement->description)
                <p class="small text-muted mb-2">{{ Str::limit($achievement->description, 80) }}</p>
            @endif

            @if($isCompleted ?? false)
                <span class="badge bg-success mb-2 align-self-center">مكتمل</span>
                @if($achievement->points_reward)
                    <span class="badge bg-warning text-dark align-self-center">+{{ $achievement->points_reward }} نقطة</span>
                @endif
                @if($userAchievement->completed_at)
                    <p class="small text-muted mt-2 mb-0">
                        <i class="fe fe-calendar me-1"></i>
                        {{ $userAchievement->completed_at->format('Y/m/d') }}
                    </p>
                @endif
            @else
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">{{ $current }} / {{ $target }}</small>
                @if($achievement->points_reward)
                    <small class="text-muted mt-1">مكافأة: {{ $achievement->points_reward }} نقطة</small>
                @endif
            @endif
        </div>
    </div>
</div>
