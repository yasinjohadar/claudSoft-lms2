@extends('admin.layouts.master')

@section('page-title')
    توليد ملاحظات AI للطالب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-robot text-primary me-2"></i>
                    توليد ملاحظات AI للطالب
                </h5>
            </div>
            <a href="{{ route('admin.ai.student-feedback.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>إعدادات التوليد</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.ai.student-feedback.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="student_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                                <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                                    <option value="">-- اختر الطالب --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="feedback_type" class="form-label">نوع الملاحظات <span class="text-danger">*</span></label>
                                <select class="form-select @error('feedback_type') is-invalid @enderror" id="feedback_type" name="feedback_type" required>
                                    @foreach($feedbackTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('feedback_type', 'general') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('feedback_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <strong>أداء:</strong> تحليل أداء الطالب في الاختبارات |
                                    <strong>عام:</strong> ملاحظات عامة |
                                    <strong>تحسين:</strong> نصائح للتحسين
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="ai_model_id" class="form-label">موديل AI</label>
                                <select class="form-select @error('ai_model_id') is-invalid @enderror" id="ai_model_id" name="ai_model_id">
                                    <option value="">-- الموديل الافتراضي --</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>
                                            {{ $model->name }} ({{ $model->provider }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('ai_model_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="custom_prompt" class="form-label">تعليمات إضافية (اختياري)</label>
                                <textarea class="form-control @error('custom_prompt') is-invalid @enderror" 
                                          id="custom_prompt" 
                                          name="custom_prompt" 
                                          rows="3"
                                          placeholder="أضف تعليمات إضافية للذكاء الاصطناعي...">{{ old('custom_prompt') }}</textarea>
                                @error('custom_prompt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">مثال: ركز على نقاط الضعف في الرياضيات</small>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.ai.student-feedback.index') }}" class="btn btn-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-robot me-1"></i> توليد الملاحظات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>ما هي ملاحظات AI للطلاب؟</strong></p>
                        <p class="text-muted small">
                            تقوم هذه الميزة بتحليل أداء الطالب وإنشاء ملاحظات مخصصة باستخدام الذكاء الاصطناعي.
                        </p>
                        
                        <hr>
                        
                        <p class="mb-2"><strong>أنواع الملاحظات:</strong></p>
                        <ul class="small text-muted">
                            <li><strong>أداء:</strong> تحليل نتائج اختبار محدد (يتطلب اختيار اختبار)</li>
                            <li><strong>عام:</strong> ملاحظات عامة بناءً على إحصائيات الطالب</li>
                            <li><strong>تحسين:</strong> نصائح وإرشادات للتحسن</li>
                        </ul>
                        
                        <hr>
                        
                        <p class="mb-2"><strong>📊 البيانات المستخدمة في التوليد:</strong></p>
                        <div class="small text-muted">
                            <p class="mb-1"><strong>للنوع "عام":</strong></p>
                            <ul class="mb-2">
                                <li>عدد الاختبارات المكتملة</li>
                                <li>متوسط الدرجات</li>
                                <li>أفضل وأسوأ أداء</li>
                                <li>عدد الكورسات المسجل فيها</li>
                                <li>آخر نشاط</li>
                            </ul>
                            
                            <p class="mb-1"><strong>للنوع "أداء":</strong></p>
                            <ul class="mb-2">
                                <li>نتائج اختبار محدد</li>
                                <li>الدرجة والنسبة المئوية</li>
                                <li>عدد الإجابات الصحيحة</li>
                                <li>تاريخ الاختبار</li>
                            </ul>
                            
                            <p class="mb-1"><strong>للنوع "تحسين":</strong></p>
                            <ul>
                                <li>المجالات التي تحتاج تحسين</li>
                                <li>نقاط الضعف</li>
                            </ul>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>ملاحظة:</strong> يمكنك إضافة تعليمات مخصصة في حقل "تعليمات إضافية" لتوجيه الذكاء الاصطناعي بشكل أفضل.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
@stop

