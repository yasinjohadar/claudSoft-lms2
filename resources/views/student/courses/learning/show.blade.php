@extends('student.layouts.master')

@section('page-title')
    {{ $course->title }} - التعلم
@stop

@section('css')
<style>
    .course-learning-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .sidebar-nav {
        position: sticky;
        top: 100px;
    }

    .nav-module {
        display: block;
        padding: 1.2rem 1.3rem;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        text-decoration: none;
        color: #374151;
        background: #f9fafb;
        font-size: 1rem;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        line-height: 1.7;
    }

    .nav-module:hover {
        background: #f3f4f6;
        color: #4f46e5;
        border-left-color: #4f46e5;
    }

    .nav-module.completed {
        background: #ecfdf5;
        color: #059669;
        border-left-color: #10b981;
    }

    .section-header {
        font-size: 0.95rem;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
        color: #4f46e5;
        font-weight: 600;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .start-learning-btn {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        border-radius: 8px;
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Breadcrumb -->
        <div class="page-header">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.courses.my-courses') }}">كورساتي</a></li>
                <li class="breadcrumb-item active">{{ $course->title }}</li>
            </ol>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Course Header -->
                <div class="course-learning-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">{{ $course->title }}</h2>
                            <p class="mb-3 opacity-75">{{ Str::limit($course->description, 150) }}</p>
                            <div class="d-flex align-items-center gap-3">
                                <span><i class="fas fa-layer-group me-2"></i>{{ $course->sections->count() }} أقسام</span>
                                <span><i class="fas fa-play-circle me-2"></i>{{ $course->modules()->count() }} دروس</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>التقدم في الكورس</span>
                            <span>{{ number_format($enrollment->completion_percentage ?? 0, 0) }}%</span>
                        </div>
                        <div class="progress bg-white bg-opacity-25">
                            <div class="progress-bar bg-success" style="width: {{ $enrollment->completion_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Start Learning -->
                @if($currentModule)
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-play-circle text-primary" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 mb-2">ابدأ التعلم الآن</h4>
                            <p class="text-muted mb-4">استمر من حيث توقفت</p>
                            <a href="{{ route('student.learn.module', $currentModule->id) }}" class="btn btn-primary start-learning-btn">
                                <i class="fas fa-play me-2"></i>ابدأ الدرس الأول
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 mb-2">لا يوجد محتوى حالياً</h4>
                            <p class="text-muted mb-0">هذا الكورس لا يحتوي على دروس بعد</p>
                        </div>
                    </div>
                @endif

                <!-- Course Sections -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>محتوى الكورس</h5>
                    </div>
                    <div class="card-body">
                        @forelse($course->sections as $section)
                            <div class="mb-4">
                                <h6 class="section-header">
                                    <i class="fas fa-folder me-2"></i>{{ $section->title }}
                                    <span class="badge bg-light text-dark ms-2">{{ $section->modules->count() }} دروس</span>
                                </h6>
                                @foreach($section->modules as $module)
                                    <a href="{{ route('student.learn.module', $module->id) }}"
                                       class="nav-module d-flex align-items-center {{ in_array($module->id, $completedModules) ? 'completed' : '' }}">
                                        @if($module->module_type == 'video')
                                            <i class="fas fa-play-circle me-3"></i>
                                        @elseif($module->module_type == 'lesson')
                                            <i class="fas fa-book-open me-3"></i>
                                        @elseif($module->module_type == 'assignment')
                                            <i class="fas fa-file-alt me-3"></i>
                                        @elseif($module->module_type == 'quiz')
                                            <i class="fas fa-question-circle me-3"></i>
                                        @else
                                            <i class="fas fa-circle me-3"></i>
                                        @endif
                                        <span class="flex-grow-1">{{ $module->title }}</span>
                                        @if(in_array($module->id, $completedModules))
                                            <i class="fas fa-check-circle text-success"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-muted text-center">لا يوجد محتوى في هذا الكورس بعد</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-nav">
                    <!-- Course Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>إحصائيات التقدم</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>الدروس المكتملة</span>
                                <strong>{{ count($completedModules) }} / {{ $course->modules()->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>نسبة الإنجاز</span>
                                <strong>{{ number_format($enrollment->completion_percentage ?? 0, 0) }}%</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>آخر زيارة</span>
                                <strong>{{ $enrollment->last_accessed_at ? $enrollment->last_accessed_at->diffForHumans() : 'الآن' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>إجراءات سريعة</h6>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('student.courses.show', $course->id) }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-info-circle me-2"></i>تفاصيل الكورس
                            </a>
                            @if($currentModule)
                                <a href="{{ route('student.learn.module', $currentModule->id) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-play me-2"></i>متابعة التعلم
                                </a>
                            @endif
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
    setTimeout(() => $('.alert').fadeOut(), 5000);
</script>
@stop
