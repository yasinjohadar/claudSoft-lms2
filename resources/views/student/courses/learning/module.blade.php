@extends('student.layouts.master')

@section('page-title')
    {{ $module->title }}
@stop

@section('css')
<style>
    .video-container {
        background: #000;
        border-radius: 12px;
        overflow: hidden;
    }

    .video-container iframe,
    .video-container video {
        display: block;
        width: 100%;
        border: none;
        border-radius: 12px;
    }


    .card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .sidebar-nav {
        position: sticky;
        top: 100px;
    }

</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Breadcrumb -->
        <div class="page-header">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.courses.my-courses') }}">كورساتي</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $module->course_id) }}">{{ $module->course->title }}</a></li>
                <li class="breadcrumb-item active">{{ $module->title }}</li>
            </ol>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-9 order-2">

                <!-- Video -->
                @if($module->module_type == 'video' && $module->modulable)
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="video-container">
                                @php $video = $module->modulable; @endphp
                                @if($video->video_type == 'youtube')
                                    @php
                                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\.be\/)([^"&?\\/ ]{11})/', $video->video_url, $matches);
                                        $youtubeId = $matches[1] ?? null;
                                    @endphp
                                    @if($youtubeId)
                                        <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                                width="100%"
                                                height="600"
                                                allowfullscreen></iframe>
                                    @endif
                                @elseif($video->video_type == 'upload' && $video->video_path)
                                    <video controls width="100%" height="600">
                                        <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                    </video>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Lesson -->
                @if($module->module_type == 'lesson' && $module->modulable)
                    <div class="card">
                        <div class="card-body p-4">
                            {!! $module->modulable->content !!}
                        </div>
                    </div>
                @endif

                <!-- Assignment -->
                @if($module->module_type == 'assignment' && $module->modulable)
                    @php
                        $assignment = $module->modulable;
                        $studentId = auth()->id();

                        // Get student's submissions for this assignment
                        $submissions = $assignment->submissions()
                            ->where('student_id', $studentId)
                            ->orderBy('attempt_number', 'desc')
                            ->get();

                        $latestSubmission = $submissions->first();

                        // Check if student can submit
                        $canSubmit = !$latestSubmission && $assignment->isAvailable() && !$assignment->isPastDue();
                        $canResubmit = $latestSubmission &&
                                      $assignment->allow_resubmission &&
                                      $assignment->canResubmit($studentId) &&
                                      $assignment->isAvailable();
                    @endphp

                    <!-- Assignment Info Card -->
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white;">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>{{ $assignment->title }}</h5>
                        </div>
                        <div class="card-body">
                            @if($assignment->description)
                                <p class="text-muted mb-3">{{ $assignment->description }}</p>
                            @endif

                            @if($assignment->instructions)
                                <div class="alert alert-info mb-4">
                                    <h6 class="mb-2"><i class="fas fa-clipboard-list me-2"></i>التعليمات:</h6>
                                    <div>{!! nl2br(e($assignment->instructions)) !!}</div>
                                </div>
                            @endif

                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-star text-warning fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">الدرجة القصوى</p>
                                        <h4 class="mb-0">{{ $assignment->max_grade }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-upload text-primary fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">نوع التسليم</p>
                                        <span class="badge bg-secondary">
                                            @if($assignment->submission_type === 'link') روابط
                                            @elseif($assignment->submission_type === 'file') ملفات
                                            @else روابط وملفات @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-clock text-danger fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">موعد التسليم</p>
                                        <p class="mb-0 small">{{ $assignment->due_date ? $assignment->due_date->format('Y-m-d') : 'غير محدد' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-redo text-info fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">إعادة التسليم</p>
                                        <span class="badge {{ $assignment->allow_resubmission ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $assignment->allow_resubmission ? 'مسموح' : 'غير مسموح' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment Attachments -->
                            @if($assignment->attachments && is_array($assignment->attachments) && count($assignment->attachments) > 0)
                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i>مرفقات الواجب</h6>
                                    <div class="row g-2">
                                        @foreach($assignment->attachments as $attachment)
                                            <div class="col-md-6">
                                                <div class="border rounded p-2 d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <i class="fas fa-file-{{ $attachment['type'] ?? 'alt' }} me-2 text-primary"></i>
                                                        <span>{{ $attachment['name'] }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $attachment['size'] ?? 'N/A' }}</small>
                                                    </div>
                                                    <a href="{{ \Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Current Grade -->
                            @if($latestSubmission && $latestSubmission->grade !== null)
                                <div class="alert alert-success">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><i class="fas fa-check-circle me-2"></i>تم التقييم</h6>
                                            <h3 class="mb-0">{{ $latestSubmission->getFinalGrade() }} / {{ $assignment->max_grade }}</h3>
                                            @if($latestSubmission->feedback)
                                                <p class="mb-0 mt-2 small"><strong>ملاحظات المدرس:</strong> {{ $latestSubmission->feedback }}</p>
                                            @endif
                                        </div>
                                        <div class="text-center">
                                            <div class="progress-circle" style="width: 80px; height: 80px;">
                                                <svg viewBox="0 0 36 36" class="circular-chart">
                                                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3"/>
                                                    <path class="circle" stroke-dasharray="{{ $latestSubmission->getGradePercentage() }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="3"/>
                                                    <text x="18" y="20.35" class="percentage" fill="#10b981" font-size="8" text-anchor="middle">{{ number_format($latestSubmission->getGradePercentage(), 0) }}%</text>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Submission Form -->
                            @if($canSubmit || $canResubmit)
                                <div class="card border-primary mt-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-upload me-2"></i>{{ $latestSubmission ? 'إعادة التسليم' : 'تسليم الواجب' }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($canResubmit)
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                يمكنك إعادة تسليم الواجب.
                                                @php
                                                    $remaining = $assignment->getRemainingResubmissions($studentId);
                                                @endphp
                                                @if($remaining !== null)
                                                    المحاولات المتبقية: <strong>{{ $remaining }}</strong>
                                                @endif
                                            </div>
                                        @endif

                                        <form action="{{ route('student.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf

                                            <!-- Submission Text -->
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-pen me-2"></i>نص التسليم (اختياري)</label>
                                                <textarea name="submission_text" class="form-control" rows="3" placeholder="أضف أي ملاحظات أو شرح للتسليم...">{{ old('submission_text') }}</textarea>
                                            </div>

                                            <!-- Links -->
                                            @if(in_array($assignment->submission_type, ['link', 'both']))
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-link me-2"></i>الروابط (حتى {{ $assignment->max_links }} روابط)</label>
                                                    <div id="links-container-{{ $assignment->id }}">
                                                        <div class="input-group mb-2">
                                                            <input type="url" name="links[]" class="form-control" placeholder="https://example.com">
                                                            <button type="button" class="btn btn-outline-success" onclick="addLinkField{{ $assignment->id }}()">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">مثال: رابط Google Drive، GitHub، أو أي رابط آخر</small>
                                                </div>
                                            @endif

                                            <!-- Files -->
                                            @if(in_array($assignment->submission_type, ['file', 'both']))
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-file-upload me-2"></i>الملفات (حتى {{ $assignment->max_files }} ملفات)</label>
                                                    <input type="file" name="files[]" class="form-control" multiple>
                                                    <small class="text-muted">الحد الأقصى: {{ number_format($assignment->max_file_size / 1024, 0) }} MB لكل ملف</small>
                                                </div>
                                            @endif

                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane me-2"></i>تسليم الواجب
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="saveDraft{{ $assignment->id }}()">
                                                    <i class="fas fa-save me-2"></i>حفظ كمسودة
                                                </button>
                                            </div>
                                        </form>

                                        <script>
                                            let linkCount{{ $assignment->id }} = 1;
                                            const maxLinks{{ $assignment->id }} = {{ $assignment->max_links }};

                                            function addLinkField{{ $assignment->id }}() {
                                                if (linkCount{{ $assignment->id }} >= maxLinks{{ $assignment->id }}) {
                                                    alert('لقد وصلت للحد الأقصى من الروابط');
                                                    return;
                                                }
                                                const container = document.getElementById('links-container-{{ $assignment->id }}');
                                                const newField = document.createElement('div');
                                                newField.className = 'input-group mb-2';
                                                newField.innerHTML = `
                                                    <input type="url" name="links[]" class="form-control" placeholder="https://example.com">
                                                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove(); linkCount{{ $assignment->id }}--;">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                `;
                                                container.appendChild(newField);
                                                linkCount{{ $assignment->id }}++;
                                            }

                                            function saveDraft{{ $assignment->id }}() {
                                                alert('سيتم تنفيذ حفظ المسودة قريباً');
                                            }
                                        </script>
                                    </div>
                                </div>
                            @elseif($assignment->isPastDue() && !$assignment->canSubmitLate())
                                <div class="alert alert-danger text-center mt-4">
                                    <i class="fas fa-times-circle fs-1 mb-3 d-block"></i>
                                    <h5>انتهى موعد التسليم</h5>
                                    <p class="mb-0">لم يعد بإمكانك تسليم هذا الواجب</p>
                                </div>
                            @elseif(!$assignment->isAvailable())
                                <div class="alert alert-warning text-center mt-4">
                                    <i class="fas fa-clock fs-1 mb-3 d-block"></i>
                                    <h5>الواجب غير متاح حالياً</h5>
                                    @if($assignment->available_from)
                                        <p class="mb-0">سيكون متاحاً من: {{ $assignment->available_from->format('Y-m-d H:i') }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Previous Submissions -->
                            @if($submissions->count() > 0)
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-history me-2"></i>محاولاتك السابقة ({{ $submissions->count() }})</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>المحاولة</th>
                                                        <th>تاريخ التسليم</th>
                                                        <th>الحالة</th>
                                                        <th>الدرجة</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($submissions as $submission)
                                                        <tr>
                                                            <td><span class="badge bg-info">#{{ $submission->attempt_number }}</span></td>
                                                            <td>
                                                                {{ $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : '-' }}
                                                                @if($submission->is_late)
                                                                    <br><span class="badge bg-danger">متأخر</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($submission->status === 'graded')
                                                                    <span class="badge bg-success">تم التقييم</span>
                                                                @elseif($submission->status === 'submitted')
                                                                    <span class="badge bg-warning">قيد الانتظار</span>
                                                                @else
                                                                    <span class="badge bg-secondary">مسودة</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($submission->grade !== null)
                                                                    <strong class="text-success">{{ $submission->getFinalGrade() }} / {{ $assignment->max_grade }}</strong>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Complete -->
                @if($enrollment)
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5><i class="fas fa-graduation-cap text-primary me-2"></i>هل أكملت هذا الدرس؟</h5>
                                    <p class="text-muted mb-0">قم بتحديده كمكتمل للمتابعة</p>
                                </div>
                                @if($isCompleted)
                                    <form action="{{ route('student.learn.module.mark-incomplete', $module->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check-circle me-2"></i>تم الإكمال
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('student.learn.module.mark-complete', $module->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-2"></i>تحديد كمكتمل
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Question Module -->
                @if($module->module_type == 'question_module' && $module->modulable)
                    @php
                        $questionModule = $module->modulable;
                    @endphp

                    <!-- Question Module Info Card -->
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); color: white;">
                            <h5 class="mb-0"><i class="fas fa-clipboard-question me-2"></i>{{ $questionModule->title }}</h5>
                        </div>
                        <div class="card-body">
                            @if($questionModule->description)
                                <p class="text-muted mb-3">{{ $questionModule->description }}</p>
                            @endif

                            @if($questionModule->instructions)
                                <div class="alert alert-info mb-4">
                                    <h6 class="mb-2"><i class="fas fa-clipboard-list me-2"></i>التعليمات:</h6>
                                    <div>{!! nl2br(e($questionModule->instructions)) !!}</div>
                                </div>
                            @endif

                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-question-circle text-info fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">عدد الأسئلة</p>
                                        <h4 class="mb-0">{{ $questionModule->questions->count() }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-star text-warning fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">إجمالي الدرجات</p>
                                        <h4 class="mb-0">{{ $questionModule->getTotalGrade() }}</h4>
                                    </div>
                                </div>
                                @if($questionModule->time_limit)
                                    <div class="col-md-3">
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-clock text-danger fs-4 mb-2"></i>
                                            <p class="mb-1 text-muted small">الوقت المحدد</p>
                                            <h4 class="mb-0">{{ $questionModule->time_limit }} <small>دقيقة</small></h4>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <i class="fas fa-redo text-primary fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">المحاولات المسموحة</p>
                                        <h4 class="mb-0">{{ $questionModule->attempts_allowed }}</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Questions Preview -->
                            @if($questionModule->questions->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3"><i class="fas fa-list me-2"></i>الأسئلة ({{ $questionModule->questions->count() }})</h6>
                                    <div class="list-group">
                                        @foreach($questionModule->questions as $index => $question)
                                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    <span class="text-dark">
                                                        {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                                    </span>
                                                </div>
                                                <div class="text-end" style="min-width: 150px;">
                                                    <span class="badge bg-info-transparent text-info me-1">
                                                        {{ $question->questionType->display_name }}
                                                    </span>
                                                    <span class="badge bg-success-transparent text-success">
                                                        {{ $question->pivot->question_grade }} نقطة
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Attempts Info -->
                            @php
                                $studentAttempts = $questionModule->studentAttempts(auth()->id());
                                $completedAttempts = $studentAttempts->where('status', 'completed')->count();
                                $inProgressAttempt = $studentAttempts->where('status', 'in_progress')->first();
                                $canAttempt = $questionModule->canStudentAttempt(auth()->id());
                                $lastAttempt = $studentAttempts->first();
                            @endphp

                            @if($completedAttempts > 0)
                                <div class="alert alert-info mb-4">
                                    <h6 class="mb-2"><i class="fas fa-history me-2"></i>محاولاتك السابقة:</h6>
                                    <div class="d-flex justify-content-between">
                                        <span>عدد المحاولات: <strong>{{ $completedAttempts }} / {{ $questionModule->attempts_allowed }}</strong></span>
                                        @if($lastAttempt && $lastAttempt->status === 'completed')
                                            <span>آخر درجة: <strong class="{{ $lastAttempt->is_passed ? 'text-success' : 'text-danger' }}">{{ number_format($lastAttempt->percentage, 1) }}%</strong></span>
                                        @endif
                                    </div>
                                    @if($lastAttempt && $lastAttempt->status === 'completed')
                                        <a href="{{ route('student.question-module.result', $lastAttempt->id) }}" class="btn btn-sm btn-outline-info mt-2">
                                            <i class="fas fa-eye me-1"></i>عرض آخر محاولة
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <!-- Start Test Button -->
                            <div class="text-center mt-4">
                                @if($inProgressAttempt)
                                    <a href="{{ route('student.question-module.take', $inProgressAttempt->id) }}" class="btn btn-lg btn-warning">
                                        <i class="fas fa-play-circle me-2"></i>متابعة الاختبار
                                    </a>
                                    <p class="text-warning small mt-2 mb-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        لديك محاولة غير مكتملة، يرجى إكمالها أو إرسالها
                                    </p>
                                @elseif($canAttempt)
                                    <a href="{{ route('student.question-module.start', $questionModule->id) }}" class="btn btn-lg btn-primary">
                                        <i class="fas fa-play me-2"></i>بدء الاختبار
                                    </a>
                                    <p class="text-muted small mt-2 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        سيتم احتساب هذه المحاولة من المحاولات المسموحة
                                    </p>
                                @else
                                    <button class="btn btn-lg btn-secondary" disabled>
                                        <i class="fas fa-ban me-2"></i>استنفدت جميع المحاولات
                                    </button>
                                    <p class="text-danger small mt-2 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        لقد استخدمت جميع المحاولات المسموحة ({{ $questionModule->attempts_allowed }})
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 order-1">
                <div class="sidebar-nav">
                    <div class="card">
                        <!-- Module Info Header -->
                        <div class="card-header" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white text-primary">
                                    @if($module->module_type == 'video')
                                        <i class="fas fa-play me-1"></i> فيديو
                                    @elseif($module->module_type == 'lesson')
                                        <i class="fas fa-book me-1"></i> درس
                                    @elseif($module->module_type == 'assignment')
                                        <i class="fas fa-file-alt me-1"></i> واجب
                                    @elseif($module->module_type == 'quiz')
                                        <i class="fas fa-question-circle me-1"></i> اختبار
                                    @elseif($module->module_type == 'question_module')
                                        <i class="fas fa-clipboard-question me-1"></i> اختبار
                                    @else
                                        <i class="fas fa-circle me-1"></i> محتوى
                                    @endif
                                </span>
                                @if($isCompleted)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> مكتمل
                                    </span>
                                @endif
                            </div>
                            <h5 class="mb-1 fw-bold">{{ $module->title }}</h5>
                            @if($module->description)
                                <p class="mb-0 small opacity-75">{{ Str::limit($module->description, 80) }}</p>
                            @endif
                        </div>

                        <!-- Course Content - Hidden for Question Modules to avoid distracting students -->
                        @if($module->module_type != 'question_module')
                            <div class="card-header bg-light border-top">
                                <h6 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>محتوى الكورس</h6>
                            </div>
                            <div class="card-body" style="max-height: 450px; overflow-y: auto; padding: 1rem;">
                                @foreach($module->course->sections as $section)
                                    <div style="margin-bottom: 1rem;">
                                        <div style="font-size: 0.85rem; font-weight: 600; color: #4f46e5; padding: 0.5rem 0; margin-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                                            <i class="fas fa-folder-open me-2" style="color: #6366f1;"></i>{{ $section->title }}
                                        </div>
                                        @foreach($section->modules as $mod)
                                            <a href="{{ route('student.learn.module', $mod->id) }}"
                                               class="d-block text-decoration-none mb-1 p-2 rounded {{ $mod->id == $module->id ? 'bg-primary text-white' : (in_array($mod->id, $completedModules) ? 'bg-success-transparent text-success' : 'bg-light text-dark') }}"
                                               style="font-size: 0.8rem; border-right: 3px solid {{ $mod->id == $module->id ? '#7c3aed' : (in_array($mod->id, $completedModules) ? '#10b981' : 'transparent') }};">
                                                @if($mod->module_type == 'video')
                                                    <i class="fas fa-play-circle me-2"></i>
                                                @elseif($mod->module_type == 'lesson')
                                                    <i class="fas fa-book-open me-2"></i>
                                                @elseif($mod->module_type == 'assignment')
                                                    <i class="fas fa-file-alt me-2"></i>
                                                @elseif($mod->module_type == 'quiz')
                                                    <i class="fas fa-question-circle me-2"></i>
                                                @elseif($mod->module_type == 'question_module')
                                                    <i class="fas fa-clipboard-question me-2"></i>
                                                @else
                                                    <i class="fas fa-circle me-2"></i>
                                                @endif
                                                {{ $mod->title }}
                                                @if(in_array($mod->id, $completedModules))
                                                    <i class="fas fa-check-circle {{ $mod->id == $module->id ? 'text-white' : 'text-success' }} ms-2"></i>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
