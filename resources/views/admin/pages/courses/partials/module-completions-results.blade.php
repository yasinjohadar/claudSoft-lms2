{{-- نتائج التقرير + شريط الأعداد + جدول + ترقيم (يُحدَّث عبر Ajax) --}}
@if($groupFilterActive || $searchActive)
    <div class="alert alert-light border py-2 px-3 mb-3 small js-completions-filter-banner" role="status">
        عدد النتائج المطابقة للفلتر:
        <strong class="text-dark">{{ $completions->total() }}</strong>
        @if($groupFilterActive)
            <span class="text-muted ms-1">من أصل <strong>{{ $totalWithoutGroupFilter }}</strong> مطابقين لحالة التقدم والبحث الحاليين (بدون فلتر المجموعة)</span>
        @elseif($searchActive)
            <span class="text-muted ms-1">من أصل <strong>{{ $totalStatusOnly }}</strong> بنفس حالة التقدم (بدون بحث)</span>
        @endif
    </div>
@endif

<div class="card custom-card">
    <div class="card-header">
        <div class="card-title mb-0">
            <i class="fas fa-user-check me-2"></i>الطلاب
        </div>
    </div>
    <div class="card-body p-0">
        @if($completions->isEmpty())
            <p class="text-muted p-4 mb-0">لا توجد سجلات تقدم مطابقة للمعايير الحالية.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover text-nowrap mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>البريد</th>
                        <th>الحالة</th>
                        <th>تاريخ الإكمال</th>
                        <th>آخر وصول</th>
                        <th>الوقت المستغرق</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($completions as $row)
                        <tr>
                            <td>{{ ($completions->currentPage() - 1) * $completions->perPage() + $loop->iteration }}</td>
                            <td>
                                @if($row->student)
                                    <a href="{{ route('users.show', $row->student_id) }}" class="text-decoration-none">{{ $row->student->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ optional($row->student)->email ?? '—' }}</td>
                            <td>
                                @if($row->completion_status === 'completed')
                                    <span class="badge bg-success">مكتمل</span>
                                @elseif($row->completion_status === 'in_progress')
                                    <span class="badge bg-warning text-dark">قيد التقدم</span>
                                @else
                                    <span class="badge bg-secondary">{{ $row->completion_status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($row->completed_at)
                                    <span title="{{ $row->completed_at->format('Y-m-d H:i:s') }}">{{ $row->completed_at->format('Y-m-d H:i') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->last_accessed_at)
                                    <span title="{{ $row->last_accessed_at->format('Y-m-d H:i:s') }}">{{ $row->last_accessed_at->format('Y-m-d H:i') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $row->time_spent !== null ? $row->time_spent . ' د' : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top module-completions-pagination">
                {{ $completions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
