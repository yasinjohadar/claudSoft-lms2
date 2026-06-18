@php
    $fields = [
        ['icon' => 'fe-user', 'label' => 'الاسم بالعربية', 'value' => $student->name_ar ?? 'غير محدد', 'empty' => empty($student->name_ar)],
        ['icon' => 'fe-user', 'label' => 'الاسم بالإنجليزية', 'value' => $student->name, 'empty' => false],
        ['icon' => 'fe-mail', 'label' => 'البريد الإلكتروني', 'value' => $student->email, 'empty' => false],
        ['icon' => 'fe-phone', 'label' => 'رقم الهاتف', 'value' => $displayPhone ?? 'غير محدد', 'empty' => empty($displayPhone)],
        ['icon' => 'fe-calendar', 'label' => 'تاريخ الميلاد', 'value' => $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : 'غير محدد', 'empty' => empty($student->date_of_birth)],
        ['icon' => 'fe-users', 'label' => 'الجنس', 'value' => $student->gender == 'male' ? 'ذكر' : ($student->gender == 'female' ? 'أنثى' : 'غير محدد'), 'empty' => empty($student->gender)],
        ['icon' => 'fe-map-pin', 'label' => 'المدينة', 'value' => $student->city ?? 'غير محدد', 'empty' => empty($student->city)],
        ['icon' => 'fe-home', 'label' => 'العنوان', 'value' => $student->address ?? 'غير محدد', 'empty' => empty($student->address)],
        ['icon' => 'fe-globe', 'label' => 'الجنسية', 'value' => $student->nationality->name ?? 'غير محدد', 'empty' => empty($student->nationality_id)],
    ];
@endphp

<div class="card custom-card student-quizzes-panel mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-user text-primary"></i>
            </span>
            <h6 class="card-title mb-0">البيانات الشخصية</h6>
        </div>
        <div class="row g-3">
            @foreach($fields as $field)
                <div class="col-md-6">
                    <div class="student-profile-field {{ $field['empty'] ? 'is-empty' : '' }}">
                        <span class="student-profile-field__icon">
                            <i class="fe {{ $field['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="student-profile-field__label">{{ $field['label'] }}</span>
                            <span class="student-profile-field__value">{{ $field['value'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card custom-card student-quizzes-panel mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="avatar avatar-sm bg-info-transparent">
                <i class="fe fe-info text-info"></i>
            </span>
            <h6 class="card-title mb-0">معلومات الحساب</h6>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="student-profile-field">
                    <span class="student-profile-field__icon">
                        <i class="fe fe-activity"></i>
                    </span>
                    <div>
                        <span class="student-profile-field__label">حالة الحساب</span>
                        <span class="student-profile-field__value">
                            @if($student->is_active ?? true)
                                <span class="badge bg-success-transparent">نشط</span>
                            @else
                                <span class="badge bg-danger-transparent">غير نشط</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="student-profile-field">
                    <span class="student-profile-field__icon">
                        <i class="fe fe-calendar"></i>
                    </span>
                    <div>
                        <span class="student-profile-field__label">تاريخ الإنشاء</span>
                        <span class="student-profile-field__value">{{ $student->created_at ? $student->created_at->format('Y-m-d h:i A') : 'غير محدد' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="student-profile-field {{ empty($student->last_login_at) ? 'is-empty' : '' }}">
                    <span class="student-profile-field__icon">
                        <i class="fe fe-log-in"></i>
                    </span>
                    <div>
                        <span class="student-profile-field__label">آخر تسجيل دخول</span>
                        <span class="student-profile-field__value">
                            {{ $student->last_login_at ? \Carbon\Carbon::parse($student->last_login_at)->format('Y-m-d h:i A') : 'لم يتم التسجيل بعد' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="student-profile-field {{ empty($student->last_login_ip) ? 'is-empty' : '' }}">
                    <span class="student-profile-field__icon">
                        <i class="fe fe-wifi"></i>
                    </span>
                    <div>
                        <span class="student-profile-field__label">آخر IP</span>
                        <span class="student-profile-field__value">{{ $student->last_login_ip ?? 'غير محدد' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
