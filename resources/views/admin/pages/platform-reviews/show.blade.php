@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التقييم
@stop

@section('styles')
    @include('admin.pages.platform-reviews.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid px-3 px-lg-4">

            @include('admin.components.alerts')

            <div class="admin-show-layout">

                <div class="my-4 page-header-breadcrumb platform-reviews-page-animate dashboard-fade-in">
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.platform-reviews.index') }}">تقييمات المنصة</a></li>
                            <li class="breadcrumb-item active">تفاصيل التقييم</li>
                        </ol>
                    </nav>
                </div>

                <div class="group-show-hero dashboard-fade-in platform-reviews-page-animate mb-4">
                    <div class="row align-items-start g-3">
                        <div class="col-lg-8">
                            <span class="group-show-hero__eyebrow"><i class="fe fe-star me-1"></i>تفاصيل التقييم</span>
                            <h2 class="group-show-hero__title mb-2">{{ $review->student_name }}</h2>
                            <p class="group-show-hero__desc mb-0">
                                @if($review->student_position){{ $review->student_position }} · @endif
                                {{ $review->rating }}/5 نجوم
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <div class="group-show-actions">
                                <a href="{{ route('admin.platform-reviews.index') }}" class="group-show-action">
                                    <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                    <span class="group-show-action__text">العودة للقائمة</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card custom-card group-show-members-card dashboard-fade-in platform-reviews-page-animate mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-message-square"></i></span>
                                    محتوى التقييم
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                    @if($review->student_image)
                                        <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}" class="avatar avatar-lg rounded-circle flex-shrink-0">
                                    @else
                                        <div class="avatar avatar-lg rounded-circle bg-primary-transparent flex-shrink-0">
                                            <span class="fw-bold fs-18">{{ mb_substr($review->student_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h5 class="mb-1">{{ $review->student_name }}</h5>
                                        @if($review->student_position)
                                            <p class="text-muted mb-1 small"><i class="fe fe-briefcase me-1"></i>{{ $review->student_position }}</p>
                                        @endif
                                        @if($review->user)
                                            <p class="text-muted mb-0 small text-truncate" title="{{ $review->user->email }}"><i class="fe fe-mail me-1"></i>{{ $review->user->email }}</p>
                                        @elseif($review->student_email)
                                            <p class="text-muted mb-0 small text-truncate" title="{{ $review->student_email }}"><i class="fe fe-mail me-1"></i>{{ $review->student_email }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="mb-2 fw-semibold">التقييم</p>
                                    <span class="platform-reviews-stars fs-16">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fe fe-star" style="opacity: {{ $i <= $review->rating ? '1' : '0.25' }};"></i>
                                        @endfor
                                    </span>
                                    <span class="ms-2 fw-semibold">({{ $review->rating }}/5)</span>
                                </div>

                                <div class="mb-4">
                                    <p class="mb-2 fw-semibold">نص التقييم</p>
                                    <div class="platform-reviews-show-text">{{ $review->review_text }}</div>
                                </div>

                                @if($review->suggestion)
                                    <div class="mb-0">
                                        <p class="mb-2 fw-semibold">الاقتراحات</p>
                                        <div class="platform-reviews-show-text">{{ $review->suggestion }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card custom-card group-show-members-card dashboard-fade-in platform-reviews-page-animate mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                                    معلومات التقييم
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="qb-show-meta-list">
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">الحالة</div>
                                        <div class="qb-show-meta-list__value">
                                            @if($review->is_active)
                                                <span class="assignments-status-chip assignments-status-chip--published">مقبول</span>
                                            @else
                                                <span class="assignments-status-chip assignments-status-chip--pending">في الانتظار</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">التميز</div>
                                        <div class="qb-show-meta-list__value">
                                            @if($review->is_featured)
                                                <span class="assignments-status-chip assignments-status-chip--graded"><i class="fe fe-heart me-1"></i>مميز</span>
                                            @else
                                                <span class="text-muted fw-normal">غير مميز</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">تاريخ الإضافة</div>
                                        <div class="qb-show-meta-list__value">{{ $review->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">آخر تحديث</div>
                                        <div class="qb-show-meta-list__value">{{ $review->updated_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card dashboard-fade-in platform-reviews-page-animate">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-zap"></i></span>
                                    إجراءات سريعة
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="d-grid gap-2">
                                    @if(!$review->is_active)
                                        <form action="{{ route('admin.platform-reviews.approve', $review->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('هل أنت متأكد من الموافقة على هذا التقييم؟');">
                                                <i class="fe fe-check me-1"></i>موافقة
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.platform-reviews.reject', $review->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning-light btn-sm w-100" onclick="return confirm('هل أنت متأكد من رفض هذا التقييم؟');">
                                                <i class="fe fe-x me-1"></i>رفض
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.platform-reviews.toggle-featured', $review->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-info-light btn-sm w-100">
                                            <i class="fe fe-heart me-1"></i>{{ $review->is_featured ? 'إلغاء التميز' : 'تمييز' }}
                                        </button>
                                    </form>
                                    <hr class="my-2">
                                    <form action="{{ route('admin.platform-reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-light btn-sm w-100">
                                            <i class="fe fe-trash-2 me-1"></i>حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@stop
