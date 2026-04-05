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
                @if(!empty($questionsEngineChoiceAvailable))
                    <p class="mb-0 mt-1 text-muted small"><span class="badge bg-secondary">محركان</span> يمكنك اختيار <strong>Laravel AI SDK</strong> أو <strong>موديلات بنك الموديلات القديمة</strong> لكل عملية إنشاء.</p>
                @elseif(!empty($useLaravelAiEngine))
                    <p class="mb-0 text-muted small"><span class="badge bg-info text-dark">Laravel AI SDK</span> — الموديل من «موديلات Laravel AI SDK» (قدرة questions.generate عند الافتراضي).</p>
                @endif
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

        @if(isset($quiz))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong>الأسئلة ستُربط بالاختبار:</strong> {{ $quiz->title }}
                        <br>
                        <small class="text-muted">سيتم إنشاء الأسئلة في بنك الأسئلة وربطها تلقائياً بهذا الاختبار.</small>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('admin.ai.question-creation.store') }}" method="POST" id="questionCreationForm">
                            @csrf

                            @if(isset($quiz))
                                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                            @endif

                            <div id="course_block" class="mb-3">
                                <label for="course_id" class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">يُحفظ مع كل سؤال في بنك الأسئلة لتسهيل الفرز</small>
                            </div>

                            <div class="mb-3">
                                <label for="source_type" class="form-label">نوع المصدر <span class="text-danger">*</span></label>
                                <select class="form-select" id="source_type" name="source_type" required>
                                    <option value="manual_text" {{ old('source_type') == 'manual_text' ? 'selected' : '' }}>نص يدوي</option>
                                    <option value="topic" {{ old('source_type') == 'topic' ? 'selected' : '' }}>موضوع</option>
                                    <option value="lesson_content" {{ old('source_type') == 'lesson_content' ? 'selected' : '' }}>محتوى الدرس</option>
                                </select>
                            </div>

                            <div id="lesson_name_block" class="mb-3">
                                <label for="lesson_name" class="form-label">اسم الدرس / الوحدة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lesson_name" name="lesson_name" value="{{ old('lesson_name') }}" maxlength="255" placeholder="كما يظهر في بنك الأسئلة">
                                <small class="text-muted">مطلوب عند النص اليدوي أو الموضوع؛ يُطابق حقل بنك الأسئلة</small>
                            </div>

                            <div id="lesson_select_block" class="mb-3" style="display: none;">
                                <label for="lesson_id" class="form-label">الدرس <span class="text-danger">*</span></label>
                                <select class="form-select" id="lesson_id" name="lesson_id"
                                    {{ old('source_type') == 'lesson_content' && old('course_id') ? '' : 'disabled' }}>
                                    <option value="">اختر الكورس أولاً</option>
                                    @if(isset($lessons) && $lessons->isNotEmpty())
                                        @foreach($lessons as $lesson)
                                            <option value="{{ $lesson->id }}" {{ (string) old('lesson_id') === (string) $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">يُحفظ اسم الدرس تلقائياً من عنوان الدرس المختار</small>
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

                            @php
                                $selectedQuestionsEngine = old('questions_engine', $useLaravelAiEngine ? 'laravel_ai' : 'legacy');
                                $questionsEngineIsLaravel = ($selectedQuestionsEngine === 'laravel_ai');
                            @endphp
                            <div class="mb-3">
                                @if(!empty($questionsEngineChoiceAvailable))
                                    <label class="form-label">محرك إنشاء الأسئلة</label>
                                    <div class="mb-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="questions_engine" id="questions_engine_laravel_ai" value="laravel_ai" {{ $questionsEngineIsLaravel ? 'checked' : '' }}>
                                            <label class="form-check-label" for="questions_engine_laravel_ai">Laravel AI SDK</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="questions_engine" id="questions_engine_legacy" value="legacy" {{ ! $questionsEngineIsLaravel ? 'checked' : '' }}>
                                            <label class="form-check-label" for="questions_engine_legacy">موديلات قديمة (بنك الموديلات)</label>
                                        </div>
                                    </div>
                                @endif
                                @if($models->isEmpty() && $laravelAiModels->isEmpty())
                                    <div class="alert alert-warning mb-0 small">لا يوجد موديل نشط في كلا النظامين. أضف موديلاً من «إدارة موديلات AI» أو «موديلات Laravel AI SDK».</div>
                                @else
                                    @if(!empty($questionsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                        <div id="questions_engine_laravel_wrap" class="questions-engine-model-wrap" style="{{ !empty($questionsEngineChoiceAvailable) && ! $questionsEngineIsLaravel ? 'display:none' : '' }}">
                                            <label for="laravel_ai_model_id" class="form-label">موديل Laravel AI SDK (اختياري)</label>
                                            <select class="form-select" id="laravel_ai_model_id" name="laravel_ai_model_id" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                <option value="">افتراضي (أولوية + قدرة questions.generate)</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}" {{ (string) old('laravel_ai_model_id') === (string) $lmodel->id ? 'selected' : '' }}>{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">إدارة الموديلات: موديلات Laravel AI SDK</small>
                                        </div>
                                    @endif
                                    @if(!empty($questionsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div id="questions_engine_legacy_wrap" class="questions-engine-model-wrap" style="{{ !empty($questionsEngineChoiceAvailable) && $questionsEngineIsLaravel ? 'display:none' : '' }}">
                                            <label for="ai_model_id" class="form-label">موديل AI (بنك الموديلات، اختياري)</label>
                                            <select class="form-select" id="ai_model_id" name="ai_model_id" @if($models->isEmpty()) disabled @endif>
                                                <option value="">استخدام الموديل الافتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
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
    const questionsEngineChoiceAvailable = @json(!empty($questionsEngineChoiceAvailable));

    function syncQuestionsEngineModelVisibility() {
        if (!questionsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('questions_engine_laravel_ai')?.checked;
        const wL = document.getElementById('questions_engine_laravel_wrap');
        const wG = document.getElementById('questions_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    syncQuestionsEngineModelVisibility();
    document.querySelectorAll('input[name="questions_engine"]').forEach(function (el) {
        el.addEventListener('change', syncQuestionsEngineModelVisibility);
    });

    const sourceType = document.getElementById('source_type');
    const lessonNameBlock = document.getElementById('lesson_name_block');
    const lessonSelectBlock = document.getElementById('lesson_select_block');
    const textSource = document.getElementById('text_source');
    const courseSelect = document.getElementById('course_id');
    const lessonIdSelect = document.getElementById('lesson_id');
    const lessonNameInput = document.getElementById('lesson_name');
    const sourceContent = document.getElementById('source_content');
    const form = document.getElementById('questionCreationForm');
    const questionTypeCheckboxes = document.querySelectorAll('.question-type-checkbox');
    const questionTypesError = document.getElementById('question_types_error');

    function loadLessonsForCourse(courseId, preserveSelectedId) {
        if (!courseId) {
            lessonIdSelect.disabled = true;
            lessonIdSelect.innerHTML = '<option value="">اختر الكورس أولاً</option>';
            return;
        }
        lessonIdSelect.disabled = false;
        const url = '{{ route("admin.courses.lessons", ":id") }}'.replace(':id', courseId);
        fetch(url)
            .then(response => response.json())
            .then(data => {
                lessonIdSelect.innerHTML = '<option value="">اختر الدرس</option>';
                if (data && data.length > 0) {
                    data.forEach(lesson => {
                        const opt = document.createElement('option');
                        opt.value = lesson.id;
                        opt.textContent = lesson.title;
                        if (preserveSelectedId && String(lesson.id) === String(preserveSelectedId)) {
                            opt.selected = true;
                        }
                        lessonIdSelect.appendChild(opt);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading lessons:', error);
                lessonIdSelect.innerHTML = '<option value="">خطأ في تحميل الدروس</option>';
            });
    }

    const initialOldLessonId = @json(old('lesson_id'));

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
                const needsFetch = lessonIdSelect.options.length <= 1;
                if (needsFetch) {
                    loadLessonsForCourse(courseSelect.value, initialOldLessonId);
                }
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
        if (sourceType.value === 'lesson_content') {
            loadLessonsForCourse(this.value, null);
        }
    });

    function validateQuestionTypes() {
        const checked = Array.from(questionTypeCheckboxes).some(cb => cb.checked);
        if (!checked) {
            questionTypesError.style.display = 'block';
            return false;
        }
        questionTypesError.style.display = 'none';
        return true;
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
        if (questionsEngineChoiceAvailable) {
            const laravelRadio = document.getElementById('questions_engine_laravel_ai');
            const laravelChecked = laravelRadio && laravelRadio.checked;
            const selL = document.getElementById('laravel_ai_model_id');
            const selG = document.getElementById('ai_model_id');
            if (selL) selL.disabled = !laravelChecked;
            if (selG) selG.disabled = laravelChecked;
        }
    });
});
</script>
@endpush
@stop
