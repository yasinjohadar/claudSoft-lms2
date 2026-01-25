@extends('admin.layouts.master')

@section('page-title')
    تفاصيل قالب البريد الإلكتروني
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-eye me-2"></i>
                    تفاصيل قالب البريد الإلكتروني
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    رجوع
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="row">
            <div class="col-lg-8">
                <!-- معلومات القالب -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">معلومات القالب</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الاسم (إنجليزي):</strong>
                                <p>{{ $emailTemplate->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>الاسم (عربي):</strong>
                                <p>{{ $emailTemplate->name_ar ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>النوع:</strong>
                                <p>
                                    @if($emailTemplate->type === 'registration_welcome')
                                        <span class="badge bg-info">ترحيب بالتسجيل</span>
                                    @elseif($emailTemplate->type === 'enrollment_confirmation')
                                        <span class="badge bg-success">تأكيد التسجيل</span>
                                    @else
                                        <span class="badge bg-secondary">مخصص</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                <p>
                                    @if($emailTemplate->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">معطل</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>الموضوع:</strong>
                            <p class="text-muted">{{ $emailTemplate->subject }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>تاريخ الإنشاء:</strong>
                            <p>{{ $emailTemplate->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>آخر تحديث:</strong>
                            <p>{{ $emailTemplate->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- معاينة القالب -->
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">معاينة القالب</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>الموضوع:</strong>
                            <div class="alert alert-info mb-0">{{ $renderedSubject }}</div>
                        </div>
                        <div>
                            <strong>المحتوى:</strong>
                            <div class="border p-3 mt-2" style="min-height: 200px; direction: rtl; text-align: right;">
                                {!! $renderedBody !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- الإجراءات -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">الإجراءات</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                تعديل القالب
                            </a>
                            <a href="{{ route('admin.email-templates.preview', $emailTemplate) }}" 
                               class="btn btn-warning" 
                               target="_blank">
                                <i class="fas fa-eye me-2"></i>
                                معاينة كاملة
                            </a>
                            <form action="{{ route('admin.email-templates.duplicate', $emailTemplate) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-secondary w-100">
                                    <i class="fas fa-copy me-2"></i>
                                    نسخ القالب
                                </button>
                            </form>
                            <form action="{{ route('admin.email-templates.destroy', $emailTemplate) }}" method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash me-2"></i>
                                    حذف القالب
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- البيانات التجريبية -->
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">البيانات التجريبية المستخدمة</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><strong>student_name:</strong> {{ $testData['student_name'] }}</li>
                            <li><strong>student_name_en:</strong> {{ $testData['student_name_en'] }}</li>
                            <li><strong>group_name:</strong> {{ $testData['group_name'] }}</li>
                            <li><strong>email:</strong> {{ $testData['email'] }}</li>
                            <li><strong>phone:</strong> {{ $testData['phone'] }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
