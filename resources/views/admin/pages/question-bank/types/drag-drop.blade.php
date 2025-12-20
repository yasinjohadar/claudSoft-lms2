@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال سحب وإفلات
@stop

@section('css')
<style>
    .drop-zone {
        min-height: 60px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    .drop-zone:hover {
        border-color: #4f46e5;
        background: rgba(79, 70, 229, 0.05);
    }
    .drag-item {
        cursor: move;
        user-select: none;
    }
    .drag-item:active {
        opacity: 0.8;
    }
    .drop-target-preview {
        background: #e8f4fd;
        border: 2px dashed #0d6efd;
        border-radius: 4px;
        padding: 8px 12px;
        margin: 4px;
        display: inline-block;
        min-width: 100px;
        text-align: center;
        color: #6c757d;
        font-style: italic;
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-hand-pointer text-purple me-2"></i>
                    سؤال سحب وإفلات
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">سحب وإفلات</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('question-bank.store') }}" method="POST">
            @csrf
            <input type="hidden" name="question_type_id" value="{{ $questionType->id }}">

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>معلومات السؤال
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                    @if($selectedCourseId)
                                        <!-- Hidden input to send the course_id -->
                                        <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                        <select class="form-select" disabled>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                    <select name="difficulty" class="form-select" required>
                                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">تعليمات السؤال <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="3" placeholder="مثال: اسحب الكلمات إلى أماكنها الصحيحة..." required>{{ old('question_text') }}</textarea>
                                    @error('question_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الدرجة <span class="text-danger">*</span></label>
                                    <input type="number" name="default_grade" class="form-control"
                                           value="{{ old('default_grade', 1) }}" min="0.5" step="0.5" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الوسوم</label>
                                    <input type="text" name="tags" class="form-control"
                                           placeholder="مثال: لغة عربية, قواعد" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drop Zones (Targets) -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-bullseye me-2 text-danger"></i>مناطق الإفلات (الأهداف)
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" id="add-zone-btn">
                                <i class="fas fa-plus me-1"></i>إضافة منطقة
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                أضف المناطق التي سيقوم الطالب بإفلات العناصر فيها. كل منطقة لها إجابة صحيحة واحدة.
                            </div>

                            <div id="zones-container">
                                <!-- Zone 1 -->
                                <div class="zone-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-danger fs-6 zone-number">1</span>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[1][label]" class="form-control"
                                                   placeholder="تسمية المنطقة (مثال: الفاعل)" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[1][correct_item]" class="form-control"
                                                   placeholder="العنصر الصحيح لهذه المنطقة" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-zone-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Zone 2 -->
                                <div class="zone-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-danger fs-6 zone-number">2</span>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[2][label]" class="form-control"
                                                   placeholder="تسمية المنطقة (مثال: المفعول به)" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[2][correct_item]" class="form-control"
                                                   placeholder="العنصر الصحيح لهذه المنطقة" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-zone-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Zone 3 -->
                                <div class="zone-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-danger fs-6 zone-number">3</span>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[3][label]" class="form-control"
                                                   placeholder="تسمية المنطقة" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="drop_zones[3][correct_item]" class="form-control"
                                                   placeholder="العنصر الصحيح لهذه المنطقة" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-zone-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Draggable Items (Distractors) -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-hand-rock me-2 text-primary"></i>عناصر إضافية (مشتتات)
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="add-distractor-btn">
                                <i class="fas fa-plus me-1"></i>إضافة مشتت
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                أضف عناصر إضافية خاطئة لزيادة صعوبة السؤال (اختياري). هذه العناصر ستظهر مع العناصر الصحيحة.
                            </div>

                            <div id="distractors-container">
                                <!-- Distractor items will be added here -->
                            </div>

                            <p class="text-muted mb-0 mt-2">
                                <i class="fas fa-lightbulb me-1"></i>
                                <small>العناصر الصحيحة ستُضاف تلقائياً من مناطق الإفلات أعلاه</small>
                            </p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-eye me-2 text-info"></i>معاينة السؤال
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>العناصر القابلة للسحب:</strong>
                                <div class="drop-zone mt-2" id="preview-items">
                                    <span class="text-muted">ستظهر العناصر هنا...</span>
                                </div>
                            </div>
                            <div>
                                <strong>مناطق الإفلات:</strong>
                                <div class="row mt-2" id="preview-zones">
                                    <span class="text-muted">ستظهر المناطق هنا...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-lightbulb me-2 text-warning"></i>شرح الإجابة (اختياري)
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea name="explanation" class="form-control" rows="3"
                                      placeholder="اكتب شرحاً يظهر للطالب بعد الإجابة...">{{ old('explanation') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="shuffle_items"
                                       id="shuffle_items" {{ old('shuffle_items', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="shuffle_items">
                                    خلط ترتيب العناصر
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="instant_feedback"
                                       id="instant_feedback">
                                <label class="form-check-label" for="instant_feedback">
                                    إظهار التغذية الراجعة الفورية
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    السؤال نشط
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_reusable"
                                       id="is_reusable" {{ old('is_reusable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_reusable">
                                    قابل لإعادة الاستخدام
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Scoring -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-star me-2 text-warning"></i>طريقة التقييم
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="scoring_method"
                                       value="all_or_nothing" id="scoring_all" checked>
                                <label class="form-check-label" for="scoring_all">
                                    <strong>الكل أو لا شيء</strong>
                                    <br><small class="text-muted">الدرجة الكاملة فقط إذا كانت جميع الإجابات صحيحة</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scoring_method"
                                       value="partial" id="scoring_partial">
                                <label class="form-check-label" for="scoring_partial">
                                    <strong>درجات جزئية</strong>
                                    <br><small class="text-muted">درجة لكل إجابة صحيحة</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-save me-2"></i>حفظ السؤال
                            </button>
                            <a href="{{ route('question-bank.create') }}" class="btn btn-light w-100">
                                <i class="fas fa-arrow-right me-2"></i>تغيير نوع السؤال
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@stop

@section('script')
<script>
let zoneCount = 3;
let distractorCount = 0;

$(document).ready(function() {
    // Add zone button
    $('#add-zone-btn').click(function() {
        zoneCount++;
        const zoneHtml = `
            <div class="zone-item mb-3 p-3 border rounded bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <span class="badge bg-danger fs-6 zone-number">${zoneCount}</span>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="drop_zones[${zoneCount}][label]" class="form-control"
                               placeholder="تسمية المنطقة" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="drop_zones[${zoneCount}][correct_item]" class="form-control"
                               placeholder="العنصر الصحيح لهذه المنطقة" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-zone-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#zones-container').append(zoneHtml);
        updatePreview();
    });

    // Remove zone
    $(document).on('click', '.remove-zone-btn', function() {
        if ($('.zone-item').length > 2) {
            $(this).closest('.zone-item').remove();
            updateZoneNumbers();
            updatePreview();
        } else {
            alert('يجب أن يكون هناك منطقتان على الأقل');
        }
    });

    // Add distractor button
    $('#add-distractor-btn').click(function() {
        distractorCount++;
        const distractorHtml = `
            <div class="distractor-item mb-2 p-2 border rounded">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-times-circle text-secondary"></i>
                    </div>
                    <div class="col">
                        <input type="text" name="distractors[]" class="form-control form-control-sm"
                               placeholder="عنصر مشتت (إجابة خاطئة)" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-distractor-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#distractors-container').append(distractorHtml);
        updatePreview();
    });

    // Remove distractor
    $(document).on('click', '.remove-distractor-btn', function() {
        $(this).closest('.distractor-item').remove();
        updatePreview();
    });

    // Update preview on input change
    $(document).on('input', 'input[name^="drop_zones"], input[name="distractors[]"]', function() {
        updatePreview();
    });

    // Initial preview update
    updatePreview();
});

function updateZoneNumbers() {
    $('.zone-item').each(function(index) {
        $(this).find('.zone-number').text(index + 1);
    });
}

function updatePreview() {
    // Update items preview
    let items = [];
    $('input[name$="[correct_item]"]').each(function() {
        const val = $(this).val().trim();
        if (val) {
            items.push(`<span class="badge bg-primary me-2 mb-2 p-2 drag-item">${val}</span>`);
        }
    });
    $('input[name="distractors[]"]').each(function() {
        const val = $(this).val().trim();
        if (val) {
            items.push(`<span class="badge bg-secondary me-2 mb-2 p-2 drag-item">${val}</span>`);
        }
    });

    if (items.length > 0) {
        $('#preview-items').html(items.join(''));
    } else {
        $('#preview-items').html('<span class="text-muted">ستظهر العناصر هنا...</span>');
    }

    // Update zones preview
    let zones = [];
    $('input[name$="[label]"]').each(function() {
        const val = $(this).val().trim();
        if (val) {
            zones.push(`
                <div class="col-md-4 mb-2">
                    <div class="drop-zone text-center">
                        <strong class="d-block mb-2">${val}</strong>
                        <div class="drop-target-preview">اسحب هنا</div>
                    </div>
                </div>
            `);
        }
    });

    if (zones.length > 0) {
        $('#preview-zones').html(zones.join(''));
    } else {
        $('#preview-zones').html('<span class="text-muted">ستظهر المناطق هنا...</span>');
    }
}
</script>
@stop
