<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th width="40">
                    <input type="checkbox" id="select-all-checkbox" title="تحديد الكل">
                </th>
                <th>#</th>
                <th>اسم الطالب</th>
                <th>مجموعات أخرى</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>تاريخ الطلب</th>
                <th>موعد تسديد الرسوم</th>
                <th>عرض الفورم</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    <td>
                        <input type="checkbox" class="request-checkbox" name="request_ids[]" value="{{ $request->id }}" data-status="{{ $request->status }}">
                    </td>
                    <td>{{ $request->id }}</td>
                    <td>
                        <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                           class="fw-semibold text-decoration-none admin-camps-table__name">
                            {{ $request->student->name }}
                        </a>
                        @if($request->student->name_ar)
                            <br><small class="text-muted">{{ $request->student->name_ar }}</small>
                        @endif
                    </td>
                    <td>
                        @php
                            $map = $otherGroupsByStudentId ?? collect();
                            $sidOg = (int) $request->student_id;
                            $otherGroups = $map[$sidOg] ?? $map[$request->student_id] ?? collect();
                        @endphp
                        @if($otherGroups->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($otherGroups->take(5) as $otherGroup)
                                    @php $otherGroupCourse = $otherGroup->courses->first(); @endphp
                                    @if($otherGroupCourse)
                                        <a href="{{ route('courses.groups.show', [$otherGroupCourse->id, $otherGroup->id]) }}"
                                           class="group-show-chip group-show-chip--sm text-decoration-none" title="{{ $otherGroup->name }}">
                                            {{ $otherGroup->name }}
                                        </a>
                                    @else
                                        <span class="group-show-chip group-show-chip--sm">{{ $otherGroup->name }}</span>
                                    @endif
                                @endforeach
                                @if($otherGroups->count() > 5)
                                    <span class="badge bg-secondary-transparent text-secondary">+{{ $otherGroups->count() - 5 }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            @if($request->student->email)
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 copy-email-btn"
                                    data-email="{{ $request->student->email }}" title="نسخ البريد">
                                    <i class="fe fe-copy"></i>
                                </button>
                            @endif
                            <span class="text-break">{{ $request->student->email }}</span>
                        </div>
                    </td>
                    <td>
                        @if($request->student->phone)
                            {{ $request->student->phone }}
                            @if($request->student->country_code)
                                <small class="text-muted">({{ $request->student->country_code }})</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><small>{{ $request->created_at->format('Y-m-d H:i') }}</small></td>
                    <td>
                        @if($request->payment_date)
                            <span class="badge bg-info-transparent text-info">{{ $request->payment_date->format('Y-m-d') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                           class="btn btn-sm btn-primary-light">
                            <i class="fe fe-eye me-1"></i>عرض البيانات
                        </a>
                    </td>
                    <td>
                        @if($request->status === 'pending')
                            <span class="badge bg-warning-transparent text-warning">
                                <i class="fe fe-clock me-1"></i>قيد المراجعة
                            </span>
                        @elseif($request->status === 'approved')
                            <span class="badge bg-success-transparent text-success">
                                <i class="fe fe-check-circle me-1"></i>مقبول
                            </span>
                            @if($request->approved_at)
                                <br><small class="text-muted">{{ $request->approved_at->format('Y-m-d') }}</small>
                            @endif
                        @elseif($request->status === 'rejected')
                            <span class="badge bg-danger-transparent text-danger">
                                <i class="fe fe-x-circle me-1"></i>مرفوض
                            </span>
                            @if($request->rejected_at)
                                <br><small class="text-muted">{{ $request->rejected_at->format('Y-m-d') }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                               class="btn btn-sm btn-info-light" title="مراجعة بيانات الفورم">
                                <i class="fe fe-file-text"></i>
                            </a>
                            @if($request->status === 'pending')
                                <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success-light" title="قبول">
                                        <i class="fe fe-check"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger-light" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal{{ $request->id }}" title="رفض">
                                    <i class="fe fe-x"></i>
                                </button>
                            @elseif($request->status === 'rejected')
                                <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام المرفوض؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success-light" title="قبول">
                                        <i class="fe fe-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من حذف طلب الانضمام نهائياً؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger-light" title="حذف">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </form>
                            @elseif($request->status === 'approved')
                                <button type="button" class="btn btn-sm btn-danger-light" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal{{ $request->id }}" title="رفض">
                                    <i class="fe fe-x"></i>
                                </button>
                                <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('حذف سجل الطلب؟ لن يؤثر على انضمام الطالب.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $request->id]) }}" method="POST">
                                @csrf
                                <div class="modal-header border-0">
                                    <h5 class="modal-title"><i class="fe fe-x-circle me-2 text-danger"></i>رفض طلب الانضمام</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>هل أنت متأكد من رفض طلب <strong>{{ $request->student->name }}</strong>؟</p>
                                    <div class="mb-0">
                                        <label class="form-label">ملاحظات (اختياري)</label>
                                        <textarea name="admin_notes" class="form-control" rows="3"
                                            placeholder="أضف ملاحظات حول سبب الرفض..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-danger">رفض الطلب</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="11">
                        <div class="group-show-empty">
                            <div class="group-show-empty__icon">
                                <i class="fe fe-inbox"></i>
                            </div>
                            <h4 class="group-show-empty__title">لا توجد طلبات</h4>
                            <p class="text-muted mb-0">لا توجد طلبات انضمام لهذه المجموعة حالياً.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($requests->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $requests->withQueryString()->links() }}
    </div>
@endif
