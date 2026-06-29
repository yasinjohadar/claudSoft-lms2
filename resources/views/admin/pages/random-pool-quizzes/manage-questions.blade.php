@extends('admin.layouts.master')

@section('page-title')
    إدارة الأسئلة - {{ $quiz->title }}
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
                        <li class="breadcrumb-item"><a href="{{ route('random-pool-quizzes.index') }}">اختبارات بنك عشوائي</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('random-pool-quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                        <li class="breadcrumb-item active">إدارة الأسئلة</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in quizzes-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-list me-1"></i>إدارة الأسئلة</span>
                        <h2 class="group-show-hero__title mb-2">{{ $quiz->title }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            بنك {{ $poolStats['pool_size'] ?? $quiz->getPoolSize() }} سؤال · {{ $quiz->questions_per_attempt }} سؤال/محاولة · الدرجة المتوقعة {{ number_format($quiz->max_score, 1) }}
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('random-pool-quizzes.show', $quiz->id) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">عرض الاختبار</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                        <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                        معلومات الاختبار
                    </h4>
                </div>
                <div class="card-body pt-3">
                    <div class="assignments-info-grid">
                        <div class="assignments-info-item">
                            <div class="assignments-info-item__label">الكورس</div>
                            <div class="assignments-info-item__value">
                                <span class="assignments-course-chip">{{ $quiz->course->title ?? 'غير محدد' }}</span>
                            </div>
                        </div>
                        <div class="assignments-info-item">
                            <div class="assignments-info-item__label">نوع الاختبار</div>
                            <div class="assignments-info-item__value">
                                <span class="quizzes-type-chip quizzes-type-chip--random">بنك عشوائي</span>
                            </div>
                        </div>
                        <div class="assignments-info-item">
                            <div class="assignments-info-item__label">عدد الأسئلة</div>
                            <div class="assignments-info-item__value fw-semibold">
                                {{ $quiz->questions_per_attempt }} / {{ $poolStats['pool_size'] ?? $quiz->getPoolSize() }}
                            </div>
                        </div>
                        <div class="assignments-info-item">
                            <div class="assignments-info-item__label">درجة النجاح</div>
                            <div class="assignments-info-item__value">{{ $quiz->passing_grade }}%</div>
                        </div>
                        @if($quiz->time_limit)
                            <div class="assignments-info-item">
                                <div class="assignments-info-item__label">الوقت المحدد</div>
                                <div class="assignments-info-item__value">{{ $quiz->time_limit }} دقيقة</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-layers"></i></span>
                            إحصائيات بنك الأسئلة
                        </h4>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">حجم البنك الكلي</div>
                                    <div class="assignments-info-item__value fw-semibold fs-18">{{ $poolStats['pool_size'] ?? 0 }} سؤال</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">يُعرض للطالب</div>
                                    <div class="assignments-info-item__value fw-semibold fs-18">{{ $poolStats['per_attempt'] ?? $quiz->questions_per_attempt }} سؤال/محاولة</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if(!empty($poolStats['config_error']))
                                    <div class="alert alert-warning mb-0 py-2">
                                        <i class="fe fe-alert-triangle me-1"></i>{{ $poolStats['config_error'] }}
                                    </div>
                                @else
                                    <div class="alert alert-success mb-0 py-2">
                                        <i class="fe fe-check-circle me-1"></i>إعدادات البنك صحيحة
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                        <h6 class="group-show-members-card__title mb-0">
                            مجموعات الأسئلة المربوطة
                            <span class="group-show-members-card__count">{{ $linkedPools->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body pt-3">
                        @if($availablePools->isEmpty())
                            <p class="text-muted mb-3">لا توجد مجموعات أسئلة في هذا الكورس. أنشئ مجموعة من <a href="{{ route('question-pools.index') }}">إدارة مجموعات الأسئلة</a>.</p>
                        @else
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">اختر مجموعة أسئلة</label>
                                    <select id="question-pool-select" class="form-select">
                                        <option value="">— اختر مجموعة —</option>
                                        @foreach($availablePools as $pool)
                                            <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100" id="attach-question-pool-btn">
                                        <i class="fe fe-plus me-1"></i>إضافة مجموعة
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($linkedPools->isEmpty())
                            <p class="text-muted mb-0">لم تُربط أي مجموعات بعد. يمكنك إضافة أسئلة مباشرة أو ربط مجموعات.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>المجموعة</th>
                                            <th style="width: 120px;">إجراء</th>
                                        </tr>
                                    </thead>
                                    <tbody id="linked-pools-body">
                                        @foreach($linkedPools as $linked)
                                            <tr data-quiz-question-id="{{ $linked->id }}">
                                                <td>{{ $linked->questionPool->name ?? '—' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger-light detach-pool-btn" data-id="{{ $linked->id }}">
                                                        <i class="fe fe-trash-2"></i> إزالة
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                الأسئلة المرتبطة
                                <span class="group-show-members-card__count" id="questions-count">{{ $quiz->questions->count() }}</span>
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-danger-light btn-sm" id="delete-selected-questions" disabled>
                                    <i class="fe fe-trash-2 me-1"></i>حذف المحدد
                                </button>
                                <a href="{{ route('quizzes.ai-generate.create', $quiz) }}" class="btn btn-info-light btn-sm">
                                    <i class="fe fe-cpu me-1"></i>توليد بالذكاء الاصطناعي
                                </a>
                                <button type="button" class="btn btn-success-light btn-sm" data-bs-toggle="modal" data-bs-target="#createQuestionModal">
                                    <i class="fe fe-plus me-1"></i>سؤال جديد
                                </button>
                                <a href="{{ route('quizzes.import-questions', $quiz->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fe fe-download me-1"></i>استيراد من بنك الأسئلة
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            @if($quiz->questions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-hover dashboard-table mb-0">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="select-all-questions-table">
                                                </th>
                                                <th width="50">#</th>
                                                <th>السؤال</th>
                                                <th>النوع</th>
                                                <th>الدرجة</th>
                                                <th>مطلوب</th>
                                                <th width="150">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="questions-sortable">
                                            @foreach($quiz->questions as $question)
                                                <tr id="question-row-{{ $question->id }}" data-id="{{ $question->id }}">
                                                    <td>
                                                        <input type="checkbox" class="question-row-checkbox" value="{{ $question->id }}">
                                                    </td>
                                                    <td><i class="fe fe-menu handle" style="cursor: move;"></i></td>
                                                    <td>
                                                        <div class="d-flex align-items-start">
                                                            <div>
                                                                <a href="{{ route('question-bank.show', $question->id) }}" target="_blank" class="fw-semibold">
                                                                    {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-transparent">
                                                            {{ $question->questionType->display_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control form-control-sm question-grade"
                                                               value="{{ $question->pivot->question_grade ?? $question->default_grade }}"
                                                               data-question-id="{{ $question->id }}"
                                                               step="0.5"
                                                               min="0"
                                                               style="width: 80px;">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox"
                                                               class="form-check-input question-required"
                                                               data-question-id="{{ $question->id }}"
                                                               {{ $question->pivot->is_required ?? false ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger-light remove-question"
                                                                data-question-id="{{ $question->id }}">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-help-circle"></i></span>
                                    <p class="text-muted mb-3">لا توجد أسئلة مرتبطة بهذا الاختبار بعد</p>
                                    <a href="{{ route('quizzes.import-questions', $quiz->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fe fe-download me-1"></i>استيراد سؤال من بنك الأسئلة
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('shared.question-types.create-modal', [
        'questionTypes' => $questionTypes,
        'context' => 'quiz',
        'contextParams' => ['quiz_id' => $quiz->id],
    ])

    <!-- Delete Single Question Modal -->
    <div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-5">
                    <!-- Icon -->
                    <div class="text-center mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-question-circle fa-3x"></i>
                        </span>
                    </div>

                    <!-- Title -->
                    <h5 class="modal-title text-center mb-4 fw-bold" id="deleteQuestionModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        حذف السؤال
                    </h5>

                    <!-- Message -->
                    <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                        <div>
                            <strong>هل أنت متأكد من إزالة هذا السؤال من الاختبار؟</strong>
                            <div class="mt-2">
                                <span class="badge bg-primary fs-6" id="deleteQuestionText">السؤال</span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                سيتم إزالة السؤال من هذا الاختبار فقط ولن يتم حذفه من بنك الأسئلة
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteQuestion">
                            <i class="fas fa-trash me-2"></i>حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Multiple Questions Modal -->
    <div class="modal fade" id="deleteMultipleQuestionsModal" tabindex="-1" aria-labelledby="deleteMultipleQuestionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-5">
                    <!-- Icon -->
                    <div class="text-center mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-question-circle fa-3x"></i>
                        </span>
                    </div>

                    <!-- Title -->
                    <h5 class="modal-title text-center mb-4 fw-bold" id="deleteMultipleQuestionsModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        حذف أسئلة متعددة
                    </h5>

                    <!-- Message -->
                    <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                        <div>
                            <strong>هل أنت متأكد من إزالة الأسئلة المحددة من الاختبار؟</strong>
                            <div class="mt-2">
                                <span class="badge bg-primary fs-6" id="deleteMultipleQuestionsCount">0</span> سؤال محدد
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                سيتم إزالة الأسئلة من هذا الاختبار فقط ولن يتم حذفها من بنك الأسئلة
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteMultipleQuestions">
                            <i class="fas fa-trash me-2"></i>حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // Cleanup modals on hide
    $('#deleteQuestionModal').on('hidden.bs.modal', function() {
        // Remove backdrop if it exists
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // Reset variables
        currentDeleteQuestionId = null;
        currentDeleteQuestionRow = null;
    });

    $('#deleteMultipleQuestionsModal').on('hidden.bs.modal', function() {
        // Remove backdrop if it exists
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // Reset variables
        window.selectedQuestionsForDeletion = null;
    });

    // Make table sortable
    const el = document.getElementById('questions-sortable');
    if (el) {
        const sortable = Sortable.create(el, {
            handle: '.handle',
            animation: 150,
            onEnd: function(evt) {
                const order = [];
                $('#questions-sortable tr').each(function() {
                    order.push($(this).data('id'));
                });

                $.ajax({
                    url: '{{ route('quizzes.reorder-questions', $quiz->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        question_ids: order
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'تم إعادة ترتيب الأسئلة بنجاح');
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء إعادة الترتيب');
                        location.reload();
                    }
                });
            }
        });
    }

    // Select all questions in table
    $('#select-all-questions-table').on('change', function() {
        $('.question-row-checkbox').prop('checked', $(this).prop('checked'));
        updateDeleteButtonState();
    });

    // Update delete button state based on selected checkboxes
    function updateDeleteButtonState() {
        const selectedCount = $('.question-row-checkbox:checked').length;
        $('#delete-selected-questions').prop('disabled', selectedCount === 0);
        if (selectedCount > 0) {
            $('#delete-selected-questions').html(`<i class="fas fa-trash me-1"></i>حذف المحدد (${selectedCount})`);
        } else {
            $('#delete-selected-questions').html('<i class="fas fa-trash me-1"></i>حذف المحدد');
        }
        
        // Update select all checkbox
        const totalCheckboxes = $('.question-row-checkbox').length;
        $('#select-all-questions-table').prop('checked', selectedCount === totalCheckboxes && totalCheckboxes > 0);
    }

    // Update delete button when individual checkbox changes
    $(document).on('change', '.question-row-checkbox', function() {
        updateDeleteButtonState();
    });

    // Update delete button when individual checkbox changes
    let currentDeleteQuestionId = null;
    let currentDeleteQuestionRow = null;

    $(document).on('click', '.remove-question', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const questionId = $(this).data('question-id');
        const row = $(this).closest('tr');
        const questionText = row.find('td:eq(2) a').text().trim() || 'هذا السؤال';
        
        currentDeleteQuestionId = questionId;
        currentDeleteQuestionRow = row;
        
        // Update modal content
        $('#deleteQuestionText').text(questionText.length > 50 ? questionText.substring(0, 50) + '...' : questionText);
        
        // Show modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteQuestionModal'));
        deleteModal.show();
    });

    // Confirm single delete
    $('#confirmDeleteQuestion').on('click', function() {
        if (!currentDeleteQuestionId || !currentDeleteQuestionRow) return;

        const questionId = currentDeleteQuestionId;
        const row = currentDeleteQuestionRow;

        // Get modal instance and hide it properly
        const modalElement = document.getElementById('deleteQuestionModal');
        const deleteModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        
        // Hide modal and remove backdrop
        deleteModal.hide();
        
        // Force remove backdrop if it exists
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }, 100);

        // Disable button
        const btn = row.find('.remove-question');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('quizzes.remove-question', [$quiz->id, ':questionId']) }}'.replace(':questionId', questionId),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'تم إزالة السؤال بنجاح');
                    
                    // Remove row with animation
                    row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update questions count
                        const remainingCount = $('#questions-sortable tr').length;
                        $('#questions-count').text(remainingCount);
                        
                        // Update card title if no questions left
                        if (remainingCount === 0) {
                            $('#questions-sortable').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-question-circle fa-3x text-muted mb-3"></i><p class="text-muted">لا توجد أسئلة مرتبطة بهذا الاختبار بعد</p><a href="{{ route('quizzes.import-questions', $quiz->id) }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>استيراد سؤال من بنك الأسئلة</a></td></tr>');
                        }
                    });
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء إزالة السؤال');
                btn.prop('disabled', false);
            },
            complete: function() {
                currentDeleteQuestionId = null;
                currentDeleteQuestionRow = null;
                
                // Ensure backdrop is removed
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
            }
        });
    });

    // Update question grade
    $(document).on('change', '.question-grade', function() {
        const questionId = $(this).data('question-id');
        const grade = $(this).val();

        $.ajax({
            url: '{{ route('quizzes.add-question', $quiz->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_id: questionId,
                question_grade: grade,
                update_existing: true
            },
            success: function(response) {
                toastr.success('تم تحديث درجة السؤال بنجاح');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء تحديث الدرجة');
            }
        });
    });

    // Update question required status
    $(document).on('change', '.question-required', function() {
        const questionId = $(this).data('question-id');
        const isRequired = $(this).prop('checked');

        $.ajax({
            url: '{{ route('quizzes.add-question', $quiz->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_id: questionId,
                is_required: isRequired ? 1 : 0,
                update_existing: true
            },
            success: function(response) {
                toastr.success('تم تحديث حالة السؤال بنجاح');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء تحديث الحالة');
            }
        });
    });

    // Delete multiple questions
    $('#delete-selected-questions').on('click', function() {
        const selectedQuestions = [];
        $('.question-row-checkbox:checked').each(function() {
            selectedQuestions.push($(this).val());
        });

        if (selectedQuestions.length === 0) {
            toastr.warning('يرجى اختيار سؤال واحد على الأقل');
            return;
        }

        // Update modal content
        $('#deleteMultipleQuestionsCount').text(selectedQuestions.length);

        // Show modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteMultipleQuestionsModal'));
        deleteModal.show();

        // Store selected questions for deletion
        window.selectedQuestionsForDeletion = selectedQuestions;
    });

    // Confirm multiple delete
    $('#confirmDeleteMultipleQuestions').on('click', function() {
        const selectedQuestions = window.selectedQuestionsForDeletion || [];
        
        if (selectedQuestions.length === 0) {
            toastr.warning('لم يتم تحديد أي أسئلة');
            return;
        }

        // Get modal instance and hide it properly
        const modalElement = document.getElementById('deleteMultipleQuestionsModal');
        const deleteModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        
        // Hide modal and remove backdrop
        deleteModal.hide();
        
        // Force remove backdrop if it exists
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }, 100);

        // Disable button
        const btn = $('#delete-selected-questions');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>جاري الحذف...');

        $.ajax({
            url: '{{ route('quizzes.remove-multiple-questions', $quiz->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_ids: selectedQuestions
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || `تم حذف ${selectedQuestions.length} سؤال بنجاح`);
                    
                    // Remove rows with animation
                    let removedCount = 0;
                    selectedQuestions.forEach(function(questionId) {
                        const row = $(`#question-row-${questionId}`);
                        if (row.length) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                                removedCount++;
                                
                                // Update count when all rows are removed
                                if (removedCount === selectedQuestions.length) {
                                    const remainingCount = $('#questions-sortable tr').length;
                                    $('#questions-count').text(remainingCount);
                                    
                                    // Update card title if no questions left
                                    if (remainingCount === 0) {
                                        $('#questions-sortable').html('<tr><td colspan="7" class="text-center py-4"><i class="fas fa-question-circle fa-3x text-muted mb-3"></i><p class="text-muted">لا توجد أسئلة مرتبطة بهذا الاختبار بعد</p><a href="{{ route('quizzes.import-questions', $quiz->id) }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>استيراد سؤال من بنك الأسئلة</a></td></tr>');
                                    }
                                    
                                    // Reset checkboxes
                                    $('#select-all-questions-table').prop('checked', false);
                                    updateDeleteButtonState();
                                }
                            });
                        }
                    });
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء حذف الأسئلة');
                btn.prop('disabled', false);
                updateDeleteButtonState();
            },
            complete: function() {
                window.selectedQuestionsForDeletion = null;
                
                // Ensure backdrop is removed
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
            }
        });
    });

    $('#attach-question-pool-btn').on('click', function() {
        const poolId = $('#question-pool-select').val();
        if (!poolId) {
            toastr.warning('يرجى اختيار مجموعة أسئلة');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('random-pool-quizzes.attach-question-pool', $quiz->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_pool_id: poolId
            },
            success: function(response) {
                toastr.success(response.message || 'تم ربط المجموعة');
                window.location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء ربط المجموعة');
                btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.detach-pool-btn', function() {
        const quizQuestionId = $(this).data('id');
        if (!confirm('هل تريد إزالة هذه المجموعة من الاختبار؟')) {
            return;
        }

        $.ajax({
            url: @json(route('random-pool-quizzes.detach-question-pool', ['id' => $quiz->id, 'quizQuestionId' => '__ID__'])).replace('__ID__', quizQuestionId),
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                toastr.success(response.message || 'تم إزالة المجموعة');
                window.location.reload();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء إزالة المجموعة');
            }
        });
    });
});
</script>
@stop

