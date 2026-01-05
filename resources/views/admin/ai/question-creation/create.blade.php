@extends('admin.layouts.master')

@section('page-title')
    إنشاء أسئلة بالذكاء الاصطناعي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء أسئلة بالذكاء الاصطناعي</h5>
            </div>
            <div>
                <a href="{{ route('question-bank.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-creation.store') }}" method="POST" id="questionCreationForm">
                            @csrf

                            <div class="mb-3">
                                <label for="source_type" class="form-label">نوع المصدر <span class="text-danger">*</span></label>
                                <select class="form-select" id="source_type" name="source_type" required>
                                    <option value="manual_text" {{ old('source_type') == 'manual_text' ? 'selected' : '' }}>نص يدوي</option>
                                    <option value="topic" {{ old('source_type') == 'topic' ? 'selected' : '' }}>موضوع</option>
                                    <option value="lesson_content" {{ old('source_type') == 'lesson_content' ? 'selected' : '' }}>محتوى الدرس</option>
                                </select>
                            </div>

                            <div id="lesson_source" class="mb-3" style="display: none;">
                                <label for="course_id" class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="lesson_select" class="mb-3" style="display: none;">
                                <label for="lesson_id" class="form-label">الدرس <span class="text-danger">*</span></label>
                                <select class="form-select" id="lesson_id" name="lesson_id" disabled>
                                    <option value="">اختر الكورس أولاً</option>
                                </select>
                            </div>

                            <div id="text_source" class="mb-3">
                                <label for="source_content" class="form-label">المحتوى المصدر <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="source_content" name="source_content" rows="10" placeholder="أدخل النص أو الموضوع الذي تريد إنشاء أسئلة منه...">{{ old('source_content') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="programming_language_id" class="form-label">اللغة <span class="text-danger">*</span></label>
                                <select class="form-select" id="programming_language_id" name="programming_language_id" required>
                                    <option value="">اختر اللغة</option>
                                    @foreach($programmingLanguages as $language)
                                        <option value="{{ $language->id }}" {{ old('programming_language_id') == $language->id ? 'selected' : '' }}>
                                            {{ $language->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">اللغة التي ستنتمي إليها الأسئلة</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">أنواع الأسئلة المطلوبة <span class="text-danger">*</span></label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($questionTypes as $questionType)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input question-type-checkbox" 
                                                   type="checkbox" 
                                                   name="question_types[]" 
                                                   value="{{ $questionType->id }}" 
                                                   id="question_type_{{ $questionType->id }}"
                                                   {{ in_array($questionType->id, old('question_types', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="question_type_{{ $questionType->id }}">
                                                {{ $questionType->display_name }}
                                                @if($questionType->requires_manual_grading)
                                                    <span class="badge bg-warning badge-sm ms-1">تصحيح يدوي</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">يمكنك اختيار أكثر من نوع. سيتم توزيع الأسئلة على الأنواع المختارة.</small>
                                <div class="text-danger mt-1" id="question_types_error" style="display: none;">
                                    يجب اختيار نوع واحد على الأقل
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="number_of_questions" class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="number_of_questions" name="number_of_questions" value="{{ old('number_of_questions', 5) }}" min="1" max="50" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="difficulty_level" class="form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                                        @foreach($difficulties as $key => $label)
                                            <option value="{{ $key }}" {{ old('difficulty_level', 'mixed') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="ai_model_id" class="form-label">موديل AI (اختياري)</label>
                                <select class="form-select" id="ai_model_id" name="ai_model_id">
                                    <option value="">استخدام الموديل الافتراضي</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-magic me-1"></i> إنشاء الأسئلة
                                </button>
                                <a href="{{ route('question-bank.index') }}" class="btn btn-secondary">
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sourceType = document.getElementById('source_type');
    const lessonSource = document.getElementById('lesson_source');
    const lessonSelect = document.getElementById('lesson_select');
    const textSource = document.getElementById('text_source');
    const courseSelect = document.getElementById('course_id');
    const lessonIdSelect = document.getElementById('lesson_id');
    const sourceContent = document.getElementById('source_content');
    const form = document.getElementById('questionCreationForm');
    const questionTypeCheckboxes = document.querySelectorAll('.question-type-checkbox');
    const questionTypesError = document.getElementById('question_types_error');

    function toggleSourceFields() {
        if (sourceType.value === 'lesson_content') {
            lessonSource.style.display = 'block';
            lessonSelect.style.display = 'block';
            textSource.style.display = 'none';
            sourceContent.removeAttribute('required');
        } else {
            lessonSource.style.display = 'none';
            lessonSelect.style.display = 'none';
            textSource.style.display = 'block';
            sourceContent.setAttribute('required', 'required');
        }
    }

    sourceType.addEventListener('change', toggleSourceFields);
    toggleSourceFields();

    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        if (courseId) {
            lessonIdSelect.disabled = false;
            // Load lessons for the selected course
            const url = '{{ route("admin.courses.lessons", ":id") }}'.replace(':id', courseId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    lessonIdSelect.innerHTML = '<option value="">اختر الدرس</option>';
                    if (data && data.length > 0) {
                        data.forEach(lesson => {
                            lessonIdSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading lessons:', error);
                    lessonIdSelect.innerHTML = '<option value="">خطأ في تحميل الدروس</option>';
                });
        } else {
            lessonIdSelect.disabled = true;
            lessonIdSelect.innerHTML = '<option value="">اختر الكورس أولاً</option>';
        }
    });

    // التحقق من اختيار نوع واحد على الأقل
    function validateQuestionTypes() {
        const checked = Array.from(questionTypeCheckboxes).some(cb => cb.checked);
        if (!checked) {
            questionTypesError.style.display = 'block';
            return false;
        } else {
            questionTypesError.style.display = 'none';
            return true;
        }
    }

    questionTypeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateQuestionTypes);
    });

    form.addEventListener('submit', function(e) {
        if (!validateQuestionTypes()) {
            e.preventDefault();
            questionTypesError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
});
</script>
@endpush
@stop

