<div class="d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 student-study-reports-table">
            <thead>
                <tr>
                    <th class="ps-4 fs-12">الكورس</th>
                    <th class="fs-12">المجموعة</th>
                    <th class="fs-12">التاريخ</th>
                    <th class="text-end pe-4 fs-12">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr class="student-my-courses-stagger" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fe fe-file-text text-primary"></i>
                                </span>
                                <strong class="fs-13">{{ $report->course?->title ?? '—' }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($report->courseGroup)
                                <span class="badge bg-info-transparent fs-11">{{ $report->courseGroup->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fs-13">{{ $report->created_at?->format('Y/m/d') }}</div>
                            <small class="text-muted">{{ $report->created_at?->format('H:i') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('student.progress.ai-reports.show', $report) }}" class="btn btn-sm btn-primary rounded-pill">
                                <i class="fe fe-eye me-1"></i>عرض التقرير
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-0 border-0">
                            @include('student.study-reports.partials.study-reports-empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
