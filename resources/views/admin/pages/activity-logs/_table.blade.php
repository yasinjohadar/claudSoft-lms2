<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 admin-users-table">
        <thead>
            <tr>
                <th>#</th>
                <th>التاريخ</th>
                <th>المستخدم</th>
                <th>النوع</th>
                <th>الحدث</th>
                <th>الوصف</th>
                <th>الكيان</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                @php
                    $queryService = app(\App\Services\Admin\ActivityLogQueryService::class);
                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($activities->currentPage() - 1) * $activities->perPage() }}</td>
                    <td><small class="text-muted">{{ $activity->created_at?->format('Y-m-d H:i:s') }}</small></td>
                    <td>{{ $activity->causer?->name ?? '—' }}</td>
                    <td>
                        <span class="group-show-chip group-show-chip--sm">
                            {{ $logNameLabels[$activity->log_name] ?? ($activity->log_name ?? '—') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $activity->event === 'deleted' ? 'danger' : ($activity->event === 'created' ? 'success' : 'primary') }}-transparent">
                            {{ $eventLabels[$activity->event] ?? ($activity->event ?? '—') }}
                        </span>
                    </td>
                    <td class="text-truncate" style="max-width: 260px;" title="{{ $activity->description }}">
                        {{ $activity->description }}
                    </td>
                    <td><small>{{ $queryService->subjectLabel($activity) }}</small></td>
                    <td>
                        <a href="{{ route('admin.activity-logs.show', $activity) }}" class="btn btn-sm btn-info-light" title="التفاصيل">
                            <i class="fe fe-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">لا توجد سجلات مطابقة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($activities->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $activities->links() }}
    </div>
@endif
