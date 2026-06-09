@php
    use App\Models\StudentWeeklyReport;
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>عنوان التقرير</th>
            <th>الموعد النهائي</th>
            <th>الحالة</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            @php
                $isOverdue = $report->due_at
                    && $report->due_at->isPast()
                    && !in_array($report->status, [StudentWeeklyReport::STATUS_SUBMITTED, StudentWeeklyReport::STATUS_REVIEWED], true);
            @endphp
            <tr class="student-weekly-reports-stagger" style="--stagger-delay: {{ $loop->index * 40 }}ms">
                <td>
                    <div class="d-flex align-items-start gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0 mt-1">
                            <i class="fe fe-file-text text-primary"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="fw-semibold d-block">{{ $report->report_title }}</span>
                            @if(filled($report->admin_feedback))
                                <span class="badge bg-info-transparent text-info mt-1">
                                    <i class="fe fe-message-circle me-1"></i>يوجد رد من الإدارة
                                </span>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column gap-1">
                        <span>{{ $report->due_at?->format('Y-m-d H:i') ?? 'غير محدد' }}</span>
                        @if($isOverdue)
                            <span class="badge bg-danger-transparent text-danger align-self-start">متأخر</span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($report->status === StudentWeeklyReport::STATUS_CLOSED)
                        <span class="badge bg-danger-transparent text-danger">مغلق</span>
                    @elseif($report->status === StudentWeeklyReport::STATUS_REVIEWED)
                        <span class="badge bg-success-transparent text-success">تمت المراجعة</span>
                    @elseif($report->status === StudentWeeklyReport::STATUS_SUBMITTED)
                        <span class="badge bg-info-transparent text-info">مرسل</span>
                    @else
                        <span class="badge bg-warning-transparent text-warning">مسودة</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('student.weekly-reports.show', $report) }}"
                       class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fe fe-eye me-1"></i>فتح التقرير
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
