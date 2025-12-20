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

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-envelope"></i> البريد الإلكتروني
                            </div>
                            <div class="info-value">{{ $student->email }}</div>
                        </div>

                        @if($student->phone)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fa-solid fa-phone"></i> رقم الهاتف
                            </div>
                            <div class="info-value">{{ $student->phone }}</div>
                        </div>
                        @endif

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
                                <h4>0</h4>
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
                                <h4>0</h4>
                                <p>الشهادات المكتسبة</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <h4>0</h4>
                                <p>التقييمات</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                @if($student->bio)
                <div class="about-card mb-4">
                    <h5 class="section-title">
                        <i class="fa-solid fa-user"></i> نبذة عني
                    </h5>
                    <p class="bio-text">{{ $student->bio }}</p>
                </div>
                @endif

                <!-- Coming Soon Sections -->
                <div class="coming-soon-card">
                    <div class="coming-soon-content">
                        <i class="fa-solid fa-hourglass-half fa-3x mb-3"></i>
                        <h4>المزيد من المعلومات قريباً</h4>
                        <p class="text-muted">سيتم عرض الكورسات المسجلة، الشهادات، والإنجازات هنا</p>
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
    border-radius: 15px;
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
    border-radius: 20px;
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
    border-radius: 15px;
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
    border-radius: 12px;
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
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.bio-text {
    color: #555;
    line-height: 1.8;
    font-size: 1rem;
}

/* Coming Soon Card */
.coming-soon-card {
    background: white;
    border-radius: 15px;
    padding: 60px 30px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    text-align: center;
}

.coming-soon-content i {
    color: var(--main-Color);
    opacity: 0.3;
}

.coming-soon-content h4 {
    color: #2c3e50;
    margin-bottom: 10px;
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
