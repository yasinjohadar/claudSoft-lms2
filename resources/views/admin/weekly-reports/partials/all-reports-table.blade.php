@php
    $statusLabels = [
        'draft' => ['label' => 'مسودة', 'class' => 'bg-secondary-transparent text-secondary'],
        'submitted' => ['label' => 'مرسل', 'class' => 'bg-primary-transparent text-primary'],
        'reviewed' => ['label' => 'مراجع', 'class' => 'bg-info-transparent text-info'],
        'closed' => ['label' => 'مغلق', 'class' => 'bg-warning-transparent text-warning'],
    ];
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th style="width: 48px;">#</th>
            <th>الطالب</th>
            <th>البريد</th>
            <th>عنوان التقرير</th>
            <th>الكورس</th>
            <th>المجموعة</th>
            <th>الحالة</th>
            <th>الموعد النهائي</th>
            <th>وقت الإرسال</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($reports as $report)
            @php
                $status = $statusLabels[$report->status] ?? ['label' => $report->status, 'class' => 'bg-secondary-transparent text-secondary'];
            @endphp
            <tr>
                <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0">
                            <i class="fe fe-user text-primary"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="d-block">{{ $report->student->name_ar ?? $report->student->name ?? '-' }}</span>
                            @if($report->student->name_ar && $report->student->name)
                                <small class="text-muted d-block">{{ $report->student->name }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <small>{{ $report->student->email ?? '—' }}</small>
                </td>
                <td>{{ $report->report_title }}</td>
                <td>{{ $report->targetCourse?->title ?? '—' }}</td>
                <td>{{ $report->targetGroup?->name ?? '—' }}</td>
                <td>
                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                </td>
                <td>{{ $report->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $report->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                    <a class="btn btn-sm btn-primary-light" href="{{ route('admin.weekly-reports.show', $report) }}">
                        <i class="fe fe-eye me-1"></i>عرض
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    لا توجد تقارير مطابقة للفلاتر المحددة.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($reports->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $reports->withQueryString()->links() }}
    </div>
@endif
