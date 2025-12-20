@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال صح وخطأ
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-check-double text-warning me-2"></i>
                    سؤال صح وخطأ
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">صح وخطأ</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('question-bank.store') }}" method="POST">
            @csrf
            <input type="hidden" name="question_type_id" value="{{ $questionType->id }}">

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>معلومات السؤال
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                    @if($selectedCourseId)
                                        <!-- Hidden input to send the course_id -->
                                        <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                        <select class="form-select" disabled>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                    <select name="difficulty" class="form-select" required>
                                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">العبارة <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="4" placeholder="اكتب العبارة التي سيحدد الطالب إذا كانت صحيحة أم خاطئة..." required>{{ old('question_text') }}</textarea>
                                    @error('question_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الدرجة <span class="text-danger">*</span></label>
                                    <input type="number" name="default_grade" class="form-control"
                                           value="{{ old('default_grade', 1) }}" min="0.5" step="0.5" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الوسوم</label>
                                    <input type="text" name="tags" class="form-control"
                                           placeholder="مثال: رياضيات, جبر" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Correct Answer -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-check-circle me-2 text-success"></i>الإجابة الصحيحة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card h-100 border-2 {{ old('correct_answer', 'true') == 'true' ? 'border-success bg-success-transparent' : '' }}"
                                         id="true-card" style="cursor: pointer;">
                                        <div class="card-body text-center py-4">
                                            <input type="radio" name="correct_answer" value="true" class="d-none"
                                                   id="answer-true" {{ old('correct_answer', 'true') == 'true' ? 'checked' : '' }}>
                                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                            <h4 class="mt-3 mb-0">صح</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-2 {{ old('correct_answer') == 'false' ? 'border-danger bg-danger-transparent' : '' }}"
                                         id="false-card" style="cursor: pointer;">
                                        <div class="card-body text-center py-4">
                                            <input type="radio" name="correct_answer" value="false" class="d-none"
                                                   id="answer-false" {{ old('correct_answer') == 'false' ? 'checked' : '' }}>
                                            <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                                            <h4 class="mt-3 mb-0">خطأ</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden inputs for options -->
                            <input type="hidden" name="options[1][option_text]" value="صح">
                            <input type="hidden" name="options[1][option_order]" value="1">
                            <input type="hidden" name="options[2][option_text]" value="خطأ">
                            <input type="hidden" name="options[2][option_order]" value="2">
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-lightbulb me-2 text-warning"></i>شرح الإجابة (اختياري)
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea name="explanation" class="form-control" rows="3"
                                      placeholder="اكتب شرحاً يظهر للطالب بعد الإجابة...">{{ old('explanation') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    السؤال نشط
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_reusable"
                                       id="is_reusable" {{ old('is_reusable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_reusable">
                                    قابل لإعادة الاستخدام
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-save me-2"></i>حفظ السؤال
                            </button>
                            <a href="{{ route('question-bank.create') }}" class="btn btn-light w-100">
                                <i class="fas fa-arrow-right me-2"></i>تغيير نوع السؤال
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@stop

@section('script')
<script>
$(document).ready(function() {
    // Card selection
    $('#true-card').click(function() {
        $('#answer-true').prop('checked', true);
        $(this).addClass('border-success bg-success-transparent');
        $('#false-card').removeClass('border-danger bg-danger-transparent');
    });

    $('#false-card').click(function() {
        $('#answer-false').prop('checked', true);
        $(this).addClass('border-danger bg-danger-transparent');
        $('#true-card').removeClass('border-success bg-success-transparent');
    });
});
</script>
@stop
