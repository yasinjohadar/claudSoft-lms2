@extends('frontend2.layouts.master')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .registration-page-section {
            background: var(--clr-bg);
        }
        .registration-form-wrapper {
            padding: 28px;
        }
        .registration-title {
            line-height: 1.5;
        }
        .registration-subtitle {
            color: var(--clr-text-secondary);
            margin-bottom: 22px;
            font-size: 0.95rem;
        }
        .registration-hero-logo {
            max-height: 62px;
            width: auto;
            display: block;
            margin: 0 auto 10px;
        }
        .registration-section-card {
            border: 1px solid var(--clr-border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .registration-section-card .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .registration-section-card .card-body {
            background: rgba(255, 255, 255, 0.02);
        }
        .form-check {
            padding-right: 1.75em;
            padding-left: 0;
        }
        .form-check .form-check-input {
            float: right;
            margin-right: -1.75em;
            margin-left: 0.5em;
            border-color: #555;
            border-width: 2px;
        }
        .form-check .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        #registrationForm .form-label:not(.required)::after {
            content: ":";
        }
        #registrationForm .form-label.required::after {
            content: ": *";
            color: #dc3545;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .select2-container--bootstrap-5 .select2-results__option img,
        .select2-container--bootstrap-5 .select2-selection__rendered img {
            border-radius: 0 !important;
        }
        .country-code-select,
        .country-code-select option {
            font-size: 1.05rem;
        }
        @media (max-width: 768px) {
            .registration-title {
                line-height: 1.85;
            }
            #registrationForm .form-label {
                font-size: 1.02rem;
                font-weight: 600;
            }
            .registration-form-wrapper {
                padding: 18px;
            }
        }
    </style>
@endpush

@section('title', 'التسجيل في ' . ($settings->diploma_name ?? 'دبلوم البرمجة') . ' - الدفعة (' . $group->name . ')')

