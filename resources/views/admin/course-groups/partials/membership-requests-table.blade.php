@php
    use App\Support\MembershipRequestFormColumns;

    $waContext = $waContext ?? [];
    $waSelectedJid = $waContext['selected_jid'] ?? '';
    $waStatusMap = $waContext['wa_status_by_student_id'] ?? [];
    $phoneDigitsMap = $waContext['phone_digits_by_student_id'] ?? [];
    $defaultInviteMessage = $waContext['default_invite_message'] ?? '';
    $showWaColumn = $waSelectedJid !== '' && empty($waContext['wa_load_error']);
    $formColumns = MembershipRequestFormColumns::definitions();
    $registrationsByRequestId = $registrationsByRequestId ?? [];
@endphp
<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0"
           id="membershipRequestsTable"
           data-columns-storage-key="mr-cols-{{ $course->id }}-{{ $group->id }}">
        <thead>
            <tr>
                <th width="40" data-mr-col="select">
                    <input type="checkbox" id="select-all-checkbox" title="تحديد الكل">
                </th>
                <th data-mr-col="id">#</th>
                <th data-mr-col="student">اسم الطالب</th>
                <th data-mr-col="other_groups">مجموعات أخرى</th>
                <th data-mr-col="email">البريد الإلكتروني</th>
                <th data-mr-col="email_invite">دعوة بريد</th>
                <th data-mr-col="phone">رقم الهاتف</th>
                @if($showWaColumn)
                    <th data-mr-col="whatsapp">واتساب</th>
                @endif
                @foreach($formColumns as $colKey => $colDef)
                    <th data-mr-col="{{ $colKey }}" class="d-none">{{ $colDef['label'] }}</th>
                @endforeach
                <th data-mr-col="request_date">تاريخ الطلب</th>
                <th data-mr-col="payment_date">موعد تسديد الرسوم</th>
                <th data-mr-col="form">عرض الفورم</th>
                <th data-mr-col="status">الحالة</th>
                <th data-mr-col="actions">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    <td data-mr-col="select">
                        <input type="checkbox" class="request-checkbox" name="request_ids[]" value="{{ $request->id }}" data-status="{{ $request->status }}">
                    </td>
                    <td data-mr-col="id">{{ $request->id }}</td>
                    <td data-mr-col="student">
                        <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                           class="fw-semibold text-decoration-none admin-camps-table__name">
                            {{ $request->student->name }}
                        </a>
                        @if($request->student->name_ar)
                            <br><small class="text-muted">{{ $request->student->name_ar }}</small>
                        @endif
                    </td>
                    <td data-mr-col="other_groups">
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
                    <td data-mr-col="email">
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
                    <td data-mr-col="email_invite">
                        @if($request->student->email)
                            @if($request->email_invite_sent_at)
                                <span class="badge bg-warning-transparent text-warning d-block mb-1">
                                    <i class="fe fe-mail me-1"></i>دُعي بالبريد
                                </span>
                                <small class="text-muted d-block mb-1">آخر دعوة: {{ $request->email_invite_sent_at->format('Y-m-d H:i') }}</small>
                            @else
                                <span class="badge bg-danger-transparent text-danger d-block mb-1">
                                    <i class="fe fe-mail me-1"></i>لم يُدعَ
                                </span>
                            @endif
                            <button type="button"
                                    class="btn btn-sm btn-{{ $request->email_invite_sent_at ? 'outline-primary' : 'primary' }} js-membership-email-invite"
                                    data-student-id="{{ $request->student_id }}"
                                    data-student-name="{{ $request->student->name }}"
                                    data-student-email="{{ $request->student->email }}">
                                <i class="fe fe-send me-1"></i>{{ $request->email_invite_sent_at ? 'إعادة الدعوة' : 'دعوة' }}
                            </button>
                        @else
                            <span class="badge bg-secondary-transparent text-secondary">لا بريد</span>
                        @endif
                    </td>
                    <td data-mr-col="phone">
                        @if($request->student->phone)
                            {{ $request->student->phone }}
                            @if($request->student->country_code)
                                <small class="text-muted">({{ $request->student->country_code }})</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @if($showWaColumn)
                        @php
                            $sid = (int) $request->student_id;
                            $waStatus = $waStatusMap[$sid] ?? 'no_phone';
                            $phoneDigits = $phoneDigitsMap[$sid] ?? null;
                            $studentPhoneDisplay = $request->student->full_phone
                                ?: trim(($request->student->country_code ?? '').($request->student->phone ?? ''))
                                ?: ($request->student->phone ?? '—');
                        @endphp
                        <td data-mr-col="whatsapp">
                            @if($waStatus === 'in_group')
                                <span class="badge bg-success-transparent text-success">
                                    <i class="ri-check-line me-1"></i>منضم
                                </span>
                            @elseif($waStatus === 'not_in_group')
                                @if($request->whatsapp_invite_sent_at)
                                    <span class="badge bg-warning-transparent text-warning d-block mb-1">
                                        <i class="ri-mail-send-line me-1"></i>دُعي — لم ينضم بعد
                                    </span>
                                    <small class="text-muted d-block mb-1">آخر دعوة: {{ $request->whatsapp_invite_sent_at->format('Y-m-d H:i') }}</small>
                                @else
                                    <span class="badge bg-danger-transparent text-danger d-block mb-1">
                                        <i class="ri-close-line me-1"></i>غير منضم
                                    </span>
                                @endif
                                @if($phoneDigits)
                                    <button type="button"
                                            class="btn btn-sm btn-{{ $request->whatsapp_invite_sent_at ? 'outline-success' : 'success' }} js-membership-wa-invite"
                                            data-student-id="{{ $request->student_id }}"
                                            data-student-name="{{ $request->student->name }}"
                                            data-student-phone="{{ $studentPhoneDisplay }}">
                                        <i class="ri-send-plane-line me-1"></i>{{ $request->whatsapp_invite_sent_at ? 'إعادة الدعوة' : 'دعوة' }}
                                    </button>
                                @endif
                            @else
                                <span class="badge bg-secondary-transparent text-secondary">لا رقم</span>
                            @endif
                        </td>
                    @endif
                    @php
                        $registration = $registrationsByRequestId[$request->id] ?? null;
                    @endphp
                    @foreach($formColumns as $colKey => $colDef)
                        @php
                            $formCellValue = MembershipRequestFormColumns::displayValue($registration, $colKey);
                        @endphp
                        <td data-mr-col="{{ $colKey }}" class="d-none">
                            @if(in_array($colKey, ['reg_has_computer', 'reg_commitment', 'reg_sufficient_time', 'reg_bootcamp'], true) && in_array($formCellValue, ['نعم', 'لا'], true))
                                <span class="badge bg-{{ $formCellValue === 'نعم' ? 'success' : 'secondary' }}-transparent text-{{ $formCellValue === 'نعم' ? 'success' : 'secondary' }}">
                                    {{ $formCellValue }}
                                </span>
                            @else
                                <span class="text-break">{{ $formCellValue }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td data-mr-col="request_date"><small>{{ $request->created_at->format('Y-m-d H:i') }}</small></td>
                    <td data-mr-col="payment_date">
                        @if($request->payment_date)
                            <span class="badge bg-info-transparent text-info">{{ $request->payment_date->format('Y-m-d') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-mr-col="form">
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <button type="button"
                                    class="btn btn-sm btn-primary-light js-membership-request-detail"
                                    title="عرض سريع في نافذة منبثقة"
                                    data-url="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                                    data-student-name="{{ $request->student->name }}">
                                <i class="fe fe-eye me-1"></i>عرض البيانات
                            </button>
                            <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                               class="btn btn-sm btn-light border"
                               title="فتح صفحة المراجعة الكاملة">
                                <i class="fe fe-external-link"></i>
                            </a>
                        </div>
                    </td>
                    <td data-mr-col="status">
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
                    <td data-mr-col="actions">
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('courses.groups.membership-requests.show', [$course->id, $group->id, $request->id]) }}"
                               class="btn btn-sm btn-info-light" title="مراجعة بيانات الفورم (صفحة كاملة)">
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
                    <td colspan="20">
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
