@if($recentAttempts->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0 qa-index-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>التقييم</th>
                    <th class="text-center">الدرجة</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center">الوقت</th>
                    <th class="text-center">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentAttempts as $attempt)
                    @php
                        $score = $attempt['score'];
                        $isCompleted = $attempt['is_completed'];
                        $scoreChipClass = $score !== null && $score >= 60 ? 'qa-score-chip--high' : 'qa-score-chip--low';
                        $minutes = !empty($attempt['time_spent']) ? round($attempt['time_spent'] / 60, 1) : null;
                    @endphp
                    <tr>
                        <td>
                            @if(!empty($attempt['student_id']))
                                <a href="{{ route('users.student-details', $attempt['student_id']) }}" class="fw-semibold text-decoration-none">
                                    {{ $attempt['student']->name ?? 'غير محدد' }}
                                </a>
                            @else
                                {{ $attempt['student']->name ?? 'غير محدد' }}
                            @endif
                        </td>
                        <td>
                            <div class="min-w-0">
                                <span class="d-block text-truncate" style="max-width: 220px;" title="{{ $attempt['title'] }}">{{ $attempt['title'] }}</span>
                                @if($attempt['type'] === 'module')
                                    <span class="quizzes-type-chip quizzes-type-chip--practice">وحدة</span>
                                @else
                                    <span class="quizzes-type-chip quizzes-type-chip--graded">اختبار</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($isCompleted && $score !== null)
                                <span class="qa-score-chip {{ $scoreChipClass }}">
                                    {{ number_format($score, 1) }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isCompleted)
                                @if($attempt['passed'] ?? ($score !== null && $score >= 60))
                                    <span class="assignments-status-chip assignments-status-chip--published">ناجح</span>
                                @else
                                    <span class="assignments-status-chip assignments-status-chip--expired">راسب</span>
                                @endif
                            @else
                                <span class="assignments-status-chip assignments-status-chip--draft">جاري</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <small class="text-muted">{{ $minutes !== null ? $minutes . ' د' : '—' }}</small>
                        </td>
                        <td class="text-center">
                            <small class="text-muted">{{ $attempt['started_at']?->format('Y-m-d H:i') }}</small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="quiz-analytics-empty py-4">
        <div><i class="fe fe-clock d-block"></i></div>
        <p class="mb-0">لا توجد محاولات حديثة ضمن الفلاتر الحالية</p>
    </div>
@endif
