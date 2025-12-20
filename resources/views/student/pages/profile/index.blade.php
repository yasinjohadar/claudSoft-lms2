@extends('student.layouts.master')

@section('page-title')
    ملفي الشخصي
@stop

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, rgb(var(--primary-rgb)) 0%, rgb(var(--primary-rgb)) 100%);
        padding: 2rem;
        border-radius: 15px;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 0.5rem 1.5rem rgba(var(--primary-rgb), 0.3);
    }

    .profile-avatar {
        width: 150px !important;
        height: 150px !important;
        min-width: 150px !important;
        min-height: 150px !important;
        max-width: 150px !important;
        max-height: 150px !important;
        border-radius: 50% !important;
        object-fit: cover;
        object-position: center;
        border: 5px solid white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        display: block;
        aspect-ratio: 1 / 1;
    }

    .avatar-placeholder {
        width: 150px !important;
        height: 150px !important;
        min-width: 150px !important;
        min-height: 150px !important;
        max-width: 150px !important;
        max-height: 150px !important;
        border-radius: 50% !important;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        font-weight: bold;
        color: rgb(var(--primary-rgb));
        border: 5px solid white;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        aspect-ratio: 1 / 1;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 0.125rem 0.625rem rgba(var(--black-rgb), 0.08);
        border: none;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: transparent;
        border-bottom: 2px solid var(--default-border);
        padding: 1.25rem 1.5rem;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--default-text-color);
    }

    .info-row {
        padding: 1rem 0;
        border-bottom: 1px solid var(--default-border);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .info-value {
        color: var(--default-text-color);
        font-size: 1.1rem;
    }

    .info-value.empty {
        color: #adb5bd;
        font-style: italic;
    }

    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong><i class="fa fa-check-circle me-1"></i> نجاح!</strong> {!! \Session::get('success') !!}
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong><i class="fa fa-times-circle me-1"></i> خطأ!</strong> {!! \Session::get('error') !!}
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h4 class="page-title fs-24 mb-1 fw-bold">
                        <i class="fa fa-user text-primary me-2"></i>
                        ملفي الشخصي
                    </h4>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">ملفي الشخصي</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center">
                    <a href="{{ route('student.profile.edit') }}" class="btn btn-primary">
                        <i class="fa fa-edit me-2"></i>تعديل الملف الشخصي
                    </a>
                </div>
            </div>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}" class="profile-avatar">
                        @else
                            <div class="avatar-placeholder">
                                {{ substr($student->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="col">
                        <h2 class="mb-2 fw-bold">{{ $student->name }}</h2>
                        <p class="mb-1"><i class="fa fa-envelope me-2"></i>{{ $student->email }}</p>
                        @if($student->phone)
                            <p class="mb-1"><i class="fa fa-phone me-2"></i>{{ $student->phone }}</p>
                        @endif
                        @if($student->city)
                            <p class="mb-0"><i class="fa fa-map-marker-alt me-2"></i>{{ $student->city }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- البيانات الشخصية -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fa fa-user me-2"></i>البيانات الشخصية</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-user text-primary me-2"></i>الاسم الكامل
                                        </div>
                                        <div class="info-value">{{ $student->name }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-envelope text-primary me-2"></i>البريد الإلكتروني
                                        </div>
                                        <div class="info-value">{{ $student->email }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-phone text-primary me-2"></i>رقم الهاتف
                                        </div>
                                        <div class="info-value {{ $student->phone ? '' : 'empty' }}">
                                            {{ $student->phone ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-id-card text-primary me-2"></i>رقم الهوية
                                        </div>
                                        <div class="info-value {{ $student->national_id ? '' : 'empty' }}">
                                            {{ $student->national_id ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-calendar text-primary me-2"></i>تاريخ الميلاد
                                        </div>
                                        <div class="info-value {{ $student->date_of_birth ? '' : 'empty' }}">
                                            {{ $student->date_of_birth ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-venus-mars text-primary me-2"></i>الجنس
                                        </div>
                                        <div class="info-value {{ $student->gender ? '' : 'empty' }}">
                                            @if($student->gender == 'male')
                                                ذكر
                                            @elseif($student->gender == 'female')
                                                أنثى
                                            @else
                                                غير محدد
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-map-marker-alt text-primary me-2"></i>المدينة
                                        </div>
                                        <div class="info-value {{ $student->city ? '' : 'empty' }}">
                                            {{ $student->city ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-home text-primary me-2"></i>العنوان
                                        </div>
                                        <div class="info-value {{ $student->address ? '' : 'empty' }}">
                                            {{ $student->address ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-globe text-primary me-2"></i>الجنسية
                                        </div>
                                        <div class="info-value {{ $student->nationality_id ? '' : 'empty' }}">
                                            {{ $student->nationality->name ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات الحساب -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-info-circle me-2"></i>معلومات الحساب</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-toggle-on text-primary me-2"></i>حالة الحساب
                                        </div>
                                        <div class="info-value">
                                            @if($student->is_active ?? true)
                                                <span class="badge badge-status bg-success">نشط</span>
                                            @else
                                                <span class="badge badge-status bg-danger">غير نشط</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-clock text-primary me-2"></i>تاريخ الإنشاء
                                        </div>
                                        <div class="info-value">
                                            {{ $student->created_at ? $student->created_at->format('Y-m-d h:i A') : 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-sign-in-alt text-primary me-2"></i>آخر تسجيل دخول
                                        </div>
                                        <div class="info-value {{ $student->last_login_at ?? false ? '' : 'empty' }}">
                                            {{ $student->last_login_at ? \Carbon\Carbon::parse($student->last_login_at)->format('Y-m-d h:i A') : 'لم يتم التسجيل بعد' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fa fa-network-wired text-primary me-2"></i>آخر IP
                                        </div>
                                        <div class="info-value {{ $student->last_login_ip ?? false ? '' : 'empty' }}">
                                            {{ $student->last_login_ip ?? 'غير محدد' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الصورة الشخصية -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-image me-2"></i>الصورة الشخصية</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                @if($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}"
                                         class="profile-avatar mx-auto">
                                @else
                                    <div class="avatar-placeholder mx-auto">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('student.profile.edit') }}#photo-section" class="btn btn-primary">
                                    <i class="fa fa-edit me-2"></i>تعديل الصورة
                                </a>
                                @if($student->photo)
                                    <form action="{{ route('student.profile.delete-photo') }}" method="POST" class="delete-photo-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="fa fa-trash me-2"></i>حذف الصورة
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- الأمان -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-shield-alt me-2"></i>الأمان</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fa fa-lock text-primary me-2"></i>كلمة المرور
                                </div>
                                <div class="info-value">••••••••</div>
                            </div>
                            <div class="d-grid mt-3">
                                <a href="{{ route('student.profile.edit') }}#password-section" class="btn btn-warning">
                                    <i class="fa fa-key me-2"></i>تغيير كلمة المرور
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete photo confirmation
        const deleteForms = document.querySelectorAll('.delete-photo-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('هل أنت متأكد من حذف الصورة الشخصية؟')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
