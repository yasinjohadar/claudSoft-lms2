@extends('student.layouts.master')

@section('page-title')
    تصفح الكورسات
@stop

@section('css')
<style>
    .course-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .course-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        position: relative;
    }
    .course-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .course-content {
        padding: 1.5rem;
    }
    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: #2c3e50;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 50px;
    }
    .course-description {
        color: #6c757d;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 1rem;
        min-height: 60px;
    }
    .course-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
        margin-top: auto;
    }
    .course-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.85rem;
        color: #6c757d;
    }
    .price-tag {
        font-size: 1.5rem;
        font-weight: 700;
        color: #667eea;
    }
    .price-free {
        color: #28a745;
    }
    .filter-card {
        position: sticky;
        top: 80px;
    }
    .category-badge {
        cursor: pointer;
        transition: all 0.3s;
        margin: 0.25rem;
        padding: 0.5rem 1rem;
    }
    .category-badge:hover, .category-badge.active {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .instructor-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
        margin-left: 8px;
    }
    .level-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
    }
    .enrolled-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 600;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تصفح الكورسات التعليمية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الكورسات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center">
                    <div class="pe-1 mb-xl-0">
                        <span class="text-muted">إجمالي الكورسات: <strong class="text-primary">{{ $courses->total() }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-xl-3 col-lg-4">
                    <div class="card custom-card filter-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-filter me-2"></i>تصفية النتائج
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('student.courses.index') }}" method="GET" id="filterForm">

                                <!-- Search -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-search me-2"></i>البحث
                                    </label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="ابحث عن كورس..."
                                           value="{{ request('search') }}">
                                </div>

                                <!-- Category -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-th-large me-2"></i>التصنيف
                                    </label>
                                    <select name="category_id" class="form-select">
                                        <option value="">جميع التصنيفات</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Level -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-layer-group me-2"></i>المستوى
                                    </label>
                                    <select name="level" class="form-select">
                                        <option value="">جميع المستويات</option>
                                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>مبتدئ</option>
                                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>متوسط</option>
                                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>متقدم</option>
                                    </select>
                                </div>

                                <!-- Language -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-language me-2"></i>اللغة
                                    </label>
                                    <select name="language" class="form-select">
                                        <option value="">جميع اللغات</option>
                                        <option value="ar" {{ request('language') == 'ar' ? 'selected' : '' }}>العربية</option>
                                        <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>الإنجليزية</option>
                                    </select>
                                </div>

                                <!-- Price -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-dollar-sign me-2"></i>السعر
                                    </label>
                                    <select name="price_filter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="free" {{ request('price_filter') == 'free' ? 'selected' : '' }}>مجاني</option>
                                        <option value="paid" {{ request('price_filter') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-sort me-2"></i>ترتيب حسب
                                    </label>
                                    <select name="sort" class="form-select">
                                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث</option>
                                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>الأكثر شعبية</option>
                                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>السعر: من الأقل</option>
                                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>السعر: من الأعلى</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>تطبيق الفلاتر
                                    </button>
                                    <a href="{{ route('student.courses.index') }}" class="btn btn-light">
                                        <i class="fas fa-redo me-2"></i>إعادة تعيين
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Popular Categories -->
                    <div class="card custom-card mt-3">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-fire me-2 text-danger"></i>التصنيفات الشائعة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap">
                                @foreach($categories->take(6) as $cat)
                                    <a href="{{ route('student.courses.index', ['category_id' => $cat->id]) }}"
                                       class="badge category-badge {{ request('category_id') == $cat->id ? 'active' : '' }}"
                                       style="background-color: {{ $cat->color }}; color: white;">
                                        {{ $cat->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Courses Grid -->
                <div class="col-xl-9 col-lg-8">
                    <div class="row">
                        @forelse ($courses as $course)
                            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                                <div class="card custom-card course-card">
                                    <div class="position-relative">
                                        @if($course->thumbnail)
                                            <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                                 alt="{{ $course->title }}"
                                                 class="course-image">
                                        @else
                                            <div class="course-image bg-gradient-primary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-book fa-4x text-white opacity-50"></i>
                                            </div>
                                        @endif

                                        @if($course->is_featured)
                                            <span class="badge bg-warning course-badge">
                                                <i class="fas fa-star me-1"></i>مميز
                                            </span>
                                        @endif
                                    </div>

                                    <div class="card-body course-content d-flex flex-column">
                                        <!-- Category & Level -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            @if($course->category)
                                                <span class="badge" style="background-color: {{ $course->category->color }}">
                                                    {{ $course->category->name }}
                                                </span>
                                            @endif

                                            @if($course->level)
                                                <span class="badge level-badge
                                                    {{ $course->level == 'beginner' ? 'bg-success' : '' }}
                                                    {{ $course->level == 'intermediate' ? 'bg-info' : '' }}
                                                    {{ $course->level == 'advanced' ? 'bg-danger' : '' }}">
                                                    {{ $course->level == 'beginner' ? 'مبتدئ' : '' }}
                                                    {{ $course->level == 'intermediate' ? 'متوسط' : '' }}
                                                    {{ $course->level == 'advanced' ? 'متقدم' : '' }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Title -->
                                        <h5 class="course-title">
                                            @php
                                                $titleEnrollment = auth()->user()->courseEnrollments()->where('course_id', $course->id)->first();
                                            @endphp
                                            @if($titleEnrollment && $titleEnrollment->enrollment_status == 'active')
                                                <a href="{{ route('student.learn.course', $course->id) }}"
                                                   class="text-dark text-decoration-none">
                                                    {{ $course->title }}
                                                </a>
                                            @else
                                                <span class="text-dark">{{ $course->title }}</span>
                                            @endif
                                        </h5>

                                        <!-- Description -->
                                        <p class="course-description">
                                            {{ $course->short_description ?? Str::limit($course->description, 100) }}
                                        </p>

                                        <!-- Instructor -->
                                        @if($course->instructor)
                                            <div class="d-flex align-items-center mb-3">
                                                @if($course->instructor->avatar)
                                                    <img src="{{ asset('storage/' . $course->instructor->avatar) }}"
                                                         alt="{{ $course->instructor->name }}"
                                                         class="instructor-avatar">
                                                @else
                                                    <div class="instructor-avatar bg-primary text-white d-flex align-items-center justify-content-center">
                                                        {{ substr($course->instructor->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <small class="text-muted">{{ $course->instructor->name }}</small>
                                            </div>
                                        @endif

                                        <!-- Meta Info -->
                                        <div class="course-meta">
                                            <div class="d-flex gap-3">
                                                <div class="course-meta-item" title="عدد الدروس">
                                                    <i class="fas fa-book-open text-primary"></i>
                                                    <span>{{ $course->modules_count ?? 0 }} درس</span>
                                                </div>
                                                <div class="course-meta-item" title="المسجلين">
                                                    <i class="fas fa-users text-success"></i>
                                                    <span class="enrolled-count">{{ $course->enrollments_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Price & Enroll Button -->
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <div>
                                                @if($course->price > 0)
                                                    <span class="price-tag">${{ number_format($course->price, 2) }}</span>
                                                @else
                                                    <span class="price-tag price-free">مجاني</span>
                                                @endif
                                            </div>

                                            @php
                                                $enrollment = auth()->user()->courseEnrollments()->where('course_id', $course->id)->first();
                                            @endphp

                                            @if($enrollment)
                                                @if($enrollment->enrollment_status == 'active')
                                                    <a href="{{ route('student.learn.course', $course->id) }}"
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-play me-1"></i>متابعة التعلم
                                                    </a>
                                                @elseif($enrollment->enrollment_status == 'pending')
                                                    <button class="btn btn-warning btn-sm" disabled>
                                                        <i class="fas fa-clock me-1"></i>قيد الانتظار
                                                    </button>
                                                @elseif($enrollment->enrollment_status == 'suspended')
                                                    <button class="btn btn-danger btn-sm" disabled>
                                                        <i class="fas fa-ban me-1"></i>معلق
                                                    </button>
                                                @elseif($enrollment->enrollment_status == 'cancelled')
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-times me-1"></i>ملغي
                                                    </button>
                                                @endif
                                            @else
                                                <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-user-plus me-1"></i>طلب الاشتراك
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card custom-card">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-graduation-cap fa-5x text-muted mb-4 opacity-25"></i>
                                        <h4 class="text-muted mb-3">لا توجد كورسات متاحة</h4>
                                        <p class="text-muted">جرب تغيير معايير البحث أو الفلاتر</p>
                                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary mt-3">
                                            <i class="fas fa-redo me-2"></i>إعادة تعيين الفلاتر
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($courses->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $courses->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Auto-submit on filter change
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // Fade out alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
