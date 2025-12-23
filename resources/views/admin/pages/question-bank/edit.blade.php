@extends('admin.layouts.master')

@section('page-title')
    تعديل السؤال
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل السؤال</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active">تعديل السؤال</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('question-bank.update', $question->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle me-2 text-primary"></i>المعلومات الأساسية
                        </div>
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

                <!-- Options Section -->
                <div class="card custom-card mb-4" id="options-section">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-list-ul me-2 text-success"></i>خيارات الإجابة
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="options-container">
                            @foreach($question->options as $index => $option)
                                <div class="option-item mb-3 p-3 border rounded" data-option-id="{{ $option->id }}">
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
                                                <label class="form-check-label" for="correct_{{ $index }}">
                                                    <i class="fas fa-check-circle text-success me-1"></i>إجابة صحيحة
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger remove-option-btn mt-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="add-option-btn">
                            <i class="fas fa-plus me-1"></i>إضافة خيار
                        </button>
                    </div>
                </div>

                <!-- Media Section -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-image me-2 text-info"></i>الوسائط (اختياري)
                        </div>
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

                <!-- Tags Section -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-tags me-2 text-warning"></i>الوسوم (اختياري)
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="text" name="tags" class="form-control"
                               placeholder="أدخل الوسوم مفصولة بفاصلة (مثال: رياضيات, جبر, معادلات)"
                               value="{{ old('tags', is_array($question->tags) ? implode(', ', $question->tags) : '') }}">
                        <small class="text-muted">الوسوم تساعد في البحث والتصنيف</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('question-bank.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('scripts')
<script>
let optionCount = {{ $question->options->count() }};

$(document).ready(function() {
    // Check if options section should be shown on load
    const selectedType = $('#question_type_id option:selected').text();
    const needsOptions = ['اختيار من متعدد', 'صح وخطأ', 'مطابقة', 'ترتيب'];
    let showOptions = false;

    needsOptions.forEach(type => {
        if (selectedType.includes(type)) {
            showOptions = true;
        }
    });

    if (!showOptions) {
        $('#options-section').hide();
    }

    // Show/hide options based on question type
    $('#question_type_id').change(function() {
        const selectedType = $(this).find('option:selected').text();
        const needsOptions = ['اختيار من متعدد', 'صح وخطأ', 'مطابقة', 'ترتيب'];

        let showOptions = false;
        needsOptions.forEach(type => {
            if (selectedType.includes(type)) {
                showOptions = true;
            }
        });

        if (showOptions) {
            $('#options-section').show();
            if ($('#options-container .option-item').length === 0) {
                addOption(); // Add first option
                addOption(); // Add second option
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
        <div class="option-item mb-3 p-3 border rounded">
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
                            <i class="fas fa-check-circle text-success me-1"></i>إجابة صحيحة
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
@endsection
