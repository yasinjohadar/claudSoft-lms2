@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التقييم
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل التقييم</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.platform-reviews.index') }}">تقييمات المنصة</a></li>
                            <li class="breadcrumb-item active">تفاصيل التقييم</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.platform-reviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Review Details -->
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-star me-2"></i>تفاصيل التقييم
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($review->student_image)
                                            <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}" class="avatar avatar-lg rounded-circle me-3">
                                        @else
                                            <div class="avatar avatar-lg rounded-circle bg-primary-transparent me-3">
                                                <span class="fw-bold fs-20">{{ substr($review->student_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="mb-1">{{ $review->student_name }}</h5>
                                            @if($review->student_position)
                                                <p class="text-muted mb-0">
                                                    <i class="fas fa-briefcase me-1"></i>{{ $review->student_position }}
                                                </p>
                                            @endif
                                            @if($review->user)
                                                <p class="text-muted mb-0">
                                                    <i class="fas fa-envelope me-1"></i>{{ $review->user->email }}
                                                </p>
                                            @elseif($review->student_email)
                                                <p class="text-muted mb-0">
                                                    <i class="fas fa-envelope me-1"></i>{{ $review->student_email }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">التقييم</label>
                                    <div class="d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-warning fs-20 me-1"></i>
                                            @else
                                                <i class="far fa-star text-warning fs-20 me-1"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 fw-semibold">({{ $review->rating }}/5)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">نص التقييم</label>
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0">{{ $review->review_text }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($review->suggestion)
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">الاقتراحات</label>
                                        <div class="p-3 bg-light rounded">
                                            <p class="mb-0">{{ $review->suggestion }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الإضافة</label>
                                    <p class="text-muted">{{ $review->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">آخر تحديث</label>
                                    <p class="text-muted">{{ $review->updated_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">الحالة الحالية</label>
                                <div>
                                    @if($review->is_active)
                                        <span class="badge bg-success fs-14 px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>مقبول
                                        </span>
                                    @else
                                        <span class="badge bg-warning fs-14 px-3 py-2">
                                            <i class="fas fa-clock me-1"></i>في الانتظار
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">التميز</label>
                                <div>
                                    @if($review->is_featured)
                                        <span class="badge bg-info fs-14 px-3 py-2">
                                            <i class="fas fa-heart me-1"></i>مميز
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-transparent fs-14 px-3 py-2">
                                            غير مميز
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid gap-2">
                                @if(!$review->is_active)
                                    <form action="{{ route('admin.platform-reviews.approve', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('هل أنت متأكد من الموافقة على هذا التقييم؟');">
                                            <i class="fas fa-check-circle me-2"></i>موافقة
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.platform-reviews.reject', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('هل أنت متأكد من رفض هذا التقييم؟');">
                                            <i class="fas fa-times-circle me-2"></i>رفض
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.platform-reviews.toggle-featured', $review->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-heart me-2"></i>{{ $review->is_featured ? 'إلغاء التميز' : 'تمييز' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.platform-reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

