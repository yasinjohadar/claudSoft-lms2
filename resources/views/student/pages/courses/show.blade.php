@extends('student.layouts.master')

@section('page-title')
    {{ $course->title }}
@stop

@section('css')
<style>
    .course-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 16px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(79, 70, 229, 0.3);
    }
    .course-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .course-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 350px;
        height: 350px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .course-hero-content {
        position: relative;
        z-index: 1;
    }
    .course-thumbnail-large {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        transition: transform 0.3s ease;
    }
    .course-thumbnail-large:hover {
        transform: scale(1.02);
    }
    .enrollment-card {
        position: sticky;
        top: 100px;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    .enrollment-card:hover {
        box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        transform: translateY(-5px);
    }
    .price-display {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .price-free {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .feature-item {
        padding: 1rem;
        border-radius: 12px;
        background: #f9fafb;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .feature-item:hover {
        background: white;
        border-color: #4f46e5;
        transform: translateX(-5px);
    }
    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-left: 1rem;
        flex-shrink: 0;
    }
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover:not(.pe-none) {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
        border-color: #4f46e5 !important;
        background: #fafbfc;
    }
    .hover-shadow:active:not(.pe-none) {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .instructor-card {
        display: flex;
        align-items: center;
        padding: 2rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    .instructor-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transform: translateY(-3px);
    }
    .instructor-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        flex-shrink: 0;
    }
    .rating-stars {
        color: #fbbf24;
        font-size: 1.25rem;
    }
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: rgba(255,255,255,0.25);
        border-radius: 25px;
        backdrop-filter: blur(10px);
        margin: 0.25rem;
        font-size: 0.9rem;
        font-weight: 500;
        border: 1px solid rgba(255,255,255,0.3);
    }
    .tab-content-section {
        padding: 1.5rem 0;
    }
    .nav-tabs {
        border: none;
        margin-bottom: 0;
        background: white;
        border-radius: 12px;
        padding: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6b7280;
        font-weight: 600;
        padding: 0.875rem 1.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        margin: 0 0.25rem;
    }
    .nav-tabs .nav-link:hover {
        color: #4f46e5;
        background: #f3f4f6;
    }
    .nav-tabs .nav-link.active {
        color: white;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    .nav-tabs .nav-link i {
        font-size: 0.875rem;
    }

    /* Responsive Tabs */
    @media (max-width: 768px) {
        .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .nav-tabs .nav-link {
            white-space: nowrap;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        .nav-tabs .nav-link i {
            display: none;
        }
    }

    .custom-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .custom-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .custom-card .card-header {
        background: #fafbfc;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
        border-radius: 12px 12px 0 0;
    }
    .badge {
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 8px;
    }
    .btn {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    }
    .breadcrumb {
        background: transparent;
        padding: 0;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        color: #9ca3af;
    }
    .breadcrumb-item a {
        color: #6b7280;
        text-decoration: none;
        transition: color 0.2s;
    }
    .breadcrumb-item a:hover {
        color: #4f46e5;
    }
    .breadcrumb-item.active {
        color: #1f2937;
        font-weight: 600;
    }
</style>
@stop

@section('content')
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

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="my-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.courses.index') }}">الكورسات</a></li>
                    @if($course->category)
                        <li class="breadcrumb-item"><a href="{{ route('student.courses.index', ['category_id' => $course->category_id]) }}">{{ $course->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($course->title, 50) }}</li>
                </ol>
            </nav>

            <!-- Course Header Card -->
            <div class="card custom-card mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            @if($course->category)
                                <span class="badge bg-primary-transparent text-primary mb-3" style="font-size: 0.875rem;">
                                    <i class="fas fa-folder me-1"></i>
                                    {{ $course->category->name }}
                                </span>
                            @endif

                            <h1 class="mb-3 fw-bold" style="font-size: 2rem; color: #1f2937;">{{ $course->title }}</h1>
                            <p class="lead mb-4 text-muted" style="font-size: 1.125rem;">{{ $course->short_description }}</p>

                            <div class="d-flex flex-wrap gap-3 mb-3">
                                @if($course->level)
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-primary-transparent text-primary me-2">
                                            <i class="fas fa-signal"></i>
                                        </span>
                                        <span class="fw-semibold">
                                            {{ $course->level == 'beginner' ? 'مبتدئ' : '' }}
                                            {{ $course->level == 'intermediate' ? 'متوسط' : '' }}
                                            {{ $course->level == 'advanced' ? 'متقدم' : '' }}
                                        </span>
                                    </div>
                                @endif

                                @if($course->language)
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-success-transparent text-success me-2">
                                            <i class="fas fa-language"></i>
                                        </span>
                                        <span class="fw-semibold">{{ $course->language == 'ar' ? 'العربية' : 'الإنجليزية' }}</span>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm bg-warning-transparent text-warning me-2">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $stats['total_students'] ?? 0 }} طالب</span>
                                </div>

                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm bg-info-transparent text-info me-2">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $course->duration_in_hours ?? 0 }} ساعة</span>
                                </div>
                            </div>

                            @if($enrollment)
                                <div class="alert alert-success d-flex align-items-center mb-0">
                                    <i class="fas fa-check-circle fs-5 me-2"></i>
                                    <span>أنت مسجل في هذا الكورس</span>
                                </div>
                            @endif
                        </div>

                        @if($course->thumbnail)
                            <div class="col-lg-4 text-center">
                                <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                     alt="{{ $course->title }}"
                                     class="img-fluid rounded"
                                     style="max-height: 200px; object-fit: cover; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-xl-8 col-lg-7">

                    <!-- Tabs Card -->
                    <div class="card custom-card mb-4">
                        <div class="card-body p-3">
                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs" id="courseTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="curriculum-tab" data-bs-toggle="tab"
                                            data-bs-target="#curriculum" type="button" role="tab">
                                        <i class="fas fa-list me-2"></i>المنهج الدراسي
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="overview-tab" data-bs-toggle="tab"
                                            data-bs-target="#overview" type="button" role="tab">
                                        <i class="fas fa-info-circle me-2"></i>نظرة عامة
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="instructor-tab" data-bs-toggle="tab"
                                            data-bs-target="#instructor" type="button" role="tab">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>عن المدرب
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab"
                                            data-bs-target="#reviews" type="button" role="tab">
                                        <i class="fas fa-star me-2"></i>التقييمات
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="courseTabsContent">

                        <!-- Overview Tab -->
                        <div class="tab-pane fade tab-content-section" id="overview" role="tabpanel">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">وصف الكورس</h5>
                                </div>
                                <div class="card-body">
                                    <div class="content">
                                        {!! nl2br(e($course->description)) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- What You'll Learn -->
                            @if($course->learning_outcomes)
                                <div class="card custom-card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                            ماذا ستتعلم
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($course->learning_outcomes as $outcome)
                                                <div class="col-md-6 mb-2">
                                                    <div class="d-flex">
                                                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                                        <span>{{ $outcome }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Requirements -->
                            @if($course->requirements)
                                <div class="card custom-card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clipboard-list me-2 text-warning"></i>
                                            المتطلبات
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($course->requirements as $requirement)
                                            <div class="mb-2">
                                                <i class="fas fa-chevron-left text-muted me-2"></i>
                                                <span>{{ $requirement }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Curriculum Tab -->
                        <div class="tab-pane fade show active tab-content-section" id="curriculum" role="tabpanel">
                            <div class="card custom-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list-ul me-2"></i>
                                        محتوى الكورس
                                    </h5>
                                    <span class="badge bg-primary-transparent text-primary">
                                        {{ $stats['total_sections'] ?? 0 }} قسم • {{ $stats['total_modules'] ?? 0 }} درس
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    @if($course->sections->count() > 0)
                                        <div class="accordion accordion-customicon1 accordion-primary" id="courseCurriculumAccordion">
                                            @foreach($course->sections as $index => $section)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading-{{ $section->id }}">
                                                        <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapse-{{ $section->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                                                aria-controls="collapse-{{ $section->id }}">
                                                            <div class="d-flex align-items-center w-100 justify-content-between me-3">
                                                                <div>
                                                                    <i class="fas fa-folder me-2"></i>
                                                                    {{ $section->title }}
                                                                    @if($section->description)
                                                                        <br><small class="text-muted fw-normal">{{ $section->description }}</small>
                                                                    @endif
                                                                </div>
                                                                <span class="badge bg-light text-default">
                                                                    {{ $section->modules->count() }} {{ $section->modules->count() == 1 ? 'درس' : 'دروس' }}
                                                                </span>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse-{{ $section->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                                         aria-labelledby="heading-{{ $section->id }}" data-bs-parent="#courseCurriculumAccordion">
                                                        <div class="accordion-body">
                                                            @forelse($section->modules as $module)
                                                                @php
                                                                    $canAccess = $enrollment || $module->is_preview;
                                                                    $moduleUrl = $canAccess ? route('student.learn.module', $module->id) : '#';
                                                                @endphp

                                                                @if($canAccess)
                                                                    <a href="{{ $moduleUrl }}"
                                                                       class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded hover-shadow text-decoration-none"
                                                                       style="transition: all 0.3s ease;">
                                                                @else
                                                                    <div class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded opacity-75"
                                                                         style="cursor: not-allowed;">
                                                                @endif
                                                                    <div class="d-flex align-items-center flex-grow-1">
                                                                        <span class="avatar avatar-md me-3
                                                                            {{ $module->module_type == 'lesson' ? 'bg-primary-transparent text-primary' : '' }}
                                                                            {{ $module->module_type == 'video' ? 'bg-danger-transparent text-danger' : '' }}
                                                                            {{ $module->module_type == 'quiz' ? 'bg-success-transparent text-success' : '' }}
                                                                            {{ $module->module_type == 'assignment' ? 'bg-warning-transparent text-warning' : '' }}
                                                                            {{ $module->module_type == 'question_module' ? 'bg-info-transparent text-info' : '' }}">
                                                                            @if($module->module_type == 'lesson')
                                                                                <i class="fas fa-book-open"></i>
                                                                            @elseif($module->module_type == 'video')
                                                                                <i class="fas fa-play"></i>
                                                                            @elseif($module->module_type == 'quiz')
                                                                                <i class="fas fa-question-circle"></i>
                                                                            @elseif($module->module_type == 'assignment')
                                                                                <i class="fas fa-tasks"></i>
                                                                            @elseif($module->module_type == 'question_module')
                                                                                <i class="fas fa-clipboard-question"></i>
                                                                            @else
                                                                                <i class="fas fa-file"></i>
                                                                            @endif
                                                                        </span>
                                                                        <div>
                                                                            <h6 class="mb-1 fw-semibold text-dark">{{ $module->title }}</h6>
                                                                            <small class="text-muted">
                                                                                <span class="badge bg-light text-default me-1">
                                                                                    @if($module->module_type == 'lesson') درس
                                                                                    @elseif($module->module_type == 'video') فيديو
                                                                                    @elseif($module->module_type == 'quiz') اختبار
                                                                                    @elseif($module->module_type == 'assignment') واجب
                                                                                    @elseif($module->module_type == 'question_module') اختبار
                                                                                    @endif
                                                                                </span>
                                                                                @if($module->module_type == 'question_module' && $module->modulable)
                                                                                    <span class="badge bg-info-transparent text-info badge-sm ms-1">
                                                                                        {{ $module->modulable->questions->count() }} سؤال
                                                                                    </span>
                                                                                @endif
                                                                                @if($module->duration)
                                                                                    <i class="fas fa-clock me-1"></i>{{ $module->duration }} دقيقة
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        @if($module->is_preview)
                                                                            <span class="badge bg-info-transparent text-info">
                                                                                <i class="fas fa-eye me-1"></i>معاينة مجانية
                                                                            </span>
                                                                        @elseif($enrollment)
                                                                            <i class="fas fa-check-circle text-success fs-5"></i>
                                                                        @else
                                                                            <i class="fas fa-lock text-muted fs-5"></i>
                                                                        @endif
                                                                    </div>
                                                                @if($canAccess)
                                                                    </a>
                                                                @else
                                                                    </div>
                                                                @endif
                                                            @empty
                                                                <div class="text-center text-muted py-3">
                                                                    <i class="fas fa-inbox fs-3 mb-2 opacity-25"></i>
                                                                    <p class="mb-0">لا توجد دروس في هذا القسم</p>
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-book-open fs-1 mb-3 opacity-25"></i>
                                            <h6>لم يتم إضافة محتوى للكورس بعد</h6>
                                            <p class="text-muted mb-0">سيتم إضافة المحتوى قريباً</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Instructor Tab -->
                        <div class="tab-pane fade tab-content-section" id="instructor" role="tabpanel">
                            @if($course->instructor)
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="instructor-card">
                                            @if($course->instructor->avatar)
                                                <img src="{{ asset('storage/' . $course->instructor->avatar) }}"
                                                     alt="{{ $course->instructor->name }}"
                                                     class="instructor-avatar-large me-4">
                                            @else
                                                <div class="instructor-avatar-large bg-primary text-white d-flex align-items-center justify-content-center me-4"
                                                     style="font-size: 2rem; font-weight: 700;">
                                                    {{ substr($course->instructor->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ $course->instructor->name }}</h4>
                                                @if($course->instructor->title)
                                                    <p class="text-muted mb-2">{{ $course->instructor->title }}</p>
                                                @endif
                                                <div class="d-flex gap-3 text-muted">
                                                    <span>
                                                        <i class="fas fa-book me-1"></i>
                                                        {{ $course->instructor->courses_count ?? 0 }} كورس
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-users me-1"></i>
                                                        {{ $course->instructor->students_count ?? 0 }} طالب
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($course->instructor->bio)
                                            <div class="mt-4">
                                                <h6 class="mb-3">نبذة عن المدرب</h6>
                                                <p class="text-muted">{{ $course->instructor->bio }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane fade tab-content-section" id="reviews" role="tabpanel">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">تقييمات الطلاب</h5>
                                </div>
                                <div class="card-body">
                                    @if($course->reviews_count > 0)
                                        <!-- Overall Rating -->
                                        <div class="text-center mb-4 pb-4 border-bottom">
                                            <div class="display-4 fw-bold mb-2">{{ number_format($course->average_rating, 1) }}</div>
                                            <div class="rating-stars mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($course->average_rating))
                                                        <i class="fas fa-star"></i>
                                                    @elseif($i - 0.5 <= $course->average_rating)
                                                        <i class="fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="far fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <div class="text-muted">{{ $course->reviews_count }} تقييم</div>
                                        </div>

                                        <!-- Reviews List -->
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                                            <p>لا توجد تقييمات نصية حتى الآن</p>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-star fa-3x mb-3 opacity-25"></i>
                                            <p>لا توجد تقييمات لهذا الكورس حتى الآن</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Sidebar - Enrollment Card -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card custom-card enrollment-card">
                        <div class="card-body p-4">
                            <!-- Price -->
                            <div class="text-center mb-4">
                                @if($course->price > 0)
                                    <div class="price-display">${{ number_format($course->price, 2) }}</div>
                                    @if($course->original_price && $course->original_price > $course->price)
                                        <div class="text-muted">
                                            <del>${{ number_format($course->original_price, 2) }}</del>
                                            <span class="badge bg-danger ms-2">
                                                خصم {{ round((($course->original_price - $course->price) / $course->original_price) * 100) }}%
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <div class="price-display price-free">مجاني</div>
                                @endif
                            </div>

                            <!-- Enroll Button -->
                            @php
                                $isEnrolled = auth()->user()->enrolledCourses()->where('course_id', $course->id)->exists();
                            @endphp

                            @if($enrollment)
                                <a href="{{ route('student.learn.course', $course->id) }}"
                                   class="btn btn-success w-100 mb-3 py-3">
                                    <i class="fas fa-play me-2"></i>الذهاب إلى الكورس
                                </a>
                            @else
                                @if($course->is_free || $course->price == 0)
                                    <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 mb-3 py-3">
                                            <i class="fas fa-user-plus me-2"></i>التسجيل المجاني
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('student.courses.checkout', $course->id) }}"
                                       class="btn btn-primary w-100 mb-3 py-3">
                                        <i class="fas fa-shopping-cart me-2"></i>شراء الكورس
                                    </a>
                                @endif
                            @endif

                            <!-- Course Features -->
                            <div class="border-top pt-4 mt-3">
                                <h6 class="mb-4 fw-bold">هذا الكورس يشمل:</h6>

                                <div class="feature-item">
                                    <div class="d-flex align-items-center">
                                        <div class="feature-icon bg-primary-transparent text-primary">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $stats['total_sections'] ?? 0 }} قسم تعليمي</div>
                                            <small class="text-muted">محتوى منظم ومرتب</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="d-flex align-items-center">
                                        <div class="feature-icon bg-success-transparent text-success">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $stats['total_modules'] ?? 0 }} درس</div>
                                            <small class="text-muted">محاضرات شاملة</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="d-flex align-items-center">
                                        <div class="feature-icon bg-warning-transparent text-warning">
                                            <i class="fas fa-infinity"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">وصول مدى الحياة</div>
                                            <small class="text-muted">تعلم في أي وقت</small>
                                        </div>
                                    </div>
                                </div>

                                @if($course->certificate_enabled)
                                    <div class="feature-item">
                                        <div class="d-flex align-items-center">
                                            <div class="feature-icon bg-danger-transparent text-danger">
                                                <i class="fas fa-certificate"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">شهادة إتمام</div>
                                                <small class="text-muted">عند إكمال الكورس</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="feature-item">
                                    <div class="d-flex align-items-center">
                                        <div class="feature-icon bg-info-transparent text-info">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">متوافق مع الجوال</div>
                                            <small class="text-muted">تعلم من أي مكان</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="d-flex align-items-center">
                                        <div class="feature-icon bg-secondary-transparent text-secondary">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $stats['total_students'] ?? 0 }} طالب</div>
                                            <small class="text-muted">انضم للمجتمع</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Share -->
                            <div class="border-top pt-4 mt-3">
                                <h6 class="mb-3 fw-bold">شارك هذا الكورس:</h6>
                                <div class="d-flex gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm flex-fill"
                                       title="شارك على Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($course->title) }}"
                                       target="_blank"
                                       class="btn btn-outline-info btn-sm flex-fill"
                                       title="شارك على Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://api.whatsapp.com/send?text={{ urlencode($course->title . ' - ' . url()->current()) }}"
                                       target="_blank"
                                       class="btn btn-outline-success btn-sm flex-fill"
                                       title="شارك على WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <button onclick="copyToClipboard('{{ url()->current() }}')"
                                            class="btn btn-outline-secondary btn-sm flex-fill"
                                            title="نسخ الرابط">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Create temporary success message
            const button = event.currentTarget;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('btn-success');
            button.classList.remove('btn-outline-secondary');

            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
        });
    }

    // Fade out alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Smooth scroll for anchor links (only for hash links within the page)
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Only prevent default for valid hash links
            if (href && href.startsWith('#') && href.length > 1) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
</script>
@stop
