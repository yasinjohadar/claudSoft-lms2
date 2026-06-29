@extends('admin.layouts.master')

@section('page-title')
    توليد أسئلة بالذكاء الاصطناعي
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.page-styles')
    <style>
        .qb-ai-generate-form {
            max-width: 1180px;
            margin-inline: auto;
        }

        .qb-ai-types-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.35rem 1rem;
            max-height: 240px;
            overflow-y: auto;
            padding: 0.75rem 1rem;
            border: 1px solid var(--default-border, #e9edf4);
            border-radius: 0.5rem;
        }

        @media (min-width: 576px) {
            .qb-ai-types-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 992px) {
            .qb-ai-types-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .qb-ai-source-panel {
            min-height: 0;
        }
    </style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb qb-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item active">توليد بالذكاء الاصطناعي</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow"><i class="fe fe-cpu me-1"></i>الذكاء الاصطناعي</span>
                    <h2 class="group-show-hero__title mb-2">توليد أسئلة لبنك الأسئلة</h2>
                    <p class="group-show-hero__desc mb-0">
                        حدد الكورس والدرس واللغة وأنواع الأسئلة، ثم راجع النتائج قبل الحفظ في البنك.
                    </p>
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

        <form action="{{ route('question-bank.ai-generate.store') }}" method="POST" id="qbAiGenerateForm" class="qb-ai-generate-form">
            @csrf

            <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصنيف السؤال والمصدر</h4>
                    <p class="fs-12 text-muted mb-0">الكورس، اللغة، الصعوبة، والمحتوى الذي يُبنى عليه التوليد.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6 col-lg-3">
                            <label for="course_id" class="form-label">الكورس <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">اختر الكورس</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" @selected((string) old('course_id', $prefillCourseId ?? '') === (string) $course->id)>{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label for="programming_language_id" class="form-label">لغة البرمجة <span class="text-danger">*</span></label>
                            <select class="form-select" id="programming_language_id" name="programming_language_id" required>
                                <option value="">اختر اللغة</option>
                                @foreach($programmingLanguages as $language)
                                    <option value="{{ $language->id }}" @selected((string) old('programming_language_id', $prefillLanguageId ?? '') === (string) $language->id)>
                                        {{ $language->display_name ?? $language->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label for="difficulty_level" class="form-label">الصعوبة <span class="text-danger">*</span></label>
                            <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                                @foreach($difficulties as $key => $label)
                                    <option value="{{ $key }}" @selected(old('difficulty_level', $prefillDifficulty ?? 'mixed') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label for="default_grade" class="form-label">الدرجة الافتراضية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="default_grade" name="default_grade"
                                   value="{{ old('default_grade', 1) }}" min="0.5" max="100" step="0.5" required>
                        </div>
                    </div>

                    <div class="row g-3 align-items-start qb-ai-source-panel">
                        <div class="col-md-4 col-xl-3">
                            <label for="source_type" class="form-label">نوع المصدر <span class="text-danger">*</span></label>
                            <select class="form-select" id="source_type" name="source_type" required>
                                <option value="manual_text" @selected(old('source_type') == 'manual_text')>نص يدوي</option>
                                <option value="topic" @selected(old('source_type') == 'topic')>موضوع</option>
                                <option value="lesson_content" @selected(old('source_type') == 'lesson_content')>محتوى الدرس</option>
                            </select>
                        </div>

                        <div class="col-md-8 col-xl-9" id="lesson_field_col">
                            <div id="lesson_name_block">
                                <label for="lesson_name" class="form-label">اسم الدرس / الوحدة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lesson_name" name="lesson_name"
                                       value="{{ old('lesson_name') }}" maxlength="255" placeholder="كما يظهر في بنك الأسئلة">
                            </div>
                            <div id="lesson_select_block" style="display: none;">
                                <label for="lesson_id" class="form-label">الدرس <span class="text-danger">*</span></label>
                                <select class="form-select" id="lesson_id" name="lesson_id"
                                    @disabled(old('source_type') != 'lesson_content' || !old('course_id', $prefillCourseId ?? null))>
                                    <option value="">اختر الكورس أولاً</option>
                                    @if(isset($lessons) && $lessons->isNotEmpty())
                                        @foreach($lessons as $lesson)
                                            <option value="{{ $lesson->id }}" @selected((string) old('lesson_id') === (string) $lesson->id)>{{ $lesson->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-12" id="text_source">
                            <label for="source_content" class="form-label">المحتوى المصدر <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="source_content" name="source_content" rows="5"
                                      placeholder="أدخل النص أو الموضوع الذي تريد إنشاء أسئلة منه...">{{ old('source_content') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">أنواع الأسئلة</h4>
                            <p class="fs-12 text-muted mb-0">اختر نوعاً واحداً أو أكثر.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div class="qb-ai-types-grid">
                                @foreach($questionTypes as $questionType)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input question-type-checkbox" type="checkbox"
                                               name="question_types[]" value="{{ $questionType->id }}"
                                               id="question_type_{{ $questionType->id }}"
                                               @checked(in_array($questionType->id, old('question_types', [])))>
                                        <label class="form-check-label" for="question_type_{{ $questionType->id }}">
                                            {{ $questionType->display_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-danger mt-2 small" id="question_types_error" style="display: none;">يجب اختيار نوع واحد على الأقل</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">إعدادات التوليد</h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="number_of_questions" class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="number_of_questions" name="number_of_questions"
                                           value="{{ old('number_of_questions', 5) }}" min="1" max="50" required>
                                </div>
                            </div>

                            @php
                                $selectedQuestionsEngine = old('questions_engine', $useLaravelAiEngine ? 'laravel_ai' : 'legacy');
                                $questionsEngineIsLaravel = ($selectedQuestionsEngine === 'laravel_ai');
                            @endphp
                            <div class="mt-3">
                                @if(!empty($questionsEngineChoiceAvailable))
                                    <label class="form-label">محرك التوليد</label>
                                    <div class="mb-2 d-flex flex-wrap gap-3">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="questions_engine" id="questions_engine_laravel_ai" value="laravel_ai" @checked($questionsEngineIsLaravel)>
                                            <label class="form-check-label" for="questions_engine_laravel_ai">Laravel AI SDK</label>
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="radio" name="questions_engine" id="questions_engine_legacy" value="legacy" @checked(! $questionsEngineIsLaravel)>
                                            <label class="form-check-label" for="questions_engine_legacy">موديلات قديمة</label>
                                        </div>
                                    </div>
                                @endif
                                @if($models->isEmpty() && $laravelAiModels->isEmpty())
                                    <div class="alert alert-warning mb-0 small">لا يوجد موديل AI نشط.</div>
                                @else
                                    @if(!empty($questionsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                        <div id="questions_engine_laravel_wrap" class="questions-engine-model-wrap mb-3" style="{{ !empty($questionsEngineChoiceAvailable) && ! $questionsEngineIsLaravel ? 'display:none' : '' }}">
                                            <label for="laravel_ai_model_id" class="form-label">موديل Laravel AI SDK (اختياري)</label>
                                            <select class="form-select" id="laravel_ai_model_id" name="laravel_ai_model_id" @disabled($laravelAiModels->isEmpty())>
                                                <option value="">افتراضي</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}" @selected((string) old('laravel_ai_model_id') === (string) $lmodel->id)>{{ $lmodel->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @if(!empty($questionsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div id="questions_engine_legacy_wrap" class="questions-engine-model-wrap" style="{{ !empty($questionsEngineChoiceAvailable) && $questionsEngineIsLaravel ? 'display:none' : '' }}">
                                            <label for="ai_model_id" class="form-label">موديل AI (اختياري)</label>
                                            <select class="form-select" id="ai_model_id" name="ai_model_id" @disabled($models->isEmpty())>
                                                <option value="">افتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}" @selected(old('ai_model_id') == $model->id)>{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4 pt-2 border-top">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fe fe-zap me-1"></i> توليد للمعاينة
                                </button>
                                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionsEngineChoiceAvailable = @json(!empty($questionsEngineChoiceAvailable));
    const sourceType = document.getElementById('source_type');
    const lessonNameBlock = document.getElementById('lesson_name_block');
    const lessonSelectBlock = document.getElementById('lesson_select_block');
    const textSource = document.getElementById('text_source');
    const courseSelect = document.getElementById('course_id');
    const lessonIdSelect = document.getElementById('lesson_id');
    const lessonNameInput = document.getElementById('lesson_name');
    const sourceContent = document.getElementById('source_content');
    const form = document.getElementById('qbAiGenerateForm');
    const questionTypeCheckboxes = document.querySelectorAll('.question-type-checkbox');
    const questionTypesError = document.getElementById('question_types_error');
    const submitBtn = document.getElementById('submitBtn');
    const initialOldLessonId = @json(old('lesson_id'));

    function syncQuestionsEngineModelVisibility() {
        if (!questionsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('questions_engine_laravel_ai')?.checked;
        const wL = document.getElementById('questions_engine_laravel_wrap');
        const wG = document.getElementById('questions_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    syncQuestionsEngineModelVisibility();
    document.querySelectorAll('input[name="questions_engine"]').forEach(el => el.addEventListener('change', syncQuestionsEngineModelVisibility));

    function loadLessonsForCourse(courseId, preserveSelectedId) {
        if (!courseId) {
            lessonIdSelect.disabled = true;
            lessonIdSelect.innerHTML = '<option value="">اختر الكورس أولاً</option>';
            return;
        }
        lessonIdSelect.disabled = false;
        fetch('{{ route("admin.courses.lessons", ":id") }}'.replace(':id', courseId))
            .then(r => r.json())
            .then(data => {
                lessonIdSelect.innerHTML = '<option value="">اختر الدرس</option>';
                (data || []).forEach(lesson => {
                    const opt = document.createElement('option');
                    opt.value = lesson.id;
                    opt.textContent = lesson.title;
                    if (preserveSelectedId && String(lesson.id) === String(preserveSelectedId)) opt.selected = true;
                    lessonIdSelect.appendChild(opt);
                });
            });
    }

    function toggleSourceFields() {
        const isLesson = sourceType.value === 'lesson_content';
        if (isLesson) {
            lessonSelectBlock.style.display = 'block';
            lessonNameBlock.style.display = 'none';
            lessonNameInput.removeAttribute('required');
            lessonNameInput.value = '';
            textSource.style.display = 'none';
            sourceContent.removeAttribute('required');
            if (courseSelect.value) {
                lessonIdSelect.disabled = false;
                if (lessonIdSelect.options.length <= 1) loadLessonsForCourse(courseSelect.value, initialOldLessonId);
            }
        } else {
            lessonSelectBlock.style.display = 'none';
            lessonNameBlock.style.display = 'block';
            lessonNameInput.setAttribute('required', 'required');
            lessonIdSelect.disabled = true;
            lessonIdSelect.innerHTML = '<option value="">اختر الكورس أولاً</option>';
            textSource.style.display = 'block';
            sourceContent.setAttribute('required', 'required');
        }
    }

    sourceType.addEventListener('change', toggleSourceFields);
    toggleSourceFields();
    courseSelect.addEventListener('change', function() {
        if (sourceType.value === 'lesson_content') loadLessonsForCourse(this.value, null);
    });

    function validateQuestionTypes() {
        const checked = Array.from(questionTypeCheckboxes).some(cb => cb.checked);
        questionTypesError.style.display = checked ? 'none' : 'block';
        return checked;
    }

    questionTypeCheckboxes.forEach(cb => cb.addEventListener('change', validateQuestionTypes));

    form.addEventListener('submit', function(e) {
        if (!validateQuestionTypes()) {
            e.preventDefault();
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري التوليد...';
    });
});
</script>
@stop
