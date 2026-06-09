<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr>
            <th style="width: 48px;">#</th>
            <th>عنوان التقرير</th>
            <th>الكورس</th>
            <th>المجموعة</th>
            <th>أنشأه</th>
            <th>تاريخ الإنشاء</th>
            <th>الموعد النهائي</th>
            <th>الطلاب</th>
            <th>مسلّم</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($batches as $batch)
            <tr>
                <td>{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                <td>
                    <a href="{{ route('admin.weekly-reports.created.batch', ['batch' => $batch['key']]) }}"
                       class="fw-semibold text-primary text-decoration-none">
                        {{ $batch['report_title'] }}
                    </a>
                </td>
                <td>{{ $batch['target_course']?->title ?? '—' }}</td>
                <td>{{ $batch['target_group']?->name ?? '—' }}</td>
                <td>{{ $batch['created_by_admin']?->name_ar ?? $batch['created_by_admin']?->name ?? '—' }}</td>
                <td>{{ $batch['created_at']?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $batch['due_at']?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>
                    <span class="badge bg-primary-transparent text-primary">{{ $batch['students_count'] }} طالب</span>
                </td>
                <td>
                    <span class="badge bg-success-transparent text-success">{{ $batch['submitted_count'] }} مسلّم</span>
                    @if($batch['pending_count'] > 0)
                        <span class="badge bg-warning-transparent text-warning">{{ $batch['pending_count'] }} بانتظار</span>
                    @endif
                </td>
                <td>
                    <a class="btn btn-sm btn-primary-light" href="{{ route('admin.weekly-reports.created.batch', ['batch' => $batch['key']]) }}">
                        <i class="fe fe-users me-1"></i>عرض الطلاب
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    لا توجد تقارير منشأة من الأدمن مطابقة للفلاتر المحددة.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($batches->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $batches->withQueryString()->links() }}
    </div>
@endif
