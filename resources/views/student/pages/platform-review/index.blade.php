@extends('student.layouts.master')

@include('shared.platform-review.assets')

@section('page-title')
    تقييمي للمنصة
@stop

@section('content')
    <div class="main-content app-content platform-review-page">
        <div class="container-fluid">

            @include('student.components.alerts')

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
                        <a href="{{ route('student.platform-review.create') }}" class="btn btn-primary rounded-pill px-3">
                            <i class="fas fa-plus me-2"></i>إضافة تقييم
                        </a>
                    @else
                        <a href="{{ route('student.platform-review.edit', $review->id) }}" class="btn btn-warning rounded-pill px-3">
                            <i class="fas fa-edit me-2"></i>تعديل التقييم
                        </a>
                    @endif
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">
                    @if($review)
                        <div class="platform-review-hero">
                            <div class="platform-review-hero__top">
                                <h2 class="platform-review-hero__title">
                                    <i class="fas fa-star"></i>
                                    تقييمك الحالي
                                </h2>
                                <p class="platform-review-hero__desc">
                                    شكراً لمشاركتك رأيك — تقييمك يساعدنا على تحسين المنصة وتجربة التعلم.
                                </p>
                            </div>
                        </div>

                        <div class="platform-review-card">
                            <div class="platform-review-card__header">
                                <h3 class="platform-review-card__title">
                                    <i class="fas fa-clipboard-check text-primary"></i>
                                    ملخص التقييم
                                </h3>
                                @if($review->is_active)
                                    <span class="platform-review-status platform-review-status--approved">
                                        <i class="fas fa-check-circle"></i>
                                        معتمد ومنشور
                                    </span>
                                @else
                                    <span class="platform-review-status platform-review-status--pending">
                                        <i class="fas fa-clock"></i>
                                        قيد المراجعة
                                    </span>
                                @endif
                            </div>

                            <div class="platform-review-card__body">
                                @if($review->is_active)
                                    <div class="platform-review-notice mb-4">
                                        <span class="platform-review-notice__icon"><i class="fas fa-check"></i></span>
                                        <div>تمت الموافقة على تقييمك وهو معروض الآن في صفحة آراء الطلاب.</div>
                                    </div>
                                @else
                                    <div class="platform-review-notice platform-review-notice--warning mb-4">
                                        <span class="platform-review-notice__icon"><i class="fas fa-hourglass-half"></i></span>
                                        <div>تقييمك في انتظار مراجعة الإدارة قبل نشره على المنصة.</div>
                                    </div>
                                @endif

                                <div class="platform-review-stars-display" aria-label="التقييم {{ $review->rating }} من 5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                    <span class="platform-review-stars-display__score">({{ $review->rating }}/5)</span>
                                </div>

                                @if($review->student_position)
                                    <div class="platform-review-field-block">
                                        <div class="platform-review-field-block__label">المسمى الوظيفي أو التعليمي</div>
                                        <div class="platform-review-field-block__content">{{ $review->student_position }}</div>
                                    </div>
                                @endif

                                <div class="platform-review-field-block">
                                    <div class="platform-review-field-block__label">رأيك حول المنصة والكورسات</div>
                                    <div class="platform-review-field-block__content">{{ $review->review_text }}</div>
                                </div>

                                @if($review->suggestion)
                                    <div class="platform-review-field-block">
                                        <div class="platform-review-field-block__label">اقتراحات التطوير</div>
                                        <div class="platform-review-field-block__content">{{ $review->suggestion }}</div>
                                    </div>
                                @endif

                                <div class="platform-review-meta">
                                    <span><i class="far fa-calendar-plus me-1"></i>تاريخ الإضافة: {{ $review->created_at->format('Y-m-d H:i') }}</span>
                                    <span><i class="far fa-clock me-1"></i>آخر تحديث: {{ $review->updated_at->format('Y-m-d H:i') }}</span>
                                </div>

                                <div class="platform-review-form-actions mt-4 pt-2">
                                    <a href="{{ route('student.platform-review.edit', $review->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>تعديل التقييم
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="platform-review-empty">
                            <div class="platform-review-empty__icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h2 class="platform-review-empty__title">لا يوجد تقييم بعد</h2>
                            <p class="platform-review-empty__text">
                                لم تُضِف تقييماً للمنصة حتى الآن. شاركنا تجربتك ورأيك — ملاحظاتك تساعدنا على تطوير الكورسات وتحسين التجربة.
                            </p>
                            <a href="{{ route('student.platform-review.create') }}" class="btn btn-primary platform-review-empty__btn">
                                <i class="fas fa-plus me-2"></i>إضافة تقييم
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
