@extends('frontend.group-registration.layout')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@section('title', 'التسجيل في ' . ($settings->diploma_name ?? 'دبلوم البرمجة') . ' - الدفعة (' . $group->name . ')')

@section('content')
    <div class="gr-page">
        <div class="gr-shell">
            <div class="gr-card">
                <div class="gr-card__inner">
                    <header class="gr-hero">
                        <img src="/frontend/assets/images/logo.png" alt="كلاودسوفت التعليمية" class="gr-hero__logo">
                        <h1 class="gr-hero__title">
                            التسجيل في {{ $settings->diploma_name ?? 'دبلوم البرمجة' }}
                            <br>
                            <span class="text-primary">الدفعة ({{ $group->name }})</span>
                        </h1>
                        <p class="gr-hero__subtitle">يرجى تعبئة جميع الحقول المطلوبة بدقة</p>
                    </header>

                    @if($errors->any())
                        <div class="alert alert-danger gr-alert alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    @endif

                    <form action="{{ route('frontend.group-registration.store', $group->id) }}" method="POST" enctype="multipart/form-data" id="registrationForm" data-phone-ajax-validate data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                        @csrf

                        <div class="gr-section gr-section--primary">
                            <h2 class="gr-section__title gr-section__title--primary">
                                <i class="fas fa-user"></i>
                                المعلومات الشخصية
                            </h2>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="gr-field">
                                        <label class="gr-label required">الاسم الكامل بالإنجليزية</label>
                                        <input type="text" name="name" class="form-control gr-input @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" required autocomplete="name">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="gr-field">
                                        <label class="gr-label required">الاسم الكامل بالعربية</label>
                                        <input type="text" name="name_ar" class="form-control gr-input @error('name_ar') is-invalid @enderror"
                                               value="{{ old('name_ar') }}" required>
                                        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="gr-field">
                                        <label class="gr-label required">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control gr-input @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}" required autocomplete="email" inputmode="email">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-5">
                                    <div class="gr-field">
                                        <label class="gr-label required">رمز الدولة</label>
                                        <select name="country_code" id="country_code_select" class="form-select gr-select @error('country_code') is-invalid @enderror" required
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
                                        @error('country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-7">
                                    <div class="gr-field">
                                        <label class="gr-label required">رقم الهاتف (بدون 0)</label>
                                        <input type="tel" name="phone" class="form-control gr-input @error('phone') is-invalid @enderror"
                                               value="{{ old('phone') }}" placeholder="5xxxxxxxx" required inputmode="numeric" autocomplete="tel-national">
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="phone-country-ajax-feedback small text-muted mt-1" data-phone-ajax-feedback aria-live="polite"></div>
                        </div>

                        <div class="gr-section gr-section--primary">
                            <h2 class="gr-section__title gr-section__title--primary">
                                <i class="fas fa-calendar-check"></i>
                                الالتزام والوقت
                            </h2>

                            <div class="gr-field">
                                <label class="gr-label required">هل أنت مستعد للالتزام بالتدريب بالكامل؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item">
                                        <input type="radio" name="commitment_to_training" id="commitment_yes" value="yes" {{ old('commitment_to_training') == 'yes' ? 'checked' : '' }} required>
                                        <label for="commitment_yes">نعم، مستعد للالتزام بكامل الفترة التدريبية</label>
                                    </div>
                                    <div class="gr-radio-item gr-radio-item--danger">
                                        <input type="radio" name="commitment_to_training" id="commitment_no" value="no" {{ old('commitment_to_training') == 'no' ? 'checked' : '' }} required>
                                        <label for="commitment_no">لا (غير مستعد للالتزام)</label>
                                    </div>
                                </div>
                                <div class="gr-note gr-note--danger">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <span>في حال عدم الاستعداد للالتزام يرجى إتاحة الفرصة لغيركم لأن العدد محدود</span>
                                </div>
                                @error('commitment_to_training')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="gr-field mb-0">
                                <label class="gr-label required">هل لديك الوقت الكافي لمتابعة الدبلوم (ساعتين يومياً على الأقل)؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item">
                                        <input type="radio" name="has_sufficient_time" id="time_yes" value="yes" {{ old('has_sufficient_time') == 'yes' ? 'checked' : '' }} required>
                                        <label for="time_yes">نعم</label>
                                    </div>
                                    <div class="gr-radio-item gr-radio-item--danger">
                                        <input type="radio" name="has_sufficient_time" id="time_no" value="no" {{ old('has_sufficient_time') == 'no' ? 'checked' : '' }} required>
                                        <label for="time_no">لا (ليس بإمكانك المتابعة)</label>
                                    </div>
                                </div>
                                <div class="gr-note gr-note--danger">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <span>في حال عدم التفرغ يرجى عدم التسجيل لأن غيركم ينتظر الفرصة والعدد محدود</span>
                                </div>
                                @error('has_sufficient_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="gr-section gr-section--info">
                            <h2 class="gr-section__title gr-section__title--info">
                                <i class="fas fa-laptop-code"></i>
                                المعدات والخبرة
                            </h2>

                            <div class="gr-field">
                                <label class="gr-label required">هل تمتلك حاسوب؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item">
                                        <input type="radio" name="has_computer" id="computer_yes" value="yes" {{ old('has_computer') == 'yes' ? 'checked' : '' }} required>
                                        <label for="computer_yes">نعم أملك حاسوب</label>
                                    </div>
                                    <div class="gr-radio-item gr-radio-item--danger">
                                        <input type="radio" name="has_computer" id="computer_no" value="no" {{ old('has_computer') == 'no' ? 'checked' : '' }} required>
                                        <label for="computer_no">لا أملك حاسوب</label>
                                    </div>
                                </div>
                                @error('has_computer')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="gr-field">
                                <label class="gr-label required">كيف تقيم خبرتك بالحاسوب بشكل عام؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item gr-radio-item--danger">
                                        <input type="radio" name="computer_experience_level" id="comp_exp_none" value="none" {{ old('computer_experience_level') == 'none' ? 'checked' : '' }} required>
                                        <label for="comp_exp_none">لا يوجد معرفة بالحاسوب نهائياً (ليس بإمكانك المتابعة)</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="computer_experience_level" id="comp_exp_beginner" value="beginner" {{ old('computer_experience_level') == 'beginner' ? 'checked' : '' }} required>
                                        <label for="comp_exp_beginner">مبتدئ</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="computer_experience_level" id="comp_exp_intermediate" value="intermediate" {{ old('computer_experience_level') == 'intermediate' ? 'checked' : '' }} required>
                                        <label for="comp_exp_intermediate">متوسط</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="computer_experience_level" id="comp_exp_good" value="good" {{ old('computer_experience_level') == 'good' ? 'checked' : '' }} required>
                                        <label for="comp_exp_good">خبرة جيدة</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="computer_experience_level" id="comp_exp_advanced" value="advanced" {{ old('computer_experience_level') == 'advanced' ? 'checked' : '' }} required>
                                        <label for="comp_exp_advanced">خبرة عالية</label>
                                    </div>
                                </div>
                                @error('computer_experience_level')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="gr-field mb-0">
                                <label class="gr-label required">هل تمتلك خبرة بالبرمجة؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item">
                                        <input type="radio" name="programming_experience" id="prog_exp_none" value="none" {{ old('programming_experience') == 'none' ? 'checked' : '' }} required>
                                        <label for="prog_exp_none">لا أملك خبرة</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="programming_experience" id="prog_exp_beginner" value="beginner" {{ old('programming_experience') == 'beginner' ? 'checked' : '' }} required>
                                        <label for="prog_exp_beginner">مبتدئ</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="programming_experience" id="prog_exp_intermediate" value="intermediate" {{ old('programming_experience') == 'intermediate' ? 'checked' : '' }} required>
                                        <label for="prog_exp_intermediate">متوسط</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="programming_experience" id="prog_exp_expert" value="expert" {{ old('programming_experience') == 'expert' ? 'checked' : '' }} required>
                                        <label for="prog_exp_expert">خبير</label>
                                    </div>
                                </div>
                                @error('programming_experience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="gr-section gr-section--success">
                            <h2 class="gr-section__title gr-section__title--success">
                                <i class="fas fa-graduation-cap"></i>
                                المعلومات التعليمية
                            </h2>
                            <div class="gr-field mb-0">
                                <label class="gr-label required">آخر مرحلة دراسية حاصل عليها</label>
                                <input type="text" name="education_level" class="form-control gr-input @error('education_level') is-invalid @enderror"
                                       value="{{ old('education_level') }}" required>
                                @error('education_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="gr-section gr-section--warning">
                            <h2 class="gr-section__title gr-section__title--warning">
                                <i class="fas fa-campground"></i>
                                المعسكر التدريبي
                            </h2>
                            <div class="gr-field mb-0">
                                <label class="gr-label required">هل أنت مهتم بدخول المعسكر التدريبي بعد الدبلوم (مأجور — ليس مجاني)؟</label>
                                <div class="gr-radio-list">
                                    <div class="gr-radio-item">
                                        <input type="radio" name="interested_in_bootcamp" id="bootcamp_yes" value="yes" {{ old('interested_in_bootcamp') == 'yes' ? 'checked' : '' }} required>
                                        <label for="bootcamp_yes">نعم، مهتم بحضور المعسكر التدريبي</label>
                                    </div>
                                    <div class="gr-radio-item">
                                        <input type="radio" name="interested_in_bootcamp" id="bootcamp_no" value="no" {{ old('interested_in_bootcamp') == 'no' ? 'checked' : '' }} required>
                                        <label for="bootcamp_no">لا، غير مهتم</label>
                                    </div>
                                </div>
                                <div class="gr-note gr-note--muted">
                                    <i class="fas fa-info-circle mt-1"></i>
                                    <span>مزايا المعسكر التدريبي مذكورة بالصفحة الخاصة بالدبلوم</span>
                                </div>
                                @error('interested_in_bootcamp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="gr-section gr-section--receipt">
                            <h2 class="gr-section__title gr-section__title--receipt">
                                <i class="fas fa-id-card"></i>
                                إثبات الشخصية
                            </h2>
                            <div class="gr-field mb-0">
                                <label for="membership_receipt" class="gr-label required">إثبات الشخصية (هوية، جواز، بطاقة جامعة، شهادة سواقة)</label>
                                <label for="membership_receipt" class="gr-file-upload @error('membership_receipt') gr-file-upload--invalid @enderror">
                                    <span class="gr-file-upload__icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </span>
                                    <span class="gr-file-upload__content">
                                        <strong>اضغط هنا لاختيار صورة أو ملف PDF</strong>
                                        <small id="membershipReceiptName">JPG، PNG، WEBP أو PDF — بحد أقصى 10 ميجابايت</small>
                                    </span>
                                    <span class="gr-file-upload__button">اختيار الملف</span>
                                </label>
                                <input
                                    type="file"
                                    name="membership_receipt"
                                    id="membership_receipt"
                                    class="gr-file-input"
                                    accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                                    required
                                >
                                <div class="gr-note gr-note--danger mt-2">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <span>ملاحظة: رفع وثيقة إثبات الشخصية إلزامي، وكل طلب بدون وثيقة سيتم رفضه.</span>
                                </div>
                                @error('membership_receipt')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        @if(!empty($registrationTerms))
                            <div class="gr-section gr-section--terms">
                                <h2 class="gr-section__title gr-section__title--terms">
                                    <i class="fas fa-file-contract"></i>
                                    الشروط والأحكام
                                </h2>
                                <div class="gr-terms-content">
                                    {!! $registrationTerms !!}
                                </div>
                                <div class="gr-field mb-0 mt-3">
                                    <div class="gr-checkbox-agree gr-checkbox-agree--danger">
                                        <input
                                            type="checkbox"
                                            name="registration_terms_ack"
                                            id="registration_terms_ack"
                                            value="1"
                                            {{ old('registration_terms_ack') ? 'checked' : '' }}
                                            required
                                        >
                                        <label for="registration_terms_ack">
                                            قرأت الشروط والأحكام وأوافق عليها.
                                        </label>
                                    </div>
                                    @error('registration_terms_ack')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endif

                        <div class="gr-field mb-3">
                            <div class="gr-checkbox-agree gr-checkbox-agree--danger">
                                <input
                                    type="checkbox"
                                    name="whatsapp_group_ack"
                                    id="whatsapp_group_ack"
                                    value="1"
                                    {{ old('whatsapp_group_ack') ? 'checked' : '' }}
                                    required
                                >
                                <label for="whatsapp_group_ack">
                                    أوافق على الانضمام الإلزامي لمجموعة واتساب عبر الرابط الذي سيظهر في نهاية التسجيل، لاستلام التحديثات والمتابعة.
                                </label>
                            </div>
                            @error('whatsapp_group_ack')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="gr-submit-wrap">
                            <button type="submit" class="btn btn-primary gr-submit-btn w-100">
                                <i class="fas fa-paper-plane me-2"></i>
                                إرسال طلب التسجيل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var flagUrlTemplate = document.getElementById('registrationForm')?.getAttribute('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';

    var $countrySelect = $('#country_code_select');
    if ($countrySelect.length) {
        $countrySelect.select2({
            placeholder: 'اختر رمز الدولة',
            allowClear: false,
            dir: 'rtl',
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('body'),
            templateResult: function(state) {
                if (!state.id) return state.text;
                var iso = $(state.element).data('iso') || 'sa';
                var url = flagUrlTemplate.replace('{iso}', iso.toLowerCase());
                var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;">'));
                $span.append(document.createTextNode(state.text));
                return $span;
            },
            templateSelection: function(state) {
                if (!state.id) return state.text;
                var iso = $(state.element).data('iso') || 'sa';
                var url = flagUrlTemplate.replace('{iso}', iso.toLowerCase());
                var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                $span.append($('<img src="' + url + '" alt="" style="width:20px;height:15px;object-fit:cover;">'));
                $span.append(document.createTextNode(state.text));
                return $span;
            }
        });
    }

    var phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            var val = phoneInput.value.trim();
            if (val && val.charAt(0) === '0') {
                phoneInput.value = val.substring(1);
            }
        });
        phoneInput.addEventListener('input', function() {
            if (phoneInput.value.length === 1 && phoneInput.value === '0') {
                phoneInput.value = '';
            }
        });
    }

    var receiptInput = document.getElementById('membership_receipt');
    var receiptName = document.getElementById('membershipReceiptName');
    if (receiptInput && receiptName) {
        receiptInput.addEventListener('change', function() {
            var file = receiptInput.files && receiptInput.files[0];
            receiptName.textContent = file
                ? 'تم اختيار: ' + file.name
                : 'JPG، PNG، WEBP أو PDF — بحد أقصى 10 ميجابايت';
            receiptInput.previousElementSibling?.classList.toggle('gr-file-upload--selected', Boolean(file));
        });
    }
});
</script>
@include('components.phone-country-ajax-script')
@endpush
