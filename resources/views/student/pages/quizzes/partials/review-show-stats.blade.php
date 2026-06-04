@php
    $resultVariant = $attempt->passed ? 'green' : 'orange';
    $statCards = [
        [
            'variant' => $resultVariant,
            'icon' => $attempt->passed ? 'fe-check-circle' : 'fe-x-circle',
            'label' => 'النتيجة النهائية',
            'value' => round($attempt->percentage_score ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => $attempt->passed ? 'ناجح' : 'راسب',
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-star',
            'label' => 'الدرجة المحصلة',
            'value' => round($attempt->total_score ?? 0, 1),
            'decimals' => true,
            'sub' => 'من ' . number_format($attempt->max_score ?? 0, 1),
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check',
            'label' => 'الإجابات الصحيحة',
            'value' => $stats['correct'] ?? 0,
            'sub' => 'من ' . ($stats['total_questions'] ?? 0),
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-clock',
            'label' => 'الوقت المستغرق',
            'display_text' => $attempt->getTimeSpentHumanReadable(),
            'sub' => $attempt->quiz->time_limit ? 'من ' . $attempt->quiz->time_limit . ' دقيقة' : null,
            'text_only' => true,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-quiz-review-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if(!empty($card['text_only']))
                            <h3 class="admin-stats-card__value mb-1">{{ $card['display_text'] ?? '' }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-1"
                                data-countup="{{ $card['value'] }}"
                                @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                                @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                        @endif
                        @if(!empty($card['sub']))
                            <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
