@extends('admin.layouts.master')

@section('page-title')
    إنشاء سؤال ترتيب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-sort-numeric-down text-pink me-2"></i>
                    سؤال الترتيب
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.create') }}">اختر النوع</a></li>
                        <li class="breadcrumb-item active">ترتيب</li>
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
                                    <select name="difficulty_level" class="form-select @error('difficulty_level') is-invalid @enderror" required>
                                        <option value="">اختر مستوى الصعوبة</option>
                                        <option value="easy" {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>سهل</option>
                                        <option value="medium" {{ old('difficulty_level', 'medium') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                        <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>صعب</option>
                                        <option value="expert" {{ old('difficulty_level') == 'expert' ? 'selected' : '' }}>خبير</option>
                                    </select>
                                    @error('difficulty_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">تعليمات السؤال <span class="text-danger">*</span></label>
                                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror"
                                              rows="3" placeholder="مثال: رتب الأحداث التالية من الأقدم إلى الأحدث..." required>{{ old('question_text') }}</textarea>
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
                                           placeholder="مثال: تاريخ, أحداث" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items to Order -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-sort-numeric-down me-2 text-pink"></i>العناصر المطلوب ترتيبها
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="add-item-btn">
                                <i class="fas fa-plus me-1"></i>إضافة عنصر
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                أدخل العناصر بالترتيب الصحيح. سيتم خلطها تلقائياً عند عرضها للطالب.
                            </div>

                            <div id="items-container">
                                <!-- Item 1 -->
                                <div class="order-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-primary fs-6 order-number">1</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="order_items[]" class="form-control" placeholder="العنصر الأول..." required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Item 2 -->
                                <div class="order-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-primary fs-6 order-number">2</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="order_items[]" class="form-control" placeholder="العنصر الثاني..." required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Item 3 -->
                                <div class="order-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-primary fs-6 order-number">3</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="order_items[]" class="form-control" placeholder="العنصر الثالث..." required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Item 4 -->
                                <div class="order-item mb-3 p-3 border rounded bg-light">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-primary fs-6 order-number">4</span>
                                        </div>
                                        <div class="col">
                                            <input type="text" name="order_items[]" class="form-control" placeholder="العنصر الرابع..." required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
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
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    السؤال نشط
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
$(document).ready(function() {
    // Add item button
    $('#add-item-btn').click(function() {
        const itemCount = $('.order-item').length + 1;
        const itemHtml = `
            <div class="order-item mb-3 p-3 border rounded bg-light">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <span class="badge bg-primary fs-6 order-number">${itemCount}</span>
                    </div>
                    <div class="col">
                        <input type="text" name="order_items[]" class="form-control" placeholder="العنصر ${itemCount}..." required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#items-container').append(itemHtml);
    });

    // Remove item
    $(document).on('click', '.remove-item-btn', function() {
        if ($('.order-item').length > 2) {
            $(this).closest('.order-item').remove();
            updateItemNumbers();
        } else {
            alert('يجب أن يكون هناك عنصران على الأقل');
        }
    });

    function updateItemNumbers() {
        $('.order-item').each(function(index) {
            $(this).find('.order-number').text(index + 1);
        });
    }
});
</script>
@stop
