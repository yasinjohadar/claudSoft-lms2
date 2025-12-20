@extends('student.layouts.master')

@section('page-title')
   تفاصيل العمل
@stop

@section('css')
<style>
    .work-detail-image {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .badge-featured {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .info-value {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">تفاصيل العمل</h4>
                    <p class="mb-0 text-muted">معلومات تفصيلية عن العمل</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.my-works.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-2"></i> العودة للقائمة
                    </a>
                </div>
            </div>
            <!-- Page Header Close -->

            <div class="row">
                <!-- Image Section -->
                <div class="col-xl-5 col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            @if($studentWork->image)
                                <img src="{{ asset('storage/' . $studentWork->image) }}"
                                     alt="{{ $studentWork->title }}"
                                     class="work-detail-image">
                            @else
                                <div class="work-detail-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted" style="font-size: 100px;"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="col-xl-7 col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-info-circle me-2"></i>
                                معلومات العمل
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Title -->
                            <div class="mb-3">
                                <div class="info-label">
                                    <i class="bi bi-card-heading me-2 text-primary"></i>العنوان:
                                </div>
                                <h4 class="info-value fw-bold">{{ $studentWork->title }}</h4>
                            </div>

                            <!-- Description -->
                            @if($studentWork->description)
                                <div class="mb-3">
                                    <div class="info-label">
                                        <i class="bi bi-file-text me-2 text-primary"></i>الوصف:
                                    </div>
                                    <p class="info-value">{{ $studentWork->description }}</p>
                                </div>
                            @endif

                            <!-- Student -->
                            <div class="mb-3">
                                <div class="info-label">
                                    <i class="bi bi-person me-2 text-primary"></i>الطالب:
                                </div>
                                <div class="info-value d-flex align-items-center">
                                    <i class="bi bi-person-circle fs-4 me-2"></i>
                                    <span class="fw-semibold">{{ $studentWork->student->name ?? 'غير محدد' }}</span>
                                </div>
                            </div>

                            <!-- Status Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-toggle-on me-2 text-primary"></i>الحالة:
                                    </div>
                                    <span class="badge {{ $studentWork->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                        {{ $studentWork->is_active ? 'مفعل' : 'معطل' }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-star me-2 text-primary"></i>مميز:
                                    </div>
                                    <span class="badge {{ $studentWork->is_featured ? 'badge-featured' : 'bg-secondary' }} fs-6">
                                        {{ $studentWork->is_featured ? 'مميز' : 'عادي' }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-label">
                                        <i class="bi bi-sort-numeric-down me-2 text-primary"></i>الترتيب:
                                    </div>
                                    <span class="badge bg-info fs-6">{{ $studentWork->order }}</span>
                                </div>
                            </div>

                            <!-- Links -->
                            <div class="mb-3">
                                <div class="info-label mb-3">
                                    <i class="bi bi-link-45deg me-2 text-primary"></i>الروابط:
                                </div>
                                <div class="d-flex gap-3 flex-wrap">
                                    @if($studentWork->video_url)
                                        <a href="{{ $studentWork->video_url }}"
                                           target="_blank"
                                           class="btn btn-danger">
                                            <i class="bi bi-play-circle me-2"></i> مشاهدة الفيديو
                                        </a>
                                    @endif
                                    @if($studentWork->website_url)
                                        <a href="{{ $studentWork->website_url }}"
                                           target="_blank"
                                           class="btn btn-primary">
                                            <i class="bi bi-globe me-2"></i> زيارة الموقع
                                        </a>
                                    @endif
                                    @if(!$studentWork->video_url && !$studentWork->website_url)
                                        <span class="text-muted">لا توجد روابط متاحة</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-calendar-plus me-2 text-primary"></i>تاريخ الإنشاء:
                                    </div>
                                    <div class="info-value">
                                        {{ $studentWork->created_at ? $studentWork->created_at->format('Y-m-d H:i') : 'غير محدد' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">
                                        <i class="bi bi-calendar-check me-2 text-primary"></i>آخر تحديث:
                                    </div>
                                    <div class="info-value">
                                        {{ $studentWork->updated_at ? $studentWork->updated_at->format('Y-m-d H:i') : 'غير محدد' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@endsection

@section('js')
@endsection
