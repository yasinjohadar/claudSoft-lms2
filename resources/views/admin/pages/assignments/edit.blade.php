@extends('admin.layouts.master')

@section('page-title')
    تعديل الواجب
@stop

@section('styles')
    @include('admin.pages.assignments.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="admin-form-layout">

            <div class="my-4 page-header-breadcrumb assignments-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">الواجبات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('assignments.show', $assignment->id) }}">{{ Str::limit($assignment->title, 30) }}</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in assignments-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-edit-2 me-1"></i>تعديل الواجب</span>
                        <h2 class="group-show-hero__title mb-2">{{ $assignment->title }}</h2>
                        <p class="group-show-hero__desc mb-0">حدّث بيانات الواجب، المواعيد، إعدادات التسليم والمرفقات.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('assignments.show', $assignment->id) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">عرض الواجب</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('assignments.update', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                            المعلومات الأساسية
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">عنوان الواجب <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $assignment->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">اختر الكورس</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $assignment->course_id) == $course->id ? 'selected' : '' }}>
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
                                    <option value="">اختر الدرس</option>
                                    @foreach($lessons as $lesson)
                                        <option value="{{ $lesson->id }}" {{ old('lesson_id', $assignment->lesson_id) == $lesson->id ? 'selected' : '' }}>
                                            {{ $lesson->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lesson_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="target_group_id">المجموعة المستهدفة</label>
                                <select name="target_group_id" id="target_group_id" class="form-select @error('target_group_id') is-invalid @enderror">
                                    <option value="">كل طلاب الكورس</option>
                                </select>
                                <small class="text-muted">حدّد مجموعة لإظهار الواجب لأعضائها فقط</small>
                                @error('target_group_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description', $assignment->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">التعليمات</label>
                                <textarea name="instructions" id="instructions" class="form-control @error('instructions') is-invalid @enderror"
                                          rows="4">{{ old('instructions', $assignment->instructions) }}</textarea>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-award"></i></span>
                            إعدادات الدرجات
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الدرجة القصوى <span class="text-danger">*</span></label>
                                <input type="number" name="max_grade" class="form-control @error('max_grade') is-invalid @enderror"
                                       value="{{ old('max_grade', $assignment->max_grade) }}" min="1" max="1000" required>
                                @error('max_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نوع التسليم <span class="text-danger">*</span></label>
                                <select name="submission_type" id="submission_type" class="form-select @error('submission_type') is-invalid @enderror" required>
                                    <option value="link" {{ old('submission_type', $assignment->submission_type) == 'link' ? 'selected' : '' }}>روابط فقط</option>
                                    <option value="file" {{ old('submission_type', $assignment->submission_type) == 'file' ? 'selected' : '' }}>ملفات فقط</option>
                                    <option value="both" {{ old('submission_type', $assignment->submission_type) == 'both' ? 'selected' : '' }}>روابط وملفات</option>
                                </select>
                                @error('submission_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-settings"></i></span>
                            إعدادات التسليم
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4" id="link_settings">
                                <label class="form-label">الحد الأقصى للروابط</label>
                                <input type="number" name="max_links" class="form-control @error('max_links') is-invalid @enderror"
                                       value="{{ old('max_links', $assignment->max_links) }}" min="0" max="20">
                                @error('max_links')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="file_settings_count">
                                <label class="form-label">الحد الأقصى للملفات</label>
                                <input type="number" name="max_files" class="form-control @error('max_files') is-invalid @enderror"
                                       value="{{ old('max_files', $assignment->max_files) }}" min="0" max="20">
                                @error('max_files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4" id="file_settings_size">
                                <label class="form-label">الحد الأقصى لحجم الملف (KB)</label>
                                <input type="number" name="max_file_size" class="form-control @error('max_file_size') is-invalid @enderror"
                                       value="{{ old('max_file_size', $assignment->max_file_size) }}" min="0" max="102400">
                                @error('max_file_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">القيمة بالكيلوبايت (1 MB = 1024 KB)</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ترتيب العرض</label>
                                <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', $assignment->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-clock"></i></span>
                            المواعيد النهائية
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">متاح من</label>
                                <input type="datetime-local" name="available_from" class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from', $assignment->available_from ? $assignment->available_from->format('Y-m-d\TH:i') : '') }}">
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">موعد التسليم</label>
                                <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">آخر موعد للتسليم المتأخر</label>
                                <input type="datetime-local" name="late_submission_until" class="form-control @error('late_submission_until') is-invalid @enderror"
                                       value="{{ old('late_submission_until', $assignment->late_submission_until ? $assignment->late_submission_until->format('Y-m-d\TH:i') : '') }}">
                                @error('late_submission_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">السماح بالتسليم المتأخر</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_late_submission" id="allow_late_submission"
                                           {{ old('allow_late_submission', $assignment->allow_late_submission) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_late_submission">
                                        تفعيل التسليم المتأخر
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">نسبة الخصم على التأخير (%)</label>
                                <input type="number" name="late_penalty_percentage" class="form-control @error('late_penalty_percentage') is-invalid @enderror"
                                       value="{{ old('late_penalty_percentage', $assignment->late_penalty_percentage) }}" min="0" max="100">
                                @error('late_penalty_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-rotate-cw"></i></span>
                            إعدادات إعادة التسليم
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_resubmission" id="allow_resubmission"
                                           {{ old('allow_resubmission', $assignment->allow_resubmission) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_resubmission">
                                        السماح بإعادة التسليم
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="resubmission_settings" style="display: none;">
                                <label class="form-label">الحد الأقصى لإعادة التسليم</label>
                                <input type="number" name="max_resubmissions" class="form-control @error('max_resubmissions') is-invalid @enderror"
                                       value="{{ old('max_resubmissions', $assignment->max_resubmissions) }}" min="1" max="10" placeholder="اتركه فارغاً للسماح بعدد غير محدود">
                                @error('max_resubmissions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="resubmission_grading" style="display: none;">
                                <label class="form-label">شرط إعادة التسليم</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="resubmit_after_grading_only" id="resubmit_after_grading_only"
                                           {{ old('resubmit_after_grading_only', $assignment->resubmit_after_grading_only) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="resubmit_after_grading_only">
                                        السماح بإعادة التسليم فقط بعد التقييم
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Attachments -->
                @if($assignment->attachments && is_array($assignment->attachments) && count($assignment->attachments) > 0)
                    <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-file"></i></span>
                                المرفقات الحالية
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($assignment->attachments as $index => $attachment)
                                    <div class="col-md-4">
                                        <div class="assignments-attachment-card">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <i class="fe fe-file me-2 text-primary"></i>
                                                    <small class="fw-semibold">{{ $attachment['name'] }}</small>
                                                    <br>
                                                    <small class="text-muted">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                                </div>
                                                <form action="{{ route('assignments.delete-attachment', $assignment->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المرفق؟')">
                                                    @csrf
                                                    <input type="hidden" name="index" value="{{ $index }}">
                                                    <button type="submit" class="btn btn-danger-light btn-sm assignments-actions__btn">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-paperclip"></i></span>
                            إضافة مرفقات جديدة
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">إضافة ملفات</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror"
                                   multiple accept=".pdf,.doc,.docx,.txt,.zip">
                            <small class="text-muted">يمكنك إضافة ملفات مساعدة للطلاب (PDF, DOC, TXT, ZIP) - الحد الأقصى 10 MB لكل ملف</small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in assignments-page-animate mb-4">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                            <span class="assignments-section-icon"><i class="fe fe-eye"></i></span>
                            إعدادات الظهور
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                           {{ old('is_published', $assignment->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        نشر الواجب
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible"
                                           {{ old('is_visible', $assignment->is_visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">
                                        إظهار الواجب للطلاب
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card assignments-form-actions dashboard-fade-in assignments-page-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-light">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ التغييرات
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
    @include('admin.pages.assignments.partials.form-scripts', [
        'currentLessonId' => $assignment->lesson_id,
        'currentGroupId' => old('target_group_id', $assignment->target_group_id),
    ])
@stop
