@extends('frontend.layouts.master')

@section('page-title')
    التسجيل في {{ $group->name }}
@endsection

@section('content')
<div class="main-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            التسجيل في {{ $group->name }}
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('frontend.group-registration.store', $group->id) }}" method="POST" id="registrationForm">
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
                                    <input type="text" name="country_code" class="form-control @error('country_code') is-invalid @enderror" 
                                           value="{{ old('country_code', '+966') }}" placeholder="+966" required>
                                    @error('country_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label required">رقم الهاتف</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- الجنسية -->
                            <div class="mb-3">
                                <label class="form-label">الجنسية</label>
                                <select name="nationality_id" class="form-select @error('nationality_id') is-invalid @enderror">
                                    <option value="">اختر الجنسية</option>
                                    @foreach($nationalities as $nationality)
                                        <option value="{{ $nationality->id }}" {{ old('nationality_id') == $nationality->id ? 'selected' : '' }}>
                                            {{ $nationality->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>أخرى</option>
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
                            <div class="card border-primary mb-4">
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
                                                في حال انتم غير مستعدين للالتزام يرجى إتاحة الفرصة لغيركم
                                            </label>
                                        </div>
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
                            <div class="card border-info mb-4">
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
                                                لا أملك حاسوب (ليس بإمكانك المتابعة)
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
                            <div class="card border-success mb-4">
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
                            <div class="card border-warning mb-4">
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
    </div>
</div>

<style>
    .required::after {
        content: " *";
        color: red;
    }
    
    /* RTL support for radio buttons - put radio circle on the right */
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
</style>
@endsection
