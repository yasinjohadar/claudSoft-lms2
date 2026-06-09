<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>الطالب</th>
            <th>عنوان التقرير</th>
            <th>الحالة</th>
            <th>وقت الإرسال</th>
            <th>الموعد النهائي</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-user text-primary"></i>
                        </span>
                        <span>{{ $report->student->name_ar ?? $report->student->name ?? '-' }}</span>
                    </div>
                </td>
                <td>{{ $report->report_title }}</td>
                <td>
                    @if($report->status === 'reviewed')
                        <span class="badge bg-info-transparent text-info">مراجع</span>
                    @else
                        <span class="badge bg-primary-transparent text-primary">مرسل</span>
                    @endif
                </td>
                <td>{{ $report->submitted_at?->format('Y-m-d H:i') ?? '-' }}</td>
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
