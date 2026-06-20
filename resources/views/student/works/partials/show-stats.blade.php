@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-eye', 'label' => 'المشاهدات', 'value' => $work->views_count],
        ['variant' => 'cyan', 'icon' => 'fe-heart', 'label' => 'الإعجابات', 'value' => $work->likes_count],
    ];

    if ($work->rating) {
        $statCards[] = [
            'variant' => $work->rating >= 7 ? 'green' : ($work->rating >= 5 ? 'orange' : 'cyan'),
            'icon' => 'fe-star',
            'label' => 'التقييم',
            'value' => $work->rating,
            'decimals' => true,
            'suffix' => ' / 10',
        ];
    } else {
        $statCards[] = [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'الحالة',
            'text' => \App\Models\StudentWork::getStatuses()[$work->status]['name'] ?? $work->status,
        ];
    }
@endphp

<div class="row g-3 dashboard-fade-in mb-4 student-work-show-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-lg-4 col-md-4 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 55 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if(!empty($card['text']))
                            <h3 class="admin-stats-card__value mb-0 fs-18">{{ $card['text'] }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-0 d-flex align-items-baseline gap-1">
                                <span data-countup="{{ $card['value'] }}"
                                      @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</span>
                                @if(!empty($card['suffix']))
                                    <small class="fs-14 text-muted fw-normal">{{ $card['suffix'] }}</small>
                                @endif
                            </h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
