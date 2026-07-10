<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 group-show-table">
        <thead>
        <tr>
            <th>الواجب</th>
            <th>الكورس</th>
            <th>موعد التسليم</th>
            <th>الحالة</th>
            <th>الدرجة</th>
            <th>المحاولات</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($assignmentsData as $data)
            @php
                $assignment = $data['assignment'];
            @endphp
            <tr class="student-quizzes-stagger" style="--stagger-delay: {{ $loop->index * 40 }}ms">
                <td>
                    <div class="d-flex align-items-start gap-2">
                        <span class="avatar avatar-sm bg-warning-transparent flex-shrink-0 mt-1">
                            <i class="fe fe-clipboard text-warning"></i>
                        </span>
                        <div class="min-w-0">
                            <a href="{{ route('student.assignments.show', $assignment->id) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                {{ $assignment->title }}
                            </a>
                            @if($assignment->lesson)
                                <small class="text-muted d-block mt-1">
                                    <i class="fe fe-book-open me-1"></i>{{ $assignment->lesson->title }}
                                </small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-primary-transparent text-primary">{{ $assignment->course?->title ?? '—' }}</span>
                </td>
                <td>
                    @if($assignment->due_date)
                        <div class="d-flex flex-column gap-1">
                            <span class="{{ $data['status'] === 'overdue' ? 'text-danger fw-semibold' : '' }}">
                                {{ $assignment->due_date->format('Y-m-d') }}
                            </span>
                            <small class="text-muted">{{ $assignment->due_date->format('h:i A') }}</small>
                            @if($data['status'] === 'overdue')
                                <span class="badge bg-danger-transparent text-danger align-self-start">متأخر</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">بدون موعد</span>
                    @endif
                </td>
                <td>
                    @switch($data['status'])
                        @case('graded')
                            <span class="badge bg-success-transparent text-success">تم التقييم</span>
                            @break
                        @case('submitted')
                            <span class="badge bg-warning-transparent text-warning">بانتظار التقييم</span>
                            @break
                        @case('overdue')
                            <span class="badge bg-danger-transparent text-danger">متأخر</span>
                            @break
                        @default
                            <span class="badge bg-secondary-transparent text-secondary">لم يُسلّم</span>
                    @endswitch
                </td>
                <td>
                    @if($data['grade'] !== null)
                        <span class="fw-semibold {{ $data['grade'] >= ($assignment->max_grade * 0.6) ? 'text-success' : 'text-danger' }}">
                            {{ $data['grade'] }} / {{ $assignment->max_grade }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-light text-dark">{{ $data['submissions_count'] }}</span>
                </td>
                <td class="text-end">
                    <a href="{{ route('student.assignments.show', $assignment->id) }}"
                       class="btn btn-sm {{ $data['can_submit'] ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">
                        @if($data['can_submit'])
                            @if($data['submissions_count'] > 0)
                                <i class="fe fe-refresh-cw me-1"></i>إعادة التسليم
                            @else
                                <i class="fe fe-upload me-1"></i>تسليم
                            @endif
                        @else
                            <i class="fe fe-eye me-1"></i>عرض
                        @endif
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
