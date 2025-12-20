@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال مقالي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-paragraph text-purple me-2"></i>
                    سؤال مقالي
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">سؤال مقالي</li>
                    </ol>
                </nav>
            </div>
        </div>

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
                                    <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="5" placeholder="اكتب السؤال المقالي هنا..." required>{{ old('question_text') }}</textarea>
                                    @error('question_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الدرجة القصوى <span class="text-danger">*</span></label>
                                    <input type="number" name="default_grade" class="form-control"
                                           value="{{ old('default_grade', 10) }}" min="1" step="0.5" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الوسوم</label>
                                    <input type="text" name="tags" class="form-control"
                                           placeholder="مثال: كتابة, تحليل" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-edit me-2 text-info"></i>إعدادات الإجابة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الحد الأدنى للكلمات</label>
                                    <input type="number" name="min_words" class="form-control"
                                           value="{{ old('min_words', 50) }}" min="0" placeholder="0 = بدون حد">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الحد الأقصى للكلمات</label>
                                    <input type="number" name="max_words" class="form-control"
                                           value="{{ old('max_words', 500) }}" min="0" placeholder="0 = بدون حد">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_attachments"
                                               id="allow_attachments" {{ old('allow_attachments') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_attachments">
                                            السماح بإرفاق ملفات مع الإجابة
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Guide -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-clipboard-check me-2 text-success"></i>دليل التصحيح
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                هذا السؤال يتطلب تصحيح يدوي من المدرس
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الإجابة النموذجية (للمدرس)</label>
                                <textarea name="model_answer" class="form-control" rows="4"
                                          placeholder="اكتب الإجابة النموذجية التي يستعين بها المدرس أثناء التصحيح...">{{ old('model_answer') }}</textarea>
                            </div>
                            <div>
                                <label class="form-label">معايير التقييم</label>
                                <textarea name="grading_criteria" class="form-control" rows="3"
                                          placeholder="مثال: المحتوى 40%، التنظيم 30%، اللغة 30%">{{ old('grading_criteria') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-lightbulb me-2 text-warning"></i>ملاحظات للطالب (اختياري)
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea name="explanation" class="form-control" rows="3"
                                      placeholder="نصائح أو إرشادات تظهر للطالب قبل الإجابة...">{{ old('explanation') }}</textarea>
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
