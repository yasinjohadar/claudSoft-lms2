@extends('student.layouts.master')

@section('page-title')
    كيف أكسب نقاط؟
@stop

@section('content')
<div class="main-content app-content student-points-page">
    <div class="container-fluid">

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="mb-1">كيف أكسب نقاط؟</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('gamification.points.index') }}">النقاط</a></li>
                        <li class="breadcrumb-item active">كيف أكسب نقاط؟</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('gamification.points.index') }}" class="btn btn-outline-primary btn-sm mt-3 mt-md-0">
                <i class="ri ri-arrow-right-line me-1"></i>العودة
            </a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                @forelse($earningByCategory ?? [] as $categoryKey => $category)
                    <div class="card custom-card student-quizzes-panel mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">{{ $category['label'] }}</h6>
                            <div class="row g-3">
                                @foreach($category['methods'] as $method)
                                    <div class="col-md-6">
                                        <div class="student-points-earn-item d-flex align-items-start gap-3 p-3 rounded h-100">
                                            <span class="student-points-earn-item__icon">
                                                <i class="ri {{ $method['icon'] }}"></i>
                                            </span>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $method['title'] }}</h6>
                                                <p class="text-muted fs-12 mb-2">{{ $method['description'] }}</p>
                                                @if($method['extra'] ?? null)
                                                    <p class="text-muted fs-11 mb-2">{{ $method['extra'] }}</p>
                                                @endif
                                                <span class="badge bg-primary-transparent">+{{ number_format($method['points']) }} نقطة</span>
                                                @if(($method['xp'] ?? 0) > 0)
                                                    <span class="badge bg-success-transparent">+{{ $method['xp'] }} XP</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">لا توجد معلومات متاحة حالياً</div>
                @endforelse

                @if(count($streakMilestones ?? []) > 0)
                    <div class="card custom-card student-quizzes-panel mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="ri ri-fire-line text-danger me-1"></i>معالم السلسلة اليومية</h6>
                            <div class="row g-3">
                                @foreach($streakMilestones as $milestone)
                                    <div class="col-md-6">
                                        <div class="student-points-earn-item p-3 rounded">
                                            <h6 class="mb-1">{{ $milestone['days'] }} يوم متتالي</h6>
                                            <p class="text-muted fs-12 mb-2">{{ $milestone['description'] }}</p>
                                            <span class="badge bg-danger-transparent">+{{ number_format($milestone['points']) }} نقطة</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(count($streakMultipliers ?? []) > 0)
                    <div class="card custom-card student-quizzes-panel">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="ri ri-flashlight-line text-warning me-1"></i>مضاعفات السلسلة</h6>
                            <div class="row g-3">
                                @foreach($streakMultipliers as $days => $multiplier)
                                    @if((int) $days > 0)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="student-points-earn-item p-3 rounded text-center">
                                                <div class="fw-semibold">{{ $days }} يوم</div>
                                                <span class="badge bg-primary mt-2">×{{ $multiplier }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card custom-card student-quizzes-panel mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">نصائح سريعة</h6>
                        <ul class="list-unstyled mb-0 fs-13">
                            <li class="mb-2"><i class="ri ri-check-line text-success me-1"></i> أكمل الدروس والاختبارات يومياً</li>
                            <li class="mb-2"><i class="ri ri-check-line text-success me-1"></i> حافظ على سلسلة الدخول للمضاعفات</li>
                            <li class="mb-2"><i class="ri ri-check-line text-success me-1"></i> شارك الكورسات وادعُ أصدقاءك</li>
                            <li><i class="ri ri-check-line text-success me-1"></i> استخدم النقاط في المتجر</li>
                        </ul>
                    </div>
                </div>

                <div class="card custom-card student-quizzes-panel">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">رابط الدعوة</h6>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-sm" value="{{ $referralLink }}" readonly>
                        </div>
                        <a href="{{ route('gamification.points.index') }}" class="btn btn-primary w-100 mb-2">عرض نقاطي</a>
                        <a href="{{ route('gamification.shop.index') }}" class="btn btn-outline-primary w-100">زيارة المتجر</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
