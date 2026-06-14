@extends('admin.layouts.master')

@section('page-title')
    بنك الأسئلة
@stop

@section('styles')
<style>
    /* إيقاف الأنيميشن أثناء إخفاء الصفحة حتى لا تنتهي قبل الظهور */
    html:not(.loaded) .qb-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }
    html.loaded .qb-page-animate {
        animation-play-state: running !important;
    }

    .qb-question-preview {
        max-width: 420px;
        line-height: 1.45;
    }

    .qb-type-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .qb-lang-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        color: #fff;
        margin-bottom: 0.15rem;
    }

    .qb-difficulty-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .qb-difficulty-chip--easy { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .qb-difficulty-chip--medium { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .qb-difficulty-chip--hard { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .qb-difficulty-chip--expert { background: rgba(33, 37, 41, 0.12); color: #212529; }

    .qb-table-row {
        transition: background-color 0.2s ease;
    }

    .qb-actions .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
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
                        <li class="breadcrumb-item active">بنك الأسئلة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-database me-1"></i>
                            إدارة بنك الأسئلة
                        </span>
                        <h2 class="group-show-hero__title mb-2">بنك الأسئلة</h2>
                        <p class="group-show-hero__desc mb-0">
                            إنشاء الأسئلة، استيرادها من Excel أو JSON، وتنظيمها حسب الكورس والنوع والصعوبة.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('question-bank.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة سؤال جديد</span>
                            </a>
                            <a href="{{ route('question-bank.import.excel') }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-file-text"></i></span>
                                <span class="group-show-action__text">استيراد من Excel</span>
                            </a>
                            <a href="{{ route('question-bank.import.type.select', 'excel') }}" class="group-show-action group-show-action--warning">
                                <span class="group-show-action__icon"><i class="fe fe-grid"></i></span>
                                <span class="group-show-action__text">Excel حسب النوع</span>
                            </a>
                            <a href="{{ route('question-bank.import.type.select', 'json') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-code"></i></span>
                                <span class="group-show-action__text">استيراد من JSON</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="questionBankStatsContainer" class="mb-4 qb-page-animate">
                @include('admin.pages.question-bank.partials.stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الأسئلة</h4>
                    <p class="fs-12 text-muted mb-0">ابحث في نص السؤال أو فلتر حسب الكورس والنوع والصعوبة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('question-bank.index') }}" id="qbFilterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="qbSearch">البحث</label>
                                <input type="text" id="qbSearch" name="search" class="form-control"
                                       placeholder="ابحث في نص السؤال..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qbCourse">الكورس</label>
                                <select name="course_id" id="qbCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qbType">نوع السؤال</label>
                                <select name="question_type_id" id="qbType" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('question_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qbDifficulty">الصعوبة</label>
                                <select name="difficulty" id="qbDifficulty" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                    <option value="expert" {{ request('difficulty') == 'expert' ? 'selected' : '' }}>خبير</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="qbLanguage">لغة البرمجة</label>
                                <select name="language_id" id="qbLanguage" class="form-select">
                                    <option value="">جميع اللغات</option>
                                    @foreach($programmingLanguages as $lang)
                                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
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
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="qbResetBtn">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح
                                    </button>
                                    <small id="qbSearchFeedback" class="text-muted ms-1"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الأسئلة
                        <span class="group-show-members-card__count" id="questions-count">{{ $questions->total() }}</span>
                    </h6>
                    <button type="button" class="btn btn-danger-light btn-sm" id="delete-selected-questions-btn" disabled>
                        <i class="fe fe-trash-2 me-1"></i>حذف المحدد (<span id="selected-questions-count">0</span>)
                    </button>
                </div>
                <div class="card-body pt-3" id="questionBankTableContainer">
                    @include('admin.pages.question-bank._questions_table', ['questions' => $questions])
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Question Modal (Single) -->
    <div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteQuestionModalLabel">
                        <i class="fe fe-alert-triangle text-danger me-2"></i>حذف السؤال
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">هل أنت متأكد من إزالة هذا السؤال من بنك الأسئلة؟</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fe fe-info me-2"></i>
                        <strong>السؤال:</strong> <span id="deleteQuestionText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteQuestion">
                        <i class="fe fe-trash-2 me-1"></i>حذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Multiple Questions Modal -->
    <div class="modal fade" id="deleteMultipleQuestionsModal" tabindex="-1" aria-labelledby="deleteMultipleQuestionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMultipleQuestionsModalLabel">
                        <i class="fe fe-alert-triangle text-danger me-2"></i>حذف أسئلة متعددة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">هل أنت متأكد من إزالة الأسئلة المحددة من بنك الأسئلة؟</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fe fe-info me-2"></i>
                        <strong>عدد الأسئلة المحددة:</strong> <span id="deleteMultipleQuestionsCount">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMultipleQuestions">
                        <i class="fe fe-trash-2 me-1"></i>حذف المحدد
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
(function() {
    function initQuestionBankCountup(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-countup]').forEach(function(el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    }

    window.initQuestionBankCountup = initQuestionBankCountup;

    function restartPageAnimations() {
        document.querySelectorAll('.qb-page-animate').forEach(function(el) {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }

    function onPageReady() {
        initQuestionBankCountup();
        restartPageAnimations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onPageReady);
    } else {
        onPageReady();
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (!document.documentElement.classList.contains('loaded')) {
                document.documentElement.classList.add('loaded');
            }
            restartPageAnimations();
            initQuestionBankCountup();
        }, 50);
    });
})();
</script>
<script>
(function() {
    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initQuestionBankAjaxFilter() {
        const form = document.getElementById('qbFilterForm');
        const tableContainer = document.getElementById('questionBankTableContainer');
        const statsContainer = document.getElementById('questionBankStatsContainer');
        const countBadge = document.getElementById('questions-count');
        const searchInput = document.getElementById('qbSearch');
        const feedback = document.getElementById('qbSearchFeedback');
        const resetBtn = document.getElementById('qbResetBtn');

        if (!form || !tableContainer) {
            return;
        }

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
            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();

            if (feedback) {
                feedback.textContent = 'جاري البحث...';
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('فشل جلب النتائج');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (!data || typeof data.table_html !== 'string') {
                        throw new Error('صيغة استجابة غير متوقعة');
                    }

                    tableContainer.innerHTML = data.table_html;

                    if (statsContainer && typeof data.stats_html === 'string') {
                        statsContainer.innerHTML = data.stats_html;
                        if (typeof window.initQuestionBankCountup === 'function') {
                            window.initQuestionBankCountup(statsContainer);
                        }
                    }

                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }

                    if (typeof window.initQuestionBankTableHandlers === 'function') {
                        window.initQuestionBankTableHandlers();
                    }

                    const queryString = url.includes('?') ? url.split('?')[1] : '';
                    updateBrowserUrl(queryString);

                    if (feedback) {
                        feedback.textContent = 'تم تحديث النتائج';
                    }
                })
                .catch(function(error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    if (feedback) {
                        feedback.textContent = 'تعذر تحميل النتائج، حاول مرة أخرى.';
                    }
                    console.error(error);
                });
        };

        const triggerSearch = function() {
            const queryString = getQueryString();
            const baseUrl = form.getAttribute('action');
            const url = queryString ? (baseUrl + '?' + queryString) : baseUrl;
            fetchAndRender(url);
        };

        const debouncedSearch = debounce(triggerSearch, 350);

        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }

        form.querySelectorAll('select').forEach(function(selectElement) {
            selectElement.addEventListener('change', triggerSearch);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                form.reset();
                if (feedback) {
                    feedback.textContent = '';
                }
                triggerSearch();
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerSearch();
        });

        tableContainer.addEventListener('click', function(event) {
            const paginationLink = event.target.closest('.pagination a, .qb-pagination a');
            if (!paginationLink) {
                return;
            }

            event.preventDefault();
            fetchAndRender(paginationLink.href);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuestionBankAjaxFilter);
    } else {
        initQuestionBankAjaxFilter();
    }
})();
</script>
<script>
(function() {
    function initQuestionBankDelete() {
        if (typeof jQuery === 'undefined' || typeof bootstrap === 'undefined') {
            setTimeout(initQuestionBankDelete, 100);
            return;
        }

        var $ = jQuery;
        const tableContainer = document.getElementById('questionBankTableContainer');
        const deleteSelectedBtn = document.getElementById('delete-selected-questions-btn');

        window.currentDeleteQuestionId = null;
        window.currentDeleteQuestionRow = null;
        window.selectedQuestionsForDeletion = null;

        function toggleBulkDeleteButton() {
            const selectedQuestions = $('#questionBankTableContainer .question-row-checkbox:checked').map(function() {
                return parseInt($(this).val());
            }).get();

            if (selectedQuestions.length > 0) {
                $('#delete-selected-questions-btn').prop('disabled', false);
                $('#selected-questions-count').text(selectedQuestions.length);
            } else {
                $('#delete-selected-questions-btn').prop('disabled', true);
                $('#selected-questions-count').text('0');
            }
        }

        window.initQuestionBankTableHandlers = function() {
            toggleBulkDeleteButton();
        };

        const deleteQuestionModal = document.getElementById('deleteQuestionModal');
        if (deleteQuestionModal) {
            $(deleteQuestionModal).on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                window.currentDeleteQuestionId = null;
                window.currentDeleteQuestionRow = null;
            });
        }

        const deleteMultipleQuestionsModal = document.getElementById('deleteMultipleQuestionsModal');
        if (deleteMultipleQuestionsModal) {
            $(deleteMultipleQuestionsModal).on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                window.selectedQuestionsForDeletion = null;
            });
        }

        if (tableContainer) {
            $(tableContainer).on('change', '#select-all-questions-table', function() {
                const isChecked = $(this).is(':checked');
                $('#questionBankTableContainer .question-row-checkbox').prop('checked', isChecked);
                toggleBulkDeleteButton();
            });

            $(tableContainer).on('change', '.question-row-checkbox', function() {
                const total = $('#questionBankTableContainer .question-row-checkbox').length;
                const checked = $('#questionBankTableContainer .question-row-checkbox:checked').length;
                $('#select-all-questions-table').prop('checked', total === checked && total > 0);
                toggleBulkDeleteButton();
            });

            $(tableContainer).on('click', '.remove-question', function(e) {
                e.preventDefault();
                window.currentDeleteQuestionId = $(this).data('question-id');
                window.currentDeleteQuestionRow = $(this).closest('tr');
                $('#deleteQuestionText').text($(this).data('question-text') || 'هذا السؤال');
                if (deleteQuestionModal) {
                    bootstrap.Modal.getOrCreateInstance(deleteQuestionModal).show();
                }
            });
        }

        const confirmDeleteQuestionBtn = document.getElementById('confirmDeleteQuestion');
        if (confirmDeleteQuestionBtn) {
            $(confirmDeleteQuestionBtn).off('click').on('click', function() {
                if (!window.currentDeleteQuestionId || !window.currentDeleteQuestionRow) return;

                const questionId = window.currentDeleteQuestionId;
                const row = window.currentDeleteQuestionRow;
                const deleteModal = bootstrap.Modal.getInstance(deleteQuestionModal);
                if (deleteModal) deleteModal.hide();

                row.find('.remove-question').prop('disabled', true);

                $.ajax({
                    url: '{{ url('admin/question-bank') }}/' + questionId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'تم حذف السؤال بنجاح');
                            }
                            row.fadeOut(300, function() {
                                $(this).remove();
                                const current = parseInt($('#questions-count').text(), 10) || 0;
                                $('#questions-count').text(Math.max(0, current - 1));
                                toggleBulkDeleteButton();
                            });
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء حذف السؤال');
                        }
                        row.find('.remove-question').prop('disabled', false);
                    }
                });
            });
        }

        if (deleteSelectedBtn) {
            $(deleteSelectedBtn).off('click').on('click', function() {
                const selectedQuestions = $('#questionBankTableContainer .question-row-checkbox:checked').map(function() {
                    return parseInt($(this).val());
                }).get();

                if (selectedQuestions.length === 0) {
                    if (typeof toastr !== 'undefined') toastr.warning('يرجى اختيار سؤال واحد على الأقل');
                    return;
                }

                $('#deleteMultipleQuestionsCount').text(selectedQuestions.length);
                if (deleteMultipleQuestionsModal) {
                    bootstrap.Modal.getOrCreateInstance(deleteMultipleQuestionsModal).show();
                }
                window.selectedQuestionsForDeletion = selectedQuestions;
            });
        }

        const confirmDeleteMultipleBtn = document.getElementById('confirmDeleteMultipleQuestions');
        if (confirmDeleteMultipleBtn) {
            $(confirmDeleteMultipleBtn).off('click').on('click', function() {
                const selectedQuestions = window.selectedQuestionsForDeletion || [];
                if (selectedQuestions.length === 0) return;

                const deleteModal = bootstrap.Modal.getInstance(deleteMultipleQuestionsModal);
                if (deleteModal) deleteModal.hide();

                const btn = $('#delete-selected-questions-btn');
                btn.prop('disabled', true).html('<i class="fe fe-loader me-1"></i>جاري الحذف...');

                $.ajax({
                    url: '{{ route('question-bank.delete-multiple') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: selectedQuestions
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'تم الحذف بنجاح');
                            }
                            selectedQuestions.forEach(function(questionId) {
                                $('#question-row-' + questionId).fadeOut(300, function() { $(this).remove(); });
                            });
                            setTimeout(function() {
                                const current = parseInt($('#questions-count').text(), 10) || 0;
                                $('#questions-count').text(Math.max(0, current - selectedQuestions.length));
                                btn.prop('disabled', true).html('<i class="fe fe-trash-2 me-1"></i>حذف المحدد (<span id="selected-questions-count">0</span>)');
                                toggleBulkDeleteButton();
                            }, 350);
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف');
                        }
                        btn.prop('disabled', false);
                        toggleBulkDeleteButton();
                    }
                });
            });
        }

        toggleBulkDeleteButton();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuestionBankDelete);
    } else {
        initQuestionBankDelete();
    }
})();
</script>
@stop
