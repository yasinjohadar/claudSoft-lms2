@extends('student.layouts.master')

@section('page-title')
    الإحصائيات التفصيلية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">الإحصائيات التفصيلية</h4>
                <a href="{{ route('gamification.dashboard') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- إحصائيات عامة -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-2"><i class="fas fa-star fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['total_points'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي النقاط</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-2"><i class="fas fa-arrow-up fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['total_xp'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي XP</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-info mb-2"><i class="fas fa-fire fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['longest_streak'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">أطول سلسلة</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-2"><i class="fas fa-trophy fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['total_badges'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي الشارات</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- إحصائيات الكورسات -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-book me-2 text-primary"></i>إحصائيات الكورسات</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-primary">{{ number_format($courseStats['total_courses'] ?? 0) }}</h4>
                                        <small class="text-muted">إجمالي الكورسات</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-success">{{ number_format($courseStats['completed_courses'] ?? 0) }}</h4>
                                        <small class="text-muted">كورسات مكتملة</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-info">{{ number_format($courseStats['in_progress'] ?? 0) }}</h4>
                                        <small class="text-muted">قيد التنفيذ</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-warning">{{ number_format($courseStats['average_completion'] ?? 0, 1) }}%</h4>
                                        <small class="text-muted">متوسط الإكمال</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إحصائيات الاختبارات -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2 text-success"></i>إحصائيات الاختبارات</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-primary">{{ number_format($quizStats['total_attempts'] ?? 0) }}</h4>
                                        <small class="text-muted">إجمالي المحاولات</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-success">{{ number_format($quizStats['passed_attempts'] ?? 0) }}</h4>
                                        <small class="text-muted">محاولات ناجحة</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-info">{{ number_format($quizStats['average_score'] ?? 0, 1) }}%</h4>
                                        <small class="text-muted">متوسط النتيجة</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="fw-bold text-warning">{{ number_format($quizStats['pass_rate'] ?? 0, 1) }}%</h4>
                                        <small class="text-muted">معدل النجاح</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات النشاط -->
            @if(isset($activityStats))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-info"></i>إحصائيات النشاط</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="fw-bold text-primary">{{ number_format($activityStats['lessons_completed'] ?? 0) }}</h5>
                                    <small class="text-muted">دروس مكتملة</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="fw-bold text-success">{{ number_format($activityStats['assignments_submitted'] ?? 0) }}</h5>
                                    <small class="text-muted">واجبات مرفوعة</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="fw-bold text-info">{{ number_format($activityStats['days_active'] ?? 0) }}</h5>
                                    <small class="text-muted">أيام نشطة</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="fw-bold text-warning">{{ number_format($activityStats['hours_studied'] ?? 0, 1) }}</h5>
                                    <small class="text-muted">ساعات دراسة</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop



