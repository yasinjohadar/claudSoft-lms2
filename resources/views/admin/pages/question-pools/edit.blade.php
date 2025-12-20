@extends('admin.layouts.master')

@section('page-title')
    تعديل مجموعة الأسئلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل مجموعة الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-pools.index') }}">مجموعات الأسئلة</a></li>
                            <li class="breadcrumb-item active">تعديل المجموعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('question-pools.update', $pool->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-info-circle me-2 text-primary"></i>المعلومات الأساسية
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المجموعة <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $pool->name) }}" placeholder="مثال: أسئلة الجبر - الفصل الأول" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $pool->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف (اختياري)</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3" placeholder="وصف مختصر عن محتوى المجموعة...">{{ old('description', $pool->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" value="1" {{ old('is_active', $pool->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <i class="fas fa-check-circle text-success me-1"></i>المجموعة نشطة
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Questions -->
                <div class="card custom-card mb-4">
                    <div class="card-header bg-success-transparent">
                        <div class="card-title mb-0">
                            <i class="fas fa-check-circle me-2 text-success"></i>الأسئلة الحالية ({{ $pool->questions->count() }})
                        </div>
                    </div>
                    <div class="card-body">
                        @if($pool->questions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all-current" class="form-check-input">
                                            </th>
                                            <th>نص السؤال</th>
                                            <th>النوع</th>
                                            <th>الصعوبة</th>
                                            <th>الدرجة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pool->questions as $question)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="remove_question_ids[]"
                                                           value="{{ $question->id }}"
                                                           class="form-check-input remove-question-checkbox">
                                                </td>
                                                <td>{{ Str::limit($question->question_text, 80) }}</td>
                                                <td>
                                                    <span class="badge bg-info-transparent">
                                                        {{ $question->questionType->display_name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($question->difficulty == 'easy')
                                                        <span class="badge bg-success">سهل</span>
                                                    @elseif($question->difficulty == 'medium')
                                                        <span class="badge bg-warning">متوسط</span>
                                                    @else
                                                        <span class="badge bg-danger">صعب</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $question->points }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-danger" id="remove-selected-btn">
                                    <i class="fas fa-trash me-1"></i>إزالة المحدد من المجموعة
                                </button>
                                <small class="text-muted ms-3">
                                    محدد: <strong id="remove-count">0</strong>
                                </small>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted mb-0">لا توجد أسئلة في هذه المجموعة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Add New Questions -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>إضافة أسئلة جديدة
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">نوع السؤال</label>
                                <select id="filter-type" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الصعوبة</label>
                                <select id="filter-difficulty" class="form-select">
                                    <option value="">جميع المستويات</option>
                                    <option value="easy">سهل</option>
                                    <option value="medium">متوسط</option>
                                    <option value="hard">صعب</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" id="filter-search" class="form-control" placeholder="ابحث في نص السؤال...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="button" class="btn btn-primary" id="apply-filters">
                                    <i class="fas fa-filter me-1"></i>تطبيق الفلتر
                                </button>
                            </div>
                        </div>

                        <!-- Available Questions Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="select-all-new" class="form-check-input">
                                        </th>
                                        <th>نص السؤال</th>
                                        <th>النوع</th>
                                        <th>الصعوبة</th>
                                        <th>الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody id="questions-table-body">
                                    @foreach($availableQuestions as $question)
                                        <tr class="question-row"
                                            data-type="{{ $question->question_type_id }}"
                                            data-difficulty="{{ $question->difficulty }}"
                                            data-text="{{ strtolower($question->question_text) }}">
                                            <td>
                                                <input type="checkbox" name="add_question_ids[]"
                                                       value="{{ $question->id }}"
                                                       class="form-check-input add-question-checkbox">
                                            </td>
                                            <td>{{ Str::limit($question->question_text, 80) }}</td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    {{ $question->questionType->display_name }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($question->difficulty == 'easy')
                                                    <span class="badge bg-success">سهل</span>
                                                @elseif($question->difficulty == 'medium')
                                                    <span class="badge bg-warning">متوسط</span>
                                                @else
                                                    <span class="badge bg-danger">صعب</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $question->points }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                تم اختيار <strong id="add-count">0</strong> سؤال لإضافتها
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Pool Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-cog me-2 text-warning"></i>إعدادات المجموعة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">عدد الأسئلة المستخدمة في كل اختبار (اختياري)</label>
                                <input type="number" name="questions_per_quiz" class="form-control"
                                       value="{{ old('questions_per_quiz', $pool->questions_per_quiz) }}" min="1"
                                       placeholder="اترك فارغاً لاستخدام جميع الأسئلة">
                                <small class="text-muted">سيتم اختيار الأسئلة بشكل عشوائي من المجموعة</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الترتيب (للعرض)</label>
                                <input type="number" name="order" class="form-control"
                                       value="{{ old('order', $pool->order) }}" min="0">
                                <small class="text-muted">يستخدم لترتيب المجموعات في القوائم</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('question-pools.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('scripts')
<script>
$(document).ready(function() {
    // Select all current questions
    $('#select-all-current').change(function() {
        $('.remove-question-checkbox').prop('checked', $(this).prop('checked'));
        updateRemoveCount();
    });

    // Select all new questions
    $('#select-all-new').change(function() {
        $('.add-question-checkbox:visible').prop('checked', $(this).prop('checked'));
        updateAddCount();
    });

    // Individual checkboxes
    $(document).on('change', '.remove-question-checkbox', function() {
        updateRemoveCount();
    });

    $(document).on('change', '.add-question-checkbox', function() {
        updateAddCount();
    });

    // Remove selected button
    $('#remove-selected-btn').click(function() {
        const count = $('.remove-question-checkbox:checked').length;
        if (count === 0) {
            alert('الرجاء تحديد أسئلة لإزالتها');
            return;
        }
        if (!confirm(`هل أنت متأكد من إزالة ${count} سؤال من المجموعة؟`)) {
            return;
        }
    });

    // Update counts
    function updateRemoveCount() {
        const count = $('.remove-question-checkbox:checked').length;
        $('#remove-count').text(count);
    }

    function updateAddCount() {
        const count = $('.add-question-checkbox:checked').length;
        $('#add-count').text(count);
    }

    // Apply filters
    $('#apply-filters').click(function() {
        applyFilters();
    });

    // Filter on enter
    $('#filter-search').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            applyFilters();
        }
    });

    function applyFilters() {
        const typeFilter = $('#filter-type').val();
        const difficultyFilter = $('#filter-difficulty').val();
        const searchFilter = $('#filter-search').val().toLowerCase();

        $('.question-row').each(function() {
            const $row = $(this);
            const type = $row.data('type');
            const difficulty = $row.data('difficulty');
            const text = $row.data('text');

            let show = true;

            // Type filter
            if (typeFilter && type != typeFilter) {
                show = false;
            }

            // Difficulty filter
            if (difficultyFilter && difficulty != difficultyFilter) {
                show = false;
            }

            // Search filter
            if (searchFilter && !text.includes(searchFilter)) {
                show = false;
            }

            if (show) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }
});
</script>
@endsection
