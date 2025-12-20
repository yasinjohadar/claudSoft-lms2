@extends('admin.layouts.master')

@section('page-title')
    معاينة الدرس - {{ $module->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $module->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $module->course_id) }}">{{ $module->course->title }}</a></li>
                            <li class="breadcrumb-item active">{{ $module->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <div class="d-flex gap-2">
                        @if($module->is_visible)
                            <span class="badge bg-success-transparent px-3 py-2">
                                <i class="fas fa-eye me-1"></i>مرئي
                            </span>
                        @else
                            <span class="badge bg-secondary-transparent px-3 py-2">
                                <i class="fas fa-eye-slash me-1"></i>مخفي
                            </span>
                        @endif

                        @if($module->is_required)
                            <span class="badge bg-danger-transparent px-3 py-2">
                                <i class="fas fa-exclamation-circle me-1"></i>مطلوب
                            </span>
                        @endif

                        @if($module->is_graded)
                            <span class="badge bg-warning-transparent px-3 py-2">
                                <i class="fas fa-star me-1"></i>له درجة
                            </span>
                        @endif

                        <a href="{{ route('sections.modules.edit', [$module->section_id, $module->id]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>تعديل
                        </a>

                        <a href="{{ route('courses.show', $module->course_id) }}" class="btn btn-light">
                            <i class="fas fa-arrow-right me-1"></i>رجوع
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Module Information Card -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الدرس</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;">الكورس</th>
                                            <td>
                                                <a href="{{ route('courses.show', $module->course_id) }}">{{ $module->course->title }}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>القسم</th>
                                            <td>{{ $module->section->title }}</td>
                                        </tr>
                                        <tr>
                                            <th>نوع الدرس</th>
                                            <td>
                                                @if($module->module_type == 'lesson')
                                                    <span class="badge bg-primary-transparent"><i class="fas fa-book me-1"></i>درس نصي</span>
                                                @elseif($module->module_type == 'video')
                                                    <span class="badge bg-danger-transparent"><i class="fas fa-video me-1"></i>فيديو</span>
                                                @elseif($module->module_type == 'resource')
                                                    <span class="badge bg-info-transparent"><i class="fas fa-file me-1"></i>مورد</span>
                                                @elseif($module->module_type == 'quiz')
                                                    <span class="badge bg-warning-transparent"><i class="fas fa-question-circle me-1"></i>اختبار</span>
                                                @elseif($module->module_type == 'assignment')
                                                    <span class="badge bg-success-transparent"><i class="fas fa-tasks me-1"></i>واجب</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($module->description)
                                        <tr>
                                            <th>الوصف</th>
                                            <td>{{ $module->description }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>الترتيب</th>
                                            <td>{{ $module->sort_order }}</td>
                                        </tr>
                                        <tr>
                                            <th>نوع الإكمال</th>
                                            <td>
                                                @if($module->completion_type == 'auto')
                                                    <span class="badge bg-success">تلقائي</span>
                                                @elseif($module->completion_type == 'manual')
                                                    <span class="badge bg-warning">يدوي</span>
                                                @elseif($module->completion_type == 'score_based')
                                                    <span class="badge bg-info">بناءً على الدرجة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($module->estimated_duration)
                                        <tr>
                                            <th>المدة المقدرة</th>
                                            <td>{{ $module->estimated_duration }} دقيقة</td>
                                        </tr>
                                        @endif
                                        @if($module->is_graded && $module->max_score)
                                        <tr>
                                            <th>الدرجة القصوى</th>
                                            <td>{{ $module->max_score }}</td>
                                        </tr>
                                        @endif
                                        @if($module->available_from || $module->available_until)
                                        <tr>
                                            <th>فترة الإتاحة</th>
                                            <td>
                                                @if($module->available_from)
                                                    من: {{ \Carbon\Carbon::parse($module->available_from)->format('Y-m-d H:i') }}
                                                @endif
                                                @if($module->available_until)
                                                    <br>حتى: {{ \Carbon\Carbon::parse($module->available_until)->format('Y-m-d H:i') }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>تاريخ الإنشاء</th>
                                            <td>{{ $module->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>آخر تحديث</th>
                                            <td>{{ $module->updated_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">إجمالي المشاركات</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total_completions'] }}</h4>
                                    <span class="badge bg-primary-transparent">مشارك</span>
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
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">الإكمالات</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['completed_count'] }}</h4>
                                    <span class="badge bg-success-transparent">مكتمل</span>
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
                                        <i class="fas fa-spinner fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">قيد التقدم</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['in_progress_count'] }}</h4>
                                    <span class="badge bg-warning-transparent">جاري</span>
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
                                        <i class="fas fa-star fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">متوسط الدرجة</p>
                                    <h4 class="fw-bold mb-2">{{ number_format($stats['average_score'], 1) }}%</h4>
                                    <span class="badge bg-info-transparent">درجة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Content Preview -->
            @if($module->modulable)
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معاينة المحتوى</div>
                        </div>
                        <div class="card-body">
                            @if($module->module_type == 'lesson')
                                <h5>{{ $module->modulable->title }}</h5>
                                @if($module->modulable->short_description)
                                    <p class="text-muted">{{ $module->modulable->short_description }}</p>
                                @endif
                                @if($module->modulable->content)
                                    <div class="border rounded p-3 bg-light">
                                        {!! Str::limit($module->modulable->content, 500) !!}
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <a href="{{ route('lessons.show', $module->modulable->id) }}" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i>عرض الدرس الكامل
                                    </a>
                                </div>

                            @elseif($module->module_type == 'video')
                                <h5>{{ $module->modulable->title }}</h5>
                                @if($module->modulable->description)
                                    <p class="text-muted">{{ $module->modulable->description }}</p>
                                @endif
                                @if($module->modulable->video_url)
                                    <div class="ratio ratio-16x9 mb-3">
                                        @if($module->modulable->video_type == 'youtube')
                                            @php
                                                // Extract YouTube video ID
                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $module->modulable->video_url, $matches);
                                                $youtubeId = $matches[1] ?? null;
                                            @endphp
                                            @if($youtubeId)
                                                <iframe
                                                    src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1"
                                                    allowfullscreen
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                                </iframe>
                                            @endif
                                        @elseif($module->modulable->video_type == 'vimeo')
                                            @php
                                                preg_match('/vimeo\.com\/(\d+)/', $module->modulable->video_url, $matches);
                                                $vimeoId = $matches[1] ?? null;
                                            @endphp
                                            @if($vimeoId)
                                                <iframe
                                                    src="https://player.vimeo.com/video/{{ $vimeoId }}"
                                                    allowfullscreen
                                                    allow="autoplay; fullscreen; picture-in-picture">
                                                </iframe>
                                            @endif
                                        @else
                                            <iframe src="{{ $module->modulable->video_url }}" allowfullscreen></iframe>
                                        @endif
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <a href="{{ route('videos.show', $module->modulable->id) }}" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i>عرض الفيديو الكامل
                                    </a>
                                </div>

                            @elseif($module->module_type == 'resource')
                                <h5>{{ $module->modulable->title }}</h5>
                                @if($module->modulable->description)
                                    <p class="text-muted">{{ $module->modulable->description }}</p>
                                @endif
                                @if($module->modulable->file_path)
                                    <div class="alert alert-info">
                                        <i class="fas fa-file me-2"></i>
                                        نوع الملف: {{ $module->modulable->file_type ?? 'غير محدد' }}
                                        <br>
                                        حجم الملف: {{ $module->modulable->file_size ? number_format($module->modulable->file_size / 1024, 2) . ' KB' : 'غير محدد' }}
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <a href="{{ route('resources.show', $module->modulable->id) }}" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-1"></i>عرض المورد الكامل
                                    </a>
                                    @if($module->modulable->file_path)
                                        <a href="{{ route('resources.download', $module->modulable->id) }}" class="btn btn-success">
                                            <i class="fas fa-download me-1"></i>تحميل الملف
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Completion Progress -->
            @if($stats['total_completions'] > 0)
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">تقدم الطلاب</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th>الحالة</th>
                                            <th>التقدم</th>
                                            <th>الدرجة</th>
                                            <th>تاريخ البدء</th>
                                            <th>تاريخ الإكمال</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($module->completions()->with('student')->latest()->limit(10)->get() as $completion)
                                        <tr>
                                            <td>{{ $completion->student->name ?? 'غير معروف' }}</td>
                                            <td>
                                                @if($completion->completion_status == 'completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                @elseif($completion->completion_status == 'in_progress')
                                                    <span class="badge bg-warning">قيد التقدم</span>
                                                @else
                                                    <span class="badge bg-secondary">لم يبدأ</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $completion->progress_percentage ?? 0 }}%"
                                                         aria-valuenow="{{ $completion->progress_percentage ?? 0 }}"
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $completion->progress_percentage ?? 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $completion->score ?? '-' }}</td>
                                            <td>{{ $completion->started_at ? $completion->started_at->format('Y-m-d H:i') : '-' }}</td>
                                            <td>{{ $completion->completed_at ? $completion->completed_at->format('Y-m-d H:i') : '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">لا توجد بيانات</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
@stop
