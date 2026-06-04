<div class="d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 student-quizzes-table">
            <thead>
                <tr>
                    <th class="ps-4 fs-12">الاختبار</th>
                    <th class="fs-12">الكورس</th>
                    <th class="fs-12">المحاولة</th>
                    <th class="fs-12">تاريخ البدء</th>
                    <th class="fs-12">تاريخ التسليم</th>
                    <th class="fs-12">النتيجة</th>
                    <th class="fs-12">الحالة</th>
                    <th class="text-end pe-4 fs-12">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts ?? [] as $attempt)
                    <tr class="student-quizzes-stagger" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fe fe-file-text text-primary"></i>
                                </span>
                                <div class="min-w-0">
                                    <strong class="d-block fs-13">{{ $attempt->quiz?->title ?? 'غير محدد' }}</strong>
                                    @if($attempt->quiz?->description)
                                        <small class="text-muted">{{ Str::limit($attempt->quiz->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($attempt->quiz?->course)
                                <span class="badge bg-info-transparent fs-11">
                                    <i class="fe fe-book me-1"></i>{{ $attempt->quiz->course->title }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-transparent fs-11">المحاولة #{{ $attempt->attempt_number }}</span>
                        </td>
                        <td>
                            @if($attempt->started_at)
                                <div class="fs-13">{{ $attempt->started_at->format('Y/m/d') }}</div>
                                <small class="text-muted">{{ $attempt->started_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attempt->submitted_at)
                                <div class="fs-13">{{ $attempt->submitted_at->format('Y/m/d') }}</div>
                                <small class="text-muted">{{ $attempt->submitted_at->format('H:i') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="min-width: 140px;">
                            @include('student.pages.quizzes.partials.review-result', ['attempt' => $attempt])
                        </td>
                        <td>
                            @include('student.pages.quizzes.partials.review-status-badge', ['attempt' => $attempt])
                        </td>
                        <td class="text-end pe-4">
                            @include('student.pages.quizzes.partials.review-actions', ['attempt' => $attempt])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0 border-0">
                            @include('student.pages.quizzes.partials.review-attempts-empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
