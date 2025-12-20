@extends('student.layouts.master')

@section('page-title')
    كيف أكسب نقاط؟
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">كيف أكسب نقاط؟</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.points.index') }}">النقاط</a></li>
                            <li class="breadcrumb-item active">كيف أكسب نقاط؟</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('gamification.points.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <div class="row">
                <!-- طرق كسب النقاط -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>طرق كسب النقاط</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($pointsConfig))
                                <!-- إكمال الدروس -->
                                @if(isset($pointsConfig['complete_lesson']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-book-reader fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">إكمال درس</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند إكمال أي درس في الكورسات</p>
                                            <span class="badge bg-primary">+{{ $pointsConfig['complete_lesson'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- إكمال الكورس -->
                                @if(isset($pointsConfig['complete_course']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-graduation-cap fa-2x text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">إكمال كورس</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند إكمال كورس كامل</p>
                                            <span class="badge bg-success">+{{ $pointsConfig['complete_course'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- اجتياز اختبار -->
                                @if(isset($pointsConfig['pass_quiz']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-check-circle fa-2x text-info"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">اجتياز اختبار</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند اجتياز أي اختبار</p>
                                            <span class="badge bg-info">+{{ $pointsConfig['pass_quiz'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- الحصول على شارة -->
                                @if(isset($pointsConfig['earn_badge']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-medal fa-2x text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">الحصول على شارة</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند الحصول على أي شارة جديدة</p>
                                            <span class="badge bg-warning">+{{ $pointsConfig['earn_badge'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- إكمال إنجاز -->
                                @if(isset($pointsConfig['complete_achievement']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-trophy fa-2x text-danger"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">إكمال إنجاز</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند إكمال أي إنجاز</p>
                                            <span class="badge bg-danger">+{{ $pointsConfig['complete_achievement'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- السلسلة اليومية -->
                                @if(isset($pointsConfig['daily_streak']))
                                    <div class="d-flex align-items-start mb-4 p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="fas fa-fire fa-2x text-danger"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">السلسلة اليومية</h6>
                                            <p class="text-muted mb-2">احصل على نقاط عند الحفاظ على السلسلة اليومية</p>
                                            <span class="badge bg-danger">+{{ $pointsConfig['daily_streak'] }} نقطة</span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا توجد معلومات متاحة حالياً
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- مضاعفات السلسلة -->
                    @if(isset($streakMultipliers) && count($streakMultipliers) > 0)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-fire me-2 text-danger"></i>مضاعفات السلسلة اليومية</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">احصل على مضاعفات إضافية عند الحفاظ على السلسلة اليومية:</p>
                                <div class="row">
                                    @foreach($streakMultipliers as $days => $multiplier)
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <div class="me-3">
                                                    <i class="fas fa-calendar-check fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0">{{ $days }} يوم متتالي</h6>
                                                    <span class="badge bg-primary">x{{ $multiplier }} مضاعف</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- معلومات إضافية -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>نصائح</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>استمر في التعلم:</strong> كل درس تكملة يعطيك نقاط
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>حافظ على السلسلة:</strong> السلسلة اليومية تعطيك مضاعفات
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>أكمل الاختبارات:</strong> اجتياز الاختبارات يعطيك نقاط إضافية
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>استخدم النقاط:</strong> استخدم النقاط في المتجر لشراء معززات
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2 text-primary"></i>إجراءات سريعة</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('gamification.points.index') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-star me-1"></i>عرض نقاطي
                            </a>
                            <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-history me-1"></i>سجل النقاط
                            </a>
                            <a href="{{ route('gamification.shop.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-store me-1"></i>زيارة المتجر
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



