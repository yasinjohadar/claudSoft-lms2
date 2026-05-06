@extends('frontend2.layouts.master')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .registration-page-section {
            background: var(--clr-bg);
            padding-top: 140px !important;
            padding-bottom: 30px !important;
        }
        .registration-form-wrapper {
            padding: 4px 8px;
            margin-top: 20px;
        }
        .registration-page-section.section-padding {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .glass-panel {
            margin-top: 0 !important;
        }
        .registration-form-wrapper {
            padding: 4px 8px;
        }
        .registration-title {
            line-height: 1.2;
            font-size: 1.1rem !important;
            margin-bottom: 4px !important;
        }
        .registration-subtitle {
            color: var(--clr-text-secondary);
            margin-bottom: 8px;
            font-size: 0.8rem;
        }
        .registration-hero-logo {
            max-height: 70px;
            width: auto;
            display: block;
            margin: 0 auto 8px;
        }
        .registration-header {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .registration-header .registration-title {
            text-align: center;
            font-size: 1.3rem !important;
            font-weight: bold;
            line-height: 1.6;
            margin-bottom: 0;
        }
        .registration-subtitle {
            color: var(--clr-text-secondary);
            margin-bottom: 12px;
            font-size: 0.85rem;
            text-align: center;
        }
        .registration-section-card {
            border: 1px solid var(--clr-border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .registration-section-card .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 6px 12px !important;
        }
        .registration-section-card .card-header h5 {
            font-size: 0.95rem !important;
        }
        .registration-section-card .card-body {
            background: rgba(255, 255, 255, 0.02);
            padding: 8px !important;
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
            font-size: 1rem;
        }
        .compact-form .form-control,
        .compact-form .form-select {
            padding: 4px 10px;
            font-size: 0.85rem;
        }
        .compact-form .form-label {
            font-size: 0.8rem !important;
            margin-bottom: 1px !important;
        }
        .compact-form .mb-3 {
            margin-bottom: 0.35rem !important;
        }
        .compact-form .card {
            margin-bottom: 0.35rem !important;
        }
        .compact-form hr {
            margin: 0.35rem 0 !important;
        }
        .compact-form .text-center.mb-2 {
            margin-bottom: 0.25rem !important;
        }
        .compact-form .row.mb-3 {
            margin-bottom: 0.35rem !important;
        }
        .compact-form .btn-lg {
            padding: 6px 16px;
            font-size: 0.9rem;
        }
        .compact-form .form-check-label {
            font-size: 0.85rem;
        }
        .compact-form .small,
        .compact-form small {
            font-size: 0.75rem !important;
        }
        .page-banner-contact-minimal {
            padding: 50px 0 !important;
            min-height: auto !important;
        }
        .page-banner-contact-minimal .page-banner-content {
            padding: 0 !important;
        }
        .page-banner-contact-minimal .page-banner-icon {
            font-size: 1.5rem !important;
            margin-bottom: 5px !important;
        }
        .page-banner-contact-minimal .page-banner-title {
            font-size: 1.3rem !important;
            margin-bottom: 5px !important;
            line-height: 1.3 !important;
        }
        .page-banner-contact-minimal .page-banner-desc {
            font-size: 0.85rem !important;
            margin-bottom: 5px !important;
        }
        .page-banner-contact-minimal .page-banner-breadcrumb {
            font-size: 0.8rem !important;
        }
        @media (max-width: 768px) {
            .registration-title {
                line-height: 1.5;
            }
            #registrationForm .form-label {
                font-size: 0.9rem !important;
                font-weight: 600;
            }
            .registration-form-wrapper {
                padding: 6px 8px;
            }
        }
    </style>
@endpush

@section('title', 'التسجيل في ' . ($settings->diploma_name ?? 'دبلوم البرمجة') . ' - الدفعة (' . $group->name . ')')

@section('content')
    <section class="section-padding registration-page-section" style="padding-top: 130px !important;">
        <div class="container" style="padding-top: 10px;">
            <div class="row justify-content-center g-1">
                <div class="col-lg-10">
                    <div class="glass-panel registration-form-wrapper compact-form">
                        <div class="registration-header">
                            <img src="{{ asset('frontend/assets/images/logo.png') }}" alt="كلاودسوفت التعليمية" class="registration-hero-logo">
                            <h1 class="registration-title">
                                التسجيل في {{ $settings->diploma_name ?? 'دبلوم البرمجة' }} - الدفعة ({{ $group->name }})
                            </h1>
                        </div>
                        <p class="registration-subtitle">يرجى تعبئة جميع الحقول المطلوبة بدقة.</p>

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

                        <form action="{{ route('frontend.group-registration.store', $group->id) }}" method="POST" id="registrationForm" data-phone-ajax-validate data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
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
                            <div class="mt-1 small phone-country-ajax-feedback" data-phone-ajax-feedback aria-live="polite"></div>

                            <!-- تاريخ الميلاد -->
                            <div class="mb-3">
                                <label class="form-label">تاريخ الميلاد</label>
                                <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}">
                                @error('date_of_birth')
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
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label required">آخر مرحلة دراسية حاصل عليها</label>
                                            <input type="text" name="education_level" class="form-control @error('education_level') is-invalid @enderror" 
                                                   value="{{ old('education_level') }}" required>
                                            @error('education_level')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
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
@include('components.phone-country-ajax-script')
@endpush
