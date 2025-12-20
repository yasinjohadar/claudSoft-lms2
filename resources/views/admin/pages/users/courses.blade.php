@extends('admin.layouts.master')

@section('page-title')
    كورسات الطالب - {{ $student->name }}
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كورسات الطالب: {{ $student->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item active">كورسات الطالب</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>رجوع
                    </a>
                    <a href="{{ route('users.show', $student->id) }}" class="btn btn-info">
                        <i class="fas fa-user me-1"></i>ملف الطالب
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

            <!-- Student Info Card -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    @if($student->avatar)
                                        <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}" class="rounded-circle">
                                    @else
                                        <div class="avatar-title bg-primary-transparent rounded-circle">
                                            <i class="fas fa-user fs-20 text-primary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $student->name }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-envelope me-1"></i>{{ $student->email }}
                                    </p>
                                    @if($student->phone)
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-phone me-1"></i>{{ $student->phone }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-book fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">إجمالي الكورسات</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total_enrollments'] }}</h4>
                                    <span class="badge bg-primary-transparent">كل التسجيلات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-play-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">الكورسات النشطة</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['active_enrollments'] }}</h4>
                                    <span class="badge bg-success-transparent">قيد التعلم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">الكورسات المكتملة</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['completed_enrollments'] }}</h4>
                                    <span class="badge bg-info-transparent">منتهية</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">معلقة / ملغية</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['pending_enrollments'] + $stats['suspended_enrollments'] + $stats['cancelled_enrollments'] }}</h4>
                                    <span class="badge bg-warning-transparent">قيد الانتظار</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Statistics -->
            <div class="row mb-4">
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-2">متوسط التقدم الإجمالي</p>
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($stats['average_progress'], 1) }}%</h3>
                            <div class="progress mt-3" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ $stats['average_progress'] }}%"
                                     aria-valuenow="{{ $stats['average_progress'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <p class="text-muted mb-2">متوسط الدرجات</p>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($stats['average_grade'], 1) }}%</h3>
                            <div class="progress mt-3" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $stats['average_grade'] }}%"
                                     aria-valuenow="{{ $stats['average_grade'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <i class="fas fa-list me-2"></i>قائمة الكورسات المسجلة
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 40px;">#</th>
                                            <th scope="col" style="min-width: 250px;">اسم الكورس</th>
                                            <th scope="col" style="min-width: 150px;">التصنيف</th>
                                            <th scope="col" style="min-width: 150px;">المدرب</th>
                                            <th scope="col" style="min-width: 130px;">تاريخ التسجيل</th>
                                            <th scope="col" style="min-width: 120px;">الحالة</th>
                                            <th scope="col" style="min-width: 120px;">نسبة الإكمال</th>
                                            <th scope="col" style="min-width: 100px;">الدرجة</th>
                                            <th scope="col" style="min-width: 100px;">الشهادة</th>
                                            <th scope="col" style="min-width: 150px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($enrollments as $enrollment)
                                            <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($enrollment->course->image)
                                                            @php
                                                                $imagePath = $enrollment->course->image;
                                                                if (strpos($imagePath, 'http') === 0) {
                                                                    $imageUrl = $imagePath;
                                                                } else {
                                                                    $imageUrl = \Storage::disk('public')->url($imagePath);
                                                                }
                                                            @endphp
                                                            <img src="{{ $imageUrl }}"
                                                                 alt="{{ $enrollment->course->title }}"
                                                                 class="rounded me-2"
                                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                                 onerror="this.style.display='none';">
                                                        @endif
                                                        <div>
                                                            <a href="{{ route('courses.show', $enrollment->course->id) }}"
                                                               class="text-decoration-none fw-semibold">
                                                                {{ $enrollment->course->title }}
                                                            </a>
                                                            @if($enrollment->course->course_code)
                                                                <br>
                                                                <small class="text-muted">{{ $enrollment->course->course_code }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    @if($enrollment->course->category)
                                                        <span class="badge bg-secondary">{{ $enrollment->course->category->name }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($enrollment->course->instructor)
                                                        {{ $enrollment->course->instructor->name }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ $enrollment->enrollment_date->format('Y-m-d') }}
                                                    <br>
                                                    <small class="text-muted">{{ $enrollment->enrollment_date->diffForHumans() }}</small>
                                                </td>

                                                <td>
                                                    @if($enrollment->enrollment_status == 'active')
                                                        <span class="badge bg-success">نشط</span>
                                                    @elseif($enrollment->enrollment_status == 'pending')
                                                        <span class="badge bg-warning">قيد الانتظار</span>
                                                    @elseif($enrollment->enrollment_status == 'completed')
                                                        <span class="badge bg-info">مكتمل</span>
                                                    @elseif($enrollment->enrollment_status == 'suspended')
                                                        <span class="badge bg-danger">معلق</span>
                                                    @elseif($enrollment->enrollment_status == 'cancelled')
                                                        <span class="badge bg-secondary">ملغي</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">{{ number_format($enrollment->completion_percentage, 1) }}%</span>
                                                        <div class="progress" style="width: 80px; height: 8px;">
                                                            <div class="progress-bar {{ $enrollment->completion_percentage == 100 ? 'bg-success' : 'bg-primary' }}"
                                                                 role="progressbar"
                                                                 style="width: {{ $enrollment->completion_percentage }}%">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    @if($enrollment->grade)
                                                        <span class="badge {{ $enrollment->grade >= 50 ? 'bg-success' : 'bg-danger' }}">
                                                            {{ number_format($enrollment->grade, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($enrollment->certificate_issued)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-certificate me-1"></i>صدرت
                                                        </span>
                                                    @else
                                                        <span class="text-muted">لا</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <a href="{{ route('courses.show', $enrollment->course->id) }}"
                                                       class="btn btn-sm btn-info me-1"
                                                       title="عرض الكورس">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('courses.enrollments.index', $enrollment->course->id) }}"
                                                       class="btn btn-sm btn-primary me-1"
                                                       title="إدارة التسجيلات">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-danger fw-bold py-5">
                                                    <i class="fas fa-inbox fs-40 mb-3 d-block"></i>
                                                    لا توجد كورسات مسجلة لهذا الطالب
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('js')
@stop
