@extends('admin.layouts.master')

@section('page-title')
    استيراد أسئلة - {{ $questionModule->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">استيراد من بنك الأسئلة</h4>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-modules.index') }}">وحدات الأسئلة</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-modules.manage-questions', $questionModule->id) }}">{{ $questionModule->title }}</a></li>
                            <li class="breadcrumb-item active">استيراد أسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-modules.manage-questions', $questionModule->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للوحدة
                    </a>
                </div>
            </div>

            <!-- Module Info -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>الوحدة:</strong> {{ $questionModule->title }}
                <span class="mx-2">|</span>
                <strong>الأسئلة الحالية:</strong> {{ $questionModule->questions->count() }}
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-filter me-2"></i>فلترة الأسئلة
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('question-modules.import-questions', $questionModule->id) }}" id="filterForm">
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
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->title }}
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
                            <div class="col-md-2">
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
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('question-modules.import-questions', $questionModule->id) }}" class="btn btn-outline-secondary w-100">
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
                    <div class="card-title">
                        الأسئلة المتاحة للاستيراد ({{ $availableQuestions->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">السؤال</th>
                                    <th width="10%">النوع</th>
                                    <th width="12%">اللغات</th>
                                    <th width="10%">الكورس</th>
                                    <th width="8%">الصعوبة</th>
                                    <th width="8%">الدرجة</th>
                                    <th width="17%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableQuestions as $question)
                                    <tr id="question-row-{{ $question->id }}">
                                        <td>{{ $loop->iteration + ($availableQuestions->currentPage() - 1) * $availableQuestions->perPage() }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="{{ strip_tags($question->question_text) }}">
                                                {!! Str::limit(strip_tags($question->question_text), 80) !!}
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
                                            <span class="badge bg-primary-transparent">
                                                {{ Str::limit($question->course->title ?? 'عام', 15) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($question->difficulty_level == 'easy')
                                                <span class="badge bg-success">سهل</span>
                                            @elseif($question->difficulty_level == 'medium')
                                                <span class="badge bg-warning">متوسط</span>
                                            @else
                                                <span class="badge bg-danger">صعب</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $question->default_grade }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number"
                                                       class="form-control form-control-sm grade-input"
                                                       value="{{ $question->default_grade }}"
                                                       step="0.5"
                                                       min="0"
                                                       style="width: 70px;"
                                                       id="grade-{{ $question->id }}">
                                                <button type="button"
                                                        class="btn btn-sm btn-success import-question"
                                                        data-question-id="{{ $question->id }}"
                                                        data-default-grade="{{ $question->default_grade }}">
                                                    <i class="fas fa-plus me-1"></i>إضافة
                                                </button>
                                                <a href="{{ route('question-bank.show', $question->id) }}"
                                                   class="btn btn-sm btn-info"
                                                   target="_blank"
                                                   title="عرض السؤال">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-search fa-3x text-muted"></i>
                                            </div>
                                            <p class="text-muted fs-16 mb-3">لا توجد أسئلة متاحة للاستيراد</p>
                                            <p class="text-muted small">جميع الأسئلة مضافة بالفعل للوحدة أو لا توجد أسئلة تطابق معايير البحث</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($availableQuestions->hasPages())
                    <div class="card-footer">
                        {{ $availableQuestions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
$(document).ready(function() {
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-left",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "rtl": true
    };

    // Import question
    $(document).on('click', '.import-question', function() {
        const btn = $(this);
        const questionId = btn.data('question-id');
        const grade = $('#grade-' + questionId).val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route('question-modules.add-question', $questionModule->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_id: questionId,
                question_grade: grade
            },
            success: function(response) {
                toastr.success('تم إضافة السؤال بنجاح');
                // Remove the row or mark as added
                $('#question-row-' + questionId).fadeOut(300, function() {
                    $(this).remove();
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>إضافة');
                toastr.error(xhr.responseJSON?.message || 'حدث خطأ أثناء إضافة السؤال');
            }
        });
    });
});
</script>
@stop
