@extends('admin.layouts.master')

@section('page-title')
    استيراد أسئلة — {{ $quiz->title }}
@stop

@section('styles')
    @include('admin.pages.quizzes.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb quizzes-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">الاختبارات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 28) }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.manage-questions', $quiz->id) }}">إدارة الأسئلة</a></li>
                        <li class="breadcrumb-item active">استيراد من بنك الأسئلة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in quizzes-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-download me-1"></i>استيراد من بنك الأسئلة</span>
                        <h2 class="group-show-hero__title mb-2">{{ $quiz->title }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            {{ $quiz->questions->count() }} سؤال في الاختبار حالياً · اختر الأسئلة ثم استوردها دفعة واحدة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('quizzes.manage-questions', $quiz->id) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">العودة لإدارة الأسئلة</span>
                            </a>
                            <a href="{{ route('question-bank.index') }}" class="group-show-action group-show-action--primary" target="_blank" rel="noopener">
                                <span class="group-show-action__icon"><i class="fe fe-database"></i></span>
                                <span class="group-show-action__text">بنك الأسئلة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الأسئلة</h4>
                    <p class="text-muted fs-12 mb-0">الفلاتر تُحدَّث تلقائياً دون إعادة تحميل الصفحة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('quizzes.import-questions', $quiz->id) }}" id="quizImportFilterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="qiSearch">البحث</label>
                                <input type="text" id="qiSearch" name="search" class="form-control"
                                       placeholder="ابحث في نص السؤال أو الدرس..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qiCourse">الكورس</label>
                                <select name="course_id" id="qiCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @selected(request('course_id', $quiz->course_id) == $course->id)>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qiType">نوع السؤال</label>
                                <select name="question_type_id" id="qiType" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}" @selected(request('question_type_id') == $type->id)>
                                            {{ $type->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qiLesson">الدرس</label>
                                <select name="lesson_name" id="qiLesson" class="form-select">
                                    <option value="">جميع الدروس</option>
                                    @foreach($lessonNames as $lessonName)
                                        <option value="{{ $lessonName }}" @selected(request('lesson_name') === $lessonName)>
                                            {{ $lessonName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qiDifficulty">الصعوبة</label>
                                <select name="difficulty" id="qiDifficulty" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="easy" @selected(request('difficulty') === 'easy')>سهل</option>
                                    <option value="medium" @selected(request('difficulty') === 'medium')>متوسط</option>
                                    <option value="hard" @selected(request('difficulty') === 'hard')>صعب</option>
                                    <option value="expert" @selected(request('difficulty') === 'expert')>خبير</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qiLanguage">لغة البرمجة</label>
                                <select name="language_id" id="qiLanguage" class="form-select">
                                    <option value="">جميع اللغات</option>
                                    @foreach($programmingLanguages as $lang)
                                        <option value="{{ $lang->id }}" @selected(request('language_id') == $lang->id)>
                                            {{ $lang->display_name ?? $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="qiResetBtn">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح الفلاتر
                                    </button>
                                    <small id="qiSearchFeedback" class="text-muted ms-1"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        الأسئلة المتاحة
                        <span class="group-show-members-card__count" id="import-questions-count">{{ $availableQuestions->total() }}</span>
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary btn-sm" id="import-selected-questions-btn" disabled>
                            <i class="fe fe-download me-1"></i>استيراد المحدد (<span id="import-selected-count">0</span>)
                        </button>
                    </div>
                </div>
                <div class="card-body pt-3" id="quizImportTableContainer">
                    @include('admin.pages.quizzes._import_questions_table', compact('availableQuestions', 'quiz'))
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
(function() {
    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function syncImportSelectionUi() {
        const checked = document.querySelectorAll('#quizImportTableContainer .import-question-checkbox:checked');
        const count = checked.length;
        const btn = document.getElementById('import-selected-questions-btn');
        const countEl = document.getElementById('import-selected-count');
        const selectAll = document.getElementById('select-all-import-questions');
        const allBoxes = document.querySelectorAll('#quizImportTableContainer .import-question-checkbox');

        if (countEl) countEl.textContent = String(count);
        if (btn) btn.disabled = count === 0;
        if (selectAll && allBoxes.length > 0) {
            selectAll.checked = count === allBoxes.length;
            selectAll.indeterminate = count > 0 && count < allBoxes.length;
        }
    }

    window.initQuizImportTableHandlers = function() {
        syncImportSelectionUi();
    };

    function initQuizImportAjaxFilter() {
        const form = document.getElementById('quizImportFilterForm');
        const tableContainer = document.getElementById('quizImportTableContainer');
        const countBadge = document.getElementById('import-questions-count');
        const searchInput = document.getElementById('qiSearch');
        const feedback = document.getElementById('qiSearchFeedback');
        const resetBtn = document.getElementById('qiResetBtn');

        if (!form || !tableContainer) return;

        let currentController = null;

        const getQueryString = function() {
            const formData = new FormData(form);
            const search = (formData.get('search') || '').toString().trim();
            formData.set('search', search);
            return new URLSearchParams(formData).toString();
        };

        const updateBrowserUrl = function(queryString) {
            const baseUrl = form.getAttribute('action');
            const nextUrl = queryString ? (baseUrl + '?' + queryString) : baseUrl;
            window.history.replaceState({}, '', nextUrl);
        };

        const fetchAndRender = function(url) {
            if (currentController) currentController.abort();
            currentController = new AbortController();

            if (feedback) feedback.textContent = 'جاري التحديث...';

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(r => {
                    if (!r.ok) throw new Error('فشل جلب النتائج');
                    return r.json();
                })
                .then(data => {
                    tableContainer.innerHTML = data.table_html;
                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }
                    if (typeof window.initQuizImportTableHandlers === 'function') {
                        window.initQuizImportTableHandlers();
                    }
                    const queryString = url.includes('?') ? url.split('?')[1] : '';
                    updateBrowserUrl(queryString);
                    if (feedback) feedback.textContent = 'تم تحديث النتائج';
                })
                .catch(err => {
                    if (err.name === 'AbortError') return;
                    if (feedback) feedback.textContent = 'تعذر تحميل النتائج.';
                    console.error(err);
                });
        };

        const triggerSearch = function() {
            const queryString = getQueryString();
            const baseUrl = form.getAttribute('action');
            fetchAndRender(queryString ? (baseUrl + '?' + queryString) : baseUrl);
        };

        const debouncedSearch = debounce(triggerSearch, 350);

        if (searchInput) searchInput.addEventListener('input', debouncedSearch);
        form.querySelectorAll('select').forEach(el => el.addEventListener('change', triggerSearch));

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                form.reset();
                if (feedback) feedback.textContent = '';
                triggerSearch();
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerSearch();
        });

        tableContainer.addEventListener('click', function(event) {
            const link = event.target.closest('.pagination a, .qb-import-pagination a');
            if (!link) return;
            event.preventDefault();
            fetchAndRender(link.href);
        });
    }

    function initQuizImportSelection() {
        const tableContainer = document.getElementById('quizImportTableContainer');
        const importBtn = document.getElementById('import-selected-questions-btn');

        if (tableContainer) {
            tableContainer.addEventListener('change', function(e) {
                if (e.target.id === 'select-all-import-questions') {
                    const checked = e.target.checked;
                    tableContainer.querySelectorAll('.import-question-checkbox').forEach(cb => {
                        cb.checked = checked;
                    });
                    syncImportSelectionUi();
                }
                if (e.target.classList.contains('import-question-checkbox')) {
                    syncImportSelectionUi();
                }
            });
        }

        if (importBtn) {
            importBtn.addEventListener('click', function() {
                const selected = [];
                document.querySelectorAll('#quizImportTableContainer .import-question-checkbox:checked').forEach(cb => {
                    const id = parseInt(cb.value, 10);
                    const gradeInput = document.querySelector('.import-question-grade[data-question-id="' + id + '"]');
                    selected.push({
                        id: id,
                        grade: gradeInput ? parseFloat(gradeInput.value) || parseFloat(cb.dataset.defaultGrade) || 1 : parseFloat(cb.dataset.defaultGrade) || 1
                    });
                });

                if (selected.length === 0) {
                    if (typeof toastr !== 'undefined') toastr.warning('يرجى اختيار سؤال واحد على الأقل');
                    return;
                }

                importBtn.disabled = true;
                importBtn.innerHTML = '<i class="fe fe-loader me-1"></i>جاري الاستيراد...';

                fetch('{{ route('quizzes.import-questions.bulk', $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ questions: selected }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof toastr !== 'undefined') toastr.success(data.message);
                            selected.forEach(item => {
                                const row = document.getElementById('import-question-row-' + item.id);
                                if (row) row.remove();
                            });
                            const remaining = document.querySelectorAll('#quizImportTableContainer .import-question-checkbox').length;
                            const countBadge = document.getElementById('import-questions-count');
                            if (countBadge) countBadge.textContent = String(Math.max(0, parseInt(countBadge.textContent, 10) - data.imported));
                            syncImportSelectionUi();
                            if (remaining === 0) {
                                document.getElementById('quizImportTableContainer').innerHTML =
                                    '<div class="text-center text-muted py-5">لا توجد أسئلة متبقية للاستيراد.</div>';
                            }
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(data.message || 'فشل الاستيراد');
                        }
                    })
                    .catch(() => {
                        if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء الاستيراد');
                    })
                    .finally(() => {
                        importBtn.disabled = false;
                        importBtn.innerHTML = '<i class="fe fe-download me-1"></i>استيراد المحدد (<span id="import-selected-count">0</span>)';
                        syncImportSelectionUi();
                    });
            });
        }

        syncImportSelectionUi();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initQuizImportAjaxFilter();
        initQuizImportSelection();
    });
})();
</script>
@stop
