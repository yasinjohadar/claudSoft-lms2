@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي الاختبارات',
            'value' => $totalQuizzes ?? 0,
            'sub' => 'في جميع الكورسات',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'الاختبارات المنشورة',
            'value' => $publishedQuizzes ?? 0,
            'sub' => 'متاحة للطلاب',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-edit-3',
            'label' => 'المسودات',
            'value' => $draftQuizzes ?? 0,
            'sub' => 'غير منشورة بعد',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-help-circle',
            'label' => 'بنك الأسئلة',
            'value' => $questionBankCount ?? 0,
            'sub' => 'سؤال متاح',
            'link' => route('question-bank.index'),
            'linkText' => 'عرض البنك',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in quizzes-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item quizzes-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                        @if(!empty($card['link']))
                            <a href="{{ $card['link'] }}" class="admin-stats-card__sub mb-0 text-primary d-inline-flex align-items-center gap-1">
                                <i class="fe fe-arrow-left"></i>{{ $card['linkText'] }}
                            </a>
                        @else
                            <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
