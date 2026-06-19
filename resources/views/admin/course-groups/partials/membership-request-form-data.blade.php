@php
    $yesNo = fn ($v) => match ($v) {
        'yes' => ['نعم', 'success'],
        'no' => ['لا', 'secondary'],
        default => ['—', null],
    };

    $computerLevels = [
        'none' => 'بدون',
        'beginner' => 'مبتدئ',
        'intermediate' => 'متوسط',
        'good' => 'جيد',
        'advanced' => 'متقدم',
    ];

    $progLevels = [
        'none' => 'بدون',
        'beginner' => 'مبتدئ',
        'intermediate' => 'متوسط',
        'expert' => 'خبير',
    ];

    $genderLabel = match ($registration->gender ?? null) {
        'male' => 'ذكر',
        'female' => 'أنثى',
        'other' => 'أخرى',
        default => null,
    };

    $personalFields = [
        ['icon' => 'fe-user', 'label' => 'الاسم بالإنجليزية', 'value' => $registration->name],
        ['icon' => 'fe-user', 'label' => 'الاسم بالعربية', 'value' => $registration->name_ar],
        ['icon' => 'fe-mail', 'label' => 'البريد الإلكتروني', 'value' => $registration->email],
        ['icon' => 'fe-phone', 'label' => 'رقم الهاتف', 'value' => $registration->full_phone ?? $registration->phone],
        ['icon' => 'fe-flag', 'label' => 'الجنسية', 'value' => $registration->nationality->name ?? null],
        ['icon' => 'fe-calendar', 'label' => 'تاريخ الميلاد', 'value' => $registration->date_of_birth?->format('Y-m-d')],
        ['icon' => 'fe-users', 'label' => 'الجنس', 'value' => $genderLabel],
        ['icon' => 'fe-map-pin', 'label' => 'المدينة', 'value' => $registration->city],
        ['icon' => 'fe-home', 'label' => 'العنوان', 'value' => $registration->address],
    ];

    $experienceFields = [
        ['icon' => 'fe-monitor', 'label' => 'يمتلك حاسوب', 'value' => $yesNo($registration->has_computer)[0], 'badge' => $yesNo($registration->has_computer)[1]],
        ['icon' => 'fe-check-circle', 'label' => 'الالتزام بالتدريب', 'value' => $yesNo($registration->commitment_to_training)[0], 'badge' => $yesNo($registration->commitment_to_training)[1]],
        ['icon' => 'fe-clock', 'label' => 'وقت كافٍ للمتابعة', 'value' => $yesNo($registration->has_sufficient_time)[0], 'badge' => $yesNo($registration->has_sufficient_time)[1]],
        ['icon' => 'fe-sliders', 'label' => 'خبرة الحاسوب', 'value' => $computerLevels[$registration->computer_experience_level] ?? $registration->computer_experience_level],
        ['icon' => 'fe-code', 'label' => 'خبرة البرمجة', 'value' => $progLevels[$registration->programming_experience] ?? $registration->programming_experience],
        ['icon' => 'fe-flag', 'label' => 'الاهتمام بالمعسكر', 'value' => $yesNo($registration->interested_in_bootcamp)[0], 'badge' => $yesNo($registration->interested_in_bootcamp)[1]],
        ['icon' => 'fe-book', 'label' => 'آخر مرحلة دراسية', 'value' => $registration->education_level],
        ['icon' => 'fe-book-open', 'label' => 'التخصص الدراسي', 'value' => $registration->education_major],
        ['icon' => 'fe-briefcase', 'label' => 'العمل الحالي', 'value' => $registration->current_job],
    ];
@endphp

<div class="card custom-card group-show-members-card mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">البيانات الشخصية (من الفورم)</h6>
        <p class="fs-12 text-muted mb-0">المعلومات التي أدخلها الطالب عند التسجيل.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            @foreach($personalFields as $field)
                <div class="col-md-6 col-xl-4">
                    <div class="admin-profile-detail-field {{ empty($field['value']) ? 'is-empty' : '' }}">
                        <span class="admin-profile-detail-field__icon"><i class="fe {{ $field['icon'] }}"></i></span>
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
        <h6 class="group-show-members-card__title mb-1">الخبرة والتعليم</h6>
        <p class="fs-12 text-muted mb-0">أسئلة الفورم حول المعدات والخبرة والالتزام.</p>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            @foreach($experienceFields as $field)
                <div class="col-md-6 col-xl-4">
                    <div class="admin-profile-detail-field {{ empty($field['value']) || $field['value'] === '—' ? 'is-empty' : '' }}">
                        <span class="admin-profile-detail-field__icon"><i class="fe {{ $field['icon'] }}"></i></span>
                        <div class="min-w-0">
                            <span class="admin-profile-detail-field__label">{{ $field['label'] }}</span>
                            <span class="admin-profile-detail-field__value">
                                @if(!empty($field['badge']))
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
        @if($registration->computer_programming_background)
            <hr class="my-3">
            <div class="admin-profile-detail-field">
                <span class="admin-profile-detail-field__icon"><i class="fe fe-file-text"></i></span>
                <div class="min-w-0">
                    <span class="admin-profile-detail-field__label">نبذة عن الحاسوب والبرمجة</span>
                    <span class="admin-profile-detail-field__value">{{ $registration->computer_programming_background }}</span>
                </div>
            </div>
        @endif
    </div>
</div>

@if($registration->notes || $registration->additional_info || $registration->special_requirements)
    <div class="card custom-card group-show-members-card mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">ملاحظات إضافية</h6>
        </div>
        <div class="card-body pt-3">
            @if($registration->notes)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">ملاحظات الطالب</small>
                    <p class="mb-0">{{ $registration->notes }}</p>
                </div>
            @endif
            @if($registration->additional_info)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">معلومات إضافية</small>
                    <p class="mb-0">{{ $registration->additional_info }}</p>
                </div>
            @endif
            @if($registration->special_requirements)
                <div class="mb-0">
                    <small class="text-muted d-block mb-1">متطلبات خاصة</small>
                    <p class="mb-0">{{ $registration->special_requirements }}</p>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="card custom-card group-show-members-card">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">معلومات التسجيل</h6>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3 small">
            <div class="col-md-4">
                <small class="text-muted d-block">تاريخ إرسال الفورم</small>
                <strong>{{ $registration->created_at->format('Y-m-d H:i') }}</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">حالة المعالجة</small>
                @if($registration->status === 'completed')
                    <span class="group-show-chip group-show-chip--sm text-success">مكتمل</span>
                @elseif($registration->status === 'pending')
                    <span class="group-show-chip group-show-chip--sm text-warning">معلق</span>
                @elseif($registration->status === 'failed')
                    <span class="group-show-chip group-show-chip--sm text-danger">فاشل</span>
                @else
                    <span class="group-show-chip group-show-chip--sm">{{ $registration->status }}</span>
                @endif
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">تم إنشاء حساب</small>
                <strong>{{ $registration->user_created ? 'نعم' : 'لا' }}</strong>
            </div>
        </div>
    </div>
</div>
