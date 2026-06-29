@if($assessments->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0 qa-index-table">
            <thead>
                <tr>
                    <th>التقييم</th>
                    <th>النوع</th>
                    <th class="text-center">المحاولات</th>
                    <th class="text-center">متوسط الدرجة</th>
                    <th style="width: 80px;">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $item)
                    @php
                        $scoreChipClass = $item->average_score >= 70 ? 'qa-score-chip--high' : ($item->average_score >= 50 ? 'qa-score-chip--mid' : 'qa-score-chip--low');
                        $link = $item->type === 'quiz'
                            ? route('quiz-analytics.quiz', $item->id)
                            : route('question-modules.show', $item->id);
                    @endphp
                    <tr>
                        <td>
                            <div class="min-w-0">
                                <span class="fw-semibold d-block text-truncate" style="max-width: 260px;" title="{{ $item->title }}">{{ $item->title }}</span>
                                @if(!empty($item->course))
                                    <small class="text-muted">{{ $item->course }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($item->type === 'quiz')
                                <span class="quizzes-type-chip quizzes-type-chip--graded">اختبار</span>
                            @else
                                <span class="quizzes-type-chip quizzes-type-chip--practice">وحدة</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="qp-questions-chip">{{ $item->attempts_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="qa-score-chip {{ $scoreChipClass }}">
                                {{ number_format($item->average_score, 1) }}%
                            </span>
                        </td>
                        <td>
                            <a href="{{ $link }}" class="btn btn-primary-light btn-sm assignments-actions__btn" title="عرض التحليلات">
                                <i class="fe fe-bar-chart-2"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="quiz-analytics-empty py-4">
        <div><i class="fe fe-file-text d-block"></i></div>
        <p class="mb-0">{{ $emptyMessage ?? 'لا توجد بيانات متاحة' }}</p>
    </div>
@endif
