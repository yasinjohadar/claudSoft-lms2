@extends('admin.layouts.master')

@section('page-title')
    تعديل الاختبار
@stop

@section('styles')
    @include('admin.pages.quizzes.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="admin-form-layout">

            <div class="my-4 page-header-breadcrumb quizzes-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">الاختبارات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.show', $quiz->id) }}">{{ Str::limit($quiz->title, 30) }}</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in quizzes-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-edit-2 me-1"></i>تعديل الاختبار</span>
                        <h2 class="group-show-hero__title mb-2">{{ $quiz->title }}</h2>
                        <p class="group-show-hero__desc mb-0">حدّث بيانات الاختبار، إعدادات الدرجات، المواعيد وخيارات النشر.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">عرض الاختبار</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('quizzes.update', $quiz->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                            المعلومات الأساسية
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">عنوان الاختبار <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $quiz->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $quiz->course_id) == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الدرس (اختياري)</label>
                                <select name="lesson_id" id="lesson_id" class="form-select @error('lesson_id') is-invalid @enderror">
                                    <option value="">لا يوجد دروس مرتبطة</option>
                                    @foreach($lessons as $lesson)
                                        <option value="{{ $lesson->id }}" {{ old('lesson_id', $quiz->lesson_id) == $lesson->id ? 'selected' : '' }}>
                                            {{ $lesson->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lesson_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نوع الاختبار <span class="text-danger">*</span></label>
                                <select name="quiz_type" class="form-select @error('quiz_type') is-invalid @enderror" required>
                                    <option value="practice" {{ old('quiz_type', $quiz->quiz_type) == 'practice' ? 'selected' : '' }}>تدريبي</option>
                                    <option value="graded" {{ old('quiz_type', $quiz->quiz_type) == 'graded' ? 'selected' : '' }}>مُقيّم</option>
                                    <option value="final_exam" {{ old('quiz_type', $quiz->quiz_type) == 'final_exam' ? 'selected' : '' }}>اختبار نهائي</option>
                                    <option value="survey" {{ old('quiz_type', $quiz->quiz_type) == 'survey' ? 'selected' : '' }}>استبيان</option>
                                </select>
                                @error('quiz_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الترتيب</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', $quiz->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description', $quiz->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">التعليمات</label>
                                <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror"
                                          rows="4">{{ old('instructions', $quiz->instructions) }}</textarea>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-award"></i></span>
                            إعدادات الدرجات
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">درجة النجاح (%) <span class="text-danger">*</span></label>
                                <input type="number" name="passing_grade" class="form-control @error('passing_grade') is-invalid @enderror"
                                       value="{{ old('passing_grade', $quiz->passing_grade) }}" min="0" max="100" step="0.01" required>
                                @error('passing_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">وقت عرض الإجابات الصحيحة <span class="text-danger">*</span></label>
                                <select name="show_correct_answers_after" class="form-select @error('show_correct_answers_after') is-invalid @enderror" required>
                                    <option value="immediately" {{ old('show_correct_answers_after', $quiz->show_correct_answers_after) == 'immediately' ? 'selected' : '' }}>فوراً بعد التسليم</option>
                                    <option value="after_due" {{ old('show_correct_answers_after', $quiz->show_correct_answers_after) == 'after_due' ? 'selected' : '' }}>بعد موعد التسليم</option>
                                    <option value="after_graded" {{ old('show_correct_answers_after', $quiz->show_correct_answers_after) == 'after_graded' ? 'selected' : '' }}>بعد التصحيح</option>
                                    <option value="never" {{ old('show_correct_answers_after', $quiz->show_correct_answers_after) == 'never' ? 'selected' : '' }}>عدم العرض</option>
                                </select>
                                @error('show_correct_answers_after')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">طريقة عرض النتيجة <span class="text-danger">*</span></label>
                                <select name="feedback_mode" class="form-select @error('feedback_mode') is-invalid @enderror" required>
                                    <option value="immediate" {{ old('feedback_mode', $quiz->feedback_mode) == 'immediate' ? 'selected' : '' }}>فورية</option>
                                    <option value="after_submission" {{ old('feedback_mode', $quiz->feedback_mode) == 'after_submission' ? 'selected' : '' }}>بعد التسليم</option>
                                    <option value="after_due" {{ old('feedback_mode', $quiz->feedback_mode) == 'after_due' ? 'selected' : '' }}>بعد موعد الاستحقاق</option>
                                    <option value="manual" {{ old('feedback_mode', $quiz->feedback_mode) == 'manual' ? 'selected' : '' }}>يدوي</option>
                                </select>
                                @error('feedback_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_grade_immediately"
                                           id="show_grade_immediately" {{ old('show_grade_immediately', $quiz->show_grade_immediately) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_grade_immediately">عرض الدرجة فوراً</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="allow_review"
                                           id="allow_review" {{ old('allow_review', $quiz->allow_review) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_review">السماح بالمراجعة</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="show_correct_answers"
                                           id="show_correct_answers" {{ old('show_correct_answers', $quiz->show_correct_answers) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_correct_answers">عرض الإجابات الصحيحة</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-settings"></i></span>
                            إعدادات الاختبار
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">الوقت المحدد (بالدقائق)</label>
                                <input type="number" name="time_limit" class="form-control @error('time_limit') is-invalid @enderror"
                                       value="{{ old('time_limit', $quiz->time_limit) }}" min="1">
                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">عدد المحاولات المسموحة</label>
                                <input type="number" name="attempts_allowed" class="form-control @error('attempts_allowed') is-invalid @enderror"
                                       value="{{ old('attempts_allowed', $quiz->attempts_allowed) }}" min="1">
                                @error('attempts_allowed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="shuffle_questions"
                                           id="shuffle_questions" {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_questions">ترتيب الأسئلة عشوائياً</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="shuffle_answers"
                                           id="shuffle_answers" {{ old('shuffle_answers', $quiz->shuffle_answers) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shuffle_answers">ترتيب الخيارات عشوائياً</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">متاح من</label>
                                <input type="datetime-local" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from', $quiz->available_from ? $quiz->available_from->format('Y-m-d\TH:i') : '') }}">
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">موعد الاستحقاق</label>
                                <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date', $quiz->due_date ? $quiz->due_date->format('Y-m-d\TH:i') : '') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">متاح حتى</label>
                                <input type="datetime-local" name="available_until" class="form-control @error('available_until') is-invalid @enderror"
                                       value="{{ old('available_until', $quiz->available_until ? $quiz->available_until->format('Y-m-d\TH:i') : '') }}">
                                @error('available_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-eye"></i></span>
                            خيارات النشر
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published"
                                           id="is_published" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">نشر الاختبار</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_visible"
                                           id="is_visible" {{ old('is_visible', $quiz->is_visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">ظاهر في القائمة</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card assignments-form-actions dashboard-fade-in quizzes-page-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-light">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ التعديلات
                            </button>
                        </div>
                    </div>
                </div>

            </form>

            </div>

        </div>
    </div>
@stop

@section('script')
    @include('admin.pages.quizzes.partials.form-scripts', ['currentLessonId' => old('lesson_id', $quiz->lesson_id)])
@stop
