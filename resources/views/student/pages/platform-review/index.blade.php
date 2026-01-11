@extends('student.layouts.master')

@section('page-title')
    تقييمي للمنصة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقييمي للمنصة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">تقييمي للمنصة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    @if(!$review)
                        <a href="{{ route('student.platform-review.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>إضافة تقييم
                        </a>
                    @else
                        <a href="{{ route('student.platform-review.edit', $review->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>تعديل التقييم
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    @if($review)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-star me-2"></i>تقييمك الحالي
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Status Alert -->
                                @if($review->is_active)
                                    <div class="alert alert-success mb-4">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>تم الموافقة على تقييمك</strong>
                                        <p class="mb-0 mt-2">تم الموافقة على تقييمك وهو معروض الآن على صفحة آراء الطلاب.</p>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-4">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>تقييمك قيد المراجعة</strong>
                                        <p class="mb-0 mt-2">تقييمك في انتظار مراجعة من قبل الإدارة قبل نشره على المنصة.</p>
                                    </div>
                                @endif

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">التقييم</label>
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fas fa-star text-warning fs-24 me-1"></i>
                                                @else
                                                    <i class="far fa-star text-warning fs-24 me-1"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2 fw-semibold fs-18">({{ $review->rating }}/5)</span>
                                        </div>
                                    </div>
                                </div>

                                @if($review->student_position)
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">المنصب</label>
                                            <p class="text-muted">{{ $review->student_position }}</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">نص التقييم</label>
                                        <div class="p-3 bg-light rounded">
                                            <p class="mb-0">{{ $review->review_text }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($review->suggestion)
                                    <div class="row mb-4">
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

                                <hr>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('student.platform-review.edit', $review->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>تعديل التقييم
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card custom-card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-star fa-5x text-muted mb-4 opacity-25"></i>
                                <h4 class="text-muted mb-3">لا يوجد تقييم</h4>
                                <p class="text-muted mb-4">لم تقم بإضافة تقييم للمنصة بعد. شاركنا رأيك وتقييمك!</p>
                                <a href="{{ route('student.platform-review.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>إضافة تقييم
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection

