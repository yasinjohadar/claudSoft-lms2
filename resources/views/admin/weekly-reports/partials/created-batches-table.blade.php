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
            <th style="min-width: 140px;">العمليات</th>
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
                    @if(filled($batch['report_description'] ?? null))
                        <div class="text-muted fs-11 mt-1 text-truncate" style="max-width: 220px;">
                            {{ Str::limit(strip_tags($batch['report_description']), 60) }}
                        </div>
                    @endif
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
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <a class="btn btn-sm btn-icon btn-outline-primary"
                           href="{{ route('admin.weekly-reports.created.batch', ['batch' => $batch['key']]) }}"
                           title="عرض الطلاب">
                            <i class="fe fe-users"></i>
                        </a>
                        <a class="btn btn-sm btn-icon btn-outline-info"
                           href="{{ route('admin.weekly-reports.created.batch.edit', ['batch' => $batch['key']]) }}"
                           title="تعديل التقرير">
                            <i class="fe fe-edit-2"></i>
                        </a>
                        <button type="button"
                                class="btn btn-sm btn-icon btn-outline-danger weekly-batch-delete-btn"
                                title="حذف الدفعة"
                                data-batch-key="{{ $batch['key'] }}"
                                data-batch-title="{{ $batch['report_title'] }}"
                                data-students-count="{{ $batch['students_count'] }}"
                                data-submitted-count="{{ $batch['submitted_count'] }}">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    </div>
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
