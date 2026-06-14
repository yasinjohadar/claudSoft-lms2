@extends('admin.layouts.master')

@section('page-title')
    تعديل السؤال
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
                        <li class="breadcrumb-item active">تعديل السؤال</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-edit-2 me-1"></i>تعديل السؤال</span>
                        <h2 class="group-show-hero__title mb-2">{{ Str::limit(strip_tags($question->question_text), 80) }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @if($question->questionType)
                                {{ $question->questionType->display_name }}
                                @if($question->course) · {{ $question->course->title }} @endif
                            @else
                                تحديث نص السؤال وخيارات الإجابة والوسائط.
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('question-bank.show', $question->id) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">عرض السؤال</span>
                            </a>
                            <a href="{{ route('question-bank.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">العودة للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('question-bank.update', $question->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                                        <option value="{{ $course->id }}" {{ old('course_id', $question->course_id) == $course->id ? 'selected' : '' }}>
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
                                        <option value="{{ $type->id }}"
                                                data-type-name="{{ $type->name }}"
                                                {{ old('question_type_id', $question->question_type_id) == $type->id ? 'selected' : '' }}>
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
                                <select name="difficulty_level" class="form-select @error('difficulty_level') is-invalid @enderror" required>
                                    <option value="">اختر مستوى الصعوبة</option>
                                    <option value="easy" {{ old('difficulty_level', $question->difficulty_level) == 'easy' ? 'selected' : '' }}>سهل</option>
                                    <option value="medium" {{ old('difficulty_level', $question->difficulty_level) == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="hard" {{ old('difficulty_level', $question->difficulty_level) == 'hard' ? 'selected' : '' }}>صعب</option>
                                    <option value="expert" {{ old('difficulty_level', $question->difficulty_level) == 'expert' ? 'selected' : '' }}>خبير</option>
                                </select>
                                @error('difficulty_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                                <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                          rows="4" placeholder="اكتب نص السؤال..." required>{{ old('question_text', $question->question_text) }}</textarea>
                                @error('question_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الدرجة <span class="text-danger">*</span></label>
                                <input type="number" name="default_grade" class="form-control @error('default_grade') is-invalid @enderror"
                                       value="{{ old('default_grade', $question->default_grade) }}" min="0.5" step="0.5" required>
                                @error('default_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1" {{ old('is_active', $question->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        السؤال نشط
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">شرح الإجابة (اختياري)</label>
                                <textarea name="explanation" class="form-control @error('explanation') is-invalid @enderror"
                                          rows="3" placeholder="اكتب شرحاً للإجابة الصحيحة...">{{ old('explanation', $question->explanation) }}</textarea>
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
                        <div id="options-container">
                            @foreach($question->options as $index => $option)
                                <div class="option-item qb-option-item mb-3 p-3 border rounded" data-option-id="{{ $option->id }}">
                                    <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">نص الخيار {{ $index + 1 }}</label>
                                            <input type="text" name="options[{{ $index }}][option_text]"
                                                   class="form-control" placeholder="أدخل نص الخيار..."
                                                   value="{{ old('options.'.$index.'.option_text', $option->option_text) }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">الترتيب</label>
                                            <input type="number" name="options[{{ $index }}][option_order]"
                                                   class="form-control" value="{{ old('options.'.$index.'.option_order', $option->option_order) }}" min="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">الوزن</label>
                                            <input type="number" name="options[{{ $index }}][score_weight]"
                                                   class="form-control" value="{{ old('options.'.$index.'.score_weight', $option->score_weight) }}" min="0" max="1" step="0.1">
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">ملاحظات (اختياري)</label>
                                            <input type="text" name="options[{{ $index }}][feedback]"
                                                   class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار..."
                                                   value="{{ old('options.'.$index.'.feedback', $option->feedback) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox"
                                                       name="options[{{ $index }}][is_correct]"
                                                       id="correct_{{ $index }}" value="1"
                                                       {{ old('options.'.$index.'.is_correct', $option->is_correct) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="correct_{{ $index }}">إجابة صحيحة</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger-light remove-option-btn mt-2">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
                                <label class="form-label">صورة السؤال</label>
                                <input type="file" name="question_image" class="form-control" accept="image/*">
                                @if($question->question_image)
                                    <div class="mt-2">
                                        <label class="form-label">الصورة الحالية:</label>
                                        <div>
                                            <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال" class="img-fluid rounded" style="max-width: 400px;">
                                        </div>
                                    </div>
                                @endif
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
                               placeholder="أدخل الوسوم مفصولة بفاصلة (مثال: رياضيات, جبر, معادلات)"
                               value="{{ old('tags', is_array($question->tags) ? implode(', ', $question->tags) : '') }}">
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
                                <i class="fe fe-save me-1"></i>حفظ التعديلات
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
let optionCount = {{ $question->options->count() }};

$(document).ready(function() {
    // Check if options section should be shown on load
    const selectedOption = $('#question_type_id option:selected');
    const selectedType = selectedOption.text();
    const selectedTypeName = selectedOption.data('type-name') || '';
    const needsOptions = ['اختيار من متعدد', 'صح / خطأ', 'صح وخطأ', 'مطابقة', 'ترتيب'];
    let showOptions = false;

    needsOptions.forEach(type => {
        if (selectedType.includes(type)) {
            showOptions = true;
        }
    });

    if (!showOptions) {
        $('#options-section').hide();
    } else {
        // إذا كان السؤال من نوع true_false وليس لديه خيارات، أضف خيارين تلقائياً
        const isTrueFalse = selectedTypeName === 'true_false' || selectedType.includes('صح / خطأ') || selectedType.includes('صح وخطأ');
        const hasNoOptions = $('#options-container .option-item').length === 0;
        
        console.log('DEBUG: Checking true_false options', {
            selectedType: selectedType,
            selectedTypeName: selectedTypeName,
            isTrueFalse: isTrueFalse,
            hasNoOptions: hasNoOptions,
            optionsCount: $('#options-container .option-item').length
        });
        
        if (isTrueFalse && hasNoOptions) {
            // إضافة خيار "صح"
            optionCount++;
            const trueOptionHtml = `
                <div class="option-item qb-option-item mb-3 p-3 border rounded">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">نص الخيار 1</label>
                            <input type="text" name="options[${optionCount}][option_text]"
                                   class="form-control" placeholder="أدخل نص الخيار..." value="صح" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="options[${optionCount}][option_order]"
                                   class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الوزن</label>
                            <input type="number" name="options[${optionCount}][score_weight]"
                                   class="form-control" value="1" min="0" max="1" step="0.1">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">ملاحظات (اختياري)</label>
                            <input type="text" name="options[${optionCount}][feedback]"
                                   class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار...">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox"
                                       name="options[${optionCount}][is_correct]"
                                       id="correct_${optionCount}" value="1">
                                <label class="form-check-label" for="correct_${optionCount}">
                                    <i class="fe fe-check me-1"></i>إجابة صحيحة
                                </label>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger-light remove-option-btn mt-2">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#options-container').append(trueOptionHtml);
            
            // إضافة خيار "خطأ"
            optionCount++;
            const falseOptionHtml = `
                <div class="option-item qb-option-item mb-3 p-3 border rounded">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">نص الخيار 2</label>
                            <input type="text" name="options[${optionCount}][option_text]"
                                   class="form-control" placeholder="أدخل نص الخيار..." value="خطأ" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="options[${optionCount}][option_order]"
                                   class="form-control" value="2" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الوزن</label>
                            <input type="number" name="options[${optionCount}][score_weight]"
                                   class="form-control" value="0" min="0" max="1" step="0.1">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">ملاحظات (اختياري)</label>
                            <input type="text" name="options[${optionCount}][feedback]"
                                   class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار...">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox"
                                       name="options[${optionCount}][is_correct]"
                                       id="correct_${optionCount}" value="1">
                                <label class="form-check-label" for="correct_${optionCount}">
                                    <i class="fe fe-check me-1"></i>إجابة صحيحة
                                </label>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger-light remove-option-btn mt-2">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#options-container').append(falseOptionHtml);
        }
    }

    // Show/hide options based on question type
    $('#question_type_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const selectedType = selectedOption.text();
        const selectedTypeName = selectedOption.data('type-name') || '';
        const needsOptions = ['اختيار من متعدد', 'صح / خطأ', 'صح وخطأ', 'مطابقة', 'ترتيب'];

        let showOptions = false;
        needsOptions.forEach(type => {
            if (selectedType.includes(type)) {
                showOptions = true;
            }
        });

        if (showOptions) {
            $('#options-section').show();
            if ($('#options-container .option-item').length === 0) {
                if (selectedTypeName === 'true_false' || selectedType.includes('صح / خطأ') || selectedType.includes('صح وخطأ')) {
                    // إضافة خيارين تلقائياً لـ true_false
                    optionCount++;
                    const trueOptionHtml = `
                        <div class="option-item qb-option-item mb-3 p-3 border rounded">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">نص الخيار 1</label>
                                    <input type="text" name="options[${optionCount}][option_text]"
                                           class="form-control" placeholder="أدخل نص الخيار..." value="صح" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="options[${optionCount}][option_order]"
                                           class="form-control" value="1" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">الوزن</label>
                                    <input type="number" name="options[${optionCount}][score_weight]"
                                           class="form-control" value="1" min="0" max="1" step="0.1">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <input type="text" name="options[${optionCount}][feedback]"
                                           class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار...">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox"
                                               name="options[${optionCount}][is_correct]"
                                               id="correct_${optionCount}" value="1">
                                        <label class="form-check-label" for="correct_${optionCount}">
                                            <i class="fe fe-check me-1"></i>إجابة صحيحة
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-option-btn mt-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#options-container').append(trueOptionHtml);
                    
                    // إضافة خيار "خطأ"
                    optionCount++;
                    const falseOptionHtml = `
                        <div class="option-item qb-option-item mb-3 p-3 border rounded">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">نص الخيار 2</label>
                                    <input type="text" name="options[${optionCount}][option_text]"
                                           class="form-control" placeholder="أدخل نص الخيار..." value="خطأ" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="options[${optionCount}][option_order]"
                                           class="form-control" value="2" min="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">الوزن</label>
                                    <input type="number" name="options[${optionCount}][score_weight]"
                                           class="form-control" value="0" min="0" max="1" step="0.1">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">ملاحظات (اختياري)</label>
                                    <input type="text" name="options[${optionCount}][feedback]"
                                           class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار...">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox"
                                               name="options[${optionCount}][is_correct]"
                                               id="correct_${optionCount}" value="1">
                                        <label class="form-check-label" for="correct_${optionCount}">
                                            <i class="fe fe-check me-1"></i>إجابة صحيحة
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-option-btn mt-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#options-container').append(falseOptionHtml);
                } else {
                    addOption(); // Add first option
                    addOption(); // Add second option
                }
            }
        } else {
            $('#options-section').hide();
        }
    });

    // Add option button
    $('#add-option-btn').click(function() {
        addOption();
    });

    // Remove option
    $(document).on('click', '.remove-option-btn', function() {
        if (confirm('هل أنت متأكد من حذف هذا الخيار؟')) {
            $(this).closest('.option-item').remove();
        }
    });
});

function addOption() {
    optionCount++;
    const optionHtml = `
        <div class="option-item qb-option-item mb-3 p-3 border rounded">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">نص الخيار ${optionCount}</label>
                    <input type="text" name="options[${optionCount}][option_text]"
                           class="form-control" placeholder="أدخل نص الخيار..." required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="options[${optionCount}][option_order]"
                           class="form-control" value="${optionCount}" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الوزن</label>
                    <input type="number" name="options[${optionCount}][score_weight]"
                           class="form-control" value="1" min="0" max="1" step="0.1">
                </div>
                <div class="col-md-9">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <input type="text" name="options[${optionCount}][feedback]"
                           class="form-control" placeholder="ملاحظات عند اختيار هذا الخيار...">
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox"
                               name="options[${optionCount}][is_correct]"
                               id="correct_${optionCount}" value="1">
                        <label class="form-check-label" for="correct_${optionCount}">
                            <i class="fe fe-check me-1"></i>إجابة صحيحة
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-option-btn mt-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    $('#options-container').append(optionHtml);
}
</script>
@stop
