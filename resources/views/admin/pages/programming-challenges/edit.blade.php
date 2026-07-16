@extends('admin.layouts.master')

@section('page-title')
    تعديل تحدي برمجي
@stop

@push('styles')
    @include('admin.pages.programming-challenges.partials.form-styles')
@endpush

@section('content')
    <div class="main-content app-content pch-form">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="pch-form__hero">
                <div>
                    <nav>
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programming-challenges.index') }}">التحديات البرمجية</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">تعديل: {{ $challenge->title }}</h5>
                    <p>حدّث المحتوى والإعدادات وتقييد الوصول لهذا التحدي.</p>
                </div>
                <div class="pch-form__side-actions">
                    <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="pch-form__side-link"><i class="fe fe-globe"></i> اللغات</a>
                    <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="pch-form__side-link"><i class="fe fe-file-text"></i> الكود</a>
                    <a href="{{ route('programming-challenges.manage-test-cases', $challenge->id) }}" class="pch-form__side-link"><i class="fe fe-check-square"></i> اختبارات</a>
                    <a href="{{ route('programming-challenges.index') }}" class="pch-form__side-link"><i class="fe fe-arrow-right"></i> القائمة</a>
                </div>
            </div>

            <form action="{{ route('programming-challenges.update', $challenge->id) }}" method="POST" id="programmingChallengeForm">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-xl-8">
                        <div class="pch-form__panel">
                            <div class="pch-form__panel-head">
                                <span class="pch-form__panel-icon"><i class="fe fe-edit-3"></i></span>
                                <div>
                                    <h6 class="pch-form__panel-title">معلومات التحدي</h6>
                                    <p class="pch-form__panel-sub">العنوان والوصف والتعليمات</p>
                                </div>
                            </div>
                            <div class="pch-form__panel-body">
                                <div class="mb-3">
                                    <label class="pch-form__label">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $challenge->title) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="pch-form__label">الوصف</label>
                                    <textarea name="description" id="programming_challenge_description" class="form-control" rows="3">{{ old('description', $challenge->description) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="pch-form__label">التعليمات</label>
                                    <textarea name="instructions" id="programming_challenge_instructions" class="form-control" rows="5">{{ old('instructions', $challenge->instructions) }}</textarea>
                                </div>
                                <div class="pch-form__grid-2">
                                    <div>
                                        <label class="pch-form__label">نوع التحدي</label>
                                        <select name="challenge_type" class="form-select">
                                            <option value="web_sandbox" @selected(old('challenge_type', $challenge->challenge_type) === 'web_sandbox')>ويب (HTML/CSS/JS)</option>
                                            <option value="code_runner" @selected(old('challenge_type', $challenge->challenge_type) === 'code_runner')>تنفيذ كود (سيرفر)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="pch-form__label">نمط التقييم</label>
                                        <select name="grading_mode" class="form-select">
                                            <option value="manual" @selected(old('grading_mode', $challenge->grading_mode) === 'manual')>يدوي</option>
                                            <option value="auto" @selected(old('grading_mode', $challenge->grading_mode) === 'auto')>آلي</option>
                                            <option value="hybrid" @selected(old('grading_mode', $challenge->grading_mode) === 'hybrid')>هجين</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="pch-form__panel">
                            <div class="pch-form__panel-head">
                                <span class="pch-form__panel-icon pch-form__panel-icon--slate"><i class="fe fe-sliders"></i></span>
                                <div>
                                    <h6 class="pch-form__panel-title">الإعدادات</h6>
                                    <p class="pch-form__panel-sub">الصعوبة والدرجة والمحاولات</p>
                                </div>
                            </div>
                            <div class="pch-form__panel-body">
                                <div class="mb-3">
                                    <label class="pch-form__label">الصعوبة</label>
                                    <select name="difficulty" class="form-select">
                                        @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $val => $label)
                                            <option value="{{ $val }}" @selected(old('difficulty', $challenge->difficulty) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="pch-form__grid-2 mb-3">
                                    <div>
                                        <label class="pch-form__label">الدرجة القصوى</label>
                                        <input type="number" name="max_score" class="form-control" value="{{ old('max_score', $challenge->max_score) }}" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="pch-form__label">المحاولات</label>
                                        <input type="number" name="attempts_allowed" class="form-control" value="{{ old('attempts_allowed', $challenge->attempts_allowed) }}" min="1">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="pch-form__label">حد الوقت (ثوانٍ)</label>
                                    <input type="number" name="time_limit_seconds" class="form-control" value="{{ old('time_limit_seconds', $challenge->time_limit_seconds) }}" min="0" placeholder="اختياري">
                                </div>
                            </div>
                        </div>

                        @include('admin.pages.programming-challenges.partials.audience-targets')

                        <div class="pch-form__panel">
                            <div class="pch-form__panel-head">
                                <span class="pch-form__panel-icon pch-form__panel-icon--green"><i class="fe fe-check-circle"></i></span>
                                <div>
                                    <h6 class="pch-form__panel-title">النشر والخيارات</h6>
                                    <p class="pch-form__panel-sub">تحكم في الظهور وإعادة التسليم</p>
                                </div>
                            </div>
                            <div class="pch-form__panel-body">
                                <div class="pch-form__switch">
                                    <input class="form-check-input" type="checkbox" name="allow_resubmit" id="allow_resubmit" value="1" @checked(old('allow_resubmit', $challenge->allow_resubmit))>
                                    <div>
                                        <label class="pch-form__switch-label" for="allow_resubmit">السماح بإعادة التسليم</label>
                                        <p class="pch-form__switch-desc">يمكن للطالب تسليم محاولة جديدة ضمن الحد المسموح.</p>
                                    </div>
                                </div>
                                <div class="pch-form__switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" @checked(old('is_published', $challenge->is_published))>
                                    <div>
                                        <label class="pch-form__switch-label" for="is_published">منشور</label>
                                        <p class="pch-form__switch-desc">متاح للطلاب المؤهلين عند التفعيل.</p>
                                    </div>
                                </div>
                                <div class="pch-form__switch">
                                    <input class="form-check-input" type="checkbox" name="is_standalone" id="is_standalone" value="1" @checked(old('is_standalone', $challenge->is_standalone))>
                                    <div>
                                        <label class="pch-form__switch-label" for="is_standalone">مكتبة مستقلة</label>
                                        <p class="pch-form__switch-desc">يظهر في قائمة التحديات البرمجية للطالب.</p>
                                    </div>
                                </div>
                                <button type="submit" class="pch-form__submit mt-3">
                                    <i class="fe fe-save"></i>
                                    حفظ التغييرات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('scripts')
    @include('admin.blog.partials.tinymce-config', [
        'formSelector' => '#programmingChallengeForm',
        'editors' => [
            ['selector' => '#programming_challenge_description', 'height' => 280],
            ['selector' => '#programming_challenge_instructions', 'height' => 420],
        ],
    ])
    <script>
        if (window.PchAudience) { window.PchAudience.bind(); }
    </script>
@endsection
