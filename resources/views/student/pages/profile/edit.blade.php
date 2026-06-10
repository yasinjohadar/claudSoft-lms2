@extends('student.layouts.master')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-results__option img,
        .select2-container--bootstrap-5 .select2-selection__rendered img { border-radius: 0 !important; }
    </style>
@endpush

@section('page-title')
تعديل الملف الشخصي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong><i class="fa fa-times-circle me-1"></i> خطأ!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h4 class="page-title fs-24 mb-1 fw-bold">
                    <i class="fa fa-edit text-primary me-2"></i>
                    تعديل الملف الشخصي
                </h4>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.profile.index') }}">ملفي الشخصي</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <a href="{{ route('student.profile.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>العودة
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa fa-user me-2"></i>البيانات الأساسية</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" data-phone-ajax-validate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الاسم بالعربية</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" value="{{ old('name_ar', $student->name_ar) }}" placeholder="أدخل الاسم بالعربية">
                                    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">سيتم استخدام هذا الاسم في الترحيب والعرض</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الاسم بالإنجليزية <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $student->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control" value="{{ $student->email }}" readonly disabled style="background-color: #e9ecef; cursor: not-allowed;">
                                    <small class="text-muted">لا يمكن تغيير البريد الإلكتروني</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">رقم الهاتف</label>
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
                                            <small class="text-muted">بدون 0 في البداية</small>
                                        </div>
                                    </div>
                                    <div class="small mt-1 phone-country-ajax-feedback" data-phone-ajax-feedback aria-live="polite"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم الهوية</label>
                                    <input type="text" class="form-control @error('national_id') is-invalid @enderror" name="national_id" value="{{ old('national_id', $student->national_id) }}">
                                    @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">تاريخ الميلاد</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجنس</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                        <option value="">اختر الجنس</option>
                                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>أنثى</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجنسية</label>
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
                                    <label class="form-label">المدينة</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $student->city) }}" placeholder="أدخل المدينة">
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">العنوان</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address', $student->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_profile_public"
                                               name="is_profile_public" value="1"
                                               {{ old('is_profile_public', $student->is_profile_public) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_profile_public">
                                            إظهار ملفي الشخصي لبقية الطلاب في الواجهة الأمامية
                                        </label>
                                        <div class="form-text">
                                            عند التفعيل يمكن للطلاب الآخرين مشاهدة صفحتك في قائمة الطلاب. عند الإلغاء يظل ملفك خاصًا.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-2"></i>حفظ التغييرات
                                </button>
                                <a href="{{ route('student.profile.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card" id="photo-section">
                    <div class="card-header">
                        <h5><i class="fa fa-image me-2"></i>الصورة الشخصية</h5>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $photoPath = $student->photo ?? null;
                            $photoUrl = student_profile_photo_url($student);
                            $usesLogoAvatar = empty($photoPath);
                        @endphp

                        <div class="position-relative d-inline-block mb-3">
                            <div id="photo-preview-container" class="position-relative">
                                <img id="profile-photo-preview"
                                     src="{{ $photoUrl }}"
                                     class="rounded-circle {{ $usesLogoAvatar ? 'student-avatar--logo' : '' }}"
                                     width="150"
                                     height="150"
                                     style="object-fit: {{ $usesLogoAvatar ? 'contain' : 'cover' }}; border: 3px solid var(--primary-color, #0d6efd);"
                                     onerror="this.onerror=null;this.src='{{ student_default_avatar_url() }}';this.classList.add('student-avatar--logo');this.style.objectFit='contain';">
                            </div>

                            @if($photoPath)
                                <form action="{{ route('student.profile.delete-photo') }}" method="POST" class="position-absolute top-0 start-100 translate-middle" style="margin-top: 5px; margin-right: -10px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm rounded-circle" style="width: 32px; height: 32px;" onclick="return confirm('هل أنت متأكد من حذف الصورة الشخصية؟')" title="حذف الصورة">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="mt-2">
                            <form action="{{ route('student.profile.upload-photo') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                @csrf
                                <label for="photo-input" class="btn btn-sm btn-outline-primary mb-2">
                                    <i class="fa fa-upload me-1"></i>اختيار صورة جديدة
                                </label>
                                <input type="file" id="photo-input" class="form-control d-none @error('photo') is-invalid @enderror" name="photo" accept="image/*" onchange="document.getElementById('photo-upload-form').submit()">
                                @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </form>
                        </div>

                        <small class="text-muted d-block mt-1">الصيغ المدعومة: JPG, PNG, GIF, WebP</small>
                        <small class="text-muted d-block">الحد الأقصى: 2MB</small>
                        <small class="text-muted d-block">الحجم المثالي: 300×300 بكسل</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card" id="password-section">
                    <div class="card-header">
                        <h5><i class="fa fa-key me-2"></i>تغيير كلمة المرور</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.profile.change-password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required>
                                    <small class="text-muted d-block mt-1">8 أحرف على الأقل، حروف كبيرة وصغيرة، أرقام ورموز</small>
                                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="new_password_confirmation" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-key me-2"></i>تغيير كلمة المرور
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    var flagUrlTemplate = $('#student_country_code_select').attr('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';

    // Select2 لرمز الدولة: عرض العلم داخل القائمة وعند الاختيار
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
@stop
