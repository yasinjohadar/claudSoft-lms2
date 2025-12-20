@extends('student.layouts.master')

@section('page-title')
   {{ $trainingCamp->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.training-camps.index') }}">المعسكرات</a></li>
                            <li class="breadcrumb-item active">{{ $trainingCamp->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    {!! \Session::get('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {!! \Session::get('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-8">
                    @if($trainingCamp->image)
                        <div class="card shadow-sm border-0 mb-4">
                            <img src="{{ asset('storage/' . $trainingCamp->image) }}"
                                 alt="{{ $trainingCamp->name }}"
                                 class="card-img-top"
                                 style="max-height: 400px; object-fit: cover;">
                        </div>
                    @endif

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h2 class="fw-bold mb-3">{{ $trainingCamp->name }}</h2>

                            <div class="mb-3">
                                @if($trainingCamp->category)
                                    <span class="badge bg-info me-2">{{ $trainingCamp->category->name }}</span>
                                @endif
                                @php
                                    $statusColors = ['upcoming' => 'bg-primary', 'ongoing' => 'bg-success', 'completed' => 'bg-secondary'];
                                @endphp
                                <span class="badge {{ $statusColors[$trainingCamp->status] ?? 'bg-secondary' }}">
                                    {{ $trainingCamp->status_label }}
                                </span>
                                @if($trainingCamp->is_featured)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> مميز</span>
                                @endif
                            </div>

                            <h5 class="mb-3">الوصف:</h5>
                            <p class="text-muted">{{ $trainingCamp->description ?? 'لا يوجد وصف متاح' }}</p>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="fw-semibold"><i class="bi bi-person-fill-gear me-2"></i>المدرب:</h6>
                                    <p class="text-muted">{{ $trainingCamp->instructor_name ?? 'غير محدد' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="fw-semibold"><i class="bi bi-geo-alt me-2"></i>الموقع:</h6>
                                    <p class="text-muted">{{ $trainingCamp->location ?? 'غير محدد' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="fw-semibold"><i class="bi bi-calendar-event me-2"></i>تاريخ البداية:</h6>
                                    <p class="text-muted">{{ $trainingCamp->start_date->format('Y-m-d') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="fw-semibold"><i class="bi bi-calendar-check me-2"></i>تاريخ النهاية:</h6>
                                    <p class="text-muted">{{ $trainingCamp->end_date->format('Y-m-d') }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h6 class="fw-semibold"><i class="bi bi-clock me-2"></i>المدة:</h6>
                                    <span class="badge bg-secondary">{{ $trainingCamp->duration_days }} يوم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">التسجيل في المعسكر</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h2 class="fw-bold text-primary mb-0">${{ number_format($trainingCamp->price, 2) }}</h2>
                                <small class="text-muted">سعر المعسكر</small>
                            </div>

                            @if($trainingCamp->max_participants)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>المقاعد المتاحة:</span>
                                        <strong>{{ $trainingCamp->available_seats }} / {{ $trainingCamp->max_participants }}</strong>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar"
                                             style="width: {{ ($trainingCamp->current_participants / $trainingCamp->max_participants) * 100 }}%">
                                            {{ round(($trainingCamp->current_participants / $trainingCamp->max_participants) * 100) }}%
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>

                            @if($isEnrolled)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    أنت مسجل في هذا المعسكر
                                    <br>
                                    <small>الحالة: <strong>{{ $enrollment->status_label }}</strong></small>
                                </div>

                                @if($enrollment->status !== 'approved' && $enrollment->payment_status !== 'paid')
                                    <form action="{{ route('student.training-camps.cancel-enrollment', $trainingCamp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-danger w-100"
                                                onclick="return confirm('هل أنت متأكد من إلغاء التسجيل؟')">
                                            <i class="bi bi-x-circle me-1"></i> إلغاء التسجيل
                                        </button>
                                    </form>
                                @endif
                            @else
                                @if($trainingCamp->hasAvailableSeats())
                                    <form action="{{ route('student.training-camps.enroll', $trainingCamp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="bi bi-check-circle me-1"></i> سجل الآن
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary btn-lg w-100" disabled>
                                        <i class="bi bi-x-circle me-1"></i> المقاعد ممتلئة
                                    </button>
                                @endif
                            @endif

                            <a href="{{ route('student.training-camps.index') }}" class="btn btn-outline-secondary w-100 mt-3">
                                <i class="bi bi-arrow-left me-1"></i> العودة للمعسكرات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