@section('content')
    <section class="page-banner page-banner-contact">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-user-plus"></i></div>
                <h1 class="page-banner-title">التسجيل في <span>{{ $group->name }}</span></h1>
                <p class="page-banner-desc">{{ $settings->diploma_name ?? 'دبلوم البرمجة' }}</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>التسجيل الجماعي</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding registration-page-section">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-lg-10">
                    <div class="glass-panel registration-form-wrapper animate-on-scroll">
                        <div class="text-center mb-4 pb-3 border-bottom">
                            <a href="{{ url('/') }}" class="d-inline-block text-decoration-none">
                                <img src="{{ asset('frontend/assets/images/logo.png') }}" alt="كلاودسوفت التعليمية" class="registration-hero-logo">
                                <h2 class="h5 mb-0 mt-2">كلاودسوفت التعليمية</h2>
                            </a>
                        </div>

                        <h4 class="registration-title fw-bold mb-2">
                            <i class="fas fa-user-plus me-2"></i>
                            التسجيل في {{ $settings->diploma_name ?? 'دبلوم البرمجة' }} - الدفعة ({{ $group->name }})
                        </h4>
                        <p class="registration-subtitle">يرجى تعبئة جميع الحقول المطلوبة بدقة لضمان مراجعة طلبك بسرعة.</p>

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('frontend.group-registration.store', $group->id) }}" method="POST" id="registrationForm" data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                            @csrf

                            <!-- الاسم -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">الاسم الكامل بالإنجليزية</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">الاسم الكامل بالعربية</label>
                                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                           value="{{ old('name_ar') }}" required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div class="mb-3">
                                <label class="form-label required">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- رقم الهاتف -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label required">رمز الدولة</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select name="country_code" id="country_code_select" class="form-select country-code-select @error('country_code') is-invalid @enderror" required
                                                data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                            <option value="">اختر رمز الدولة</option>
                                            @foreach(config('country_codes.list', []) as $code => $label)
                                                @php
                                                    $isoList = config('country_codes.iso', []);
                                                    $iso = $isoList[$code] ?? '';
                                                    $textOnly = config('country_codes.list_text_only', [])[$code] ?? $label;
                                                    $separator = config('country_codes.separator', '  ·  ');
                                                    $display = $iso !== '' ? $textOnly . $separator . $iso : $textOnly;
                                                @endphp
                                                <option value="{{ $code }}" data-iso="{{ strtolower($iso) }}" {{ old('country_code', config('country_codes.default', '+966')) == $code ? 'selected' : '' }}>
                                                    {{ $display }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('country_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label required">رقم الهاتف (بدون 0)</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" placeholder="5xxxxxxxx" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- الجنسية -->
                            <div class="mb-3">
                                <label class="form-label">الجنسية</label>
                                <div class="d-flex align-items-center gap-2">
                                    <select name="nationality_id" id="nationality_id_select" class="form-select nationality-select-with-flag @error('nationality_id') is-invalid @enderror"
                                            data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                        <option value="">اختر الجنسية</option>
                                        @foreach($nationalities as $nationality)
                                            @php
                                                $isoMap = config('country_codes.nationality_iso', []);
                                                $displayMap = config('country_codes.nationality_display', []);
                                                $iso = $isoMap[$nationality->name] ?? '';
                                                $displayText = $displayMap[$nationality->name] ?? $nationality->name;
                                            @endphp
                                            <option value="{{ $nationality->id }}" data-flag-iso="{{ $iso }}" {{ old('nationality_id') == $nationality->id ? 'selected' : '' }}>
                                                {{ $displayText }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('nationality_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ الميلاد والجنس -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">تاريخ الميلاد</label>
                                    <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجنس</label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="">اختر الجنس</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- العنوان والمدينة -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">العنوان</label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" 
                                           value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">المدينة</label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                           value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">معلومات إضافية</label>
                                <textarea name="additional_info" class="form-control @error('additional_info') is-invalid @enderror" rows="3">{{ old('additional_info') }}</textarea>
                                @error('additional_info')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">متطلبات خاصة</label>
                                <textarea name="special_requirements" class="form-control @error('special_requirements') is-invalid @enderror" rows="3">{{ old('special_requirements') }}</textarea>
                                @error('special_requirements')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- قسم الالتزام والوقت -->
                            <div class="card registration-section-card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>
                                        الالتزام والوقت
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- الالتزام بالتدريب -->
                                    <div class="mb-4">
                                        <label class="form-label required">هل أنت مستعد للالتزام بالتدريب بالكامل؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="commitment_to_training" id="commitment_yes" value="yes" {{ old('commitment_to_training') == 'yes' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="commitment_yes">
                                                نعم مستعد للالتزام بكامل الفترة التدريبية
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="commitment_to_training" id="commitment_no" value="no" {{ old('commitment_to_training') == 'no' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger" for="commitment_no">
                                                لا (غير مستعد للالتزام)
                                            </label>
                                        </div>
                                        <small class="text-danger d-block mt-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            في حال عدم الاستعداد للالتزام يرجى إتاحة الفرصة لغيركم لأن العدد محدود
                                        </small>
                                        @error('commitment_to_training')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الوقت الكافي -->
                                    <div class="mb-3">
                                        <label class="form-label required">هل لديك الوقت الكافي لمتابعة الدبلوم (ساعتين يومياً على الأقل)؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_sufficient_time" id="time_yes" value="yes" {{ old('has_sufficient_time') == 'yes' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="time_yes">
                                                نعم
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_sufficient_time" id="time_no" value="no" {{ old('has_sufficient_time') == 'no' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger" for="time_no">
                                                لا (ليس بإمكانك المتابعة)
                                            </label>
                                        </div>
                                        <small class="text-danger d-block mt-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            في حال عدم التفرغ يرجى عدم التسجيل لان غيركم ينتظر الفرصة للتسجيل لأن العدد محدود
                                        </small>
                                        @error('has_sufficient_time')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- قسم المعدات والخبرة -->
                            <div class="card registration-section-card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-laptop-code me-2"></i>
                                        المعدات والخبرة
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- امتلاك الحاسوب -->
                                    <div class="mb-4">
                                        <label class="form-label required">هل تمتلك حاسوب؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_computer" id="computer_yes" value="yes" {{ old('has_computer') == 'yes' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="computer_yes">
                                                نعم أملك حاسوب
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="has_computer" id="computer_no" value="no" {{ old('has_computer') == 'no' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger" for="computer_no">
                                                لا أملك حاسوب
                                            </label>
                                        </div>
                                        @error('has_computer')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- خبرة الحاسوب -->
                                    <div class="mb-4">
                                        <label class="form-label required">كيف تقيم خبرتك بالحاسوب بشكل عام؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="computer_experience_level" id="comp_exp_none" value="none" {{ old('computer_experience_level') == 'none' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger" for="comp_exp_none">
                                                لايوجد معرفة بالحاسوب نهائياً (ليس بإمكانك المتابعة)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="computer_experience_level" id="comp_exp_beginner" value="beginner" {{ old('computer_experience_level') == 'beginner' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="comp_exp_beginner">
                                                مبتدئ
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="computer_experience_level" id="comp_exp_intermediate" value="intermediate" {{ old('computer_experience_level') == 'intermediate' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="comp_exp_intermediate">
                                                متوسط
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="computer_experience_level" id="comp_exp_good" value="good" {{ old('computer_experience_level') == 'good' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="comp_exp_good">
                                                خبرة جيدة
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="computer_experience_level" id="comp_exp_advanced" value="advanced" {{ old('computer_experience_level') == 'advanced' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="comp_exp_advanced">
                                                خبرة عالية
                                            </label>
                                        </div>
                                        @error('computer_experience_level')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- خبرة البرمجة -->
                                    <div class="mb-4">
                                        <label class="form-label required">هل تمتلك خبرة بالبرمجة؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="programming_experience" id="prog_exp_none" value="none" {{ old('programming_experience') == 'none' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="prog_exp_none">
                                                لا أملك خبرة
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="programming_experience" id="prog_exp_beginner" value="beginner" {{ old('programming_experience') == 'beginner' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="prog_exp_beginner">
                                                مبتدئ
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="programming_experience" id="prog_exp_intermediate" value="intermediate" {{ old('programming_experience') == 'intermediate' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="prog_exp_intermediate">
                                                متوسط
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="programming_experience" id="prog_exp_expert" value="expert" {{ old('programming_experience') == 'expert' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="prog_exp_expert">
                                                خبير
                                            </label>
                                        </div>
                                        @error('programming_experience')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- نبذة عن الخبرة -->
                                    <div class="mb-3">
                                        <label class="form-label required">نبذة عن خبرتك بالحاسوب والبرمجة</label>
                                        <textarea name="computer_programming_background" class="form-control @error('computer_programming_background') is-invalid @enderror" rows="4" required>{{ old('computer_programming_background') }}</textarea>
                                        @error('computer_programming_background')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- قسم المعلومات التعليمية -->
                            <div class="card registration-section-card border-success mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-graduation-cap me-2"></i>
                                        المعلومات التعليمية
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">آخر مرحلة دراسية حاصل عليها</label>
                                            <input type="text" name="education_level" class="form-control @error('education_level') is-invalid @enderror" 
                                                   value="{{ old('education_level') }}" required>
                                            @error('education_level')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">التخصص الدراسي</label>
                                            <input type="text" name="education_major" class="form-control @error('education_major') is-invalid @enderror" 
                                                   value="{{ old('education_major') }}" required>
                                            @error('education_major')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label required">العمل الحالي</label>
                                        <input type="text" name="current_job" class="form-control @error('current_job') is-invalid @enderror" 
                                               value="{{ old('current_job') }}" required>
                                        @error('current_job')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- قسم المعسكر -->
                            <div class="card registration-section-card border-warning mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-campground me-2"></i>
                                        المعسكر التدريبي
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label required">هل أنت مهتم بدخول المعسكر التدريبي بعد الدبلوم (مأجور - ليس مجاني قيمته 100 دولار)؟</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="interested_in_bootcamp" id="bootcamp_yes" value="yes" {{ old('interested_in_bootcamp') == 'yes' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="bootcamp_yes">
                                                نعم مهتم بحضور المعسكر التدريبي
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="interested_in_bootcamp" id="bootcamp_no" value="no" {{ old('interested_in_bootcamp') == 'no' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="bootcamp_no">
                                                لا غير مهتم
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            مزايا المعسكر التدريبي مذكورة بالصفحة الخاصة بالدبلوم
                                        </small>
                                        @error('interested_in_bootcamp')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- زر الإرسال -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    إرسال طلب التسجيل
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var flagUrlTemplate = document.getElementById('registrationForm') && document.getElementById('registrationForm').getAttribute('data-flag-url')
        ? document.getElementById('registrationForm').getAttribute('data-flag-url') : 'https://flagcdn.com/w20/{iso}.png';

    // Select2 لرمز الدولة: عرض العلم داخل القائمة وعند الاختيار
    var $countrySelect = $('#country_code_select');
    if ($countrySelect.length) {
        $countrySelect.select2({
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
    }

    // Select2 للجنسية: نفس القائمة العلوية 100% (علم + اسم الدولة + الرمز + · + ISO)
    var $nationalitySelect = $('#nationality_id_select');
    if ($nationalitySelect.length) {
        var natFlagUrl = $nationalitySelect.attr('data-flag-url') || flagUrlTemplate;
        $nationalitySelect.select2({
            placeholder: 'اختر الجنسية',
            allowClear: false,
            dir: 'rtl',
            width: '100%',
            theme: 'bootstrap-5',
            templateResult: function(state) {
                if (!state.id) return state.text;
                var iso = $(state.element).data('flag-iso') || '';
                if (!iso) return state.text;
                var url = natFlagUrl.replace('{iso}', iso.toLowerCase());
                var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
                $span.append(document.createTextNode(state.text));
                return $span;
            },
            templateSelection: function(state) {
                if (!state.id) return state.text;
                var iso = $(state.element).data('flag-iso') || '';
                if (!iso) return state.text;
                var url = natFlagUrl.replace('{iso}', iso.toLowerCase());
                var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
                $span.append(document.createTextNode(state.text));
                return $span;
            }
        });
    }

    var phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        function stripLeadingZero() {
            var val = phoneInput.value.trim();
            if (val && val.charAt(0) === '0') {
                phoneInput.value = val.substring(1);
            }
        }
        phoneInput.addEventListener('blur', stripLeadingZero);
        phoneInput.addEventListener('input', function() {
            if (phoneInput.value.length === 1 && phoneInput.value === '0') {
                phoneInput.value = '';
            }
        });
    }
});
</script>
@endpush
