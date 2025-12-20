@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال اختيار من متعدد (إجابة واحدة)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-check-circle text-primary me-2"></i>
                    سؤال اختيار من متعدد (إجابة واحدة)
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">اختيار من متعدد</li>
                    </ol>
                </nav>
            </div>
        </div>

        <form action="{{ route('question-bank.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <select name="difficulty" class="form-select @error('difficulty') is-invalid @enderror" required>
                                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                    </select>
                                    @error('difficulty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="4" placeholder="اكتب نص السؤال هنا..." required>{{ old('question_text') }}</textarea>
                                    @error('question_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الدرجة <span class="text-danger">*</span></label>
                                    <input type="number" name="default_grade" class="form-control @error('default_grade') is-invalid @enderror"
                                           value="{{ old('default_grade', 1) }}" min="0.5" step="0.5" required>
                                    @error('default_grade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الوسوم</label>
                                    <input type="text" name="tags" class="form-control"
                                           placeholder="مثال: رياضيات, جبر" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Answer Options -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-list-ul me-2 text-success"></i>خيارات الإجابة
                                <small class="text-muted">(اختر إجابة واحدة صحيحة)</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="add-option-btn">
                                <i class="fas fa-plus me-1"></i>إضافة خيار
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                حدد الإجابة الصحيحة بالنقر على الدائرة بجانب الخيار
                            </div>
                            <div id="options-container">
                                <!-- Option 1 -->
                                <div class="option-item mb-3 p-3 border rounded">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-secondary fs-6">أ</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="options[1][option_text]" class="form-control" placeholder="أدخل نص الخيار..." required>
                                            <input type="hidden" name="options[1][option_order]" value="1">
                                        </div>
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" value="1" id="correct_1">
                                                <label class="form-check-label text-success" for="correct_1">
                                                    <i class="fas fa-check me-1"></i>صحيح
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Option 2 -->
                                <div class="option-item mb-3 p-3 border rounded">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-secondary fs-6">ب</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="options[2][option_text]" class="form-control" placeholder="أدخل نص الخيار..." required>
                                            <input type="hidden" name="options[2][option_order]" value="2">
                                        </div>
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" value="2" id="correct_2">
                                                <label class="form-check-label text-success" for="correct_2">
                                                    <i class="fas fa-check me-1"></i>صحيح
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Option 3 -->
                                <div class="option-item mb-3 p-3 border rounded">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-secondary fs-6">ج</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="options[3][option_text]" class="form-control" placeholder="أدخل نص الخيار..." required>
                                            <input type="hidden" name="options[3][option_order]" value="3">
                                        </div>
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" value="3" id="correct_3">
                                                <label class="form-check-label text-success" for="correct_3">
                                                    <i class="fas fa-check me-1"></i>صحيح
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Option 4 -->
                                <div class="option-item mb-3 p-3 border rounded">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-secondary fs-6">د</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="options[4][option_text]" class="form-control" placeholder="أدخل نص الخيار..." required>
                                            <input type="hidden" name="options[4][option_order]" value="4">
                                        </div>
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="correct_option" value="4" id="correct_4">
                                                <label class="form-check-label text-success" for="correct_4">
                                                    <i class="fas fa-check me-1"></i>صحيح
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
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
                                       id="shuffle_options" {{ old('shuffle_options') ? 'checked' : '' }}>
                                <label class="form-check-label" for="shuffle_options">
                                    خلط ترتيب الخيارات
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

                    <!-- Media -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-image me-2 text-info"></i>وسائط (اختياري)
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">نوع الوسائط</label>
                                <select name="media_type" class="form-select">
                                    <option value="">لا يوجد</option>
                                    <option value="image">صورة</option>
                                    <option value="audio">صوت</option>
                                    <option value="video">فيديو</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">رابط الوسائط</label>
                                <input type="url" name="media_url" class="form-control" placeholder="https://...">
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
let optionCount = 0;

$(document).ready(function() {
    // Add initial 4 options
    for (let i = 0; i < 4; i++) {
        addOption();
    }

    // Add option button
    $('#add-option-btn').click(function() {
        addOption();
    });

    // Remove option
    $(document).on('click', '.remove-option-btn', function() {
        if ($('.option-item').length > 2) {
            $(this).closest('.option-item').remove();
            updateOptionNumbers();
        } else {
            alert('يجب أن يكون هناك خياران على الأقل');
        }
    });

    // Single correct answer (radio behavior)
    $(document).on('change', '.correct-radio', function() {
        $('.correct-radio').not(this).prop('checked', false);
    });
});

function addOption() {
    optionCount++;
    const letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح'];
    const letter = letters[optionCount - 1] || optionCount;

    const optionHtml = `
        <div class="option-item mb-3 p-3 border rounded position-relative">
            <div class="d-flex align-items-start gap-3">
                <div class="pt-2">
                    <input class="form-check-input correct-radio" type="radio"
                           name="correct_option" value="${optionCount}"
                           id="correct_${optionCount}" style="transform: scale(1.3);">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">${letter}</span>
                        <input type="text" name="options[${optionCount}][option_text]"
                               class="form-control" placeholder="أدخل نص الخيار..." required>
                        <input type="hidden" name="options[${optionCount}][option_order]" value="${optionCount}">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-8">
                            <input type="text" name="options[${optionCount}][feedback]"
                                   class="form-control form-control-sm"
                                   placeholder="ملاحظات عند اختيار هذا الخيار (اختياري)">
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#options-container').append(optionHtml);
}

function updateOptionNumbers() {
    const letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح'];
    $('.option-item').each(function(index) {
        $(this).find('.badge').text(letters[index] || (index + 1));
    });
}
</script>
@stop
