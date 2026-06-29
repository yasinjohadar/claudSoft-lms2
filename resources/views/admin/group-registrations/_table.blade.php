<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>المسجّل</th>
                <th>التواصل</th>
                <th>المجموعة</th>
                <th>حاسوب</th>
                <th>الحالة</th>
                <th>الحساب</th>
                <th>الإشعارات</th>
                <th>تاريخ التسجيل</th>
                <th style="width: 130px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $registration)
                @php
                    $group = $registration->group;
                    $course = $group?->courses?->first();
                @endphp
                <tr class="gr-table-row">
                    <td>{{ $loop->iteration + ($registrations->currentPage() - 1) * $registrations->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="gr-reg-icon"><i class="fe fe-user"></i></span>
                            <div class="min-w-0">
                                <span class="fw-semibold d-block text-truncate" style="max-width: 200px;" title="{{ $registration->name_ar ?? $registration->name }}">
                                    {{ $registration->name_ar ?? $registration->name }}
                                </span>
                                @if($registration->name_ar && $registration->name !== $registration->name_ar)
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $registration->name }}</small>
                                @endif
                                @if($registration->nationality)
                                    <span class="gr-meta-chip mt-1">{{ $registration->nationality->name }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="min-w-0">
                            <span class="d-block text-truncate" style="max-width: 180px;" title="{{ $registration->email }}">{{ $registration->email }}</span>
                            <small class="text-muted">{{ $registration->full_phone ?? $registration->phone }}</small>
                        </div>
                    </td>
                    <td>
                        @if($group)
                            @if($course)
                                <a href="{{ route('courses.groups.show', [$course->id, $registration->group_id]) }}" class="assignments-course-chip text-decoration-none" title="{{ $group->name }}">
                                    {{ $group->name }}
                                </a>
                            @else
                                <span class="assignments-course-chip" title="{{ $group->name }}">{{ $group->name }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->has_computer === 'yes')
                            <span class="gr-bool-chip gr-bool-chip--yes">نعم</span>
                        @elseif($registration->has_computer === 'no')
                            <span class="gr-bool-chip gr-bool-chip--no">لا</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->status === 'pending')
                            <span class="gr-status-chip gr-status-chip--pending">معلق</span>
                        @elseif($registration->status === 'processing')
                            <span class="gr-status-chip gr-status-chip--processing">قيد المعالجة</span>
                        @elseif($registration->status === 'completed')
                            <span class="gr-status-chip gr-status-chip--completed">مكتمل</span>
                        @else
                            <span class="gr-status-chip gr-status-chip--failed">فاشل</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->user_created)
                            <span class="gr-bool-chip gr-bool-chip--yes"><i class="fe fe-check me-1"></i>نعم</span>
                        @else
                            <span class="gr-bool-chip gr-bool-chip--no">لا</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @if($registration->email_sent)
                                <span class="gr-meta-chip"><i class="fe fe-mail"></i> بريد</span>
                            @endif
                            @if($registration->whatsapp_sent)
                                <span class="gr-meta-chip"><i class="fe fe-message-circle"></i> واتساب</span>
                            @elseif($registration->whatsapp_error)
                                <span class="gr-status-chip gr-status-chip--failed" title="{{ $registration->whatsapp_error }}">واتساب ✗</span>
                            @endif
                            @if(!$registration->email_sent && !$registration->whatsapp_sent && !$registration->whatsapp_error)
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <small class="text-muted d-block">{{ $registration->created_at->format('Y-m-d') }}</small>
                        <small class="text-muted">{{ $registration->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('admin.group-registrations.show', $registration->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            @if($registration->status === 'failed' || $registration->status === 'pending')
                                <form action="{{ route('admin.group-registrations.reprocess', $registration->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning-light btn-sm assignments-actions__btn" title="إعادة المعالجة">
                                        <i class="fe fe-rotate-cw"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.group-registrations.destroy', $registration->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا التسجيل؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-light btn-sm assignments-actions__btn" title="حذف">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="group-show-empty py-2">
                            <i class="fe fe-user-plus group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد تسجيلات</h5>
                            <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو إعادة تعيين البحث.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($registrations->hasPages())
    <div class="mt-3">{{ $registrations->withQueryString()->links() }}</div>
@endif
