@extends('admin.layouts.master')

@section('page-title')
    إضافة اختبار جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة اختبار جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">إضافة اختبار</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('quizzes.store') }}" method="POST">
                @csrf

                <!-- إخفاء section_id للإرسال -->
                @if(isset($selectedSection) && $selectedSection)
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                @endif

                <!-- رسالة تنبيه إذا تم التحديد من القسم -->
                @if(isset($selectedSection) && $selectedSection)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>إضافة اختبار للقسم:</strong> {{ $selectedSection->title }} -
                        <strong>الكورس:</strong> {{ $selectedCourse->title }}
                    </div>
                @endif

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
                                <label class="form-label">عنوان الاختبار <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" placeholder="مثال: اختبار الوحدة الأولى" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror"
                                        {{ isset($selectedSection) && $selectedSection ? 'disabled' : '' }} required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ old('course_id', $selectedCourse?->id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <!-- إرسال القيمة حتى لو disabled -->
                                @if(isset($selectedSection) && $selectedSection)
                                    <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">
                                @endif
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(!isset($selectedSection) || !$selectedSection)
                                <div class="col-md-3">
                                    <label class="form-label">الدرس (اختياري)</label>
                                    <select name="lesson_id" id="lesson_id" class="form-select @error('lesson_id') is-invalid @enderror">
                                        <option value="">لا يوجد دروس مرتبطة</option>
                                    </select>
                                    <small class="text-muted">الدروس مرتبطة بالأقسام عبر course_modules</small>
                                    @error('lesson_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">نوع الاختبار <span class="text-danger">*</span></label>
                                <select name="quiz_type" class="form-select @error('quiz_type') is-invalid @enderror" required>
                                    <option value="practice" {{ old('quiz_type') == 'practice' ? 'selected' : '' }}>تدريبي (Practice)</option>
                                    <option value="graded" {{ old('quiz_type', 'graded') == 'graded' ? 'selected' : '' }}>مُقيّم (Graded)</option>
                                    <option value="final_exam" {{ old('quiz_type') == 'final_exam' ? 'selected' : '' }}>اختبار نهائي (Final Exam)</option>
                                    <option value="survey" {{ old('quiz_type') == 'survey' ? 'selected' : '' }}>استبيان (Survey)</option>
                                </select>
                                <small class="text-muted">التدريبي: للممارسة فقط، المُقيّم: يحتسب للدرجة النهائية</small>
                                @error('quiz_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الترتيب</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', 0) }}" min="0">
                                <small class="text-muted">الترتيب في عرض الاختبارات (الأصغر يظهر أولاً)</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3" placeholder="وصف مختصر عن الاختبار...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">التعليمات</label>
                                <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror"
                                          rows="4" placeholder="تعليمات الاختبار للطلاب...">{{ old('instructions') }}</textarea>
                                <small class="text-muted">سيتم عرض هذه التعليمات للطالب قبل بدء الاختبار</small>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grading Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-star me-2 text-warning"></i>إعدادات الدرجات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">درجة النجاح (%) <span class="text-danger">*</span></label>
                                <input type="number" name="passing_grade" class="form-control @error('passing_grade') is-invalid @enderror"
                                       value="{{ old('passing_grade', 60) }}" min="0" max="100" step="0.01" required>
                                <small class="text-muted">النسبة المئوية المطلوبة للنجاح (0-100)</small>
                                @error('passing_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">وقت عرض الإجابات الصحيحة <span class="text-danger">*</span></label>
                                <select name="show_correct_answers_after" class="form-select @error('show_correct_answers_after') is-invalid @enderror" required>
                                    <option value="immediately" {{ old('show_correct_answers_after') == 'immediately' ? 'selected' : '' }}>فوراً بعد التسليم</option>
                                    <option value="after_due" {{ old('show_correct_answers_after', 'after_due') == 'after_due' ? 'selected' : '' }}>بعد موعد التسليم</option>
                                    <option value="after_graded" {{ old('show_correct_answers_after') == 'after_graded' ? 'selected' : '' }}>بعد التصحيح</option>
                                    <option value="never" {{ old('show_correct_answers_after') == 'never' ? 'selected' : '' }}>عدم العرض</option>
                                </select>
                                @error('show_correct_answers_after')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">طريقة عرض النتيجة <span class="text-danger">*</span></label>
                                <select name="feedback_mode" class="form-select @error('feedback_mode') is-invalid @enderror" required>
                                    <option value="immediate" {{ old('feedback_mode') == 'immediate' ? 'selected' : '' }}>فورية (مع كل سؤال)</option>
                                    <option value="after_submission" {{ old('feedback_mode', 'after_submission') == 'after_submission' ? 'selected' : '' }}>بعد التسليم</option>
                                    <option value="after_due" {{ old('feedback_mode') == 'after_due' ? 'selected' : '' }}>بعد موعد الاستحقاق</option>
                                    <option value="manual" {{ old('feedback_mode') == 'manual' ? 'selected' : '' }}>يدوي (بعد التصحيح اليدوي)</option>
                                </select>
                                @error('feedback_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_grade_immediately"
                                           id="show_grade_immediately" {{ old('show_grade_immediately') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_grade_immediately">
                                        عرض الدرجة فوراً بعد التسليم
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="allow_review"
                                           id="allow_review" {{ old('allow_review', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_review">
                                        السماح بمراجعة الاختبار
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="show_correct_answers"
                                           id="show_correct_answers" {{ old('show_correct_answers', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_correct_answers">
                                        عرض الإجابات الصحيحة
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Settings -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-cog me-2 text-info"></i>إعدادات الاختبار
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">الوقت المحدد (بالدقائق)</label>
                                <input type="number" name="time_limit" class="form-control @error('time_limit') is-invalid @enderror"
                                       value="{{ old('time_limit') }}" min="1" placeholder="غير محدد">
                                <small class="text-muted">اتركه فارغاً إذا لم يكن هناك حد زمني</small>
                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">عدد المحاولات المسموحة</label>
                                <input type="number" name="attempts_allowed" class="form-control @error('attempts_allowed') is-invalid @enderror"
                                       value="{{ old('attempts_allowed') }}" min="1" placeholder="غير محدود">
                                <small class="text-muted">اتركه فارغاً للسماح بمحاولات غير محدودة</small>
                                @error('attempts_allowed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="shuffle_questions"
                                           id="shuffle_questions" {{ old('shuffle_questions') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_questions">
                                        <i class="fas fa-random me-1"></i>ترتيب الأسئلة عشوائياً
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="shuffle_answers"
                                           id="shuffle_answers" {{ old('shuffle_answers') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_answers">
                                        <i class="fas fa-random me-1"></i>ترتيب الخيارات عشوائياً
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">متاح من</label>
                                <input type="datetime-local" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from') }}">
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">موعد الاستحقاق</label>
                                <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">متاح حتى</label>
                                <input type="datetime-local" name="available_until" class="form-control @error('available_until') is-invalid @enderror"
                                       value="{{ old('available_until') }}">
                                @error('available_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Publishing Options -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-eye me-2 text-success"></i>خيارات النشر
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published"
                                           id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        <i class="fas fa-check-circle me-1"></i>نشر الاختبار
                                    </label>
                                    <small class="d-block text-muted mt-1">الاختبار المنشور يمكن للطلاب رؤيته</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_visible"
                                           id="is_visible" {{ old('is_visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">
                                        <i class="fas fa-eye me-1"></i>ظاهر في القائمة
                                    </label>
                                    <small class="d-block text-muted mt-1">إذا كان مخفياً، يمكن الوصول إليه فقط عبر الرابط المباشر</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('quizzes.index') }}" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>حفظ الاختبار
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
    // Load lessons when course changes
    document.getElementById('course_id').addEventListener('change', function() {
        const courseId = this.value;
        const lessonSelect = document.getElementById('lesson_id');

        if (!courseId) {
            lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
            return;
        }

        // Show loading
        lessonSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        // Fetch lessons
        fetch(`{{ url('admin/quizzes/course') }}/${courseId}/lessons`)
            .then(response => response.json())
            .then(data => {
                lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
                data.forEach(lesson => {
                    lessonSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                lessonSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
    });
</script>
@endsection
