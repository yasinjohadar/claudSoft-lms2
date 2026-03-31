<div class="table-responsive">
    <table class="table table-striped table-hover align-middle table-nowrap mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 40px;">#</th>
                <th scope="col" style="min-width: 150px;">اسم المستخدم</th>
                <th scope="col" style="min-width: 200px;">البريد</th>
                <th scope="col" style="min-width: 120px;">الهاتف</th>
                <th scope="col" style="min-width: 130px;">اخر دخول</th>
                <th scope="col" style="min-width: 150px;">الأدوار</th>
                <th scope="col" style="min-width: 110px;">الحالة</th>
                <th scope="col" style="min-width: 120px;">الحالة النشطة</th>
                <th scope="col" style="min-width: 200px;">العمليات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                @php
                    $userSessions = $sessions->get($user->id);
                    $lastSession = $userSessions ? $userSessions->first() : null;
                @endphp
                <tr>
                    <th scope="row">{{ $loop->iteration }}</th>

                    <td>
                        <a href="{{ route('users.show', $user->id) }}" class="text-decoration-none">
                            {{ $user->name }}
                        </a>
                    </td>

                    <td>
                        @if ($user->email)
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-email-btn me-1"
                                data-email="{{ $user->email }}" title="نسخ البريد">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="mailto:{{ $user->email }}" class="text-primary text-decoration-none"
                                title="إرسال بريد إلكتروني">
                                {{ $user->email }}
                            </a>
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @php
                            $displayPhone =
                                $user->full_phone ?? ($user->country_code && $user->phone ? $user->country_code . $user->phone : null) ?? $user->phone;
                            $linkUrl = $user->whatsapp_url ?? ($displayPhone ? 'tel:' . preg_replace('/[^0-9+]/', '', $displayPhone) : null);
                        @endphp
                        @if ($displayPhone)
                            @if ($linkUrl)
                                <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="text-success text-decoration-none"
                                    title="{{ $user->whatsapp_url ? 'فتح WhatsApp' : 'اتصال' }}">
                                    <i class="fab fa-whatsapp me-1"></i>{{ $displayPhone }}
                                </a>
                            @else
                                <i class="fab fa-whatsapp me-1 text-success"></i>{{ $displayPhone }}
                            @endif
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if ($lastSession)
                            {{ \Carbon\Carbon::createFromTimestamp($lastSession->last_activity)->diffForHumans() }}
                        @else
                            لا توجد جلسات
                        @endif
                    </td>

                    <td>
                        @foreach ($user->getRoleNames() as $role)
                            <span class="badge bg-primary me-1">{{ $role }}</span>
                        @endforeach
                    </td>

                    <td>
                        @if ($user->is_connected)
                            <span class="badge bg-success">متصل</span>
                        @else
                            <span class="badge bg-secondary">غير متصل</span>
                        @endif
                    </td>

                    <td>
                        <button class="btn btn-sm {{ $user->is_active ? 'btn-success' : 'btn-secondary' }}"
                            data-bs-toggle="modal" data-bs-target="#toggleStatus{{ $user->id }}" title="تغيير الحالة">
                            <i class="fas fa-power-off me-1"></i>
                            {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                        </button>
                    </td>

                    <td>
                        @if($user->hasRole('student'))
                            <a class="btn btn-primary btn-sm me-1" href="{{ route('admin.users.courses', $user->id) }}"
                                title="عرض الكورسات">
                                <i class="fas fa-book"></i>
                            </a>
                            @if($user->is_active)
                                <button type="button" class="btn btn-success btn-sm me-1 impersonate-btn"
                                    data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                    title="الدخول كطالب في تبويب جديد">
                                    <i class="fas fa-user-secret"></i>
                                </button>
                            @endif
                            @if($user->hasRole('student'))
                                <a href="{{ route('users.student-details', $user->id) }}" class="btn btn-info btn-sm me-1"
                                    title="عرض تفاصيل الطالب والمجموعات">
                                    <i class="fas fa-users"></i>
                                </a>
                            @endif
                        @endif
                        <a class="btn btn-info btn-sm me-1" href="{{ route('users.edit', $user->id) }}"
                            title="تعديل المستخدم">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger btn-sm me-1" data-bs-toggle="modal"
                            data-bs-target="#delete{{ $user->id }}" title="حذف المستخدم">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                        <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                            data-bs-target="#change_password{{ $user->id }}" title="تعديل كلمة السر">
                            <i class="fa-solid fa-key"></i>
                        </a>
                    </td>
                </tr>

                @include('admin.pages.users.delete')
                @include('admin.pages.users.change_password')
                @include('admin.pages.users.toggle_status')
            @empty
                <tr>
                    <td colspan="8" class="text-center text-danger fw-bold">لا توجد بيانات متاحة</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
