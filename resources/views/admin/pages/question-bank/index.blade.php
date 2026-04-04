@extends('admin.layouts.master')

@section('page-title')
    بنك الأسئلة
@stop

@section('styles')
<style>
    /* Fix excessive spacing before footer - Override theme CSS */
    body .page {
        min-height: auto !important;
        justify-content: flex-start !important;
        display: flex !important;
        flex-direction: column !important;
    }
    
    body .main-content.app-content {
        padding-bottom: 1rem !important;
        min-height: auto !important;
        margin-block-end: 0 !important;
        flex: 0 0 auto !important;
    }
    
    body .container-fluid {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    
    body .card.custom-card:last-child {
        margin-bottom: 1rem !important;
    }
    
    /* Ensure footer doesn't create extra space */
    body .footer {
        margin-top: 0 !important;
        flex: 0 0 auto !important;
    }
    
    /* Remove any extra padding/margin from app-content */
    body .app-content > .container-fluid {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Fix for empty state causing extra space */
    body .table-responsive {
        margin-bottom: 0 !important;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">بنك الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">بنك الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-bank.import.excel') }}" class="btn btn-success me-2">
                        <i class="fas fa-file-excel me-2"></i>استيراد من Excel
                    </a>
                    <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questions->total() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-question-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">قابلة لإعادة الاستخدام</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questions->where('is_reusable', true)->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-recycle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">أنواع الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionTypes->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-list fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الكورسات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $courses->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-book fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('question-bank.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث في نص السؤال..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">نوع السؤال</label>
                                <select name="question_type_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('question_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الصعوبة</label>
                                <select name="difficulty" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">لغة البرمجة</label>
                                <select name="language_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع اللغات</option>
                                    @foreach($programmingLanguages as $lang)
                                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Questions Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            قائمة الأسئلة (<span id="questions-count">{{ $questions->total() }}</span>)
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" id="delete-selected-questions-btn" disabled>
                            <i class="fas fa-trash me-1"></i>حذف المحدد (<span id="selected-questions-count">0</span>)
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="select-all-questions-table"></th>
                                    <th width="5%">#</th>
                                    <th width="30%">السؤال</th>
                                    <th width="10%">النوع</th>
                                    <th width="12%">اللغات</th>
                                    <th width="10%">الكورس</th>
                                    <th width="10%">الدرس</th>
                                    <th width="8%">الصعوبة</th>
                                    <th width="6%">الدرجة</th>
                                    <th width="6%">الاستخدام</th>
                                    <th width="13%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $question)
                                    <tr id="question-row-{{ $question->id }}">
                                        <td><input type="checkbox" class="question-row-checkbox" value="{{ $question->id }}"></td>
                                        <td>{{ $loop->iteration + ($questions->currentPage() - 1) * $questions->perPage() }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 400px;" title="{{ $question->question_text }}">
                                                {{ $question->question_text }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-user fs-10 me-1"></i>{{ $question->creator->name ?? 'غير محدد' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent">
                                                <i class="{{ $question->questionType->icon ?? 'fas fa-question' }} me-1"></i>
                                                {{ $question->questionType->display_name }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($question->programmingLanguages->count() > 0)
                                                @foreach($question->programmingLanguages as $lang)
                                                    <span class="badge mb-1" style="background-color: {{ $lang->color ?? '#6c757d' }}; color: white;">
                                                        <i class="{{ $lang->icon ?? 'fas fa-code' }} me-1"></i>{{ $lang->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($question->course)
                                                <span class="badge bg-primary-transparent">
                                                    {{ $question->course->title }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-transparent">
                                                    عام
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $lessonLabel = $question->lesson_name ?? ($question->metadata['lesson_name'] ?? null);
                                            @endphp
                                            @if($lessonLabel)
                                                <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $lessonLabel }}">{{ $lessonLabel }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($question->difficulty_level == 'easy')
                                                <span class="badge bg-success">سهل</span>
                                            @elseif($question->difficulty_level == 'medium')
                                                <span class="badge bg-warning">متوسط</span>
                                            @elseif($question->difficulty_level == 'hard')
                                                <span class="badge bg-danger">صعب</span>
                                            @else
                                                <span class="badge bg-dark">خبير</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $question->default_grade ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple-transparent" title="عدد مرات الاستخدام">
                                                {{ $question->quizQuestions()->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('question-bank.show', $question->id) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('question-bank.edit', $question->id) }}"
                                                   class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('question-bank.duplicate', $question->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="نسخ">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger remove-question" 
                                                        data-question-id="{{ $question->id }}" 
                                                        data-question-text="{{ Str::limit(strip_tags($question->question_text), 50) }}"
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-question-circle fs-48 text-muted"></i>
                                            </div>
                                            <p class="text-muted fs-16 mb-3">لا توجد أسئلة في البنك</p>
                                            <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($questions->hasPages())
                    <div class="card-footer">
                        {{ $questions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Delete Question Modal (Single) -->
    <div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteQuestionModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>حذف السؤال
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">هل أنت متأكد من إزالة هذا السؤال من بنك الأسئلة؟</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>السؤال:</strong> <span id="deleteQuestionText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteQuestion">
                        <i class="fas fa-trash me-2"></i>حذف
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
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>حذف أسئلة متعددة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">هل أنت متأكد من إزالة الأسئلة المحددة من بنك الأسئلة؟</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>عدد الأسئلة المحددة:</strong> <span id="deleteMultipleQuestionsCount">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>إلغاء
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMultipleQuestions">
                        <i class="fas fa-trash me-2"></i>حذف المحدد
                    </button>
                </div>
            </div>
        </div>
    </div>

@section('scripts')
<script>
(function() {
    console.log('Question Bank Script: Starting initialization...');
    
    // Wait for jQuery and Bootstrap to be available
    function initQuestionBankDelete() {
        // Check for jQuery
        if (typeof jQuery === 'undefined') {
            console.log('Question Bank Script: jQuery not loaded, retrying...');
            setTimeout(initQuestionBankDelete, 100);
            return;
        }

        // Check for Bootstrap
        if (typeof bootstrap === 'undefined') {
            console.log('Question Bank Script: Bootstrap not loaded, retrying...');
            setTimeout(initQuestionBankDelete, 100);
            return;
        }

        var $ = jQuery;
        console.log('Question Bank Script: jQuery and Bootstrap loaded, initializing...');

        // Use both document.ready and window.load as fallback
        function initializeHandlers() {
            console.log('Question Bank Script: Initializing event handlers...');
            
            // Check if required elements exist
            const selectAllCheckbox = document.getElementById('select-all-questions-table');
            const deleteSelectedBtn = document.getElementById('delete-selected-questions-btn');
            const questionCheckboxes = document.querySelectorAll('.question-row-checkbox');
            
            console.log('Question Bank Script: Elements check:', {
                selectAllCheckbox: !!selectAllCheckbox,
                deleteSelectedBtn: !!deleteSelectedBtn,
                questionCheckboxes: questionCheckboxes.length
            });

            // Variables for single delete
            window.currentDeleteQuestionId = null;
            window.currentDeleteQuestionRow = null;
            window.selectedQuestionsForDeletion = null;

            // Cleanup modals on hide
            const deleteQuestionModal = document.getElementById('deleteQuestionModal');
            if (deleteQuestionModal) {
                $(deleteQuestionModal).on('hidden.bs.modal', function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                    window.currentDeleteQuestionId = null;
                    window.currentDeleteQuestionRow = null;
                });
            }

            const deleteMultipleQuestionsModal = document.getElementById('deleteMultipleQuestionsModal');
            if (deleteMultipleQuestionsModal) {
                $(deleteMultipleQuestionsModal).on('hidden.bs.modal', function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                    window.selectedQuestionsForDeletion = null;
                });
            }

            // Toggle bulk delete button
            function toggleBulkDeleteButton() {
                const selectedQuestions = $('.question-row-checkbox:checked').map(function() {
                    return parseInt($(this).val());
                }).get();
                
                const btn = $('#delete-selected-questions-btn');
                const countSpan = $('#selected-questions-count');
                
                console.log('Selected questions:', selectedQuestions.length);
                
                if (selectedQuestions.length > 0) {
                    btn.prop('disabled', false);
                    countSpan.text(selectedQuestions.length);
                } else {
                    btn.prop('disabled', true);
                    countSpan.text('0');
                }
            }

            // Select all checkbox - use direct binding if element exists
            if (selectAllCheckbox) {
                $(selectAllCheckbox).off('change').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    console.log('Question Bank Script: Select all changed:', isChecked);
                    $('.question-row-checkbox').prop('checked', isChecked);
                    toggleBulkDeleteButton();
                });
                console.log('Question Bank Script: Select all checkbox handler attached');
            } else {
                console.warn('Question Bank Script: Select all checkbox not found!');
            }

            // Individual checkbox change - use event delegation
            $(document).off('change', '.question-row-checkbox').on('change', '.question-row-checkbox', function() {
                const totalCheckboxes = $('.question-row-checkbox').length;
                const checkedCheckboxes = $('.question-row-checkbox:checked').length;
                
                console.log('Question Bank Script: Individual checkbox changed. Total:', totalCheckboxes, 'Checked:', checkedCheckboxes);
                
                if (selectAllCheckbox) {
                    $(selectAllCheckbox).prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
                }
                toggleBulkDeleteButton();
            });
            console.log('Question Bank Script: Individual checkbox handlers attached');

            // Initialize toggle button state
            toggleBulkDeleteButton();
            console.log('Question Bank Script: Initial toggle button state set');

            // Single delete - open modal
            $(document).on('click', '.remove-question', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Remove question clicked');
                
                const questionId = $(this).data('question-id');
                const questionText = $(this).data('question-text');
                const row = $(this).closest('tr');
                
                console.log('Question ID:', questionId, 'Text:', questionText);
                
                window.currentDeleteQuestionId = questionId;
                window.currentDeleteQuestionRow = row;
                
                // Update modal content
                $('#deleteQuestionText').text(questionText || 'هذا السؤال');
                
                // Show modal
                const modalElement = document.getElementById('deleteQuestionModal');
                if (modalElement) {
                    const deleteModal = new bootstrap.Modal(modalElement);
                    deleteModal.show();
                } else {
                    console.error('Delete modal not found');
                }
            });

            // Confirm single delete
            const confirmDeleteQuestionBtn = document.getElementById('confirmDeleteQuestion');
            if (confirmDeleteQuestionBtn) {
                $(confirmDeleteQuestionBtn).off('click').on('click', function() {
                if (!window.currentDeleteQuestionId || !window.currentDeleteQuestionRow) {
                    console.error('No question selected for deletion');
                    return;
                }

                const questionId = window.currentDeleteQuestionId;
                const row = window.currentDeleteQuestionRow;

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
            url: '{{ url('admin/question-bank') }}/' + questionId,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'تم حذف السؤال بنجاح');
                    
                    // Remove row with animation
                    row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update questions count
                        const remainingCount = $('.question-row-checkbox').length;
                        $('#questions-count').text(remainingCount);
                        
                        // Update select all checkbox
                        if (remainingCount === 0) {
                            $('#select-all-questions-table').prop('checked', false);
                        }
                        
                        // Check if table is empty
                        if (remainingCount === 0) {
                            $('tbody').html('<tr><td colspan="11" class="text-center py-5"><div class="mb-3"><i class="fas fa-question-circle fs-48 text-muted"></i></div><p class="text-muted fs-16 mb-3">لا توجد أسئلة في البنك</p><a href="{{ route('question-bank.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>إضافة سؤال جديد</a></td></tr>');
                        }
                    });
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء حذف السؤال');
                btn.prop('disabled', false);
            },
            complete: function() {
                window.currentDeleteQuestionId = null;
                window.currentDeleteQuestionRow = null;
                
                // Ensure backdrop is removed
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
            }
        });
                });
                console.log('Question Bank Script: Confirm delete question handler attached');
            } else {
                console.warn('Question Bank Script: Confirm delete question button not found!');
            }

            // Bulk delete button handler - opens modal
            if (deleteSelectedBtn) {
                $(deleteSelectedBtn).off('click').on('click', function() {
                    const selectedQuestions = $('.question-row-checkbox:checked').map(function() {
                        return parseInt($(this).val());
                    }).get();
                    
                    if (selectedQuestions.length === 0) {
                        toastr.warning('يرجى اختيار سؤال واحد على الأقل');
                        return;
                    }

                    // Update modal content
                    $('#deleteMultipleQuestionsCount').text(selectedQuestions.length);

                    // Show modal
                    const modalElement = document.getElementById('deleteMultipleQuestionsModal');
                    if (modalElement) {
                        const deleteModal = new bootstrap.Modal(modalElement);
                        deleteModal.show();
                    } else {
                        console.error('Delete multiple modal not found');
                    }

                    // Store selected questions for deletion
                    window.selectedQuestionsForDeletion = selectedQuestions;
                });
                console.log('Question Bank Script: Bulk delete button handler attached');
            } else {
                console.warn('Question Bank Script: Delete selected button not found!');
            }

            // Confirm multiple delete - executes deletion
            const confirmDeleteMultipleBtn = document.getElementById('confirmDeleteMultipleQuestions');
            if (confirmDeleteMultipleBtn) {
                $(confirmDeleteMultipleBtn).off('click').on('click', function() {
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
                    const btn = $('#delete-selected-questions-btn');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>جاري الحذف...');

                    $.ajax({
                        url: '{{ route('question-bank.delete-multiple') }}',
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
                                                const remainingCount = $('.question-row-checkbox').length;
                                                $('#questions-count').text(remainingCount);
                                                
                                                // Reset checkboxes
                                                if (selectAllCheckbox) {
                                                    $(selectAllCheckbox).prop('checked', false);
                                                }
                                                toggleBulkDeleteButton();
                                                
                                                // Check if table is empty
                                                if (remainingCount === 0) {
                                                    $('tbody').html('<tr><td colspan="11" class="text-center py-5"><div class="mb-3"><i class="fas fa-question-circle fs-48 text-muted"></i></div><p class="text-muted fs-16 mb-3">لا توجد أسئلة في البنك</p><a href="{{ route('question-bank.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>إضافة سؤال جديد</a></td></tr>');
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء حذف الأسئلة');
                            btn.prop('disabled', false);
                            toggleBulkDeleteButton();
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
                console.log('Question Bank Script: Confirm delete multiple handler attached');
            } else {
                console.warn('Question Bank Script: Confirm delete multiple button not found!');
            }
            
            console.log('Question Bank Script: All handlers initialized successfully');
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeHandlers);
        } else {
            initializeHandlers();
        }

        // Also initialize on window load as fallback (for deferred scripts)
        window.addEventListener('load', function() {
            console.log('Question Bank Script: Window loaded, re-initializing handlers...');
            setTimeout(initializeHandlers, 100);
        });
    }

    // Start initialization
    let initAttempts = 0;
    const maxAttempts = 50; // 5 seconds max wait time
    
    function tryInit() {
        initAttempts++;
        if (initAttempts > maxAttempts) {
            console.error('Question Bank Script: Failed to load jQuery/Bootstrap after', maxAttempts * 100, 'ms');
            return;
        }
        initQuestionBankDelete();
    }

    // Try to initialize immediately
    tryInit();
    
    // Also try on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    }
    
    // And on window load as final fallback
    window.addEventListener('load', function() {
        setTimeout(tryInit, 200);
    });
})();
</script>
@stop
