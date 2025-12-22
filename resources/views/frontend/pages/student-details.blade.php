@extends('frontend.layouts.master')

@section('title', $student->name . ' - الملف الشخصي')
@section('meta_description', 'الملف الشخصي للطالب ' . $student->name)

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">الملف الشخصي للطالب</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.students.index') }}">الطلاب</a></li>
                        <li class="breadcrumb-item active">{{ $student->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Student Profile Section -->
<section class="student-profile-section py-5">
    <div class="container">
        <div class="row">

            <!-- Student Info Card -->
            <div class="col-lg-4 mb-4">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            @if($student->avatar)
                                <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}">
                            @else
                                <div class="avatar-placeholder-large">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h3 class="profile-name">{{ $student->name }}</h3>
                        <span class="profile-badge">
                            <i class="fa-solid fa-graduation-cap"></i> طالب
                        </span>
                    </div>

                    <div class="profile-body">
                        <h5 class="section-title">
                            <i class="fa-solid fa-info-circle"></i> المعلومات الشخصية
                        </h5>

                        @if($student->address)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-location-dot"></i> العنوان
                            </div>
                            <div class="info-value">{{ $student->address }}</div>
                        </div>
                        @endif

                        @if($student->date_of_birth)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-calendar-days"></i> تاريخ الميلاد
                            </div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') }}</div>
                        </div>
                        @endif

                        @if($student->gender)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-venus-mars"></i> الجنس
                            </div>
                            <div class="info-value">
                                {{ $student->gender == 'male' ? 'ذكر' : ($student->gender == 'female' ? 'أنثى' : $student->gender) }}
                            </div>
                        </div>
                        @endif

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-clock"></i> تاريخ التسجيل
                            </div>
                            <div class="info-value">{{ $student->created_at->format('Y-m-d') }}</div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-calendar-check"></i> عضو منذ
                            </div>
                            <div class="info-value">{{ $student->created_at->diffForHumans() }}</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Student Activity & Stats -->
            <div class="col-lg-8">

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <div class="stat-info">
                                <h4>{{ $stats['total_courses'] ?? 0 }}</h4>
                                <p>الكورسات المسجلة</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <div class="stat-info">
                                <h4>{{ $stats['certificates_count'] ?? 0 }}</h4>
                                <p>الشهادات المكتسبة</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                            <div class="stat-info">
                                <h4>{{ $stats['badges_count'] ?? 0 }}</h4>
                                <p>الإنجازات</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Section -->
                <div class="tabs-container">
                    <ul class="nav nav-tabs custom-tabs" id="studentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">
                                <i class="fa-solid fa-book me-2"></i>الكورسات
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="certificates-tab" data-bs-toggle="tab" data-bs-target="#certificates" type="button" role="tab">
                                <i class="fa-solid fa-certificate me-2"></i>الشهادات
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="badges-tab" data-bs-toggle="tab" data-bs-target="#badges" type="button" role="tab">
                                <i class="fa-solid fa-award me-2"></i>الأوسمة
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button" role="tab">
                                <i class="fa-solid fa-trophy me-2"></i>الإنجازات
                            </button>
                        </li>
                        @if($student->bio)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">
                                <i class="fa-solid fa-user me-2"></i>نبذة عني
                            </button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content" id="studentTabsContent">
                        <!-- Courses Tab -->
                        <div class="tab-pane fade show active" id="courses" role="tabpanel">
                            <div class="tab-content-card">
                                @if($enrollments && $enrollments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>اسم الكورس</th>
                                                <th>الحالة</th>
                                                <th>نسبة الإنجاز</th>
                                                <th>تاريخ التسجيل</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrollments as $enrollment)
                                            <tr>
                                                <td>
                                                    <strong>{{ $enrollment->course->title ?? 'كورس محذوف' }}</strong>
                                                </td>
                                                <td>
                                                    @if($enrollment->enrollment_status == 'completed')
                                                        <span class="badge bg-success">مكتمل</span>
                                                    @elseif($enrollment->enrollment_status == 'active')
                                                        <span class="badge bg-primary">نشط</span>
                                                    @elseif($enrollment->enrollment_status == 'suspended')
                                                        <span class="badge bg-warning">متوقف</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $enrollment->enrollment_status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px; width: 100px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: {{ $enrollment->completion_percentage ?? 0 }}%">
                                                            {{ number_format($enrollment->completion_percentage ?? 0, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '-' }}</td>
                                                <td>
                                                    @if($enrollment->course)
                                                    <a href="{{ route('frontend.course-details', $enrollment->course->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-eye"></i> عرض
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="empty-state text-center py-5">
                                    <i class="fa-solid fa-book fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد كورسات مسجلة</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Certificates Tab -->
                        <div class="tab-pane fade" id="certificates" role="tabpanel">
                            <div class="tab-content-card">
                                @if($certificates && $certificates->count() > 0)
                                <div class="row">
                                    @foreach($certificates as $certificate)
                                    <div class="col-md-6 mb-3">
                                        <div class="certificate-card">
                                            <div class="certificate-icon">
                                                <i class="fa-solid fa-certificate"></i>
                                            </div>
                                            <div class="certificate-info">
                                                <h5>{{ $certificate->course->title ?? 'كورس محذوف' }}</h5>
                                                <p class="text-muted mb-2">
                                                    <i class="fa-solid fa-calendar me-1"></i>
                                                    {{ $certificate->completed_at ? $certificate->completed_at->format('Y-m-d') : '-' }}
                                                </p>
                                                @if($certificate->course)
                                                <a href="{{ route('frontend.course-details', $certificate->course->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-download me-1"></i>تحميل الشهادة
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="empty-state text-center py-5">
                                    <i class="fa-solid fa-certificate fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد شهادات متاحة</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Badges Tab -->
                        <div class="tab-pane fade" id="badges" role="tabpanel">
                            <div class="tab-content-card">
                                @if($badges && $badges->count() > 0)
                                <div class="row">
                                    @foreach($badges as $badge)
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="badge-card">
                                            <div class="badge-icon" style="background: {{ $badge->color ?? '#0555a2' }};">
                                                <i class="{{ $badge->icon ?? 'fa-solid fa-award' }}"></i>
                                            </div>
                                            <h6>{{ $badge->name }}</h6>
                                            @if($badge->pivot->awarded_at)
                                            <small class="text-muted">
                                                <i class="fa-solid fa-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($badge->pivot->awarded_at)->format('Y-m-d') }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="empty-state text-center py-5">
                                    <i class="fa-solid fa-award fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد أوسمة متاحة</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Achievements Tab -->
                        <div class="tab-pane fade" id="achievements" role="tabpanel">
                            <div class="tab-content-card">
                                @if($achievements && $achievements->count() > 0)
                                <div class="row">
                                    @foreach($achievements as $achievement)
                                    <div class="col-md-6 mb-3">
                                        <div class="achievement-card">
                                            <div class="achievement-icon">
                                                <i class="{{ $achievement->icon ?? 'fa-solid fa-trophy' }}"></i>
                                            </div>
                                            <div class="achievement-info">
                                                <h6>{{ $achievement->name }}</h6>
                                                <p class="text-muted small mb-2">{{ $achievement->description ?? '' }}</p>
                                                @if($achievement->pivot->completed_at)
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($achievement->pivot->completed_at)->format('Y-m-d') }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="empty-state text-center py-5">
                                    <i class="fa-solid fa-trophy fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد إنجازات متاحة</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- About Tab -->
                        @if($student->bio)
                        <div class="tab-pane fade" id="about" role="tabpanel">
                            <div class="tab-content-card">
                                <h5 class="section-title mb-3">
                                    <i class="fa-solid fa-user"></i> نبذة عني
                                </h5>
                                <p class="bio-text">{{ $student->bio }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.page-header .breadcrumb {
    background: transparent;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.page-header .breadcrumb-item.active {
    color: #ffffff;
}

.page-header .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
}

/* Student Profile Section */
.student-profile-section {
    background-color: #f8f9fa;
    min-height: 70vh;
}

/* Profile Card */
.profile-card {
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.profile-header {
    background: var(--secondary-Color);
    color: white;
    padding: 40px 20px;
    text-align: center;
}

.profile-avatar {
    margin-bottom: 20px;
}

.profile-avatar img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.avatar-placeholder-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    font-weight: bold;
    border: 5px solid white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.profile-name {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.profile-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 0.9rem;
}

.profile-body {
    padding: 30px;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.section-title i {
    color: var(--main-Color);
    margin-left: 10px;
}

.info-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.info-label i {
    color: var(--main-Color);
    margin-left: 8px;
    width: 20px;
}

.info-value {
    color: #2c3e50;
    font-size: 1rem;
    padding-right: 28px;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 6px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: var(--secondary-Color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info h4 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 5px;
}

.stat-info p {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

/* About Card */
.about-card {
    background: white;
    border-radius: 6px;
    padding: 30px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.bio-text {
    color: #555;
    line-height: 1.8;
    font-size: 1rem;
}

/* Tabs Container */
.tabs-container {
    background: white;
    border-radius: 6px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}

.custom-tabs {
    border-bottom: 2px solid #e9ecef;
    padding: 0;
    margin: 0;
}

.custom-tabs .nav-link {
    border: none;
    border-radius: 0;
    color: #6c757d;
    padding: 15px 20px;
    font-weight: 600;
    transition: all 0.3s;
}

.custom-tabs .nav-link:hover {
    color: var(--secondary-Color);
    background: #f8f9fa;
}

.custom-tabs .nav-link.active {
    color: var(--secondary-Color);
    background: transparent;
    border-bottom: 3px solid var(--secondary-Color);
}

.tab-content-card {
    padding: 30px;
}

/* Certificate Card */
.certificate-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s;
}

.certificate-card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.certificate-icon {
    width: 60px;
    height: 60px;
    background: var(--secondary-Color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.certificate-info h5 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

/* Badge Card */
.badge-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
}

.badge-card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.badge-icon {
    width: 70px;
    height: 70px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin-bottom: 15px;
}

.badge-card h6 {
    margin: 10px 0 5px 0;
    color: #2c3e50;
}

/* Achievement Card */
.achievement-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s;
}

.achievement-card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.achievement-icon {
    width: 60px;
    height: 60px;
    background: var(--main-Color);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.achievement-info h6 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state p {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .profile-avatar img,
    .avatar-placeholder-large {
        width: 120px;
        height: 120px;
        font-size: 3rem;
    }

    .profile-name {
        font-size: 1.5rem;
    }

    .stat-card {
        flex-direction: column;
        text-align: center;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }

    .stat-info h4 {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }

    .profile-body {
        padding: 20px;
    }

    .info-value {
        padding-right: 0;
        margin-top: 5px;
    }
}
</style>

@endsection
