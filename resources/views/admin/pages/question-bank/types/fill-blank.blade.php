@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال ملء الفراغات
@stop

@section('styles')
<style>
    .fb-blank-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(var(--primary-rgb), 0.12);
        color: rgb(var(--primary-rgb));
    }
    .fb-option-row,
    .fb-blank-answer-row {
        background: rgba(var(--primary-rgb), 0.03);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
    }
    .fb-live-preview {
        line-height: 2.1;
        font-size: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 12px;
        border: 1px dashed rgba(var(--primary-rgb), 0.25);
        background: rgba(var(--primary-rgb), 0.04);
        min-height: 3.5rem;
    }
    .fb-live-preview select {
        display: inline-block;
        width: auto;
        min-width: 8rem;
        margin: 0 0.15rem;
        vertical-align: middle;
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                    <li class="breadcrumb-item active">ملء الفراغات</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-list me-1"></i>
                        قوائم منسدلة مشتركة
                    </span>
                    <h2 class="group-show-hero__title mb-2">سؤال ملء الفراغات</h2>
                    <p class="group-show-hero__desc mb-0">
                        ضع أكثر من فراغ في النص، عرّف قائمة خيارات واحدة، ثم حدّد الإجابة الصحيحة لكل فراغ. يظهر للطالب قائمة منسدلة في كل فراغ.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('question-bank.create') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">تغيير النوع</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger dashboard-fade-in">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('question-bank.store') }}" method="POST" id="fillBlanksForm">
            @csrf
            <input type="hidden" name="question_type_id" value="{{ $questionType->id }}">
            <input type="hidden" name="input_mode" value="dropdown">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">معلومات السؤال</h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                    @if($selectedCourseId)
                                        <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                        <select class="form-select" disabled>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" @selected($selectedCourseId == $course->id)>{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                    <select name="difficulty_level" class="form-select" required>
                                        <option value="easy" @selected(old('difficulty_level') == 'easy')>سهل</option>
                                        <option value="medium" @selected(old('difficulty_level', 'medium') == 'medium')>متوسط</option>
                                        <option value="hard" @selected(old('difficulty_level') == 'hard')>صعب</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الدرجة <span class="text-danger">*</span></label>
                                    <input type="number" name="default_grade" class="form-control"
                                           value="{{ old('default_grade', 1) }}" min="0.5" step="0.5" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الوسوم</label>
                                    <input type="text" name="tags" class="form-control"
                                           placeholder="مثال: HTML, روابط" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">نص السؤال مع الفراغات</h4>
                                <p class="fs-12 text-muted mb-0">استخدم <code>[[blank]]</code> لكل فراغ.</p>
                            </div>
                            <span class="fb-blank-chip" id="blankCountChip">0 فراغ</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="alert alert-info mb-3">
                                <i class="fe fe-info me-1"></i>
                                مثال: في HTML5 استخدم <code>[[blank]]</code> داخل وسم <code>[[blank]]</code> لتحديد الرابط.
                            </div>
                            <label class="form-label">نص السؤال <span class="text-danger">*</span></label>
                            <textarea name="question_text" id="questionText"
                                      class="form-control @error('question_text') is-invalid @enderror"
                                      rows="4"
                                      placeholder="اكتب النص وضع [[blank]] في أماكن الفراغات..."
                                      required>{{ old('question_text') }}</textarea>
                            @error('question_text')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            <div class="mt-3">
                                <label class="form-label fs-12 text-muted">معاينة حية</label>
                                <div class="fb-live-preview" id="livePreview">سيظهر النص والقوائم هنا بعد الكتابة...</div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">خيارات القائمة المنسدلة</h4>
                                <p class="fs-12 text-muted mb-0">نفس القائمة تظهر في كل فراغ للطالب.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="addOptionBtn">
                                <i class="fe fe-plus me-1"></i>إضافة خيار
                            </button>
                        </div>
                        <div class="card-body pt-3">
                            <div id="optionsContainer">
                                @php $oldOptions = old('dropdown_options', ['', '', '']); @endphp
                                @foreach($oldOptions as $opt)
                                    <div class="fb-option-row d-flex gap-2 align-items-center">
                                        <input type="text" name="dropdown_options[]" class="form-control dropdown-option-input"
                                               placeholder="نص الخيار..." value="{{ $opt }}" required>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-option-btn">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('dropdown_options')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">الإجابة الصحيحة لكل فراغ</h4>
                            <p class="fs-12 text-muted mb-0">اختر من قائمة الخيارات أعلاه.</p>
                        </div>
                        <div class="card-body pt-3">
                            <div id="blankAnswersContainer">
                                <p class="text-muted mb-0" id="blankAnswersPlaceholder">أضف فراغات في نص السؤال أولاً.</p>
                            </div>
                            @error('blank_answers')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">شرح الإجابة (اختياري)</h4>
                        </div>
                        <div class="card-body pt-3">
                            <textarea name="explanation" class="form-control" rows="3"
                                      placeholder="يظهر للطالب بعد الإجابة...">{{ old('explanation') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1">الإعدادات</h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">السؤال نشط</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_reusable" id="is_reusable" value="1"
                                       {{ old('is_reusable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_reusable">قابل لإعادة الاستخدام</label>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fe fe-save me-1"></i>حفظ السؤال
                            </button>
                            <a href="{{ route('question-bank.create') }}" class="btn btn-light w-100">
                                <i class="fe fe-arrow-right me-1"></i>تغيير نوع السؤال
                            </a>
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
(function () {
    var oldBlankAnswers = @json(old('blank_answers', []));

    function countBlanks(text) {
        var normalized = String(text || '').replace(/_{3,}/g, '[[blank]]');
        var matches = normalized.match(/\[\[blank\]\]/gi);
        return matches ? matches.length : 0;
    }

    function getOptions() {
        return Array.prototype.slice.call(document.querySelectorAll('.dropdown-option-input'))
            .map(function (el) { return (el.value || '').trim(); })
            .filter(function (v) { return v !== ''; });
    }

    function optionRowHtml(value) {
        return '<div class="fb-option-row d-flex gap-2 align-items-center">' +
            '<input type="text" name="dropdown_options[]" class="form-control dropdown-option-input" placeholder="نص الخيار..." value="' + (value || '') + '" required>' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-option-btn"><i class="fe fe-trash-2"></i></button>' +
            '</div>';
    }

    function refreshBlankAnswers() {
        var count = countBlanks(document.getElementById('questionText').value);
        var chip = document.getElementById('blankCountChip');
        chip.textContent = count + (count === 1 ? ' فراغ' : ' فراغات');

        var container = document.getElementById('blankAnswersContainer');
        var options = getOptions();

        if (count < 1) {
            container.innerHTML = '<p class="text-muted mb-0" id="blankAnswersPlaceholder">أضف فراغات في نص السؤال أولاً.</p>';
            refreshPreview();
            return;
        }

        var html = '';
        for (var i = 0; i < count; i++) {
            var selected = oldBlankAnswers[i] || oldBlankAnswers[String(i)] || '';
            html += '<div class="fb-blank-answer-row">' +
                '<label class="form-label mb-2">الفراغ ' + (i + 1) + ' <span class="text-danger">*</span></label>' +
                '<select name="blank_answers[' + i + ']" class="form-select blank-answer-select" required data-blank-index="' + i + '">' +
                '<option value="">-- اختر الإجابة الصحيحة --</option>';
            options.forEach(function (opt) {
                html += '<option value="' + opt.replace(/"/g, '&quot;') + '"' + (selected === opt ? ' selected' : '') + '>' + opt + '</option>';
            });
            html += '</select></div>';
        }
        container.innerHTML = html;
        oldBlankAnswers = {};
        refreshPreview();
    }

    function refreshPreview() {
        var text = document.getElementById('questionText').value || '';
        var preview = document.getElementById('livePreview');
        var options = getOptions();
        var normalized = text.replace(/_{3,}/g, '[[blank]]');

        if (!normalized.trim()) {
            preview.textContent = 'سيظهر النص والقوائم هنا بعد الكتابة...';
            return;
        }

        var parts = normalized.split(/\[\[blank\]\]/i);
        var html = '';
        parts.forEach(function (part, index) {
            html += part.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            if (index < parts.length - 1) {
                html += '<select disabled><option>-- اختر --</option>';
                options.forEach(function (opt) {
                    html += '<option>' + opt.replace(/</g, '&lt;') + '</option>';
                });
                html += '</select>';
            }
        });
        preview.innerHTML = html;
    }

    document.getElementById('addOptionBtn').addEventListener('click', function () {
        document.getElementById('optionsContainer').insertAdjacentHTML('beforeend', optionRowHtml(''));
        refreshBlankAnswers();
    });

    document.getElementById('optionsContainer').addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-option-btn');
        if (!btn) return;
        var rows = document.querySelectorAll('.fb-option-row');
        if (rows.length <= 2) {
            alert('يجب توفر خيارين على الأقل في القائمة المنسدلة');
            return;
        }
        btn.closest('.fb-option-row').remove();
        refreshBlankAnswers();
    });

    document.getElementById('optionsContainer').addEventListener('input', function (e) {
        if (e.target.classList.contains('dropdown-option-input')) {
            refreshBlankAnswers();
        }
    });

    document.getElementById('questionText').addEventListener('input', refreshBlankAnswers);

    document.getElementById('fillBlanksForm').addEventListener('submit', function (e) {
        var count = countBlanks(document.getElementById('questionText').value);
        var options = getOptions();
        if (count < 1) {
            e.preventDefault();
            alert('يجب إضافة فراغ واحد على الأقل باستخدام [[blank]]');
            return;
        }
        if (options.length < 2) {
            e.preventDefault();
            alert('أضف خيارين على الأقل للقائمة المنسدلة');
            return;
        }
        var selects = document.querySelectorAll('.blank-answer-select');
        for (var i = 0; i < selects.length; i++) {
            if (!selects[i].value) {
                e.preventDefault();
                alert('حدّد الإجابة الصحيحة لكل فراغ');
                return;
            }
            if (options.indexOf(selects[i].value) === -1) {
                e.preventDefault();
                alert('الإجابة الصحيحة يجب أن تكون من خيارات القائمة');
                return;
            }
        }
    });

    refreshBlankAnswers();
})();
</script>
@stop
