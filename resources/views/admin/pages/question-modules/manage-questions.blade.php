@extends('admin.layouts.master')

@section('page-title')
    إدارة الأسئلة - {{ $questionModule->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">إدارة الأسئلة</h4>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-modules.index') }}">وحدات الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $questionModule->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Module Info Card -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">معلومات الوحدة</div>
                </div>
                <div class="card-body">
                    <p><strong>العنوان:</strong> {{ $questionModule->title }}</p>
                    @if($questionModule->description)
                        <p><strong>الوصف:</strong> {{ $questionModule->description }}</p>
                    @endif
                    <p><strong>عدد الأسئلة:</strong> {{ $questionModule->questions->count() }}</p>
                    <p><strong>إجمالي الدرجات:</strong> {{ $questionModule->getTotalGrade() }}</p>
                </div>
            </div>

            <!-- Questions Management -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">الأسئلة المرتبطة بالوحدة ({{ $questionModule->questions->count() }})</div>
                            <div>
                                <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#createQuestionModal">
                                    <i class="fas fa-plus-circle me-1"></i>إنشاء سؤال جديد
                                </button>
                                <a href="{{ route('question-modules.import-questions', $questionModule->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>استيراد من بنك الأسئلة
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($questionModule->questions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">#</th>
                                                <th>السؤال</th>
                                                <th>النوع</th>
                                                <th>الدرجة</th>
                                                <th width="150">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="questions-sortable">
                                            @foreach($questionModule->questions as $question)
                                                <tr data-id="{{ $question->id }}">
                                                    <td><i class="fas fa-grip-vertical handle" style="cursor: move;"></i></td>
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
                                                               value="{{ $question->pivot->question_grade }}"
                                                               data-question-id="{{ $question->id }}"
                                                               step="0.5"
                                                               min="0"
                                                               style="width: 80px;">
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger remove-question"
                                                                data-question-id="{{ $question->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد أسئلة مرتبطة بهذه الوحدة بعد</p>
                                    <a href="{{ route('question-modules.import-questions', $questionModule->id) }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>استيراد سؤال من بنك الأسئلة
                                    </a>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">استيراد سؤال من بنك الأسئلة</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap table-hover">
                            <thead>
                                <tr>
                                    <th>السؤال</th>
                                    <th>النوع</th>
                                    <th>الدرجة</th>
                                    <th width="100">إضافة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableQuestions as $question)
                                    <tr>
                                        <td>{!! Str::limit(strip_tags($question->question_text), 80) !!}</td>
                                        <td><span class="badge bg-info-transparent">{{ $question->questionType->display_name }}</span></td>
                                        <td>{{ $question->default_grade }}</td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-success import-question"
                                                    data-question-id="{{ $question->id }}"
                                                    data-grade="{{ $question->default_grade }}">
                                                <i class="fas fa-plus"></i> إضافة
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">لا توجد أسئلة متاحة للاستيراد</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Question Modal -->
    <div class="modal fade" id="createQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">إنشاء سؤال جديد</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">اختر نوع السؤال الذي تريد إنشاءه:</p>
                    <div class="row g-3">
                        @foreach($questionTypes as $type)
                            <div class="col-md-4">
                                <a href="{{ route('question-bank.create.type', $type->name) }}?question_module_id={{ $questionModule->id }}"
                                   class="card custom-card text-center hover-card"
                                   style="text-decoration: none; transition: all 0.3s;">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <i class="{{ $type->icon ?? 'fas fa-question-circle' }} fa-3x text-primary"></i>
                                        </div>
                                        <h6 class="card-title mb-2">{{ $type->display_name }}</h6>
                                        @if($type->description)
                                            <p class="card-text text-muted small">{{ $type->description }}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
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
                    url: '{{ route('question-modules.reorder-questions', $questionModule->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        order: order
                    },
                    success: function(response) {
                        toastr.success('تم إعادة ترتيب الأسئلة بنجاح');
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء إعادة الترتيب');
                        location.reload();
                    }
                });
            }
        });
    }

    // Import question
    $(document).on('click', '.import-question', function() {
        const questionId = $(this).data('question-id');
        const grade = $(this).data('grade');

        console.log('Import Question - ID:', questionId, 'Grade:', grade);

        $.ajax({
            url: '{{ route('question-modules.add-question', $questionModule->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_id: questionId,
                question_grade: grade
            },
            success: function(response) {
                console.log('Success:', response);
                toastr.success('تم إضافة السؤال بنجاح');
                location.reload();
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                console.error('Response:', xhr.responseJSON);
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء إضافة السؤال');
            }
        });
    });

    // Remove question
    $(document).on('click', '.remove-question', function() {
        if (!confirm('هل أنت متأكد من إزالة هذا السؤال من الوحدة؟')) return;

        const questionId = $(this).data('question-id');

        $.ajax({
            url: '{{ route('question-modules.remove-question', [$questionModule->id, ':questionId']) }}'.replace(':questionId', questionId),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success('تم إزالة السؤال بنجاح');
                location.reload();
            },
            error: function() {
                toastr.error('حدث خطأ أثناء إزالة السؤال');
            }
        });
    });

    // Update question grade
    $(document).on('change', '.question-grade', function() {
        const questionId = $(this).data('question-id');
        const grade = $(this).val();

        $.ajax({
            url: '{{ route('question-modules.update-question-settings', [$questionModule->id, ':questionId']) }}'.replace(':questionId', questionId),
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                grade: grade
            },
            success: function(response) {
                toastr.success('تم تحديث درجة السؤال بنجاح');
            },
            error: function() {
                toastr.error('حدث خطأ أثناء تحديث الدرجة');
            }
        });
    });
});
</script>
@stop
