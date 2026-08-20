@extends('student.layouts.master')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .student-profile-edit-page .select2-container--bootstrap-5 .select2-results__option img,
        .student-profile-edit-page .select2-container--bootstrap-5 .select2-selection__rendered img { border-radius: 0 !important; }
    </style>
@endpush

@section('page-title')
    {{ ($profileLocked ?? false) ? 'إكمال الملف الشخصي' : 'تعديل الملف الشخصي' }}
@stop

@section('content')
<div class="main-content app-content student-profile-page student-profile-edit-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        @include('student.pages.profile.partials.profile-edit-required-notice', ['profileLocked' => $profileLocked ?? false])

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1 {{ ($profileLocked ?? false) ? 'text-danger' : '' }}">
                    @if($profileLocked ?? false)
                        <i class="fe fe-alert-circle me-1"></i>
                    @endif
                    {{ ($profileLocked ?? false) ? 'صفحة إكمال الملف الشخصي (إلزامية)' : 'تعديل الملف الشخصي' }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if($profileLocked ?? false)
                            <li class="breadcrumb-item active">إكمال الملف الشخصي — خطوة مطلوبة</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.profile.index') }}">ملفي الشخصي</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        @endif
                    </ol>
                </nav>
                <p class="mb-0 mt-2 fs-13 {{ ($profileLocked ?? false) ? 'text-danger fw-semibold' : 'text-muted' }}">
                    @if($profileLocked ?? false)
                        أنت الآن في الصفحة الوحيدة المتاحة لك — أكمل البيانات ثم احفظ.
                    @else
                        حدّث بياناتك لتظهر بشكل صحيح في المنصة والشهادات
                    @endif
                </p>
            </div>
            @unless($profileLocked ?? false)
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.profile.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fe fe-arrow-right me-1"></i>العودة للملف
                    </a>
                </div>
            @endunless
        </div>

        @include('student.pages.profile.partials.profile-hero', ['student' => $student])

        @include('student.pages.profile.partials.profile-completion', [
            'student' => $student,
            'requiredMode' => ($profileLocked ?? false) || session('profile_completion_required'),
        ])

        <div class="row g-4">
            <div class="col-xl-8 order-2 order-xl-1">
                <div class="card custom-card student-quizzes-panel">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="avatar avatar-sm bg-primary-transparent">
                                <i class="fe fe-user text-primary"></i>
                            </span>
                            <div>
                                <h6 class="card-title mb-0">البيانات الأساسية</h6>
                                <p class="text-muted fs-12 mb-0">المعلومات الشخصية والتواصل</p>
                            </div>
                        </div>

                        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" data-phone-ajax-validate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">الاسم بالعربية</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" value="{{ old('name_ar', $student->name_ar) }}" placeholder="أدخل الاسم بالعربية">
                                    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="student-profile-form-hint">يُستخدم في الترحيب وعرض اسمك في المنصة</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">الاسم بالإنجليزية <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $student->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control student-profile-form-readonly" value="{{ $student->email }}" readonly disabled>
                                    <div class="student-profile-form-hint">لا يمكن تغيير البريد الإلكتروني</div>
                                </div>
                                <div class="col-12">
                                    <label class="student-profile-form-label">رقم الهاتف</label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <select name="country_code" id="student_country_code_select" class="form-select @error('country_code') is-invalid @enderror" data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                                <option value="">اختر رمز الدولة</option>
                                                @foreach(config('country_codes.list', []) as $code => $label)
                                                    @php
                                                        $isoList = config('country_codes.iso', []);
                                                        $iso = $isoList[$code] ?? '';
                                                        $textOnly = config('country_codes.list_text_only', [])[$code] ?? $label;
                                                        $separator = config('country_codes.separator', '  ·  ');
                                                        $display = $iso !== '' ? $textOnly . $separator . $iso : $textOnly;
                                                        $selectedCode = old('country_code', $student->country_code ?? config('country_codes.default', '+966'));
                                                    @endphp
                                                    <option value="{{ $code }}" data-iso="{{ strtolower($iso) }}" {{ $selectedCode == $code ? 'selected' : '' }}>
                                                        {{ $display }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}" placeholder="5xxxxxxxx">
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <div class="student-profile-form-hint">بدون 0 في البداية</div>
                                        </div>
                                    </div>
                                    <div class="small mt-1 phone-country-ajax-feedback" data-phone-ajax-feedback aria-live="polite"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">تاريخ الميلاد</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">الجنس</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                        <option value="">اختر الجنس</option>
                                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>أنثى</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">الجنسية</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="student-nationality-flag" src="" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;flex-shrink:0;display:none;">
                                        <select class="form-select @error('nationality_id') is-invalid @enderror" name="nationality_id" id="student_nationality_select" data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                            <option value="">اختر الجنسية</option>
                                            @foreach($nationalities as $nationality)
                                                @php
                                                    $isoMap = config('country_codes.nationality_iso', []);
                                                    $displayMap = config('country_codes.nationality_display', []);
                                                    $iso = $isoMap[$nationality->name] ?? '';
                                                    $displayText = $displayMap[$nationality->name] ?? $nationality->name;
                                                @endphp
                                                <option value="{{ $nationality->id }}" data-flag-iso="{{ $iso }}" {{ old('nationality_id', $student->nationality_id) == $nationality->id ? 'selected' : '' }}>
                                                    {{ $displayText }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="student-profile-form-label">المدينة</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $student->city) }}" placeholder="أدخل المدينة">
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="student-profile-form-label">العنوان</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3" placeholder="الحي، الشارع، رقم المبنى...">{{ old('address', $student->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="student-profile-field mt-1">
                                        <span class="student-profile-field__icon">
                                            <i class="fe fe-globe"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="is_profile_public"
                                                       name="is_profile_public" value="1"
                                                       {{ old('is_profile_public', $student->is_profile_public) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="is_profile_public">
                                                    إظهار ملفي الشخصي لبقية الطلاب
                                                </label>
                                            </div>
                                            <div class="student-profile-form-hint mt-1 mb-0">
                                                عند التفعيل يمكن للطلاب الآخرين مشاهدة صفحتك في قائمة الطلاب.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="student-profile-edit-actions">
                                <button type="submit" class="btn {{ ($profileLocked ?? false) ? 'btn-danger' : 'btn-primary' }} rounded-pill px-4">
                                    <i class="fe fe-save me-1"></i>{{ ($profileLocked ?? false) ? 'حفظ وإكمال الملف' : 'حفظ التغييرات' }}
                                </button>
                                @unless($profileLocked ?? false)
                                    <a href="{{ route('student.profile.index') }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                                @endunless
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 order-1 order-xl-2">
                <div class="card custom-card student-quizzes-panel" id="photo-section">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="avatar avatar-sm bg-info-transparent">
                                <i class="fe fe-image text-info"></i>
                            </span>
                            <h6 class="card-title mb-0">الصورة الشخصية</h6>
                        </div>

                        @php
                            $photoPath = $student->photo ?? null;
                            $photoUrl = student_profile_photo_url($student);
                            $usesLogoAvatar = empty($photoPath);
                        @endphp

                        <div class="text-center mb-3 position-relative d-inline-block w-100">
                            <div class="student-profile-photo mx-auto position-relative" style="width: fit-content;">
                                <img id="profile-photo-preview"
                                     src="{{ $photoUrl }}"
                                     alt="{{ $student->name }}"
                                     class="student-profile-photo__img {{ $usesLogoAvatar ? 'student-avatar--logo' : '' }}"
                                     onerror="this.onerror=null;this.src='{{ student_default_avatar_url() }}';this.classList.add('student-avatar--logo');">

                                @if($photoPath)
                                    <form action="{{ route('student.profile.delete-photo') }}" method="POST" class="student-profile-photo__delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف الصورة الشخصية؟')" title="حذف الصورة">
                                            <i class="fe fe-x"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="student-profile-photo-upload text-center">
                            <form action="{{ route('student.profile.upload-photo') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                @csrf
                                <label for="photo-input" class="btn btn-primary btn-sm rounded-pill mb-2">
                                    <i class="fe fe-upload me-1"></i>اختيار صورة جديدة
                                </label>
                                <input type="file" id="photo-input" class="form-control d-none @error('photo') is-invalid @enderror" name="photo" accept="image/*" onchange="document.getElementById('photo-upload-form').submit()">
                                @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </form>
                            <div class="student-profile-form-hint mb-0">
                                JPG, PNG, GIF, WebP — حتى 2MB — مثالي 300×300
                            </div>
                        </div>
                    </div>
                </div>
                @include('student.components.telegram-connect-card', ['compact' => true, 'class' => 'mt-4'])
                <div class="card custom-card student-quizzes-panel student-profile-edit-tips mt-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-success-transparent">
                                <i class="fe fe-check-circle text-success"></i>
                            </span>
                            <h6 class="card-title mb-0">نصائح سريعة</h6>
                        </div>
                        <ul class="list-unstyled mb-0 ps-0">
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>استخدم اسمك الحقيقي كما تريد ظهوره في الشهادات.</li>
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>تأكد من صحة رقم الهاتف لاستلام الإشعارات.</li>
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>أكمل جميع الحقول للوصول إلى 100% في اكتمال الملف.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    var flagUrlTemplate = $('#student_country_code_select').attr('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';

    $('#student_country_code_select').select2({
        placeholder: 'اختر رمز الدولة',
        allowClear: false,
        dir: 'rtl',
        width: '100%',
        theme: 'bootstrap-5',
        templateResult: function(state) {
            if (!state.id) return state.text;
            var iso = $(state.element).data('iso') || 'sa';
            var url = flagUrlTemplate.replace('{iso}', iso.toLowerCase());
            var $span = $('<span class="d-flex align-items-center gap-2"></span>');
            $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
            $span.append(document.createTextNode(state.text));
            return $span;
        },
        templateSelection: function(state) {
            if (!state.id) return state.text;
            var iso = $(state.element).data('iso') || 'sa';
            var url = flagUrlTemplate.replace('{iso}', iso.toLowerCase());
            var $span = $('<span class="d-flex align-items-center gap-2"></span>');
            $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
            $span.append(document.createTextNode(state.text));
            return $span;
        }
    });

    function updateStudentNationalityFlag() {
        var sel = document.getElementById('student_nationality_select');
        var img = document.getElementById('student-nationality-flag');
        if (!sel || !img) return;
        var opt = sel.options[sel.selectedIndex];
        var iso = opt && opt.getAttribute('data-flag-iso') ? opt.getAttribute('data-flag-iso') : '';
        var urlTemplate = sel.getAttribute('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';
        if (iso) {
            img.src = urlTemplate.replace('{iso}', iso.toLowerCase());
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
    }
    if (document.getElementById('student_nationality_select')) {
        updateStudentNationalityFlag();
        document.getElementById('student_nationality_select').addEventListener('change', updateStudentNationalityFlag);
    }

    var phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (phoneInput.value.length === 1 && phoneInput.value === '0') {
                phoneInput.value = '';
            }
        });
        phoneInput.addEventListener('blur', function() {
            var val = phoneInput.value.trim();
            if (val && val.charAt(0) === '0') {
                phoneInput.value = val.substring(1);
            }
        });
    }
});
</script>
@include('components.phone-country-ajax-script')
@endpush
