@php
    $paymentMethodsList = $paymentMethods ?? collect();
    $trainingCampsForModal = $trainingCampsForModal ?? collect();
    $groupMembersFilterActive = request()->filled('search')
        || request()->filled('other_group_id')
        || request()->filled('groups_count')
        || request()->filled('online_status')
        || request()->filled('login_status');
    $groupStats = $stats ?? [];
    $groupTotalMemberCount = $groupStats['total_members'] ?? null;
@endphp
@if($groupMembersFilterActive && isset($members))
    <div class="alert alert-light border py-2 px-3 mb-3 small" role="status">
        عدد النتائج المطابقة للفلتر:
        <strong class="text-dark">{{ $members->total() }}</strong>
        @if($groupTotalMemberCount !== null)
            <span class="text-muted ms-1">من أصل <strong>{{ $groupTotalMemberCount }}</strong> عضواً في المجموعة</span>
        @endif
    </div>
@endif
@if($members && $members->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0">
            <thead>
                <tr>
                    <th width="50">
                        <input type="checkbox" id="selectAllMembers" title="تحديد الكل">
                    </th>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الهاتف</th>
                    <th>تاريخ الانضمام</th>
                    @if($group->is_camp)
                        <th>حالة الدفع</th>
                    @endif
                    <th>آخر دخول</th>
                    <th>اكتمال البروفايل</th>
                    <th>حالة الحساب</th>
                    <th>حالة الاتصال</th>
                    <th>المبلغ المستحق</th>
                    <th>المجموعات الأخرى</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $index => $memberRecord)
                    @if($memberRecord->student)
                        <tr data-student-row-id="{{ $memberRecord->student_id }}">
                            <td>
                                <input type="checkbox" class="member-checkbox" value="{{ $memberRecord->student_id }}"
                                       data-member-name="{{ $memberRecord->student->name }}"
                                       data-account-active="{{ $memberRecord->student->is_active ? '1' : '0' }}">
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
                            <td>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="text-break">{{ $memberRecord->student->email }}</span>
                                    @if($memberRecord->student->email)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary py-0 px-1 js-copy-member-email"
                                                title="نسخ البريد"
                                                data-email="{{ $memberRecord->student->email }}"
                                                aria-label="نسخ البريد الإلكتروني">
                                            <i class="fas fa-copy fa-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($memberRecord->student->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $memberRecord->student->phone) }}" target="_blank" class="text-success" title="مراسلة عبر واتساب">
                                        <i class="fab fa-whatsapp me-1"></i>{{ $memberRecord->student->phone }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $memberRecord->joined_at ? $memberRecord->joined_at->format('Y-m-d') : '-' }}</td>
                            @if($group->is_camp)
                                <td>
                                    @php
                                        $paymentClasses = [
                                            'paid' => 'admin-camp-enrollment-payment admin-camp-enrollment-payment--paid',
                                            'unpaid' => 'admin-camp-enrollment-payment admin-camp-enrollment-payment--unpaid',
                                            'refunded' => 'admin-camp-enrollment-payment admin-camp-enrollment-payment--refunded',
                                        ];
                                    @endphp
                                    <span class="{{ $paymentClasses[$memberRecord->payment_status] ?? 'admin-camp-enrollment-payment' }}">
                                        {{ $memberRecord->payment_status_label }}
                                    </span>
                                </td>
                            @endif
                            <td>
                                @php
                                    $studentId = $memberRecord->student_id;
                                    $lastActivity = $lastActivityByUserId[$studentId] ?? null;
                                    $isOnline = in_array($studentId, $onlineUserIds ?? []);
                                    $lastLoginAt = $memberRecord->student->last_login_at;
                                @endphp
                                @if($lastLoginAt)
                                    @php
                                        $lastLoginCarbon = \Carbon\Carbon::parse($lastLoginAt);
                                    @endphp
                                    <span title="آخر تسجيل دخول: {{ $lastLoginCarbon->format('Y-m-d H:i:s') }}">
                                        {{ $lastLoginCarbon->format('Y-m-d H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @include('admin.pages.users.partials.profile-completion-cell', ['user' => $memberRecord->student])
                            </td>
                            <td>
                                <span class="admin-users-status-chip {{ $memberRecord->student->is_active ? 'admin-users-status-chip--active' : 'admin-users-status-chip--inactive' }}"
                                      title="{{ $memberRecord->student->is_active ? 'الحساب مفعل ويمكنه تسجيل الدخول' : 'الحساب موقوف ولا يمكنه تسجيل الدخول' }}">
                                    <i class="fe fe-power"></i>
                                    {{ $memberRecord->student->is_active ? 'مفعل' : 'موقوف' }}
                                </span>
                            </td>
                            <td>
                                @if($isOnline)
                                    <span class="badge bg-success" title="نشاط جلسة خلال آخر 5 دقائق — آخر نشاط: {{ $lastActivity ? $lastActivity->format('Y-m-d H:i:s') : 'الآن' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>نشط حالياً
                                    </span>
                                @else
                                    <span class="badge bg-secondary" title="لا يوجد نشاط جلسة خلال آخر 5 دقائق{{ $lastActivity ? ' — آخر نشاط جلسة: ' . $lastActivity->format('Y-m-d H:i:s') : '' }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>غير نشط حالياً
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $sidDue = (int) $memberRecord->student_id;
                                    $dueAmount = (float) (
                                        $dueAmountsByStudentId[$sidDue]
                                        ?? $dueAmountsByStudentId[$memberRecord->student_id]
                                        ?? $dueAmountsByStudentId[(string) $memberRecord->student_id]
                                        ?? 0
                                    );
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
                                    @if($memberRecord->student->hasRole('student'))
                                        <x-admin.impersonate-trigger
                                            :user="$memberRecord->student"
                                            variant="btn"
                                            title="الدخول كطالب في تبويب جديد" />
                                    @endif
                                    @if($dueAmount > 0 && $paymentMethodsList->isNotEmpty())
                                        @php
                                            $sid = (int) $memberRecord->student_id;
                                            $invoicesPayload = $studentOutstandingInvoicesById[$sid]
                                                ?? $studentOutstandingInvoicesById[$memberRecord->student_id]
                                                ?? $studentOutstandingInvoicesById[(string) $memberRecord->student_id]
                                                ?? [];
                                        @endphp
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success js-open-record-payment"
                                                title="تسجيل دفعة"
                                                data-student-id="{{ $memberRecord->student_id }}"
                                                data-student-name="{{ $memberRecord->student->name }}"
                                                data-total-due="{{ number_format($dueAmount, 2, '.', '') }}"
                                                data-invoices='@json($invoicesPayload)'>
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="تغيير الدور">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    @php
                                        $sidDetails = (int) $memberRecord->student_id;
                                        $paymentDetailsPayload = [
                                            'name' => $memberRecord->student->name,
                                            'due' => (float) (
                                                $dueAmountsByStudentId[$sidDetails]
                                                ?? $dueAmountsByStudentId[$memberRecord->student_id]
                                                ?? $dueAmountsByStudentId[(string) $memberRecord->student_id]
                                                ?? 0
                                            ),
                                            'paid' => (float) (
                                                $studentPaidTotalsById[$sidDetails]
                                                ?? $studentPaidTotalsById[$memberRecord->student_id]
                                                ?? $studentPaidTotalsById[(string) $memberRecord->student_id]
                                                ?? 0
                                            ),
                                            'invoices' => $studentOutstandingInvoicesById[$sidDetails]
                                                ?? $studentOutstandingInvoicesById[$memberRecord->student_id]
                                                ?? $studentOutstandingInvoicesById[(string) $memberRecord->student_id]
                                                ?? [],
                                            'payments' => $studentPaymentsById[$sidDetails]
                                                ?? $studentPaymentsById[$memberRecord->student_id]
                                                ?? $studentPaymentsById[(string) $memberRecord->student_id]
                                                ?? [],
                                        ];
                                    @endphp
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info js-open-payment-details"
                                            title="تفاصيل الدفع"
                                            data-details='@json($paymentDetailsPayload)'>
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger js-open-remove-member"
                                            title="إزالة"
                                            data-remove-url="{{ route('groups.remove-member', [$group->id, $memberRecord->student_id]) }}"
                                            data-member-name="{{ $memberRecord->student->name }}">
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
@elseif($members && $groupMembersFilterActive && $members->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-filter fa-3x text-muted mb-3 opacity-50"></i>
        <h5 class="text-muted mb-2">لا توجد نتائج مطابقة للفلتر</h5>
        <p class="text-muted small mb-0">جرّب تغيير معايير البحث أو <a href="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}">إعادة التعيين</a>.</p>
    </div>
@else
    <div class="group-show-empty">
        <div class="group-show-empty__icon">
            <i class="fe fe-users"></i>
        </div>
        <h4 class="group-show-empty__title">لا يوجد أعضاء</h4>
        <p class="text-muted mb-0">ابدأ بإضافة أعضاء إلى هذه المجموعة</p>
        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fe fe-user-plus me-1"></i>إضافة عضو
        </button>
    </div>
@endif
