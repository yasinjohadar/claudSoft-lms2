@php
    $difficultyCounts = [
        'all' => $questionAnalysis?->count() ?? 0,
        'hard' => $questionAnalysis?->where('difficulty', 'hard')->count() ?? 0,
        'medium' => $questionAnalysis?->where('difficulty', 'medium')->count() ?? 0,
        'easy' => $questionAnalysis?->where('difficulty', 'easy')->count() ?? 0,
    ];
@endphp

@if($questionAnalysis && $questionAnalysis->count() > 0)
    <div class="quiz-analytics-q-filters" role="tablist" aria-label="تصفية الأسئلة">
        <button type="button" class="quiz-analytics-q-filter is-active" data-filter="all">
            <i class="fe fe-layers"></i>
            <span>الكل</span>
            <span class="quiz-analytics-q-filter__count">{{ $difficultyCounts['all'] }}</span>
        </button>
        <button type="button" class="quiz-analytics-q-filter quiz-analytics-q-filter--hard" data-filter="hard">
            <i class="fe fe-alert-triangle"></i>
            <span>صعبة</span>
            <span class="quiz-analytics-q-filter__count">{{ $difficultyCounts['hard'] }}</span>
        </button>
        <button type="button" class="quiz-analytics-q-filter" data-filter="medium">
            <i class="fe fe-minus-circle"></i>
            <span>متوسطة</span>
            <span class="quiz-analytics-q-filter__count">{{ $difficultyCounts['medium'] }}</span>
        </button>
        <button type="button" class="quiz-analytics-q-filter quiz-analytics-q-filter--easy" data-filter="easy">
            <i class="fe fe-check-circle"></i>
            <span>سهلة</span>
            <span class="quiz-analytics-q-filter__count">{{ $difficultyCounts['easy'] }}</span>
        </button>
    </div>

    <div class="quiz-analytics-questions">
        @foreach($questionAnalysis as $index => $analysis)
            @php
                $question = $analysis['question'];
                $successRate = $analysis['success_rate'] ?? 0;
                $difficulty = $analysis['difficulty'] ?? 'medium';
                $rateClass = $successRate >= 70 ? 'is-high' : ($successRate >= 50 ? 'is-mid' : 'is-low');
            @endphp
            <article class="quiz-analytics-question" data-difficulty="{{ $difficulty }}">
                <div class="quiz-analytics-question__header"
                     role="button"
                     tabindex="0"
                     aria-expanded="false"
                     aria-controls="qa-detail-{{ $index }}">
                    <span class="quiz-analytics-question__num">{{ $index + 1 }}</span>
                    <div class="quiz-analytics-question__body">
                        <p class="quiz-analytics-question__text mb-0">{!! Str::limit(strip_tags($question->question_text ?? 'سؤال بدون نص'), 140) !!}</p>
                        <span class="quiz-analytics-question__meta">{{ $question->questionType->display_name ?? 'غير معروف' }}</span>
                    </div>
                    <div class="quiz-analytics-question__aside">
                        <div class="quiz-analytics-rate {{ $rateClass }}" aria-label="معدل النجاح {{ number_format($successRate, 1) }}%">
                            <div class="quiz-analytics-rate__track">
                                <div class="quiz-analytics-rate__fill" style="width: {{ min(100, max(0, $successRate)) }}%;"></div>
                            </div>
                            <span class="quiz-analytics-rate__value">{{ number_format($successRate, 0) }}%</span>
                        </div>
                        <i class="fe fe-chevron-down quiz-analytics-question__chevron" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="quiz-analytics-question__detail" id="qa-detail-{{ $index }}" hidden>
                    <div class="quiz-analytics-question__counts">
                        <span class="quiz-analytics-count-pill quiz-analytics-count-pill--success">
                            <i class="fe fe-check"></i>
                            {{ $analysis['correct_responses'] ?? 0 }} صحيح
                        </span>
                        <span class="quiz-analytics-count-pill quiz-analytics-count-pill--danger">
                            <i class="fe fe-x"></i>
                            {{ $analysis['incorrect_responses'] ?? 0 }} خطأ
                        </span>
                        <span class="quiz-analytics-count-pill quiz-analytics-count-pill--total">
                            <i class="fe fe-users"></i>
                            {{ $analysis['total_responses'] ?? 0 }} إجابة
                        </span>
                    </div>

                    @if(!empty($analysis['option_distribution']))
                        <p class="quiz-analytics-options-title">توزيع اختيارات الطلاب</p>
                        <div class="quiz-analytics-options">
                            @foreach($analysis['option_distribution'] as $option)
                                <div class="quiz-analytics-option {{ $option['is_correct'] ? 'is-correct' : 'is-wrong' }}">
                                    <div class="quiz-analytics-option__top">
                                        <div class="quiz-analytics-option__label">
                                            @if($option['is_correct'])
                                                <span class="quiz-analytics-option__badge">صحيح</span>
                                            @endif
                                            <span>{{ Str::limit($option['text'], 100) }}</span>
                                        </div>
                                        <span class="quiz-analytics-option__pct">{{ $option['percentage'] }}% <small>({{ $option['count'] }})</small></span>
                                    </div>
                                    <div class="quiz-analytics-option__bar-wrap">
                                        <div class="quiz-analytics-option__bar" style="width: {{ $option['percentage'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="quiz-analytics-no-options mb-0">لا يتوفر توزيع خيارات لهذا النوع من الأسئلة.</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="quiz-analytics-empty">
        <div><i class="fe fe-help-circle d-block"></i></div>
        <p class="mb-0">لا توجد بيانات متاحة</p>
    </div>
@endif
