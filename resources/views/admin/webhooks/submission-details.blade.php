@extends('admin.layouts.master')

@section('page-title')
تفاصيل الإرسالية #{{ $submission->id }}
@stop

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">تفاصيل الإرسالية #{{ $submission->id }}</h1>
            <div>
                @if($submission->status === 'failed')
                    <form action="{{ route('admin.webhooks.submission.retry', $submission) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('هل أنت متأكد من إعادة محاولة معالجة هذه الإرسالية؟')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo"></i> إعادة المحاولة
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.webhooks.submissions') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة للقائمة
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Information -->
            <div class="col-lg-8 mb-4">
                <!-- Basic Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">المعلومات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>نوع الإرسالية:</strong>
                                <div class="mt-2">
                                    @switch($submission->submission_type)
                                        @case('enrollment') <span class="badge badge-primary badge-lg"><i class="fas fa-user-plus"></i> تسجيل في كورس</span> @break
                                        @case('contact') <span class="badge badge-info badge-lg"><i class="fas fa-envelope"></i> رسالة تواصل</span> @break
                                        @case('review') <span class="badge badge-warning badge-lg"><i class="fas fa-star"></i> مراجعة كورس</span> @break
                                        @case('payment') <span class="badge badge-success badge-lg"><i class="fas fa-money-bill"></i> دفع رسوم</span> @break
                                        @case('application') <span class="badge badge-secondary badge-lg"><i class="fas fa-file-alt"></i> طلب التحاق</span> @break
                                        @case('survey') <span class="badge badge-dark badge-lg"><i class="fas fa-poll"></i> استبيان</span> @break
                                        @default <span class="badge badge-light badge-lg">{{ $submission->submission_type }}</span> @break
                                    @endswitch
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                <div class="mt-2">
                                    @switch($submission->status)
                                        @case('pending')
                                            <span class="badge badge-warning badge-lg">
                                                <i class="fas fa-clock"></i> قيد المعالجة
                                            </span>
                                        @break
                                        @case('processed')
                                            <span class="badge badge-success badge-lg">
                                                <i class="fas fa-check-circle"></i> تمت المعالجة بنجاح
                                            </span>
                                        @break
                                        @case('failed')
                                            <span class="badge badge-danger badge-lg">
                                                <i class="fas fa-exclamation-triangle"></i> فشلت المعالجة
                                            </span>
                                        @break
                                    @endswitch
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-fingerprint text-muted"></i> Form ID:</strong>
                                <div class="mt-1">
                                    <code class="bg-light p-2 d-inline-block">{{ $submission->form_id }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-hashtag text-muted"></i> Entry ID:</strong>
                                <div class="mt-1">
                                    <code class="bg-light p-2 d-inline-block">{{ $submission->entry_id ?? '-' }}</code>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-calendar text-muted"></i> تاريخ الاستلام:</strong>
                                <div class="mt-1">
                                    {{ $submission->created_at->format('Y-m-d H:i:s') }}
                                    <br>
                                    <small class="text-muted">({{ $submission->created_at->diffForHumans() }})</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-clock text-muted"></i> آخر تحديث:</strong>
                                <div class="mt-1">
                                    {{ $submission->updated_at->format('Y-m-d H:i:s') }}
                                    <br>
                                    <small class="text-muted">({{ $submission->updated_at->diffForHumans() }})</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Data Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">بيانات النموذج</h6>
                    </div>
                    <div class="card-body">
                        <x-webhook.form-fields-display
                            :formData="$submission->form_data"
                            :submissionType="$submission->submission_type"
                        />
                    </div>
                </div>

                <!-- Processing Notes Card -->
                @if($submission->processing_notes)
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-{{ $submission->status === 'failed' ? 'danger' : 'success' }} text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-{{ $submission->status === 'failed' ? 'exclamation-triangle' : 'info-circle' }}"></i>
                                ملاحظات المعالجة
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-{{ $submission->status === 'failed' ? 'danger' : 'success' }} mb-0">
                                {{ $submission->processing_notes }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Related Student Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> الطالب المرتبط
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($submission->user)
                            <div class="text-center mb-3">
                                <div class="avatar-circle bg-primary text-white mx-auto mb-3"
                                     style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                    {{ strtoupper(substr($submission->user->name, 0, 1)) }}
                                </div>
                                <h5 class="mb-0">{{ $submission->user->name }}</h5>
                                <p class="text-muted mb-0">{{ $submission->user->email }}</p>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>رقم المستخدم:</strong> #{{ $submission->user->id }}
                            </div>
                            <div class="mb-2">
                                <strong>تاريخ التسجيل:</strong><br>
                                <small>{{ $submission->user->created_at->format('Y-m-d') }}</small>
                            </div>
                            <div class="mt-3">
                                <a href="#" class="btn btn-sm btn-primary btn-block">
                                    <i class="fas fa-user"></i> عرض ملف الطالب
                                </a>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-3x mb-3"></i>
                                <p class="mb-0">لا يوجد طالب مرتبط</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Course Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-book"></i> الكورس المرتبط
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($submission->course)
                            <div class="mb-3">
                                @if($submission->course->image)
                                    <img src="{{ asset('storage/' . $submission->course->image) }}"
                                         class="img-fluid rounded mb-3"
                                         alt="{{ $submission->course->title }}">
                                @endif
                                <h6 class="mb-2">{{ $submission->course->title }}</h6>
                                @if($submission->course->description)
                                    <p class="text-muted small mb-2">
                                        {{ Str::limit(strip_tags($submission->course->description), 100) }}
                                    </p>
                                @endif
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>رقم الكورس:</strong> #{{ $submission->course->id }}
                            </div>
                            <div class="mb-2">
                                <strong>المدرب:</strong><br>
                                <small>{{ $submission->course->instructor->name ?? '-' }}</small>
                            </div>
                            <div class="mt-3">
                                <a href="#" class="btn btn-sm btn-primary btn-block">
                                    <i class="fas fa-book"></i> عرض الكورس
                                </a>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-book-open fa-3x mb-3"></i>
                                <p class="mb-0">لا يوجد كورس مرتبط</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog"></i> إجراءات
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($submission->status === 'failed')
                            <form action="{{ route('admin.webhooks.submission.retry', $submission) }}" method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من إعادة محاولة معالجة هذه الإرسالية؟')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-redo"></i> إعادة المحاولة
                                </button>
                            </form>
                            <hr>
                        @endif
                        <a href="{{ route('admin.webhooks.submissions') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> جميع الإرساليات
                        </a>
                        <a href="{{ route('admin.webhooks.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->
@endsection

@push('styles')
<style>
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
</style>
@endpush
