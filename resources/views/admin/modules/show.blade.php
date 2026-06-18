@extends('admin.layouts.master')

@section('page-title')
    معاينة الدرس - {{ $module->title }}
@stop

@section('content')
    <div class="main-content app-content admin-module-show-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.show', $module->course_id) }}">{{ Str::limit($module->course->title, 32) }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($module->title, 40) }}</li>
                    </ol>
                </nav>
            </div>

            @php
                $moduleTypeMeta = match ($module->module_type) {
                    'lesson' => ['icon' => 'fe-book-open', 'label' => 'درس نصي', 'tone' => 'primary'],
                    'video' => ['icon' => 'fe-play-circle', 'label' => 'فيديو', 'tone' => 'danger'],
                    'resource' => ['icon' => 'fe-file-text', 'label' => 'مورد', 'tone' => 'info'],
                    'quiz' => ['icon' => 'fe-help-circle', 'label' => 'اختبار', 'tone' => 'warning'],
                    'assignment' => ['icon' => 'fe-check-square', 'label' => 'واجب', 'tone' => 'success'],
                    default => ['icon' => 'fe-layers', 'label' => 'وحدة', 'tone' => 'secondary'],
                };
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-users', 'label' => 'إجمالي المشاركات', 'value' => $stats['total_completions'], 'sub' => 'طالب شارك'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'الإكمالات', 'value' => $stats['completed_count'], 'sub' => 'أكملوا الدرس'],
                    ['variant' => 'orange', 'icon' => 'fe-loader', 'label' => 'قيد التقدم', 'value' => $stats['in_progress_count'], 'sub' => 'ما زالوا يتابعون'],
                    ['variant' => 'cyan', 'icon' => 'fe-star', 'label' => 'متوسط الدرجة', 'value' => number_format($stats['average_score'], 1) . '%', 'sub' => 'عبر المشاركين', 'countup' => false],
                ];
            @endphp

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-course-module-card__icon admin-course-module-card__icon--{{ $moduleTypeMeta['tone'] }} admin-module-show-page__icon">
                                <i class="fe {{ $moduleTypeMeta['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-eye me-1"></i>معاينة الدرس
                                </span>
                                <h2 class="group-show-hero__title mb-2">{{ $module->title }}</h2>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="group-show-chip group-show-chip--sm">{{ $moduleTypeMeta['label'] }}</span>
                                    @if($module->is_visible)
                                        <span class="group-show-chip group-show-chip--sm text-success"><i class="fe fe-eye me-1"></i>مرئي</span>
                                    @else
                                        <span class="group-show-chip group-show-chip--sm text-muted"><i class="fe fe-eye-off me-1"></i>مخفي</span>
                                    @endif
                                    @if($module->is_required)
                                        <span class="group-show-chip group-show-chip--sm text-danger"><i class="fe fe-asterisk me-1"></i>مطلوب</span>
                                    @endif
                                    @if($module->is_graded)
                                        <span class="group-show-chip group-show-chip--sm text-warning"><i class="fe fe-star me-1"></i>له درجة</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('sections.modules.edit', [$module->section_id, $module->id]) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل الدرس</span>
                            </a>
                            <a href="{{ route('courses.modules.completions', ['course' => $module->course_id, 'module' => $module->id]) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-user-check"></i></span>
                                <span class="group-show-action__text">تقدم الطلاب</span>
                            </a>
                            <a href="{{ route('courses.show', $module->course_id) }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للكورس</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    @if($card['countup'] ?? true)
                                        <h3 class="admin-stats-card__value mb-1" data-countup="{{ is_numeric($card['value']) ? $card['value'] : 0 }}">0</h3>
                                    @else
                                        <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                                    @endif
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 dashboard-fade-in mb-4">
                <div class="col-lg-4">
                    <div class="card custom-card group-show-members-card h-100">
                        <div class="card-header border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">معلومات الدرس</h6>
                        </div>
                        <div class="card-body pt-3">
                            <dl class="admin-module-show-page__meta mb-0">
                                <div class="admin-module-show-page__meta-row">
                                    <dt>الكورس</dt>
                                    <dd><a href="{{ route('courses.show', $module->course_id) }}" class="text-decoration-none">{{ Str::limit($module->course->title, 36) }}</a></dd>
                                </div>
                                <div class="admin-module-show-page__meta-row">
                                    <dt>القسم</dt>
                                    <dd>{{ $module->section->title }}</dd>
                                </div>
                                <div class="admin-module-show-page__meta-row">
                                    <dt>الترتيب</dt>
                                    <dd>#{{ $module->sort_order }}</dd>
                                </div>
                                <div class="admin-module-show-page__meta-row">
                                    <dt>نوع الإكمال</dt>
                                    <dd>
                                        @if($module->completion_type == 'auto')
                                            <span class="group-show-chip group-show-chip--sm text-success">تلقائي</span>
                                        @elseif($module->completion_type == 'manual')
                                            <span class="group-show-chip group-show-chip--sm text-warning">يدوي</span>
                                        @else
                                            <span class="group-show-chip group-show-chip--sm text-info">بناءً على الدرجة</span>
                                        @endif
                                    </dd>
                                </div>
                                @if($module->estimated_duration)
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>المدة المقدرة</dt>
                                        <dd>{{ $module->estimated_duration }} دقيقة</dd>
                                    </div>
                                @endif
                                @if($module->is_graded && $module->max_score)
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>الدرجة القصوى</dt>
                                        <dd>{{ $module->max_score }}</dd>
                                    </div>
                                @endif
                                @if($module->available_from || $module->available_until)
                                    <div class="admin-module-show-page__meta-row">
                                        <dt>فترة الإتاحة</dt>
                                        <dd class="small">
                                            @if($module->available_from)
                                                من {{ \Carbon\Carbon::parse($module->available_from)->format('Y-m-d H:i') }}
                                            @endif
                                            @if($module->available_until)
                                                <br>حتى {{ \Carbon\Carbon::parse($module->available_until)->format('Y-m-d H:i') }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                                <div class="admin-module-show-page__meta-row">
                                    <dt>تاريخ الإنشاء</dt>
                                    <dd>{{ $module->created_at->format('Y-m-d H:i') }}</dd>
                                </div>
                                <div class="admin-module-show-page__meta-row">
                                    <dt>آخر تحديث</dt>
                                    <dd>{{ $module->updated_at->format('Y-m-d H:i') }}</dd>
                                </div>
                            </dl>
                            @if($module->description)
                                <div class="admin-module-show-page__description mt-3">
                                    <small class="text-muted d-block mb-1">الوصف</small>
                                    <p class="mb-0 fs-13">{{ $module->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    @if($module->modulable)
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="group-show-members-card__title mb-0">معاينة المحتوى</h6>
                                @if($module->module_type == 'lesson')
                                    <a href="{{ route('lessons.show', $module->modulable->id) }}" class="btn btn-sm btn-primary-light rounded-pill" target="_blank" rel="noopener">
                                        <i class="fe fe-external-link me-1"></i>الدرس الكامل
                                    </a>
                                @elseif($module->module_type == 'video')
                                    <a href="{{ route('videos.show', $module->modulable->id) }}" class="btn btn-sm btn-primary-light rounded-pill" target="_blank" rel="noopener">
                                        <i class="fe fe-external-link me-1"></i>صفحة الفيديو
                                    </a>
                                @elseif($module->module_type == 'resource')
                                    <a href="{{ route('resources.show', $module->modulable->id) }}" class="btn btn-sm btn-primary-light rounded-pill" target="_blank" rel="noopener">
                                        <i class="fe fe-external-link me-1"></i>صفحة المورد
                                    </a>
                                @endif
                            </div>
                            <div class="card-body pt-3">
                                @if($module->module_type == 'lesson')
                                    <h5 class="admin-module-show-page__content-title">{{ $module->modulable->title }}</h5>
                                    @if($module->modulable->short_description)
                                        <p class="text-muted fs-13 mb-3">{{ $module->modulable->short_description }}</p>
                                    @endif
                                    @if($module->modulable->content)
                                        <div class="admin-module-show-page__text-preview">
                                            {!! Str::limit($module->modulable->content, 800) !!}
                                        </div>
                                    @endif

                                @elseif($module->module_type == 'video')
                                    <h5 class="admin-module-show-page__content-title">{{ $module->modulable->title }}</h5>
                                    @if($module->modulable->description)
                                        <p class="text-muted fs-13 mb-3">{{ $module->modulable->description }}</p>
                                    @endif
                                    @if($module->modulable->video_url)
                                        <div class="admin-module-show-page__video-wrap">
                                            <div class="admin-module-show-page__video">
                                                @if($module->modulable->video_type == 'youtube')
                                                    @php
                                                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $module->modulable->video_url, $matches);
                                                        $youtubeId = $matches[1] ?? null;
                                                    @endphp
                                                    @if($youtubeId)
                                                        <iframe
                                                            src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1"
                                                            allowfullscreen
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            title="{{ $module->modulable->title }}"></iframe>
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
                                                            allow="autoplay; fullscreen; picture-in-picture"
                                                            title="{{ $module->modulable->title }}"></iframe>
                                                    @endif
                                                @else
                                                    <iframe src="{{ $module->modulable->video_url }}" allowfullscreen title="{{ $module->modulable->title }}"></iframe>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                @elseif($module->module_type == 'resource')
                                    <h5 class="admin-module-show-page__content-title">{{ $module->modulable->title }}</h5>
                                    @if($module->modulable->description)
                                        <p class="text-muted fs-13 mb-3">{{ $module->modulable->description }}</p>
                                    @endif
                                    @if($module->modulable->file_path)
                                        <div class="admin-module-show-page__resource-box">
                                            <i class="fe fe-file-text me-2"></i>
                                            <div>
                                                <div>نوع الملف: {{ $module->modulable->file_type ?? 'غير محدد' }}</div>
                                                <div class="text-muted fs-12">الحجم: {{ $module->modulable->file_size ? number_format($module->modulable->file_size / 1024, 2) . ' KB' : 'غير محدد' }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('resources.download', $module->modulable->id) }}" class="btn btn-sm btn-success-light rounded-pill mt-3">
                                            <i class="fe fe-download me-1"></i>تحميل الملف
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-body">
                                <div class="group-show-empty py-5">
                                    <i class="fe fe-inbox group-show-empty__icon" style="width:56px;height:56px;font-size:1.35rem;"></i>
                                    <p class="group-show-empty__desc mb-0">لا يوجد محتوى مرتبط بهذه الوحدة</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($stats['total_completions'] > 0)
                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                        <h6 class="group-show-members-card__title mb-0">
                            تقدم الطلاب
                            <span class="group-show-members-card__count">{{ min(10, $module->completions()->count()) }}</span>
                        </h6>
                        <a href="{{ route('courses.modules.completions', ['course' => $module->course_id, 'module' => $module->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            عرض الكل
                        </a>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap dashboard-table admin-users-table mb-0">
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
                                        <tr class="admin-users-table__row">
                                            <td class="fw-semibold">{{ $completion->student->name ?? 'غير معروف' }}</td>
                                            <td>
                                                @if($completion->completion_status == 'completed')
                                                    <span class="group-show-chip group-show-chip--sm text-success">مكتمل</span>
                                                @elseif($completion->completion_status == 'in_progress')
                                                    <span class="group-show-chip group-show-chip--sm text-warning">قيد التقدم</span>
                                                @else
                                                    <span class="group-show-chip group-show-chip--sm text-muted">لم يبدأ</span>
                                                @endif
                                            </td>
                                            <td style="min-width: 140px;">
                                                <div class="progress admin-module-show-page__progress">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $completion->progress_percentage ?? 0 }}%">
                                                        {{ $completion->progress_percentage ?? 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $completion->score ?? '—' }}</td>
                                            <td><small class="text-muted">{{ $completion->started_at ? $completion->started_at->format('Y-m-d H:i') : '—' }}</small></td>
                                            <td><small class="text-muted">{{ $completion->completed_at ? $completion->completed_at->format('Y-m-d H:i') : '—' }}</small></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">لا توجد بيانات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop

@section('scripts')
<script>
    document.querySelectorAll('.admin-module-show-page [data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        if (!target) {
            el.textContent = '0';
            return;
        }
        var duration = 700;
        var start = performance.now();
        function tick(now) {
            var progress = Math.min((now - start) / duration, 1);
            el.textContent = Math.floor(progress * target).toLocaleString('ar-EG');
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });
</script>
@stop
