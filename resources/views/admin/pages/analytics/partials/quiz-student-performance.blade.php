@if($studentPerformance && $studentPerformance->count() > 0)
    <div class="quiz-analytics-student-list">
        @foreach($studentPerformance as $performance)
            @php
                $improvement = $performance['improvement'] ?? 0;
                $improvementClass = $improvement > 0 ? 'quiz-analytics-improvement--up' : ($improvement < 0 ? 'quiz-analytics-improvement--down' : 'quiz-analytics-improvement--flat');
                $improvementIcon = $improvement > 0 ? 'fe-trending-up' : ($improvement < 0 ? 'fe-trending-down' : 'fe-minus');
            @endphp
            <div class="quiz-analytics-student">
                <div class="quiz-analytics-student__name">
                    <span class="quiz-analytics-student__rank">{{ $loop->iteration }}</span>
                    <span class="text-truncate">{{ $performance['student']->name ?? 'غير معروف' }}</span>
                </div>
                <div class="quiz-analytics-student__metric">
                    <span class="quiz-analytics-student__metric-label">المحاولات</span>
                    <span class="quiz-analytics-student__metric-value">{{ $performance['attempts_count'] ?? 0 }}</span>
                </div>
                <div class="quiz-analytics-student__metric">
                    <span class="quiz-analytics-student__metric-label">أفضل درجة</span>
                    <span class="quiz-analytics-student__metric-value text-success">{{ number_format($performance['best_score'] ?? 0, 1) }}%</span>
                </div>
                <div class="quiz-analytics-student__metric">
                    <span class="quiz-analytics-student__metric-label">التحسن</span>
                    <span class="quiz-analytics-student__metric-value {{ $improvementClass }}">
                        <i class="fe {{ $improvementIcon }}"></i>
                        {{ number_format($improvement, 1) }}%
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="quiz-analytics-empty">
        <div><i class="fe fe-users d-block"></i></div>
        <p class="mb-0">لا توجد بيانات متاحة</p>
    </div>
@endif
