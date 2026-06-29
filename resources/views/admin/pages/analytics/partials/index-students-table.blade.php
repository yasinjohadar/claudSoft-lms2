@if($students->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0 qa-index-table">
            <thead>
                <tr>
                    <th style="width: 48px;">#</th>
                    <th>الطالب</th>
                    <th class="text-center">المحاولات</th>
                    <th class="text-center">متوسط الدرجة</th>
                    <th style="width: 80px;">الإجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    @php
                        $rank = $loop->iteration;
                        $rankClass = match(true) {
                            $rank === 1 => 'qa-index-rank--gold',
                            $rank === 2 => 'qa-index-rank--silver',
                            $rank === 3 => 'qa-index-rank--bronze',
                            default => 'qa-index-rank--default',
                        };
                        $scoreChipClass = $student->average_score >= 70 ? 'qa-score-chip--high' : ($student->average_score >= 50 ? 'qa-score-chip--mid' : 'qa-score-chip--low');
                    @endphp
                    <tr>
                        <td><span class="qa-index-rank {{ $rankClass }}">{{ $rank }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="qa-index-student-icon"><i class="fe fe-user"></i></span>
                                <span class="fw-semibold text-truncate">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="qp-questions-chip">{{ $student->attempts_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="qa-score-chip {{ $scoreChipClass }}">
                                {{ number_format($student->average_score, 1) }}%
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('users.student-details', $student->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="ملف الطالب">
                                <i class="fe fe-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="quiz-analytics-empty py-4">
        <div><i class="fe fe-users d-block"></i></div>
        <p class="mb-0">{{ $emptyMessage ?? 'لا توجد بيانات متاحة' }}</p>
    </div>
@endif
