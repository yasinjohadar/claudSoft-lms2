<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-clock text-primary"></i>
            </span>
            <div>
                <h4 class="card-title mb-1">آخر المحاولات</h4>
                <p class="fs-12 text-muted mb-0">أحدث نتائج اختباراتك المكتملة.</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3 px-0 pb-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 dashboard-table student-quizzes-table">
                <thead>
                    <tr>
                        <th class="ps-4 fs-12">الاختبار</th>
                        <th class="fs-12">المحاولة</th>
                        <th class="fs-12">الدرجة</th>
                        <th class="fs-12">النسبة</th>
                        <th class="fs-12">النتيجة</th>
                        <th class="fs-12">التاريخ</th>
                        <th class="text-end pe-4 fs-12">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttempts as $attempt)
                        <tr class="dashboard-stagger-item" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                        <i class="fe fe-file-text text-primary"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <strong class="d-block fs-13 text-truncate" style="max-width: 220px;">
                                            {{ $attempt->questionModule->title ?? 'اختبار' }}
                                        </strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-transparent fs-11">#{{ $attempt->attempt_number }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">{{ number_format($attempt->total_score, 2) }}</strong>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="student-quizzes-result">
                                    <div class="student-quizzes-result__track">
                                        <div class="student-quizzes-result__bar {{ $attempt->is_passed ? 'is-passed' : 'is-failed' }}"
                                             style="width: {{ min(100, max(0, $attempt->percentage)) }}%"
                                             aria-valuenow="{{ $attempt->percentage }}"></div>
                                    </div>
                                    <span class="student-quizzes-result__score">{{ number_format($attempt->percentage, 0) }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($attempt->is_passed)
                                    <span class="badge bg-success-transparent text-success">
                                        <i class="fe fe-check-circle me-1"></i>ناجح
                                    </span>
                                @else
                                    <span class="badge bg-danger-transparent text-danger">
                                        <i class="fe fe-x-circle me-1"></i>راسب
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fs-13">{{ $attempt->completed_at->format('Y-m-d') }}</div>
                                <small class="text-muted">{{ $attempt->completed_at->format('H:i') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('student.question-module.result', $attempt->id) }}"
                                   class="btn btn-sm btn-primary rounded-pill">
                                    <i class="fe fe-eye me-1"></i>عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <div class="group-show-empty py-5">
                                    <i class="fe fe-inbox group-show-empty__icon"></i>
                                    <h5 class="group-show-empty__title">لا توجد محاولات بعد</h5>
                                    <p class="group-show-empty__desc mb-0">ستظهر نتائج اختباراتك هنا عند إكمال أول محاولة.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
