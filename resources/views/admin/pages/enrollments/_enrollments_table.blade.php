<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-enrollments-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">الطالب</th>
                <th scope="col">الكورس</th>
                <th scope="col">تاريخ الانضمام</th>
                <th scope="col">الحالة</th>
                <th scope="col">نسبة الإنجاز</th>
                <th scope="col" style="width: 120px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($enrollments as $enrollment)
                @php
                    $student = $enrollment->student;
                    $initial = $student ? mb_strtoupper(mb_substr($student->name, 0, 1)) : '?';
                @endphp
                <tr class="admin-enrollments-table__row">
                    <td>{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>

                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="admin-enrollments-table__avatar flex-shrink-0">
                                @if($student && $student->avatar)
                                    <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}">
                                @elseif($student && $student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                @if($student)
                                    <a href="{{ route('users.show', $student->id) }}"
                                       class="fw-semibold text-decoration-none d-block text-truncate admin-enrollments-table__name">
                                        {{ $student->name }}
                                    </a>
                                    @if($student->email)
                                        <small class="text-muted d-block text-truncate">{{ $student->email }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        @if($enrollment->course)
                            <a href="{{ route('courses.show', $enrollment->course_id) }}"
                               class="admin-enrollments-table__course text-decoration-none text-truncate d-inline-block"
                               style="max-width: 14rem;"
                               title="{{ $enrollment->course->title }}">
                                {{ $enrollment->course->title }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if($enrollment->enrollment_date)
                            <span class="d-inline-flex align-items-center gap-1 small text-muted">
                                <i class="fe fe-calendar"></i>
                                {{ $enrollment->enrollment_date->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @include('admin.pages.enrollments.partials.enrollment-status-chip', ['enrollment' => $enrollment])
                    </td>

                    <td>
                        @include('admin.pages.enrollments.partials.enrollment-progress', ['enrollment' => $enrollment])
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">
                            @if($enrollment->enrollment_status === 'pending')
                                <button type="button" class="btn btn-sm btn-success-light"
                                        data-bs-toggle="modal" data-bs-target="#approveModal{{ $enrollment->id }}"
                                        title="قبول الطلب">
                                    <i class="fe fe-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger-light"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $enrollment->id }}"
                                        title="رفض الطلب">
                                    <i class="fe fe-x"></i>
                                </button>
                            @endif

                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-primary-light"
                                        data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed","placement":"bottom-end"}'
                                        aria-expanded="false" title="المزيد">
                                    <i class="fe fe-more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end admin-enrollments-dropdown shadow-sm">
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('courses.enrollments.index', $enrollment->course_id) }}">
                                            <i class="fe fe-eye me-2"></i>عرض تسجيلات الكورس
                                        </a>
                                    </li>
                                    @if($student)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('users.show', $student->id) }}">
                                                <i class="fe fe-user me-2"></i>ملف الطالب
                                            </a>
                                        </li>
                                    @endif
                                    @if($enrollment->course)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('courses.show', $enrollment->course_id) }}">
                                                <i class="fe fe-book-open me-2"></i>صفحة الكورس
                                            </a>
                                        </li>
                                    @endif
                                    @if($enrollment->enrollment_status === 'pending')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-success"
                                                    data-bs-toggle="modal" data-bs-target="#approveModal{{ $enrollment->id }}">
                                                <i class="fe fe-check me-2"></i>قبول الطلب
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $enrollment->id }}">
                                                <i class="fe fe-x me-2"></i>رفض الطلب
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-user-check group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد انضمامات</h5>
                            <p class="group-show-empty__desc mb-3">لم يتم العثور على انضمامات مطابقة للبحث أو الفلاتر الحالية.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectCourseModal">
                                <i class="fe fe-plus me-1"></i>إضافة انضمام جديد
                            </button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($enrollments->count() > 0)
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->withQueryString()->links() }}
    </div>
@endif
