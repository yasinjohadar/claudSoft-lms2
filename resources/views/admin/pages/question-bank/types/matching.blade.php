@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال مطابقة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-arrows-alt-h text-cyan me-2"></i>
                    سؤال المطابقة
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">مطابقة</li>
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
                                    <label class="form-label">تعليمات السؤال <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="3" placeholder="مثال: طابق العناصر في العمود الأول مع ما يناسبها في العمود الثاني..." required>{{ old('question_text') }}</textarea>
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

                    <!-- Matching Pairs -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-arrows-alt-h me-2 text-cyan"></i>أزواج المطابقة
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="add-pair-btn">
                                <i class="fas fa-plus me-1"></i>إضافة زوج
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                أضف العناصر المطلوب مطابقتها. العمود الأيسر يحتوي على الأسئلة والعمود الأيمن يحتوي على الإجابات.
                            </div>

                            <div class="row mb-3">
                                <div class="col-5">
                                    <strong class="text-primary"><i class="fas fa-question me-1"></i>السؤال</strong>
                                </div>
                                <div class="col-2 text-center">
                                    <i class="fas fa-arrows-alt-h text-muted"></i>
                                </div>
                                <div class="col-5">
                                    <strong class="text-success"><i class="fas fa-check me-1"></i>الإجابة</strong>
                                </div>
                            </div>

                            <div id="pairs-container">
                                <!-- Pair 1 -->
                                <div class="pair-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-5">
                                            <input type="text" name="matching_pairs[1][question]" class="form-control" placeholder="السؤال..." required>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="fas fa-arrows-alt-h text-primary"></i>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="matching_pairs[1][answer]" class="form-control" placeholder="الإجابة..." required>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-pair-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pair 2 -->
                                <div class="pair-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-5">
                                            <input type="text" name="matching_pairs[2][question]" class="form-control" placeholder="السؤال..." required>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="fas fa-arrows-alt-h text-primary"></i>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="matching_pairs[2][answer]" class="form-control" placeholder="الإجابة..." required>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-pair-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pair 3 -->
                                <div class="pair-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-5">
                                            <input type="text" name="matching_pairs[3][question]" class="form-control" placeholder="السؤال..." required>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="fas fa-arrows-alt-h text-primary"></i>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="matching_pairs[3][answer]" class="form-control" placeholder="الإجابة..." required>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-pair-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pair 4 -->
                                <div class="pair-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-5">
                                            <input type="text" name="matching_pairs[4][question]" class="form-control" placeholder="السؤال..." required>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="fas fa-arrows-alt-h text-primary"></i>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="matching_pairs[4][answer]" class="form-control" placeholder="الإجابة..." required>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-pair-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                <input class="form-check-input" type="checkbox" name="shuffle_options"
                                       id="shuffle_options" {{ old('shuffle_options', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="shuffle_options">
                                    خلط ترتيب الإجابات
                                </label>
                            </div>
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
let pairCount = 4;

$(document).ready(function() {
    // Add pair button
    $('#add-pair-btn').click(function() {
        pairCount++;
        const pairHtml = `
            <div class="pair-item mb-3 p-3 border rounded bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-5">
                        <input type="text" name="matching_pairs[${pairCount}][question]" class="form-control" placeholder="السؤال..." required>
                    </div>
                    <div class="col-2 text-center">
                        <i class="fas fa-arrows-alt-h text-primary"></i>
                    </div>
                    <div class="col-4">
                        <input type="text" name="matching_pairs[${pairCount}][answer]" class="form-control" placeholder="الإجابة..." required>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-pair-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#pairs-container').append(pairHtml);
    });

    // Remove pair
    $(document).on('click', '.remove-pair-btn', function() {
        if ($('.pair-item').length > 2) {
            $(this).closest('.pair-item').remove();
        } else {
            alert('يجب أن يكون هناك زوجان على الأقل');
        }
    });
});
</script>
@stop
