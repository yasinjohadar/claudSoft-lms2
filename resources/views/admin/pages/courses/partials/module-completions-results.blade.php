@if($groupFilterActive || ($searchActive ?? false))
    <div class="alert alert-light border py-2 px-3 mb-3 small js-completions-filter-banner" role="status">
        عدد النتائج المطابقة للفلتر:
        <strong class="text-dark">{{ $completions->total() }}</strong>
        @if($groupFilterActive)
            <span class="text-muted ms-1">من أصل <strong>{{ $totalWithoutGroupFilter }}</strong> مطابقين لحالة التقدم والبحث (بدون فلتر المجموعة)</span>
        @elseif($searchActive ?? false)
            <span class="text-muted ms-1">من أصل <strong>{{ $totalStatusOnly }}</strong> بنفس حالة التقدم (بدون بحث)</span>
        @endif
    </div>
@endif

<div class="card custom-card group-show-members-card dashboard-fade-in module-completions-table">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
        <h6 class="group-show-members-card__title mb-0">
            الطلاب
            <span class="group-show-members-card__count">{{ $completions->total() }}</span>
        </h6>
        <small class="text-muted">أرسل رسالة مخصصة لكل طالب عبر القوالب الجاهزة</small>
    </div>
    <div class="card-body p-0 pt-3">
        @if($completions->isEmpty())
            <p class="text-muted p-4 mb-0">لا توجد سجلات تقدم مطابقة للمعايير الحالية.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الطالب</th>
                        <th>التواصل</th>
                        <th>الحالة</th>
                        <th>تاريخ الإكمال</th>
                        <th>آخر وصول</th>
                        <th>الوقت</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($completions as $row)
                        @php
                            $student = $row->student;
                            $phoneDisplay = $student?->full_phone
                                ?: trim(($student?->country_code ?? '').($student?->phone ?? ''))
                                ?: null;
                            $hasPhone = $phoneDisplay !== null && $phoneDisplay !== '';
                            $hasEmail = trim((string) ($student?->email ?? '')) !== '';
                        @endphp
                        <tr>
                            <td class="text-muted">{{ ($completions->currentPage() - 1) * $completions->perPage() + $loop->iteration }}</td>
                            <td>
                                @if($student)
                                    <a href="{{ route('users.show', $student->id) }}" class="fw-semibold text-decoration-none">{{ $student->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($hasEmail)
                                    <div class="student-meta" dir="ltr">{{ $student->email }}</div>
                                @endif
                                @if($hasPhone)
                                    <div class="student-meta" dir="ltr">{{ $phoneDisplay }}</div>
                                @endif
                                @if(! $hasEmail && ! $hasPhone)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->completion_status === 'completed')
                                    <span class="badge bg-success-transparent text-success">مكتمل</span>
                                @elseif($row->completion_status === 'in_progress')
                                    <span class="badge bg-warning-transparent text-warning">قيد التقدم</span>
                                @else
                                    <span class="badge bg-secondary-transparent text-secondary">{{ $row->completion_status }}</span>
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
                            <td>{{ $row->time_spent !== null ? $row->time_spent.' د' : '—' }}</td>
                            <td class="text-end module-completions-actions">
                                @if($student)
                                    @if($hasPhone)
                                        <button type="button"
                                                class="btn btn-sm btn-success js-module-wa-message"
                                                title="واتساب"
                                                data-student-id="{{ $student->id }}"
                                                data-completion-id="{{ $row->id }}"
                                                data-student-name="{{ $student->name }}"
                                                data-student-phone="{{ $phoneDisplay }}">
                                            <i class="ri-whatsapp-line"></i>
                                        </button>
                                    @endif
                                    @if($hasEmail)
                                        <button type="button"
                                                class="btn btn-sm btn-primary js-module-email-message"
                                                title="بريد"
                                                data-student-id="{{ $student->id }}"
                                                data-completion-id="{{ $row->id }}"
                                                data-student-name="{{ $student->name }}"
                                                data-student-email="{{ $student->email }}">
                                            <i class="ri-mail-send-line"></i>
                                        </button>
                                    @endif
                                @endif
                            </td>
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
