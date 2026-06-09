@php
    $statusLabels = [
        'draft' => ['label' => 'مسودة', 'class' => 'bg-secondary-transparent text-secondary'],
        'submitted' => ['label' => 'مرسل', 'class' => 'bg-primary-transparent text-primary'],
        'reviewed' => ['label' => 'مراجع', 'class' => 'bg-info-transparent text-info'],
        'closed' => ['label' => 'مغلق', 'class' => 'bg-warning-transparent text-warning'],
    ];
    $isFiltered = $batch['is_filtered'] ?? false;
    $filteredCount = $batch['filtered_count'] ?? $batch['student_reports']->count();
    $totalCount = $batch['students_count'] ?? $batch['student_reports']->count();
@endphp

@if($isFiltered)
    <div class="alert alert-light border py-2 px-3 mb-3 small" role="status" id="batchStudentsFilterSummary">
        عدد النتائج المطابقة:
        <strong class="text-dark">{{ $filteredCount }}</strong>
        <span class="text-muted ms-1">من أصل <strong>{{ $totalCount }}</strong> طالب</span>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th style="width: 48px;">#</th>
            <th>الطالب</th>
            <th>البريد</th>
            <th>الهاتف</th>
            <th>الحالة</th>
            <th>وقت الإرسال</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($batch['student_reports'] as $report)
            @php
                $student = $report->student;
                $displayPhone = $student->full_phone
                    ?? (($student->country_code && $student->phone) ? $student->country_code . $student->phone : null)
                    ?? $student->phone;
                $status = $statusLabels[$report->status] ?? ['label' => $report->status, 'class' => 'bg-secondary-transparent text-secondary'];
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0">
                            <i class="fe fe-user text-primary"></i>
                        </span>
                        <div>
                            <span class="d-block fw-semibold">{{ $student->name_ar ?? $student->name ?? '—' }}</span>
                            @if($student->name_ar && $student->name)
                                <small class="text-muted">{{ $student->name }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>{{ $student->email ?? '—' }}</td>
                <td>{{ $displayPhone ?? '—' }}</td>
                <td>
                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                </td>
                <td>{{ $report->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                    <a class="btn btn-sm btn-primary-light" href="{{ route('admin.weekly-reports.show', $report) }}">
                        <i class="fe fe-eye me-1"></i>عرض التقرير
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    @if($isFiltered)
                        لا توجد نتائج مطابقة للبحث أو الفلتر المحدد.
                    @else
                        لا يوجد طلاب في هذه الدفعة.
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
