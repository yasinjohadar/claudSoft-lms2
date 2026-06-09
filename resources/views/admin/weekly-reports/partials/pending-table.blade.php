<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>الطالب</th>
            <th>عنوان التقرير</th>
            <th>الكورس</th>
            <th>المجموعة</th>
            <th>الحالة</th>
            <th>الموعد النهائي</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-warning-transparent">
                            <i class="fe fe-user text-warning"></i>
                        </span>
                        <span>{{ $report->student->name_ar ?? $report->student->name ?? '-' }}</span>
                    </div>
                </td>
                <td>{{ $report->report_title }}</td>
                <td>{{ $report->targetCourse?->title ?? '-' }}</td>
                <td>{{ $report->targetGroup?->name ?? '-' }}</td>
                <td>
                    @if($report->status === 'closed')
                        <span class="badge bg-danger-transparent text-danger">مغلق (لم يُسلّم)</span>
                    @else
                        <span class="badge bg-warning-transparent text-warning">مسودة</span>
                    @endif
                </td>
                <td>{{ $report->due_at?->format('Y-m-d H:i') ?? 'غير محدد' }}</td>
                <td>
                    <a class="btn btn-sm btn-primary-light" href="{{ route('admin.weekly-reports.show', $report) }}">
                        <i class="fe fe-eye me-1"></i>عرض
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
