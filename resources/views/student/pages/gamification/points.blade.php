@extends('student.layouts.master')

@section('page-title')
    النقاط
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">النقاط</h4>
                <div>
                    <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-history me-1"></i>سجل النقاط
                    </a>
                    <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-question-circle me-1"></i>كيف أكسب نقاط؟
                    </a>
                </div>
            </div>

            <!-- إحصائيات النقاط -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-2"><i class="fas fa-star fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-primary">{{ number_format($totalPoints ?? 0) }}</h2>
                            <p class="text-muted mb-0">إجمالي النقاط</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-2"><i class="fas fa-wallet fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-success">{{ number_format($availablePoints ?? 0) }}</h2>
                            <p class="text-muted mb-0">النقاط المتاحة</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-2"><i class="fas fa-shopping-cart fa-3x"></i></div>
                            <h2 class="fw-bold mb-1 text-warning">{{ number_format($spentPoints ?? 0) }}</h2>
                            <p class="text-muted mb-0">النقاط المستهلكة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>معلومات عن النقاط</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>كيف تستخدم النقاط؟</h6>
                                <p class="mb-2">يمكنك استخدام النقاط المتاحة في <a href="{{ route('gamification.shop.index') }}" class="alert-link">المتجر</a> لشراء:</p>
                                <ul class="mb-0">
                                    <li>معززات XP والنقاط</li>
                                    <li>عناصر تجميلية للملف الشخصي</li>
                                    <li>حماية السلسلة اليومية</li>
                                    <li>عناصر خاصة أخرى</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2 text-warning"></i>إجراءات سريعة</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('gamification.shop.index') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-store me-1"></i>زيارة المتجر
                            </a>
                            <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-history me-1"></i>عرض السجل
                            </a>
                            <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-question-circle me-1"></i>كيف أكسب نقاط؟
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



