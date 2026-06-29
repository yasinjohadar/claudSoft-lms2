@if($coursePerformance->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0 qa-index-table">
            <thead>
                <tr>
                    <th>الكورس</th>
                    <th class="text-center">المحاولات</th>
                    <th class="text-center">متوسط الدرجة</th>
                    <th class="text-center">معدل النجاح</th>
                    <th style="width: 80px;">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coursePerformance as $course)
                    @php
                        $scoreChipClass = $course->average_score >= 70 ? 'qa-score-chip--high' : ($course->average_score >= 50 ? 'qa-score-chip--mid' : 'qa-score-chip--low');
                    @endphp
                    <tr>
                        <td>
                            <span class="assignments-course-chip" title="{{ $course->title }}">{{ $course->title }}</span>
                        </td>
                        <td class="text-center">
                            <span class="qp-questions-chip">{{ $course->attempts_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="qa-score-chip {{ $scoreChipClass }}">
                                {{ number_format($course->average_score, 1) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="assignments-status-chip assignments-status-chip--{{ $course->pass_rate >= 60 ? 'published' : 'expired' }}">
                                {{ number_format($course->pass_rate, 1) }}%
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="عرض الكورس">
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
        <div><i class="fe fe-book-open d-block"></i></div>
        <p class="mb-0">لا توجد بيانات أداء للكورسات ضمن الفلاتر الحالية</p>
    </div>
@endif
