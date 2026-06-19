<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-users-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">المستخدم</th>
                <th scope="col">البريد</th>
                <th scope="col">الهاتف</th>
                <th scope="col">آخر دخول</th>
                <th scope="col">اكتمال البروفايل</th>
                <th scope="col">الاتصال</th>
                <th scope="col">التفعيل</th>
                <th scope="col" style="width: 120px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                @php
                    $userSessions = $sessions->get($user->id);
                    $lastSession = $userSessions ? $userSessions->first() : null;
                    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
                    $displayPhone = $user->full_phone
                        ?? (($user->country_code && $user->phone) ? $user->country_code . $user->phone : null)
                        ?? $user->phone;
                    $linkUrl = $user->whatsapp_url ?? ($displayPhone ? 'tel:' . preg_replace('/[^0-9+]/', '', $displayPhone) : null);
                @endphp
                <tr class="admin-users-table__row">
                    <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>

                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="admin-users-table__avatar flex-shrink-0">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                @elseif($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('users.show', $user->id) }}"
                                   class="fw-semibold text-decoration-none d-block text-truncate admin-users-table__name">
                                    {{ $user->name }}
                                </a>
                                @if($user->name_ar)
                                    <small class="text-muted d-block text-truncate">{{ $user->name_ar }}</small>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        @if ($user->email)
                            <div class="d-flex align-items-center gap-1 min-w-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 copy-email-btn flex-shrink-0"
                                    data-email="{{ $user->email }}" title="نسخ البريد">
                                    <i class="fe fe-copy"></i>
                                </button>
                                <a href="mailto:{{ $user->email }}" class="text-truncate text-decoration-none" title="إرسال بريد">
                                    {{ $user->email }}
                                </a>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if ($displayPhone)
                            @if ($linkUrl)
                                <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="text-success text-decoration-none d-inline-flex align-items-center gap-1"
                                   title="{{ $user->whatsapp_url ? 'فتح WhatsApp' : 'اتصال' }}">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>{{ $displayPhone }}</span>
                                </a>
                            @else
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="fab fa-whatsapp text-success"></i>{{ $displayPhone }}
                                </span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if ($lastSession)
                            <span class="d-inline-flex align-items-center gap-1 small text-muted">
                                <i class="fe fe-clock"></i>
                                {{ \Carbon\Carbon::createFromTimestamp($lastSession->last_activity)->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted small">لا توجد جلسات</span>
                        @endif
                    </td>

                    <td>
                        @include('admin.pages.users.partials.profile-completion-cell', ['user' => $user])
                    </td>

                    <td>
                        @if ($user->is_connected)
                            <span class="admin-users-online-badge">
                                <span class="admin-users-online-dot admin-users-online-dot--active"></span>
                                متصل
                            </span>
                        @else
                            <span class="admin-users-online-badge admin-users-online-badge--offline">
                                <span class="admin-users-online-dot"></span>
                                غير متصل
                            </span>
                        @endif
                    </td>

                    <td>
                        <button type="button"
                            class="admin-users-status-chip {{ $user->is_active ? 'admin-users-status-chip--active' : 'admin-users-status-chip--inactive' }}"
                            data-bs-toggle="modal" data-bs-target="#toggleStatus{{ $user->id }}"
                            title="تغيير حالة التفعيل">
                            <i class="fe fe-power"></i>
                            {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                        </button>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <a class="btn btn-sm btn-info-light" href="{{ route('users.edit', $user->id) }}" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>

                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-primary-light"
                                        data-bs-toggle="dropdown"
                                        data-bs-popper-config='{"strategy":"fixed","placement":"bottom-end"}'
                                        aria-expanded="false" title="المزيد">
                                    <i class="fe fe-more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end admin-users-dropdown shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.show', $user->id) }}">
                                            <i class="fe fe-user me-2"></i>عرض الملف
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item js-open-change-password"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-update-url="{{ route('users.update-password', $user) }}">
                                            <i class="fe fe-key me-2"></i>تغيير كلمة المرور
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item js-open-admin-notes"
                                            data-notes-url="{{ route('admin.users.admin-notes', $user) }}"
                                            data-user-name="{{ $user->name }}">
                                            <i class="fe fe-file-text me-2"></i>الملاحظات الإدارية
                                        </button>
                                    </li>
                                    @if($user->email)
                                        <li>
                                            <button type="button" class="dropdown-item js-open-send-email"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->email }}"
                                                data-preview-url="{{ route('users.send-email.preview', $user) }}"
                                                data-send-url="{{ route('users.send-email.send', $user) }}">
                                                <i class="fe fe-mail me-2"></i>إرسال بريد
                                            </button>
                                        </li>
                                    @endif
                                    @if($user->hasRole('student'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.courses', $user->id) }}">
                                                <i class="fe fe-book me-2"></i>كورسات الطالب
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('users.student-details', $user->id) }}">
                                                <i class="fe fe-users me-2"></i>المجموعات والتفاصيل
                                            </a>
                                        </li>
                                        <li>
                                            <x-admin.impersonate-trigger :user="$user" variant="dropdown-item" />
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger"
                                                data-bs-toggle="modal" data-bs-target="#delete{{ $user->id }}">
                                            <i class="fe fe-trash-2 me-2"></i>حذف المستخدم
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-users group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا يوجد مستخدمون</h5>
                            <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو أنشئ مستخدماً جديداً.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $users->withQueryString()->links() }}
</div>
