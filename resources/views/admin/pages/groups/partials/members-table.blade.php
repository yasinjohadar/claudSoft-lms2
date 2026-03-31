@if($members && $members->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllMembers" title="تحديد الكل">
                    </th>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الهاتف</th>
                    <th>الدور</th>
                    <th>تاريخ الانضمام</th>
                    <th>آخر دخول</th>
                    <th>الحالة</th>
                    <th>المبلغ المستحق</th>
                    <th>المجموعات الأخرى</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $index => $memberRecord)
                    @if($memberRecord->student)
                        <tr>
                            <td>
                                <input type="checkbox" class="member-checkbox" value="{{ $memberRecord->student_id }}" data-member-name="{{ $memberRecord->student->name }}">
                            </td>
                            <td>{{ ($members->currentPage() - 1) * $members->perPage() + $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($memberRecord->student->avatar)
                                        <img src="{{ asset('storage/' . $memberRecord->student->avatar) }}" alt="{{ $memberRecord->student->name }}" class="avatar avatar-sm rounded-circle me-2">
                                    @else
                                        <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                            <span class="fw-bold">{{ substr($memberRecord->student->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <a href="{{ route('users.show', $memberRecord->student_id) }}" class="text-decoration-none">
                                        <strong>{{ $memberRecord->student->name }}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $memberRecord->student->email }}</td>
                            <td>
                                @if($memberRecord->student->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $memberRecord->student->phone) }}" target="_blank" class="text-success" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp me-1"></i>{{ $memberRecord->student->phone }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($memberRecord->role == 'leader')
                                    <span class="badge bg-warning">قائد</span>
                                @else
                                    <span class="badge bg-info">عضو</span>
                                @endif
                            </td>
                            <td>{{ $memberRecord->joined_at ? $memberRecord->joined_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                @php
                                    $studentId = $memberRecord->student_id;
                                    $lastActivity = $lastActivityByUserId[$studentId] ?? null;
                                    $isOnline = in_array($studentId, $onlineUserIds ?? []);
                                @endphp
                                @if($lastActivity)
                                    <span title="{{ $lastActivity->format('Y-m-d H:i:s') }}">
                                        {{ $lastActivity->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($isOnline)
                                    <span class="badge bg-success" title="متصل الآن - آخر نشاط: {{ $lastActivity ? $lastActivity->format('Y-m-d H:i:s') : 'الآن' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>متصل
                                    </span>
                                @else
                                    <span class="badge bg-secondary" title="غير متصل{{ $lastActivity ? ' - آخر نشاط: ' . $lastActivity->format('Y-m-d H:i:s') : '' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>غير متصل
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $dueAmount = (float) ($dueAmountsByStudentId[$memberRecord->student_id] ?? 0);
                                @endphp
                                @if($dueAmount > 0)
                                    <span class="badge bg-danger">${{ number_format($dueAmount, 2) }}</span>
                                @else
                                    <span class="badge bg-success">لا يوجد مستحقات</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $otherGroups = $memberRecord->student->courseGroupMemberships
                                        ->filter(function($membership) use ($group) {
                                            return $membership->group_id != $group->id && $membership->group;
                                        })
                                        ->pluck('group')
                                        ->filter();
                                @endphp
                                
                                @if($otherGroups->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($otherGroups->take(3) as $otherGroup)
                                            @php
                                                $otherGroupCourse = $otherGroup->courses->first();
                                            @endphp
                                            @if($otherGroupCourse)
                                                <a href="{{ route('courses.groups.show', [$otherGroupCourse->id, $otherGroup->id]) }}" class="badge bg-primary-transparent text-primary" title="{{ $otherGroup->name }}">
                                                    {{ $otherGroup->name }}
                                                </a>
                                            @else
                                                <span class="badge bg-primary-transparent text-primary" title="{{ $otherGroup->name }}">
                                                    {{ $otherGroup->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($otherGroups->count() > 3)
                                            <span class="badge bg-secondary-transparent text-secondary" title="{{ $otherGroups->skip(3)->pluck('name')->implode(', ') }}">
                                                +{{ $otherGroups->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="تغيير الدور">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="إزالة"
                                            data-bs-toggle="modal"
                                            data-bs-target="#removeMemberModal{{ $memberRecord->student_id }}"
                                            data-member-name="{{ $memberRecord->student->name }}"
                                            data-member-id="{{ $memberRecord->student_id }}">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $members->withQueryString()->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
        <h4 class="text-muted mb-3">لا يوجد أعضاء</h4>
        <p class="text-muted">ابدأ بإضافة أعضاء إلى هذه المجموعة</p>
        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fas fa-user-plus me-2"></i>إضافة عضو
        </button>
    </div>
@endif

@foreach($members as $memberRecord)
    @if($memberRecord->student)
        <div class="modal fade" id="removeMemberModal{{ $memberRecord->student_id }}" tabindex="-1" aria-labelledby="removeMemberModalLabel{{ $memberRecord->student_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                            <i class="fas fa-user-times text-danger fs-24"></i>
                        </div>
                        <h5 class="mb-3">تأكيد إزالة العضو</h5>
                        <p class="text-muted mb-4">
                            هل أنت متأكد من إزالة
                            <strong class="text-dark">{{ $memberRecord->student->name }}</strong>
                            من المجموعة؟
                        </p>
                        <div class="alert alert-warning py-2 mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            سيتم أيضاً إلغاء تسجيله من الكورسات المرتبطة بهذه المجموعة
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>إلغاء
                            </button>
                            <form action="{{ route('groups.remove-member', [$group->id, $memberRecord->student_id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="fas fa-user-times me-1"></i>نعم، إزالة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
