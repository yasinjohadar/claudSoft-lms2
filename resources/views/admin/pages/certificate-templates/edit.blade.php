@extends('admin.layouts.master')

@section('page-title')
    تعديل القالب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل القالب: {{ $template->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.certificate-templates.index') }}">القوالب</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">تعديل معلومات القالب</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.certificate-templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Basic Info -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم القالب <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $template->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">النوع <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="html" {{ old('type', $template->type) == 'html' ? 'selected' : '' }}>HTML</option>
                                            <option value="image" {{ old('type', $template->type) == 'image' ? 'selected' : '' }}>صورة</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                  rows="3">{{ old('description', $template->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- HTML Content -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label">محتوى HTML <span class="text-danger">*</span></label>
                                        <textarea name="html_content" class="form-control @error('html_content') is-invalid @enderror"
                                                  rows="15" style="font-family: monospace;">{{ old('html_content', $template->html_content) }}</textarea>
                                        @error('html_content')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            استخدم الحقول الديناميكية: {student_name}, {course_name}, {certificate_number}, {issue_date_ar}, {qr_code}
                                        </small>
                                    </div>
                                </div>

                                <!-- Requirements -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>متطلبات الحصول على الشهادة</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="requires_completion"
                                                           id="requires_completion" value="1" {{ old('requires_completion', $template->requires_completion) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="requires_completion">
                                                        يتطلب إكمال الكورس
                                                    </label>
                                                </div>
                                                <input type="number" name="min_completion_percentage" class="form-control mt-2"
                                                       placeholder="نسبة الإكمال %" value="{{ old('min_completion_percentage', $template->min_completion_percentage) }}" min="0" max="100">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="requires_attendance"
                                                           id="requires_attendance" value="1" {{ old('requires_attendance', $template->requires_attendance) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="requires_attendance">
                                                        يتطلب حضور
                                                    </label>
                                                </div>
                                                <input type="number" name="min_attendance_percentage" class="form-control mt-2"
                                                       placeholder="نسبة الحضور %" value="{{ old('min_attendance_percentage', $template->min_attendance_percentage) }}" min="0" max="100">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="requires_final_exam"
                                                           id="requires_final_exam" value="1" {{ old('requires_final_exam', $template->requires_final_exam) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="requires_final_exam">
                                                        يتطلب اختبار نهائي
                                                    </label>
                                                </div>
                                                <input type="number" name="min_final_exam_score" class="form-control mt-2"
                                                       placeholder="الدرجة المطلوبة" value="{{ old('min_final_exam_score', $template->min_final_exam_score) }}" min="0" max="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Settings -->
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الاتجاه</label>
                                        <select name="orientation" class="form-select">
                                            <option value="portrait" {{ old('orientation', $template->orientation) == 'portrait' ? 'selected' : '' }}>عمودي (Portrait)</option>
                                            <option value="landscape" {{ old('orientation', $template->orientation) == 'landscape' ? 'selected' : '' }}>أفقي (Landscape)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">حجم الصفحة</label>
                                        <select name="page_size" class="form-select">
                                            <option value="A4" {{ old('page_size', $template->page_size) == 'A4' ? 'selected' : '' }}>A4</option>
                                            <option value="A3" {{ old('page_size', $template->page_size) == 'A3' ? 'selected' : '' }}>A3</option>
                                            <option value="Letter" {{ old('page_size', $template->page_size) == 'Letter' ? 'selected' : '' }}>Letter</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الترتيب</label>
                                        <input type="number" name="sort_order" class="form-control"
                                               value="{{ old('sort_order', $template->sort_order) }}" min="0">
                                    </div>
                                </div>

                                <!-- Expiry Settings -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="has_expiry"
                                                   id="has_expiry" value="1" {{ old('has_expiry', $template->has_expiry) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has_expiry">
                                                لها تاريخ انتهاء
                                            </label>
                                        </div>
                                        <input type="number" name="expiry_months" class="form-control mt-2"
                                               placeholder="عدد الأشهر" value="{{ old('expiry_months', $template->expiry_months) }}" min="1">
                                    </div>
                                </div>

                                <!-- Checkboxes -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="auto_issue"
                                                   id="auto_issue" value="1" {{ old('auto_issue', $template->auto_issue) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auto_issue">
                                                إصدار تلقائي عند الإكمال
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_default"
                                                   id="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_default">
                                                تعيين كقالب افتراضي
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active"
                                                   id="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                تفعيل القالب
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-light">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>حفظ التعديلات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
