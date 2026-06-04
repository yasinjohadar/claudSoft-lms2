<div class="card custom-card student-quizzes-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-book text-primary"></i>
            </span>
            <h6 class="card-title mb-0">الأداء حسب الكورس</h6>
        </div>
    </div>
    <div class="card-body pt-3">
        @if(count($performanceByCourse ?? []) > 0)
            <div class="table-responsive d-none d-lg-block">
                <table class="table student-quizzes-table mb-0">
                    <thead>
                        <tr>
                            <th>الكورس</th>
                            <th>عدد المحاولات</th>
                            <th>متوسط النتيجة</th>
                            <th>معدل النجاح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performanceByCourse as $course)
                            <tr>
                                <td class="fw-semibold">{{ $course['name'] ?? 'غير محدد' }}</td>
                                <td>{{ $course['attempts'] ?? 0 }}</td>
                                <td>
                                    <div class="student-quizzes-result">
                                        <span class="student-quizzes-result__pct">{{ number_format($course['average_score'] ?? 0, 1) }}%</span>
                                        <div class="student-quizzes-result__track">
                                            <div class="student-quizzes-result__bar {{ ($course['average_score'] ?? 0) >= 70 ? 'is-passed' : 'is-failed' }}"
                                                 style="width: {{ min(100, max(0, $course['average_score'] ?? 0)) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ ($course['pass_rate'] ?? 0) >= 70 ? 'success' : 'warning' }}-transparent text-{{ ($course['pass_rate'] ?? 0) >= 70 ? 'success' : 'warning' }}">
                                        {{ number_format($course['pass_rate'] ?? 0, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-lg-none">
                @foreach($performanceByCourse as $course)
                    <div class="student-quiz-analytics-course-card">
                        <p class="student-quiz-analytics-course-card__title">{{ $course['name'] ?? 'غير محدد' }}</p>
                        <div class="student-quiz-analytics-course-card__stats">
                            <div class="student-quiz-analytics-course-card__stat">
                                <span class="student-quiz-analytics-course-card__stat-label">المحاولات</span>
                                <span class="student-quiz-analytics-course-card__stat-value">{{ $course['attempts'] ?? 0 }}</span>
                            </div>
                            <div class="student-quiz-analytics-course-card__stat">
                                <span class="student-quiz-analytics-course-card__stat-label">متوسط النتيجة</span>
                                <span class="student-quiz-analytics-course-card__stat-value">{{ number_format($course['average_score'] ?? 0, 1) }}%</span>
                            </div>
                            <div class="student-quiz-analytics-course-card__stat">
                                <span class="student-quiz-analytics-course-card__stat-label">معدل النجاح</span>
                                <span class="student-quiz-analytics-course-card__stat-value text-{{ ($course['pass_rate'] ?? 0) >= 70 ? 'success' : 'warning' }}">
                                    {{ number_format($course['pass_rate'] ?? 0, 1) }}%
                                </span>
                            </div>
                            <div class="student-quiz-analytics-course-card__stat">
                                <span class="student-quiz-analytics-course-card__stat-label">اختبارات</span>
                                <span class="student-quiz-analytics-course-card__stat-value">{{ $course['quizzes_taken'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="student-quiz-analytics-empty">
                <div class="student-quiz-analytics-empty__icon"><i class="fe fe-bar-chart-2"></i></div>
                <p class="mb-0">لا توجد بيانات أداء حسب الكورس</p>
            </div>
        @endif
    </div>
</div>
