@php
    $displayPhone = $displayPhone
        ?? $user->full_phone
        ?? (($user->country_code && $user->phone) ? $user->country_code . $user->phone : null)
        ?? $user->phone;

    $genderLabel = match ($user->gender) {
        'male' => 'ذكر',
        'female' => 'أنثى',
        default => null,
    };

    $profileCompletion = $user->profile_completion_data;
    $pct = (int) ($profileCompletion['percentage'] ?? 0);

    $personalFields = [
        ['icon' => 'fe-user', 'label' => 'الاسم بالإنجليزية', 'value' => $user->name, 'empty' => empty($user->name)],
        ['icon' => 'fe-user', 'label' => 'الاسم بالعربية', 'value' => $user->name_ar, 'empty' => empty($user->name_ar)],
        ['icon' => 'fe-mail', 'label' => 'البريد الإلكتروني', 'value' => $user->email, 'empty' => empty($user->email)],
        ['icon' => 'fe-phone', 'label' => 'رقم الهاتف', 'value' => $displayPhone, 'empty' => empty($displayPhone)],
        ['icon' => 'fe-calendar', 'label' => 'تاريخ الميلاد', 'value' => $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : null, 'empty' => empty($user->date_of_birth)],
        ['icon' => 'fe-users', 'label' => 'الجنس', 'value' => $genderLabel, 'empty' => empty($genderLabel)],
        ['icon' => 'fe-flag', 'label' => 'الجنسية', 'value' => $user->nationality->name ?? null, 'empty' => empty($user->nationality_id)],
        ['icon' => 'fe-map-pin', 'label' => 'المدينة', 'value' => $user->city, 'empty' => empty($user->city)],
        ['icon' => 'fe-home', 'label' => 'العنوان', 'value' => $user->address, 'empty' => empty($user->address)],
        ['icon' => 'fe-credit-card', 'label' => 'رقم الهوية', 'value' => $user->national_id, 'empty' => empty($user->national_id)],
    ];

    if ($user->student_id) {
        $personalFields[] = ['icon' => 'fe-hash', 'label' => 'رقم الطالب', 'value' => $user->student_id, 'empty' => false];
    }

    $accountFields = [
        ['icon' => 'fe-activity', 'label' => 'حالة الحساب', 'value' => $user->is_active ? 'نشط' : 'غير نشط', 'empty' => false, 'badge' => $user->is_active ? 'success' : 'danger'],
        ['icon' => 'fe-award', 'label' => 'نوع الحساب', 'value' => ($accountTierLabel ?? null) ?: (($accountTier ?? 'silver') === 'gold' ? 'ذهبي' : 'فضي'), 'empty' => false, 'tier_badge' => $accountTier ?? 'silver'],
        ['icon' => 'fe-shield', 'label' => 'الدور', 'value' => $user->roles->pluck('name')->join('، ') ?: '—', 'empty' => $user->roles->isEmpty()],
        ['icon' => 'fe-calendar', 'label' => 'تاريخ التسجيل', 'value' => $user->created_at?->format('Y-m-d H:i'), 'empty' => empty($user->created_at)],
        ['icon' => 'fe-log-in', 'label' => 'آخر تسجيل دخول', 'value' => $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('Y-m-d H:i') : null, 'empty' => empty($user->last_login_at)],
        ['icon' => 'fe-wifi', 'label' => 'آخر IP', 'value' => $user->last_login_ip, 'empty' => empty($user->last_login_ip)],
        ['icon' => 'fe-smartphone', 'label' => 'آخر جهاز', 'value' => $user->last_device_type, 'empty' => empty($user->last_device_type)],
    ];
@endphp

@if($user->hasRole('student'))
    <div class="card custom-card group-show-members-card mb-4">
        <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="group-show-members-card__title mb-1">اكتمال الملف الشخصي</h6>
                <p class="fs-12 text-muted mb-0">تم إكمال {{ $profileCompletion['completed'] }} من {{ $profileCompletion['total'] }} حقول مطلوبة.</p>
            </div>
            <span class="badge {{ $pct >= 100 ? 'bg-success-transparent' : 'bg-warning-transparent' }} fs-12">
                {{ $pct }}%
            </span>
        </div>
        <div class="card-body pt-3">
            <div class="admin-users-profile-pct mb-0" style="min-width: 100%;">
                <div class="admin-users-profile-pct__bar flex-fill" role="progressbar"
                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="admin-users-profile-pct__fill {{ $pct >= 100 ? 'is-complete' : '' }}"
                         style="width: {{ max(0, min(100, $pct)) }}%"></div>
                </div>
                <span class="admin-users-profile-pct__label {{ $pct >= 100 ? 'is-complete' : '' }}">{{ $pct }}%</span>
            </div>
            @if(($profileCompletion['missing_count'] ?? 0) > 0)
                <p class="fs-12 text-muted mb-0 mt-2">
                    <i class="fe fe-alert-circle me-1"></i>
                    <strong>الحقول الناقصة:</strong> {{ implode(' — ', $profileCompletion['missing_fields']) }}
                </p>
            @endif
        </div>
    </div>
@endif

<div class="card custom-card group-show-members-card mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">البيانات الشخصية</h6>
        <p class="fs-12 text-muted mb-0">معلومات الهوية والتواصل والعنوان.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            @foreach($personalFields as $field)
                <div class="col-md-6 col-xl-4">
                    <div class="admin-profile-detail-field {{ ($field['empty'] ?? false) ? 'is-empty' : '' }}">
                        <span class="admin-profile-detail-field__icon">
                            <i class="fe {{ $field['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="admin-profile-detail-field__label">{{ $field['label'] }}</span>
                            <span class="admin-profile-detail-field__value">{{ $field['value'] ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card custom-card group-show-members-card mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">معلومات الحساب</h6>
        <p class="fs-12 text-muted mb-0">حالة الحساب، الجلسات، وآخر نشاط.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            @foreach($accountFields as $field)
                <div class="col-md-6 col-xl-4">
                    <div class="admin-profile-detail-field {{ ($field['empty'] ?? false) ? 'is-empty' : '' }}">
                        <span class="admin-profile-detail-field__icon">
                            <i class="fe {{ $field['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="admin-profile-detail-field__label">{{ $field['label'] }}</span>
                            <span class="admin-profile-detail-field__value">
                                @if(!empty($field['tier_badge']))
                                    @include('admin.pages.users.partials.account-tier-badge', ['tier' => $field['tier_badge']])
                                @elseif(!empty($field['badge']))
                                    <span class="group-show-chip group-show-chip--sm text-{{ $field['badge'] }}">{{ $field['value'] }}</span>
                                @else
                                    {{ $field['value'] ?? '—' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
