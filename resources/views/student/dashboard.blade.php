@extends('student.layouts.master')

@section('page-title')
لوحة التحكم
@stop

@push('styles')
<style>
    /* Enhanced Stats Cards - Big Colorful Style */
    .stats-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        min-height: 140px;
        color: white !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .stats-card:hover::before {
        opacity: 1;
    }

    .stats-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25) !important;
    }

    .stats-card .card-body {
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .stats-card .stat-icon {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        color: white;
        opacity: 0.95;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .stats-card .stat-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        letter-spacing: -0.5px;
    }

    .stats-card .stat-subtitle {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.95);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Card specific colors with enhanced gradients */
    .stats-card.card-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stats-card.card-info {
        background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
    }

    .stats-card.card-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stats-card.card-danger {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stats-card.card-success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .stats-card.card-purple {
        background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
    }

    .stats-card.card-teal {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stats-card.card-dark {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    /* Quick Links Enhancement */
    .quick-link-card {
        border: 2px solid #f0f0f0;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 2rem 1.5rem;
        text-align: center;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .quick-link-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
        transition: left 0.5s ease;
    }

    .quick-link-card:hover::before {
        left: 100%;
    }

    .quick-link-card:hover {
        transform: translateY(-5px);
        border-color: #667eea;
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.2);
        text-decoration: none;
    }

    .quick-link-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .quick-link-card:hover .icon-wrapper {
        transform: scale(1.15) rotate(5deg);
    }

    .quick-link-card .link-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--default-text-color);
        margin: 0;
        transition: color 0.3s ease;
    }

    .quick-link-card:hover .link-title {
        color: #667eea;
    }

    /* Welcome Section */
    .page-header-breadcrumb h4 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Custom Card Styling */
    .custom-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .custom-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .custom-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 2px solid #f0f0f0;
        border-radius: 16px 16px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }

    .custom-card .card-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #2c3e50;
        margin: 0;
    }

    /* Progress bars enhancement */
    .progress {
        height: 10px;
        border-radius: 10px;
        background: #f0f0f0;
        overflow: hidden;
    }

    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stats-card, .quick-link-card {
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }

    .stats-card:nth-child(1) { animation-delay: 0.1s; }
    .stats-card:nth-child(2) { animation-delay: 0.2s; }
    .stats-card:nth-child(3) { animation-delay: 0.3s; }
    .stats-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-2">مرحباً بك! 👋</h4>
                <p class="mb-0 text-muted fs-6">
                    <i class="bi bi-calendar-event me-2"></i>{{ now()->locale('ar')->translatedFormat('l، j F Y') }}
                </p>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <a href="{{ route('student.courses.my-courses') }}" class="text-decoration-none">
                    <div class="card stats-card card-primary">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <h3 class="stat-title">{{ $courseStats['total_courses'] ?? 0 }}</h3>
                            <p class="stat-subtitle">إجمالي الكورسات المسجلة</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <a href="{{ route('student.question-module.stats.index') }}" class="text-decoration-none">
                    <div class="card stats-card card-info">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <h3 class="stat-title">{{ $questionModuleStats['average_score'] ?? 0 }}%</h3>
                            <p class="stat-subtitle">متوسط درجات الاختبارات</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <a href="{{ route('student.question-module.stats.index') }}" class="text-decoration-none">
                    <div class="card stats-card card-warning">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <h3 class="stat-title">{{ $questionModuleStats['passed_attempts'] ?? 0 }}</h3>
                            <p class="stat-subtitle">اختبارات ناجحة</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <a href="{{ route('student.question-module.stats.index') }}" class="text-decoration-none">
                    <div class="card stats-card card-danger">
                        <div class="card-body">
                            <div class="stat-icon">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <h3 class="stat-title">{{ $questionModuleStats['total_attempts'] ?? 0 }}</h3>
                            <p class="stat-subtitle">إجمالي المحاولات</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-link-45deg me-2"></i>روابط سريعة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.courses.my-courses') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-primary-transparent">
                                        <i class="bi bi-book text-primary fs-4"></i>
                                    </div>
                                    <p class="link-title">كورساتي</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.question-module.stats.index') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-info-transparent">
                                        <i class="bi bi-question-circle text-info fs-4"></i>
                                    </div>
                                    <p class="link-title">إحصائيات الاختبارات</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.training-camps.index') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-success-transparent">
                                        <i class="bi bi-buildings text-success fs-4"></i>
                                    </div>
                                    <p class="link-title">المعسكرات التدريبية</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.invoices.index') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-danger-transparent">
                                        <i class="bi bi-receipt-cutoff text-danger fs-4"></i>
                                    </div>
                                    <p class="link-title">فواتيري</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('gamification.badges.index') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-warning-transparent">
                                        <i class="bi bi-award text-warning fs-4"></i>
                                    </div>
                                    <p class="link-title">شاراتي</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('gamification.leaderboards.index') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-info-transparent">
                                        <i class="bi bi-bar-chart-line text-info fs-4"></i>
                                    </div>
                                    <p class="link-title">لوحة المتصدرين</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('student.progress.overview') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-secondary-transparent">
                                        <i class="bi bi-graph-up text-secondary fs-4"></i>
                                    </div>
                                    <p class="link-title">تقدمي في الكورسات</p>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                <a href="{{ route('gamification.dashboard') }}" class="quick-link-card d-block">
                                    <div class="icon-wrapper bg-purple-transparent">
                                        <i class="bi bi-trophy text-purple fs-4"></i>
                                    </div>
                                    <p class="link-title">لوحة التلعيب</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-xl-8">
                <!-- Courses In Progress -->
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            <i class="bi bi-arrow-clockwise me-2"></i>الكورسات قيد التقدم
                        </div>
                        <a href="{{ route('student.courses.my-courses') }}" class="btn btn-sm btn-primary-light">
                            عرض الكل
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <lottie-player src="https://assets9.lottiefiles.com/packages/lf20_x62chJ.json" background="transparent" speed="1" style="width: 200px; height: 200px; margin: 0 auto;" loop autoplay></lottie-player>
                            </div>
                            <h5 class="text-muted mb-2">لا توجد كورسات قيد التقدم</h5>
                            <p class="text-muted small">ابدأ رحلتك التعليمية الآن!</p>
                            <a href="{{ route('student.courses.index') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-search me-2"></i>تصفح الكورسات
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card custom-card mt-3">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-clock-history me-2"></i>آخر الأنشطة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-clipboard-data" style="font-size: 4rem; color: #e0e0e0;"></i>
                            </div>
                            <h6 class="text-muted mb-2">لا توجد أنشطة حديثة</h6>
                            <p class="text-muted small mb-0">ستظهر آخر أنشطتك هنا</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-xl-4">
                <!-- Alerts -->
                <div class="card custom-card">
                    <div class="card-header bg-warning-transparent">
                        <div class="card-title text-warning">
                            <i class="bi bi-bell-fill me-2"></i>تنبيهات مهمة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                            <p class="text-muted mt-2 mb-0">لا توجد تنبيهات جديدة</p>
                        </div>
                    </div>
                </div>

                <!-- Latest Badges -->
                <div class="card custom-card mt-3">
                    <div class="card-header bg-warning-transparent">
                        <div class="card-title text-warning">
                            <i class="bi bi-trophy-fill me-2"></i>آخر الشارات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            <i class="bi bi-award fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-2 mb-0 fs-12">لم تحصل على شارات بعد</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card custom-card mt-3">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-pie-chart-fill me-2"></i>إحصائيات سريعة
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fs-13">الواجبات المسلمة</span>
                                <span class="fs-13 fw-semibold">0/0</span>
                            </div>
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-success" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fs-13">الكورسات المكتملة</span>
                                <span class="fs-13 fw-semibold">0/0</span>
                            </div>
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-primary" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fs-13 text-muted">
                                    <i class="bi bi-question-circle me-1"></i>الاختبارات المكتملة
                                </span>
                                <span class="fw-semibold">{{ $questionModuleStats['total_attempts'] ?? 0 }}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="fs-13 text-muted">
                                    <i class="bi bi-check-circle me-1"></i>اختبارات ناجحة
                                </span>
                                <span class="fw-semibold text-success">{{ $questionModuleStats['passed_attempts'] ?? 0 }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="fs-13 text-muted">
                                    <i class="bi bi-percent me-1"></i>متوسط الدرجات
                                </span>
                                <span class="fw-semibold text-primary">{{ $questionModuleStats['average_score'] ?? 0 }}%</span>
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

@push('scripts')
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script>
    // تحريك العدادات عند التحميل
    document.addEventListener('DOMContentLoaded', function() {
        const stats = document.querySelectorAll('.stat-title');
        stats.forEach(stat => {
            const finalValue = parseInt(stat.textContent);
            if (!isNaN(finalValue)) {
                let currentValue = 0;
                const increment = Math.ceil(finalValue / 50);
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        stat.textContent = finalValue + (stat.textContent.includes('%') ? '%' : '');
                        clearInterval(timer);
                    } else {
                        stat.textContent = currentValue + (stat.textContent.includes('%') ? '%' : '');
                    }
                }, 30);
            }
        });
    });
</script>
@endpush
