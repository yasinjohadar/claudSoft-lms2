@extends('admin.layouts.master')

@section('page-title')
    {{ $video->title }}
@stop

@section('css')
<style>
    .video-player-container {
        position: relative;
        width: 100% !important;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        background: #000;
        border-radius: 8px;
        overflow: hidden;
    }

    .video-player-container iframe,
    .video-player-container video {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        border: none !important;
    }

    .video-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .video-info-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        backdrop-filter: blur(10px);
        margin: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
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

    .module-item {
        padding: 1rem;
        border-radius: 8px;
        background: #f9fafb;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .module-item:hover {
        background: white;
        border-color: #667eea;
        transform: translateX(-5px);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 1.5rem;
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
        color: #667eea;
    }

    .action-btn {
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .thumbnail-preview {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $video->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('videos.index') }}">الفيديوهات</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($video->title, 50) }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <div class="d-flex gap-2">
                        @if($video->video_type == 'youtube')
                            <span class="badge bg-danger-transparent px-3 py-2">
                                <i class="fab fa-youtube me-1"></i>YouTube
                            </span>
                        @elseif($video->video_type == 'vimeo')
                            <span class="badge bg-info-transparent px-3 py-2">
                                <i class="fab fa-vimeo me-1"></i>Vimeo
                            </span>
                        @elseif($video->video_type == 'upload')
                            <span class="badge bg-primary-transparent px-3 py-2">
                                <i class="fas fa-upload me-1"></i>مرفوع
                            </span>
                        @else
                            <span class="badge bg-secondary-transparent px-3 py-2">
                                <i class="fas fa-link me-1"></i>خارجي
                            </span>
                        @endif

                        @if($video->is_published)
                            <span class="badge bg-success-transparent px-3 py-2">منشور</span>
                        @else
                            <span class="badge bg-warning-transparent px-3 py-2">مسودة</span>
                        @endif

                        @if($video->processing_status == 'completed')
                            <span class="badge bg-success-transparent px-3 py-2">
                                <i class="fas fa-check me-1"></i>جاهز
                            </span>
                        @elseif($video->processing_status == 'processing')
                            <span class="badge bg-warning-transparent px-3 py-2">
                                <i class="fas fa-spinner fa-spin me-1"></i>يعالج
                            </span>
                        @endif

                        <a href="{{ route('videos.edit', $video->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>تعديل
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-times-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-info-circle me-2"></i>معلومة!</strong> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Description Card -->
            @if($video->description)
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $video->description }}</p>
                </div>
            </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-graduation-cap fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">مستخدم في</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['used_in_modules'] ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">وحدة دراسية</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">المدة</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['duration_minutes'] ?? $video->duration ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">دقيقة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-eye fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">الحالة</p>
                                    <h4 class="fw-bold mb-2">{{ $video->is_visible ? 'مرئي' : 'مخفي' }}</h4>
                                    <span class="badge bg-info-transparent">الظهور</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-download fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">التحميل</p>
                                    <h4 class="fw-bold mb-2">{{ $video->allow_download ? 'نعم' : 'لا' }}</h4>
                                    <span class="badge bg-warning-transparent">السماح</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Card with Tabs -->
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#video-player" role="tab">
                                <i class="fas fa-play-circle me-2"></i>مشغل الفيديو
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#video-details" role="tab">
                                <i class="fas fa-info-circle me-2"></i>التفاصيل
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#used-in" role="tab">
                                <i class="fas fa-graduation-cap me-2"></i>مستخدم في الكورسات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#actions" role="tab">
                                <i class="fas fa-cog me-2"></i>الإجراءات
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <!-- Video Player Tab -->
                        <div class="tab-pane fade show active" id="video-player" role="tabpanel">
                            <div class="row">
                                <div class="col-xl-12">
                                <div class="video-player-container">
                                    @if($video->video_type == 'youtube')
                                        @php
                                            // Extract YouTube video ID
                                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $video->video_url, $matches);
                                            $youtubeId = $matches[1] ?? null;
                                        @endphp
                                        @if($youtubeId)
                                            <iframe
                                                src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1&showinfo=0"
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                            </iframe>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                                <div class="text-center">
                                                    <i class="fas fa-exclamation-triangle fs-1 mb-3"></i>
                                                    <p>رابط YouTube غير صالح</p>
                                                </div>
                                            </div>
                                        @endif

                                    @elseif($video->video_type == 'vimeo')
                                        @php
                                            // Extract Vimeo video ID
                                            preg_match('/vimeo\.com\/(\d+)/', $video->video_url, $matches);
                                            $vimeoId = $matches[1] ?? null;
                                        @endphp
                                        @if($vimeoId)
                                            <iframe
                                                src="https://player.vimeo.com/video/{{ $vimeoId }}?title=0&byline=0&portrait=0"
                                                allowfullscreen
                                                allow="autoplay; fullscreen; picture-in-picture">
                                            </iframe>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                                <div class="text-center">
                                                    <i class="fas fa-exclamation-triangle fs-1 mb-3"></i>
                                                    <p>رابط Vimeo غير صالح</p>
                                                </div>
                                            </div>
                                        @endif

                                    @elseif($video->video_type == 'upload' && $video->video_path)
                                        <video controls controlsList="{{ $video->allow_download ? '' : 'nodownload' }}"
                                               {{ $video->allow_speed_control ? '' : 'disablepictureinpicture' }}>
                                            <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                            متصفحك لا يدعم تشغيل الفيديو.
                                        </video>

                                    @elseif($video->video_type == 'external' && $video->video_url)
                                        @if(Str::contains($video->video_url, 'bunny.net') || Str::contains($video->video_url, 'b-cdn.net'))
                                            {{-- Bunny.net Video --}}
                                            <iframe
                                                src="{{ $video->video_url }}"
                                                loading="lazy"
                                                style="border: none; position: absolute; top: 0; height: 100%; width: 100%;"
                                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                                                allowfullscreen="true">
                                            </iframe>
                                        @else
                                            {{-- Generic External Video --}}
                                            <iframe
                                                src="{{ $video->video_url }}"
                                                allowfullscreen
                                                allow="autoplay; fullscreen; picture-in-picture">
                                            </iframe>
                                        @endif

                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                                            <div class="text-center">
                                                <i class="fas fa-video-slash fs-1 mb-3"></i>
                                                <p>الفيديو غير متوفر</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Video Details Tab -->
                        <div class="tab-pane fade" id="video-details" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;">نوع الفيديو</th>
                                            <td>
                                                @if($video->video_type == 'youtube')
                                                    <span class="badge bg-danger-transparent"><i class="fab fa-youtube me-1"></i>YouTube</span>
                                                @elseif($video->video_type == 'vimeo')
                                                    <span class="badge bg-info-transparent"><i class="fab fa-vimeo me-1"></i>Vimeo</span>
                                                @elseif($video->video_type == 'upload')
                                                    <span class="badge bg-primary-transparent"><i class="fas fa-upload me-1"></i>مرفوع</span>
                                                @else
                                                    <span class="badge bg-secondary-transparent"><i class="fas fa-link me-1"></i>خارجي</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($video->video_url)
                                        <tr>
                                            <th>رابط الفيديو</th>
                                            <td><a href="{{ $video->video_url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i>عرض الرابط</a></td>
                                        </tr>
                                        @endif
                                        @if($video->duration)
                                        <tr>
                                            <th>المدة</th>
                                            <td>{{ $stats['formatted_duration'] ?? $video->duration . ' دقيقة' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>حالة الظهور</th>
                                            <td>
                                                @if($video->is_visible)
                                                    <span class="badge bg-success">مرئي</span>
                                                @else
                                                    <span class="badge bg-secondary">مخفي</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>حالة النشر</th>
                                            <td>
                                                @if($video->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-warning">مسودة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>حالة المعالجة</th>
                                            <td>
                                                @if($video->processing_status == 'completed')
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>مكتملة</span>
                                                @elseif($video->processing_status == 'processing')
                                                    <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>قيد المعالجة</span>
                                                @elseif($video->processing_status == 'pending')
                                                    <span class="badge bg-secondary"><i class="fas fa-hourglass-half me-1"></i>بانتظار</span>
                                                @elseif($video->processing_status == 'failed')
                                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>فشلت</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>السماح بالتحميل</th>
                                            <td>{{ $video->allow_download ? 'نعم' : 'لا' }}</td>
                                        </tr>
                                        <tr>
                                            <th>التحكم بالسرعة</th>
                                            <td>{{ $video->allow_speed_control ? 'نعم' : 'لا' }}</td>
                                        </tr>
                                        @if($video->thumbnail)
                                        <tr>
                                            <th>صورة مصغرة</th>
                                            <td>
                                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" style="max-width: 200px; border-radius: 8px;">
                                            </td>
                                        </tr>
                                        @endif
                                        @if($video->available_from || $video->available_until)
                                        <tr>
                                            <th>فترة التوفر</th>
                                            <td>
                                                @if($video->available_from)
                                                    من: {{ $video->available_from->format('Y-m-d H:i') }}<br>
                                                @endif
                                                @if($video->available_until)
                                                    إلى: {{ $video->available_until->format('Y-m-d H:i') }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        @if($video->creator)
                                        <tr>
                                            <th>المنشئ</th>
                                            <td>{{ $video->creator->name }} ({{ $video->creator->email }})</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>تاريخ الإنشاء</th>
                                            <td>{{ $video->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>آخر تحديث</th>
                                            <td>{{ $video->updated_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Used In Courses Tab -->
                        <div class="tab-pane fade" id="used-in" role="tabpanel">
                            @if($video->courseModules && $video->courseModules->count() > 0)
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    هذا الفيديو مستخدم في <strong>{{ $video->courseModules->count() }}</strong> وحدة دراسية
                                </div>
                                @foreach($video->courseModules as $module)
                                    <div class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded">
                                        <div>
                                            <h6 class="mb-1 fw-semibold">{{ $module->title }}</h6>
                                            @if($module->course)
                                                <small class="text-muted">
                                                    <i class="fas fa-book me-1"></i>{{ $module->course->title }}
                                                    @if($module->section)
                                                        <span class="mx-1">›</span>
                                                        <i class="fas fa-folder me-1"></i>{{ $module->section->title }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                        @if($module->course)
                                            <a href="{{ route('courses.show', $module->course_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>عرض الكورس
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fs-1 mb-3 opacity-25"></i>
                                    <p class="mb-0">هذا الفيديو غير مستخدم في أي كورس حتى الآن</p>
                                </div>
                            @endif
                        </div>

                        <!-- Actions Tab -->
                        <div class="tab-pane fade" id="actions" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg bg-primary-transparent mb-3 mx-auto">
                                                <i class="fas fa-edit fs-18"></i>
                                            </div>
                                            <h6 class="mb-2">تعديل الفيديو</h6>
                                            <p class="text-muted mb-3 small">قم بتحديث معلومات الفيديو والإعدادات</p>
                                            <a href="{{ route('videos.edit', $video->id) }}" class="btn btn-primary w-100">
                                                <i class="fas fa-edit me-2"></i>تعديل
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg bg-{{ $video->is_published ? 'warning' : 'success' }}-transparent mb-3 mx-auto">
                                                <i class="fas fa-{{ $video->is_published ? 'eye-slash' : 'eye' }} fs-18"></i>
                                            </div>
                                            <h6 class="mb-2">{{ $video->is_published ? 'إلغاء النشر' : 'نشر الفيديو' }}</h6>
                                            <p class="text-muted mb-3 small">{{ $video->is_published ? 'إخفاء الفيديو عن الطلاب' : 'جعل الفيديو متاحاً للطلاب' }}</p>
                                            <form action="{{ route('videos.toggle-publish', $video->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-{{ $video->is_published ? 'warning' : 'success' }} w-100">
                                                    <i class="fas fa-{{ $video->is_published ? 'eye-slash' : 'eye' }} me-2"></i>
                                                    {{ $video->is_published ? 'إلغاء النشر' : 'نشر' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg bg-info-transparent mb-3 mx-auto">
                                                <i class="fas fa-copy fs-18"></i>
                                            </div>
                                            <h6 class="mb-2">نسخ الفيديو</h6>
                                            <p class="text-muted mb-3 small">إنشاء نسخة جديدة من هذا الفيديو</p>
                                            <form action="{{ route('videos.duplicate', $video->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-info w-100">
                                                    <i class="fas fa-copy me-2"></i>نسخ
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border border-danger">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg bg-danger-transparent mb-3 mx-auto">
                                                <i class="fas fa-trash fs-18"></i>
                                            </div>
                                            <h6 class="mb-2">حذف الفيديو</h6>
                                            <p class="text-muted mb-3 small">حذف الفيديو نهائياً من النظام</p>
                                            <form action="{{ route('videos.destroy', $video->id) }}" method="POST"
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الفيديو؟ لا يمكن التراجع عن هذا الإجراء.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-trash me-2"></i>حذف نهائياً
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
