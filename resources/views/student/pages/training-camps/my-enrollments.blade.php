@extends('student.layouts.master')

@section('page-title')
   تسجيلاتي في المعسكرات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-list-check me-2"></i>
                        تسجيلاتي في المعسكرات التدريبية
                    </h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.training-camps.index') }}">المعسكرات</a></li>
                            <li class="breadcrumb-item active">تسجيلاتي</li>
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

            <!-- Filter Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.training-camps.my-enrollments') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">حالة التسجيل</label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i> تصفية
                            </button>
                            <a href="{{ route('student.training-camps.my-enrollments') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enrollments List -->
            @if($enrollments->count() > 0)
                <div class="row">
                    @foreach($enrollments as $enrollment)
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold mb-2">{{ $enrollment->camp->name }}</h5>
                                            @if($enrollment->camp->category)
                                                <span class="badge bg-info">{{ $enrollment->camp->category->name }}</span>
                                            @endif
                                        </div>
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-warning text-dark',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'cancelled' => 'bg-secondary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusColors[$enrollment->status] ?? 'bg-secondary' }}">
                                            {{ $enrollment->status_label }}
                                        </span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                تاريخ البداية
                                            </small>
                                            <strong>{{ $enrollment->camp->start_date->format('Y-m-d') }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                تاريخ النهاية
                                            </small>
                                            <strong>{{ $enrollment->camp->end_date->format('Y-m-d') }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-cash me-1"></i>
                                                السعر
                                            </small>
                                            <strong class="text-primary">${{ number_format($enrollment->camp->price, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-credit-card me-1"></i>
                                                حالة الدفع
                                            </small>
                                            @php
                                                $paymentColors = [
                                                    'unpaid' => 'bg-warning text-dark',
                                                    'paid' => 'bg-success',
                                                    'refunded' => 'bg-secondary'
                                                ];
                                            @endphp
                                            <span class="badge {{ $paymentColors[$enrollment->payment_status] ?? 'bg-secondary' }}">
                                                {{ $enrollment->payment_status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($enrollment->camp->instructor_name)
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill-gear me-1"></i>
                                                المدرب: <strong>{{ $enrollment->camp->instructor_name }}</strong>
                                            </small>
                                        </div>
                                    @endif

                                    @if($enrollment->camp->location)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                الموقع: <strong>{{ $enrollment->camp->location }}</strong>
                                            </small>
                                        </div>
                                    @endif

                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-clock me-1"></i>
                                        تم التسجيل: {{ $enrollment->created_at->diffForHumans() }}
                                    </div>

                                    @if($enrollment->notes)
                                        <div class="alert alert-info py-2 mb-3">
                                            <small><strong>ملاحظات:</strong> {{ $enrollment->notes }}</small>
                                        </div>
                                    @endif

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.training-camps.show', $enrollment->camp) }}"
                                           class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="bi bi-eye me-1"></i> عرض التفاصيل
                                        </a>

                                        @if($enrollment->status !== 'approved' && $enrollment->payment_status !== 'paid' && $enrollment->status !== 'cancelled')
                                            <form action="{{ route('student.training-camps.cancel-enrollment', $enrollment->id) }}"
                                                  method="POST"
                                                  class="flex-fill"
                                                  onsubmit="return confirm('هل أنت متأكد من إلغاء التسجيل؟')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                    <i class="bi bi-x-circle me-1"></i> إلغاء التسجيل
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $enrollments->links() }}
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">لا توجد تسجيلات</h5>
                        <p class="text-muted mb-4">لم تقم بالتسجيل في أي معسكر تدريبي بعد</p>
                        <a href="{{ route('student.training-camps.index') }}" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> تصفح المعسكرات المتاحة
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
