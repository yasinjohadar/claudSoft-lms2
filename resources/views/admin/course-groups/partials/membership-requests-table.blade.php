<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="select-all-checkbox" title="تحديد الكل">
                </th>
                <th>#</th>
                <th>اسم الطالب</th>
                <th>مجموعات أخرى</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>تاريخ الطلب</th>
                <th>موعد تسديد الرسوم</th>
                <th>الرسالة</th>
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
                        <strong>{{ $request->student->name }}</strong>
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
                                    @php
                                        $otherGroupCourse = $otherGroup->courses->first();
                                    @endphp
                                    @if($otherGroupCourse)
                                        <a href="{{ route('courses.groups.show', [$otherGroupCourse->id, $otherGroup->id]) }}" class="badge bg-primary-transparent text-primary text-decoration-none" title="{{ $otherGroup->name }}">
                                            {{ $otherGroup->name }}
                                        </a>
                                    @else
                                        <span class="badge bg-primary-transparent text-primary" title="{{ $otherGroup->name }}">
                                            {{ $otherGroup->name }}
                                        </span>
                                    @endif
                                @endforeach
                                @if($otherGroups->count() > 5)
                                    <span class="badge bg-secondary-transparent text-secondary" title="{{ $otherGroups->skip(5)->pluck('name')->implode('، ') }}">
                                        +{{ $otherGroups->count() - 5 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary copy-email-btn me-1"
                            data-email="{{ $request->student->email }}" title="نسخ البريد">
                            <i class="bi bi-copy"></i>
                        </button>
                        {{ $request->student->email }}
                    </td>
                    <td>
                        @if($request->student->phone)
                            {{ $request->student->phone }}
                            @if($request->student->country_code)
                                ({{ $request->student->country_code }})
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($request->payment_date)
                            <span class="badge bg-info">{{ $request->payment_date->format('Y-m-d') }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($request->message)
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                data-bs-target="#messageModal{{ $request->id }}">
                                <i class="bi bi-envelope"></i> عرض
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($request->status === 'pending')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock-history"></i> قيد المراجعة
                            </span>
                        @elseif($request->status === 'approved')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> مقبول
                            </span>
                            @if($request->approved_at)
                                <br><small class="text-muted">{{ $request->approved_at->format('Y-m-d') }}</small>
                            @endif
                        @elseif($request->status === 'rejected')
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> مرفوض
                            </span>
                            @if($request->rejected_at)
                                <br><small class="text-muted">{{ $request->rejected_at->format('Y-m-d') }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($request->status === 'pending')
                            <div class="btn-group" role="group">
                                <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="قبول">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal{{ $request->id }}" title="رفض">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        @elseif($request->status === 'rejected')
                            <div class="btn-group" role="group">
                                <form action="{{ route('courses.groups.membership-requests.approve', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من قبول طلب الانضمام المرفوض؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="قبول">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من حذف طلب الانضمام نهائياً؟ سيتم الاحتفاظ بالتسجيل المرتبط.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف نهائي">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @elseif($request->status === 'approved')
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal{{ $request->id }}" title="رفض">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                <form action="{{ route('courses.groups.membership-requests.delete', [$course->id, $group->id, $request->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('هل أنت متأكد من حذف سجل طلب الانضمام نهائياً؟ لن يؤثر ذلك على انضمام الطالب للمجموعة.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف نهائي">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>

                @if($request->message)
                    <div class="modal fade" id="messageModal{{ $request->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">رسالة من الطالب</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>{{ $request->message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('courses.groups.membership-requests.reject', [$course->id, $group->id, $request->id]) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">رفض طلب الانضمام</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>هل أنت متأكد من رفض طلب انضمام <strong>{{ $request->student->name }}</strong>؟</p>
                                    <div class="mb-3">
                                        <label class="form-label">ملاحظات (اختياري)</label>
                                        <textarea name="admin_notes" class="form-control" rows="3"
                                            placeholder="أضف ملاحظات حول سبب الرفض..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-danger">رفض الطلب</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">لا توجد طلبات</h5>
                        <p class="text-muted">لا توجد طلبات انضمام لهذه المجموعة</p>
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
