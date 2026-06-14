@extends('admin.layouts.master')

@section('page-title')
    إضافة سؤال جديد
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="admin-form-layout">

            <div class="my-4 page-header-breadcrumb qb-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item active">إضافة سؤال</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-plus me-1"></i>سؤال جديد</span>
                        <h2 class="group-show-hero__title mb-2">إضافة سؤال جديد</h2>
                        <p class="group-show-hero__desc mb-0">حدد الكورس ونوع السؤال، أضف الخيارات والوسائط، ثم احفظه في بنك الأسئلة.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('question-bank.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">العودة للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('question-bank.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                            المعلومات الأساسية
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">نوع السؤال <span class="text-danger">*</span></label>
                                <select name="question_type_id" id="question_type_id" class="form-select @error('question_type_id') is-invalid @enderror" required>
                                    <option value="">اختر نوع السؤال</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}" {{ old('question_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('question_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                <select name="difficulty" class="form-select @error('difficulty') is-invalid @enderror" required>
                                    <option value="">اختر مستوى الصعوبة</option>
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
                                          rows="4" placeholder="اكتب نص السؤال..." required>{{ old('question_text') }}</textarea>
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
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        السؤال نشط
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_reusable"
                                           id="is_reusable" {{ old('is_reusable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_reusable">
                                        قابل لإعادة الاستخدام
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">شرح الإجابة (اختياري)</label>
                                <textarea name="explanation" class="form-control @error('explanation') is-invalid @enderror"
                                          rows="3" placeholder="اكتب شرحاً للإجابة الصحيحة...">{{ old('explanation') }}</textarea>
                                @error('explanation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4" id="options-section">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-list"></i></span>
                            خيارات الإجابة
                        </h4>
                    </div>
                    <div class="card-body">
                        <div id="options-container"></div>
                        <button type="button" class="btn btn-sm btn-primary-light" id="add-option-btn">
                            <i class="fe fe-plus me-1"></i>إضافة خيار
                        </button>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-image"></i></span>
                            الوسائط (اختياري)
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">نوع الوسائط</label>
                                <select name="media_type" id="media_type" class="form-select">
                                    <option value="text" selected>لا يوجد</option>
                                    <option value="image">صورة</option>
                                    <option value="audio">صوت</option>
                                    <option value="video">فيديو</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رابط الوسائط</label>
                                <input type="url" name="media_url" class="form-control" placeholder="https://example.com/image.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-tag"></i></span>
                            الوسوم (اختياري)
                        </h4>
                    </div>
                    <div class="card-body">
                        <input type="text" name="tags" class="form-control"
                               placeholder="أدخل الوسوم مفصولة بفاصلة (مثال: رياضيات, جبر, معادلات)">
                        <small class="text-muted">الوسوم تساعد في البحث والتصنيف</small>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card assignments-form-actions dashboard-fade-in qb-page-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('question-bank.index') }}" class="btn btn-light">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ السؤال
                            </button>
                        </div>
                    </div>
                </div>

            </form>

            </div>

        </div>
    </div>
@stop

@section('script')
<script>
let optionCount = 0;

$(document).ready(function() {
    // Add 4 default options on page load
    addOption();
    addOption();
    addOption();
    addOption();

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

    // For single choice questions, only one correct answer
    $(document).on('change', '.correct-radio', function() {
        $('.correct-radio').not(this).prop('checked', false);
    });
});

function addOption() {
    optionCount++;
    const letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح'];
    const letter = letters[optionCount - 1] || optionCount;

    const optionHtml = `
        <div class="option-item qb-option-item mb-3 p-3 border rounded">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="badge bg-secondary fs-6">${letter}</span>
                </div>
                <div class="col">
                    <input type="text" name="options[${optionCount}][option_text]"
                           class="form-control" placeholder="أدخل نص الخيار..." required>
                    <input type="hidden" name="options[${optionCount}][option_order]" value="${optionCount}">
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input correct-radio" type="radio"
                               name="correct_option" value="${optionCount}"
                               id="correct_${optionCount}">
                        <label class="form-check-label text-success" for="correct_${optionCount}">
                            <i class="fe fe-check me-1"></i>صحيح
                        </label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-danger-light remove-option-btn">
                        <i class="fe fe-trash-2"></i>
                    </button>
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
