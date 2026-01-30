@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التسجيل
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-user-plus me-2"></i>
                    تفاصيل التسجيل
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.group-registrations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    رجوع
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="row">
            <!-- معلومات التسجيل -->
            <div class="col-lg-8">
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">معلومات التسجيل</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الاسم بالإنجليزية:</strong>
                                <p>{{ $registration->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>الاسم بالعربية:</strong>
                                <p>{{ $registration->name_ar }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>البريد الإلكتروني:</strong>
                                <p>{{ $registration->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>الهاتف:</strong>
                                <p>{{ $registration->full_phone ?? $registration->phone }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الجنسية:</strong>
                                <p>{{ $registration->nationality->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>تاريخ الميلاد:</strong>
                                <p>{{ $registration->date_of_birth ? $registration->date_of_birth->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الجنس:</strong>
                                <p>
                                    @if($registration->gender === 'male')
                                        ذكر
                                    @elseif($registration->gender === 'female')
                                        أنثى
                                    @elseif($registration->gender === 'other')
                                        أخرى
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>المدينة:</strong>
                                <p>{{ $registration->city ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>العنوان:</strong>
                                <p>{{ $registration->address ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>رمز الدولة:</strong>
                                <p>{{ $registration->country_code ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- المعدات والخبرة والتدريب -->
                @php
                    $computerLevels = ['none' => 'بدون', 'beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'good' => 'جيد', 'advanced' => 'متقدم'];
                    $progLevels = ['none' => 'بدون', 'beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'expert' => 'خبير'];
                @endphp
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">المعدات والخبرة والتدريب</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>هل تمتلك حاسوب:</strong>
                                <p>
                                    @if($registration->has_computer === 'yes')
                                        <span class="badge bg-success">نعم</span>
                                    @elseif($registration->has_computer === 'no')
                                        <span class="badge bg-secondary">لا</span>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>الالتزام بالتدريب:</strong>
                                <p>{{ $registration->commitment_to_training === 'yes' ? 'نعم' : ($registration->commitment_to_training === 'no' ? 'لا' : '-') }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>الوقت الكافي للمتابعة:</strong>
                                <p>{{ $registration->has_sufficient_time === 'yes' ? 'نعم' : ($registration->has_sufficient_time === 'no' ? 'لا' : '-') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>خبرة الحاسوب:</strong>
                                <p>{{ $computerLevels[$registration->computer_experience_level] ?? $registration->computer_experience_level ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>خبرة البرمجة:</strong>
                                <p>{{ $progLevels[$registration->programming_experience] ?? $registration->programming_experience ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>الاهتمام بالمعسكر التدريبي:</strong>
                                <p>{{ $registration->interested_in_bootcamp === 'yes' ? 'نعم' : ($registration->interested_in_bootcamp === 'no' ? 'لا' : '-') }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>نبذة عن الحاسوب والبرمجة:</strong>
                            <p>{{ $registration->computer_programming_background ?? '-' }}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>آخر مرحلة دراسية:</strong>
                                <p>{{ $registration->education_level ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>التخصص الدراسي:</strong>
                                <p>{{ $registration->education_major ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>العمل الحالي:</strong>
                            <p>{{ $registration->current_job ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- الحقول المخصصة -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">الحقول المخصصة</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>ملاحظات:</strong>
                            <p>{{ $registration->notes ?? '-' }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>معلومات إضافية:</strong>
                            <p>{{ $registration->additional_info ?? '-' }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>متطلبات خاصة:</strong>
                            <p>{{ $registration->special_requirements ?? '-' }}</p>
                        </div>
                        @if($registration->custom_fields)
                            <div class="mb-3">
                                <strong>حقول إضافية:</strong>
                                <pre class="bg-light p-3 rounded">{{ json_encode($registration->custom_fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- معلومات المجموعة والحالة -->
            <div class="col-lg-4">
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">معلومات المجموعة</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>المجموعة:</strong> 
                            <a href="{{ route('courses.groups.show', [$registration->group->course_id ?? 1, $registration->group_id]) }}">
                                {{ $registration->group->name }}
                            </a>
                        </p>
                    </div>
                </div>

                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">حالة التسجيل</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>الحالة:</strong>
                            @if($registration->status === 'pending')
                                <span class="badge bg-warning">معلق</span>
                            @elseif($registration->status === 'processing')
                                <span class="badge bg-info">قيد المعالجة</span>
                            @elseif($registration->status === 'completed')
                                <span class="badge bg-success">مكتمل</span>
                            @else
                                <span class="badge bg-danger">فاشل</span>
                            @endif
                        </p>
                        <p><strong>تم إنشاء الحساب:</strong>
                            @if($registration->user_created)
                                <span class="badge bg-success">نعم</span>
                            @else
                                <span class="badge bg-secondary">لا</span>
                            @endif
                        </p>
                        @if($registration->user)
                            <p><strong>المستخدم:</strong>
                                <a href="{{ route('users.show', $registration->user_id) }}">
                                    {{ $registration->user->name_ar ?? $registration->user->name }}
                                </a>
                            </p>
                        @endif
                        @if($registration->error_message)
                            <div class="alert alert-danger mt-3">
                                <strong>خطأ:</strong>
                                <p class="mb-0">{{ $registration->error_message }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- الإجراءات -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">الإجراءات</h6>
                    </div>
                    <div class="card-body">
                        @if($registration->status === 'failed' || $registration->status === 'pending')
                            <form action="{{ route('admin.group-registrations.reprocess', $registration->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-redo me-2"></i>
                                    إعادة المعالجة
                                </button>
                            </form>
                        @endif
                        @if(!$registration->email_sent)
                            <form action="{{ route('admin.group-registrations.resend-email', $registration->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-envelope me-2"></i>
                                    إعادة إرسال البريد
                                </button>
                            </form>
                        @endif
                        @if(!$registration->whatsapp_sent && $registration->phone)
                            <form action="{{ route('admin.group-registrations.resend-whatsapp', $registration->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    إعادة إرسال واتساب
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.group-registrations.destroy', $registration->id) }}" method="POST" 
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا التسجيل؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>
                                حذف التسجيل
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
