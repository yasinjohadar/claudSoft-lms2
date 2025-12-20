@extends('admin.layouts.master')

@section('page-title')
    إدارة الأسئلة - {{ $section->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة أسئلة القسم: {{ $section->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course_id) }}">{{ $section->course->title }}</a></li>
                            <li class="breadcrumb-item active">إدارة الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content">
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importQuestionModal">
                        <i class="fas fa-file-import me-2"></i>استيراد من بنك الأسئلة
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createQuestionModal">
                        <i class="fas fa-plus me-2"></i>إنشاء سؤال جديد
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Current Questions -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">الأسئلة المرتبطة بالقسم ({{ $section->questions->count() }})</div>
                        </div>
                        <div class="card-body">
                            @if($section->questions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap" id="questionsTable">
                                        <thead>
                                            <tr>
                                                <th width="50">#</th>
                                                <th>السؤال</th>
                                                <th>النوع</th>
                                                <th>الدرجة</th>
                                                <th>إجباري</th>
                                                <th>الترتيب</th>
                                                <th width="150">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortableQuestions">
                                            @foreach($section->questions as $question)
                                                <tr data-question-id="{{ $question->id }}">
                                                    <td>
                                                        <i class="fas fa-grip-vertical handle" style="cursor: move;"></i>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-start">
                                                            <div>
                                                                <a href="{{ route('question-bank.show', $question->id) }}" target="_blank" class="fw-semibold">
                                                                    {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                                                </a>
                                                                @if($question->tags)
                                                                    <div class="mt-1">
                                                                        @foreach($question->tags as $tag)
                                                                            <span class="badge bg-secondary-transparent">{{ $tag }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-transparent">
                                                            {{ $question->questionType->display_name ?? 'غير محدد' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control form-control-sm question-grade"
                                                               data-question-id="{{ $question->id }}"
                                                               value="{{ $question->pivot->question_grade ?? $question->default_grade }}"
                                                               style="width: 80px;">
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input question-required" type="checkbox"
                                                                   data-question-id="{{ $question->id }}"
                                                                   {{ $question->pivot->is_required ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-transparent">{{ $question->pivot->question_order }}</span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-icon btn-danger remove-question"
                                                                data-question-id="{{ $question->id }}"
                                                                title="إزالة من القسم">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد أسئلة مرتبطة بهذا القسم بعد</p>
                                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importQuestionModal">
                                        استيراد من بنك الأسئلة
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createQuestionModal">
                                        إنشاء سؤال جديد
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Import Question Modal -->
    <div class="modal fade" id="importQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">استيراد سؤال من بنك الأسئلة</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="searchQuestions" class="form-control" placeholder="ابحث عن سؤال...">
                    </div>

                    @if($availableQuestions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>السؤال</th>
                                        <th>النوع</th>
                                        <th>الدرجة</th>
                                        <th>المصدر</th>
                                        <th width="100">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="questionsListBody">
                                    @foreach($availableQuestions as $question)
                                        <tr class="question-row" data-search-text="{{ strtolower(strip_tags($question->question_text)) }}">
                                            <td>
                                                {!! Str::limit(strip_tags($question->question_text), 150) !!}
                                                @if($question->tags)
                                                    <div class="mt-1">
                                                        @foreach($question->tags as $tag)
                                                            <span class="badge bg-secondary-transparent">{{ $tag }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-transparent">
                                                    {{ $question->questionType->display_name ?? 'غير محدد' }}
                                                </span>
                                            </td>
                                            <td>{{ $question->default_grade }}</td>
                                            <td>
                                                @if($question->course_id)
                                                    <span class="badge bg-success-transparent">{{ $question->course->title }}</span>
                                                @else
                                                    <span class="badge bg-info-transparent">عام</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary import-question-btn"
                                                        data-question-id="{{ $question->id }}"
                                                        data-question-grade="{{ $question->default_grade }}">
                                                    <i class="fas fa-plus me-1"></i>استيراد
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            لا توجد أسئلة متاحة للاستيراد. جميع الأسئلة في بنك الأسئلة تم إضافتها بالفعل.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Question Modal -->
    <div class="modal fade" id="createQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">إنشاء سؤال جديد</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">اختر نوع السؤال الذي تريد إنشاءه</p>

                    <div class="row g-3">
                        @foreach($questionTypes as $type)
                            @php
                                $icons = [
                                    'multiple_choice_single' => 'fa-dot-circle',
                                    'multiple_choice_multiple' => 'fa-check-double',
                                    'true_false' => 'fa-check-circle',
                                    'short_answer' => 'fa-font',
                                    'essay' => 'fa-pen-fancy',
                                    'matching' => 'fa-arrows-alt-h',
                                    'ordering' => 'fa-sort-numeric-down',
                                    'fill_blank' => 'fa-pen-square',
                                    'fill_blanks' => 'fa-pen-square',
                                    'numerical' => 'fa-calculator',
                                    'calculated' => 'fa-square-root-alt',
                                    'drag_drop' => 'fa-hand-pointer',
                                ];
                                $iconClass = $type->icon ?? ($icons[$type->name] ?? 'fa-question');
                                if (!str_starts_with($iconClass, 'fas ') && !str_starts_with($iconClass, 'far ') && !str_starts_with($iconClass, 'fab ')) {
                                    $iconClass = 'fas ' . $iconClass;
                                }
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <a href="{{ route('sections.questions.create', [$section->id, $type->name]) }}"
                                   class="card custom-card text-center card-hover h-100">
                                    <div class="card-body">
                                        <i class="{{ $iconClass }} fa-3x text-primary mb-3"></i>
                                        <h6 class="mb-2">{{ $type->display_name }}</h6>
                                        @if($type->description)
                                            <p class="text-muted small mb-0">{{ Str::limit($type->description, 60) }}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Question Confirmation Modal -->
    <div class="modal fade" id="removeQuestionModal" tabindex="-1" aria-labelledby="removeQuestionModalLabel" aria-hidden="true">
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
                    <h5 class="modal-title text-center mb-4 fw-bold" id="removeQuestionModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        إزالة السؤال من القسم
                    </h5>

                    <!-- Message -->
                    <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                        <div>
                            <strong>هل أنت متأكد من إزالة هذا السؤال من القسم؟</strong>
                            <div class="mt-2">
                                <span class="badge bg-primary fs-6" id="removeQuestionText">السؤال</span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                سيتم إزالة السؤال من هذا القسم فقط ولن يتم حذفه من بنك الأسئلة
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmRemoveQuestion">
                            <i class="fas fa-trash-alt me-2"></i>إزالة من القسم
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .bg-danger-transparent {
        background: rgba(220, 53, 69, 0.1) !important;
    }
</style>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    const sectionId = {{ $section->id }};
    const baseUrl = '{{ url("/") }}';
    
    console.log('Section ID:', sectionId);
    console.log('Base URL:', baseUrl);

    // Sortable for reordering questions
    @if($section->questions->count() > 0)
    const sortable = new Sortable(document.getElementById('sortableQuestions'), {
        animation: 150,
        handle: '.handle',
        onEnd: function(evt) {
            const questionIds = [];
            $('#sortableQuestions tr').each(function() {
                questionIds.push($(this).data('question-id'));
            });

            $.ajax({
                url: '{{ route("sections.questions.reorder", $section->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    questions: questionIds
                },
                success: function(response) {
                    if(response.success) {
                        showToast('success', response.message);
                        // Update order badges
                        $('#sortableQuestions tr').each(function(index) {
                            $(this).find('.badge.bg-info-transparent').text(index + 1);
                        });
                    }
                },
                error: function(xhr) {
                    showToast('error', 'حدث خطأ أثناء إعادة الترتيب');
                }
            });
        }
    });
    @endif

    // Import question
    $(document).on('click', '.import-question-btn', function() {
        const btn = $(this);
        const questionId = btn.data('question-id');
        const questionGrade = btn.data('question-grade');

        console.log('Import Question:', {
            questionId: questionId,
            questionGrade: questionGrade
        });

        if (!questionId) {
            showToast('error', 'معرف السؤال غير موجود');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>جاري الاستيراد...');

        // Prepare data with proper types
        const requestData = {
            _token: '{{ csrf_token() }}',
            question_id: parseInt(questionId, 10),
            is_required: 1  // Send as 1 for Laravel to convert to boolean
        };

        // Add question_grade only if it exists and is valid
        if (questionGrade && questionGrade !== '' && questionGrade !== null && questionGrade !== undefined) {
            const grade = parseFloat(questionGrade);
            if (!isNaN(grade) && grade >= 0) {
                requestData.question_grade = grade;
            }
        }

        console.log('Sending data:', requestData);
        console.log('Section ID:', sectionId);
        
        // Build URL - use route helper directly with section ID
        let importUrl = '{{ route("sections.questions.import", $section->id) }}';
        console.log('Import URL:', importUrl);

        $.ajax({
            url: importUrl,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: requestData,
            success: function(response) {
                console.log('Import Success:', response);
                if(response.success) {
                    showToast('success', response.message);
                    
                    // Remove the imported question from the modal list
                    const questionRow = btn.closest('tr');
                    questionRow.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if there are no more questions
                        if ($('#questionsListBody tr.question-row').length === 0) {
                            $('#questionsListBody').html(`
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            لا توجد أسئلة متاحة للاستيراد. جميع الأسئلة في بنك الأسئلة تم إضافتها بالفعل.
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                    });
                    
                    // Reload the main questions table via AJAX without closing modal
                    $.ajax({
                        url: window.location.href,
                        method: 'GET',
                        success: function(html) {
                            // Extract the questions table from the response
                            const $response = $(html);
                            const newTable = $response.find('#sortableQuestions').html();
                            
                            if (newTable) {
                                $('#sortableQuestions').html(newTable);
                                
                                // Update the count in the card header
                                const newCount = $response.find('.card-title').text().match(/\d+/);
                                if (newCount) {
                                    $('.card-title').text('الأسئلة المرتبطة بالقسم (' + newCount[0] + ')');
                                }
                                
                                // Reinitialize sortable if questions exist
                                if ($('#sortableQuestions tr').length > 0 && typeof Sortable !== 'undefined') {
                                    new Sortable(document.getElementById('sortableQuestions'), {
                                        animation: 150,
                                        handle: '.handle',
                                        onEnd: function(evt) {
                                            const questionIds = [];
                                            $('#sortableQuestions tr').each(function() {
                                                questionIds.push($(this).data('question-id'));
                                            });

                                            $.ajax({
                                                url: '{{ route("sections.questions.reorder", $section->id) }}',
                                                method: 'POST',
                                                data: {
                                                    _token: '{{ csrf_token() }}',
                                                    questions: questionIds
                                                },
                                                success: function(response) {
                                                    if(response.success) {
                                                        showToast('success', response.message);
                                                        $('#sortableQuestions tr').each(function(index) {
                                                            $(this).find('.badge.bg-info-transparent').text(index + 1);
                                                        });
                                                    }
                                                },
                                                error: function(xhr) {
                                                    showToast('error', 'حدث خطأ أثناء إعادة الترتيب');
                                                }
                                            });
                                        }
                                    });
                                }
                            }
                        },
                        error: function() {
                            // If AJAX fails, just reload the page
                            setTimeout(() => location.reload(), 500);
                        }
                    });
                    
                    // Reset button state
                    btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>استيراد');
                } else {
                    showToast('error', response.message || 'حدث خطأ أثناء استيراد السؤال');
                    btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>استيراد');
                }
            },
            error: function(xhr) {
                console.error('Import Error:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseJSON);
                console.error('Request Data:', {
                    question_id: questionId,
                    question_grade: questionGrade,
                    sectionId: sectionId
                });
                
                let errorMessage = 'حدث خطأ أثناء استيراد السؤال';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        // Get first error message
                        const firstErrorKey = Object.keys(errors)[0];
                        const firstError = errors[firstErrorKey];
                        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                } else if (xhr.status === 422) {
                    errorMessage = 'البيانات المرسلة غير صحيحة. يرجى التحقق من البيانات';
                } else if (xhr.status === 404) {
                    errorMessage = 'القسم أو السؤال غير موجود';
                } else if (xhr.status === 500) {
                    errorMessage = 'حدث خطأ في الخادم. يرجى المحاولة مرة أخرى';
                } else if (xhr.status === 0) {
                    errorMessage = 'فشل الاتصال بالخادم. يرجى التحقق من الاتصال';
                }
                
                showToast('error', errorMessage);
                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>استيراد');
            }
        });
    });

    // Remove question
    let currentRemoveQuestionId = null;
    let currentRemoveRow = null;

    $(document).on('click', '.remove-question', function() {
        const questionId = $(this).data('question-id');
        const row = $(this).closest('tr');
        const questionText = row.find('td:eq(1) a').text().trim() || 'هذا السؤال';
        
        currentRemoveQuestionId = questionId;
        currentRemoveRow = row;
        
        // Update modal content
        $('#removeQuestionText').text(questionText);
        
        // Show modal
        const removeModal = new bootstrap.Modal(document.getElementById('removeQuestionModal'));
        removeModal.show();
    });

    // Confirm remove
    $('#confirmRemoveQuestion').on('click', function() {
        if (!currentRemoveQuestionId || !currentRemoveRow) return;

        const questionId = currentRemoveQuestionId;
        const row = currentRemoveRow;

        // Hide modal
        const removeModal = bootstrap.Modal.getInstance(document.getElementById('removeQuestionModal'));
        removeModal.hide();

        $.ajax({
            url: baseUrl + '/admin/sections/' + sectionId + '/questions/' + questionId,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message);
                    row.fadeOut(300, function() {
                        $(this).remove();
                        if($('#sortableQuestions tr').length === 0) {
                            location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                showToast('error', 'حدث خطأ أثناء إزالة السؤال');
            },
            complete: function() {
                currentRemoveQuestionId = null;
                currentRemoveRow = null;
            }
        });
    });

    // Update question grade
    $(document).on('change', '.question-grade', function() {
        const input = $(this);
        const questionId = input.data('question-id');
        const grade = parseFloat(input.val()) || null;

        // Validate grade
        if (grade !== null && (isNaN(grade) || grade < 0)) {
            showToast('error', 'الدرجة يجب أن تكون رقماً موجباً');
            input.focus();
            return;
        }

        // Disable input during request
        input.prop('disabled', true);

        $.ajax({
            url: baseUrl + '/admin/sections/' + sectionId + '/questions/' + questionId + '/settings',
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}',
                question_grade: grade
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message || 'تم تحديث الدرجة بنجاح');
                    // Update input value from response if provided
                    if (response.data && response.data.question_grade !== undefined) {
                        input.val(response.data.question_grade);
                    }
                } else {
                    showToast('error', response.message || 'حدث خطأ أثناء التحديث');
                }
            },
            error: function(xhr) {
                console.error('Error updating question grade:', xhr);
                let errorMessage = 'حدث خطأ أثناء التحديث';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const firstErrorKey = Object.keys(errors)[0];
                    const firstError = errors[firstErrorKey];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                } else if (xhr.status === 404) {
                    errorMessage = 'السؤال غير موجود في هذا القسم';
                } else if (xhr.status === 422) {
                    errorMessage = 'الدرجة المدخلة غير صحيحة';
                }
                
                showToast('error', errorMessage);
            },
            complete: function() {
                // Re-enable input
                input.prop('disabled', false);
            }
        });
    });

    // Update question required
    $(document).on('change', '.question-required', function() {
        const checkbox = $(this);
        const questionId = checkbox.data('question-id');
        const isRequired = checkbox.is(':checked');

        // Disable checkbox during request
        checkbox.prop('disabled', true);

        $.ajax({
            url: baseUrl + '/admin/sections/' + sectionId + '/questions/' + questionId + '/settings',
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: {
                _token: '{{ csrf_token() }}',
                is_required: isRequired ? 1 : 0  // Send as 1 or 0 for Laravel to convert to boolean
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message || 'تم تحديث الإعداد بنجاح');
                    // Update checkbox state from response if provided
                    if (response.data && typeof response.data.is_required !== 'undefined') {
                        checkbox.prop('checked', response.data.is_required);
                    }
                } else {
                    showToast('error', response.message || 'حدث خطأ أثناء التحديث');
                    // Revert checkbox state on error
                    checkbox.prop('checked', !isRequired);
                }
            },
            error: function(xhr) {
                console.error('Error updating question required:', xhr);
                let errorMessage = 'حدث خطأ أثناء التحديث';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'السؤال غير موجود في هذا القسم';
                } else if (xhr.status === 422) {
                    errorMessage = 'البيانات المرسلة غير صحيحة';
                }
                
                showToast('error', errorMessage);
                // Revert checkbox state on error
                checkbox.prop('checked', !isRequired);
            },
            complete: function() {
                // Re-enable checkbox
                checkbox.prop('disabled', false);
            }
        });
    });

    // Search questions in import modal
    $('#searchQuestions').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        $('.question-row').each(function() {
            const rowText = $(this).data('search-text');
            if(rowText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    function showToast(type, message) {
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const toast = `
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
                <div class="toast align-items-center text-white ${bgColor} border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(toast);
        setTimeout(() => $('.toast').remove(), 3000);
    }
});
</script>
@stop
