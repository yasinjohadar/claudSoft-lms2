                <!-- Video -->
                @if($module->module_type == 'video' && $module->modulable)
                    @php 
                        $video = $module->modulable;
                        $isBunnyUrl = $video && $video->isBunnyStreamVideo();
                    @endphp
                    <div class="student-learn-video-sticky-wrap">
                    <div class="card custom-card student-learn-video-shell dashboard-fade-in">
                        <div class="card-body p-0">
                            <div class="student-learn-video-frame">
                                @if($isBunnyUrl)
                                    @include('shared.video.bunny-player', ['video' => $video])
                                @else
                                @php
                                    $embedCode = $video->getEmbedCode();
                                    $videoUrl = $video->video_url ?? '';
                                @endphp
                                @if($embedCode)
                                    {{-- Use embed code if available --}}
                                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                        {!! $embedCode !!}
                                    </div>
                                @elseif($video->video_type == 'youtube')
                                    @php
                                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\.be\/)([^"&?\\/ ]{11})/', $videoUrl, $matches);
                                        $youtubeId = $matches[1] ?? $video->youtube_id ?? null;
                                    @endphp
                                    @if($youtubeId)
                                        <iframe 
                                            src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen>
                                        </iframe>
                                    @endif
                                @elseif($video->video_type == 'upload' && $video->video_path)
                                    <video 
                                        controls 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                        <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                    </video>
                                @elseif(!empty($videoUrl))
                                    {{-- External URL --}}
                                    <video 
                                        controls 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                        <source src="{{ $videoUrl }}" type="video/mp4">
                                    </video>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                @endif

                <!-- Documentation page -->
                @if($module->module_type == 'documentation' && $module->modulable)
                    @php /** @var \App\Models\DocumentationPage $docPage */ $docPage = $module->modulable; @endphp
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fe fe-book me-2"></i>{{ $docPage->title ?? $module->title }}
                            </h5>
                            @if($docPage->category)
                                <span class="badge bg-primary-transparent text-primary">{{ $docPage->category->name }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($docPage->excerpt)
                                <p class="text-muted mb-3">{{ $docPage->excerpt }}</p>
                            @endif
                            <a href="{{ $docPage->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-primary">
                                <i class="fe fe-external-link me-2"></i>فتح صفحة التوثيق
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Interactive lesson simulator -->
                @if($module->module_type == 'simulator' && $module->modulable)
                    @php /** @var \App\Models\LessonSimulator $simulator */ $simulator = $module->modulable; @endphp
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fe fe-cpu me-2"></i>{{ $simulator->title ?? $module->title }}
                            </h5>
                            <span class="badge bg-info-transparent text-info">محاكاة تفاعلية</span>
                        </div>
                        <div class="card-body">
                            @if($simulator->description)
                                <p class="text-muted mb-3">{{ $simulator->description }}</p>
                            @endif

                            @if($simulator->hasPlayableContent())
                                <div class="simulator-embed-wrap mb-3" style="min-height:75vh;border-radius:12px;overflow:hidden;background:#f8f9fa;border:1px solid rgba(0,0,0,.08);">
                                    <iframe
                                        src="{{ $simulator->playUrl($module) }}"
                                        title="{{ $simulator->title }}"
                                        style="width:100%;min-height:75vh;border:0;display:block;"
                                        allow="clipboard-write"
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            @else
                                <div class="alert alert-warning mb-3">المحاكاة لا تحتوي على محتوى قابل للعرض بعد.</div>
                            @endif

                            <a href="{{ $simulator->playerUrl($module) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                                <i class="fe fe-external-link me-2"></i>فتح في نافذة جديدة
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Resource (external link / embedded) -->
                @if($module->module_type == 'resource' && $module->modulable)
                    @include('student.courses.learning.partials.resource-link-panel', [
                        'resource' => $module->modulable,
                        'module' => $module,
                    ])
                @endif

                <!-- Lesson -->
                @if($module->module_type == 'lesson' && $module->modulable)
                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-body p-4 student-learn-lesson-content">
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
                                    <div class="text-center p-3 rounded student-learn-stat-tile">
                                        <i class="fas fa-star text-warning fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">الدرجة القصوى</p>
                                        <h4 class="mb-0">{{ $assignment->max_grade }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 rounded student-learn-stat-tile">
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
                                    <div class="text-center p-3 rounded student-learn-stat-tile">
                                        <i class="fas fa-clock text-danger fs-4 mb-2"></i>
                                        <p class="mb-1 text-muted small">موعد التسليم</p>
                                        <p class="mb-0 small">{{ $assignment->due_date ? $assignment->due_date->format('Y-m-d') : 'غير محدد' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 rounded student-learn-stat-tile">
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

                                        <form action="{{ route('student.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" data-turbo="false">
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
                @include('student.courses.learning.partials.learn-completion')

                <!-- Question Module -->
                @if($module->module_type == 'question_module' && $module->modulable)
                    @php
                        $questionModule = $module->modulable;
                        $studentAttempts = $questionModule->studentAttempts(auth()->id());
                        $completedAttempts = $studentAttempts->where('status', 'completed')->count();
                        $inProgressAttempt = $studentAttempts->where('status', 'in_progress')->first();
                        $canAttempt = $questionModule->canStudentAttempt(auth()->id());
                        $lastAttempt = $studentAttempts->first();
                    @endphp

                    @php
                        $qmStats = [
                            ['icon' => 'fe-help-circle', 'label' => 'عدد الأسئلة', 'value' => $questionModule->questions->count(), 'color' => 'blue'],
                            ['icon' => 'fe-star', 'label' => 'إجمالي الدرجات', 'value' => $questionModule->getTotalGrade(), 'color' => 'gold'],
                        ];
                        if ($questionModule->time_limit) {
                            $qmStats[] = ['icon' => 'fe-clock', 'label' => 'الوقت المحدد', 'value' => $questionModule->time_limit . ' <small>دقيقة</small>', 'color' => 'red'];
                        }
                        $qmStats[] = ['icon' => 'fe-refresh-cw', 'label' => 'المحاولات المسموحة', 'value' => $questionModule->attempts_allowed, 'color' => 'cyan'];
                    @endphp

                    @include('shared.quizzes.intro-panel', [
                        'title' => $questionModule->title,
                        'description' => $questionModule->description,
                        'instructions' => $questionModule->instructions,
                        'heroVariant' => 'question_module',
                        'heroIcon' => 'fe-clipboard',
                        'stats' => $qmStats,
                        'showHistory' => true,
                        'completedAttempts' => $completedAttempts,
                        'attemptsAllowed' => $questionModule->attempts_allowed,
                        'lastScore' => ($lastAttempt && $lastAttempt->status === 'completed') ? number_format($lastAttempt->percentage, 1) . '%' : null,
                        'lastPassed' => $lastAttempt ? $lastAttempt->is_passed : false,
                        'reviewUrl' => ($lastAttempt && $lastAttempt->status === 'completed') ? route('student.question-module.result', $lastAttempt->id) : null,
                        'inProgressAttempt' => $inProgressAttempt,
                        'continueUrl' => $inProgressAttempt ? route('student.question-module.take', $inProgressAttempt->id) : null,
                        'canAttempt' => $canAttempt,
                        'startUrl' => route('student.question-module.start', $questionModule->id) . '?module_id=' . $module->id,
                        'remainingAttempts' => $questionModule->attempts_allowed - $completedAttempts,
                        'blockedLabel' => 'استنفدت جميع المحاولات',
                        'blockedHint' => 'لقد استخدمت جميع المحاولات المسموحة (' . $questionModule->attempts_allowed . ')',
                        'tips' => [
                            ['icon' => 'fe-wifi', 'text' => 'اتصال إنترنت مستقر'],
                            ['icon' => 'fe-clock', 'text' => 'الوقت يبدأ فور البدء'],
                        ],
                    ])
                @endif

                <!-- Programming Challenge Module -->
                @if($module->module_type == 'programming_challenge' && $module->modulable)
                    @php
                        $progChallenge = $module->modulable;
                        $studentId = auth()->id();
                        $pcAttempts = $progChallenge->studentAttempts($studentId)->get();
                        $pcCompleted = $pcAttempts->whereIn('status', ['submitted', 'graded'])->count();
                        $pcInProgress = $pcAttempts->where('status', 'in_progress')->where('course_module_id', $module->id)->first();
                        $pcCanAttempt = $progChallenge->canStudentAttempt($studentId);
                        $pcLastAttempt = $pcAttempts->first();
                        $pcStats = [
                            ['icon' => 'fe-star', 'label' => 'الدرجة القصوى', 'value' => $progChallenge->max_score, 'color' => 'gold'],
                            ['icon' => 'fe-code', 'label' => 'النوع', 'value' => $progChallenge->challenge_type === 'web_sandbox' ? 'ويب' : 'كود', 'color' => 'blue'],
                            ['icon' => 'fe-refresh-cw', 'label' => 'المحاولات', 'value' => $progChallenge->attempts_allowed, 'color' => 'cyan'],
                        ];
                    @endphp

                    @include('shared.quizzes.intro-panel', [
                        'title' => $progChallenge->title,
                        'description' => $progChallenge->description,
                        'instructions' => $progChallenge->instructions,
                        'heroVariant' => 'question_module',
                        'heroIcon' => 'fe-code',
                        'stats' => $pcStats,
                        'showHistory' => true,
                        'completedAttempts' => $pcCompleted,
                        'attemptsAllowed' => $progChallenge->attempts_allowed,
                        'lastScore' => ($pcLastAttempt && $pcLastAttempt->isGraded()) ? $pcLastAttempt->score . '/' . $pcLastAttempt->max_score : null,
                        'lastPassed' => false,
                        'reviewUrl' => null,
                        'inProgressAttempt' => $pcInProgress,
                        'continueUrl' => $pcInProgress ? route('student.challenges.work', $progChallenge->id) . '?module_id=' . $module->id : null,
                        'canAttempt' => $pcCanAttempt || $pcInProgress,
                        'startUrl' => route('student.challenges.start', $progChallenge->id) . '?module_id=' . $module->id,
                        'remainingAttempts' => $progChallenge->attempts_allowed - $pcCompleted,
                        'blockedLabel' => 'استنفدت المحاولات',
                        'blockedHint' => 'لقد استخدمت جميع المحاولات المسموحة',
                        'tips' => [
                            ['icon' => 'fe-monitor', 'text' => 'محرر كود تفاعلي'],
                            ['icon' => 'fe-save', 'text' => 'حفظ تلقائي'],
                        ],
                    ])
                @endif

                <!-- Quiz Module -->
                @if($module->module_type == 'quiz' && $module->modulable)
                    @php
                        $quiz = $module->modulable;
                        $studentId = auth()->id();
                        $studentAttempts = $quiz->attempts()->where('student_id', $studentId)->orderBy('attempt_number', 'desc')->get();
                        $completedAttempts = $studentAttempts->where('status', 'completed')->count();
                        $inProgressAttempt = $studentAttempts->where('status', 'in_progress')->first();
                        $canAttempt = $quiz->canAttempt($studentId);
                        $remainingAttempts = $quiz->getRemainingAttempts($studentId);
                        $lastAttempt = $studentAttempts->where('status', 'completed')->first();
                    @endphp

                    @php
                        $quizStats = [
                            ['icon' => 'fe-help-circle', 'label' => 'عدد الأسئلة', 'value' => $quiz->quizQuestions->count(), 'color' => 'blue'],
                            ['icon' => 'fe-star', 'label' => 'إجمالي الدرجات', 'value' => number_format($quiz->max_score ?? $quiz->calculateMaxScore() ?? $quiz->quizQuestions->sum('max_score'), 2), 'color' => 'gold'],
                        ];
                        if ($quiz->time_limit) {
                            $quizStats[] = ['icon' => 'fe-clock', 'label' => 'الوقت المحدد', 'value' => $quiz->time_limit . ' <small>دقيقة</small>', 'color' => 'red'];
                        }
                        if ($quiz->attempts_allowed) {
                            $quizStats[] = ['icon' => 'fe-refresh-cw', 'label' => 'المحاولات المسموحة', 'value' => $quiz->attempts_allowed, 'color' => 'cyan'];
                        }
                        if ($quiz->passing_grade) {
                            $quizStats[] = ['icon' => 'fe-award', 'label' => 'درجة النجاح', 'value' => $quiz->passing_grade . '%', 'color' => 'green'];
                        }
                    @endphp

                    @include('shared.quizzes.intro-panel', [
                        'title' => $quiz->title,
                        'description' => $quiz->description,
                        'instructions' => $quiz->instructions,
                        'heroVariant' => 'quiz',
                        'stats' => $quizStats,
                        'chips' => collect([
                            $quiz->quiz_type === 'practice' ? ['icon' => 'fe-book-open', 'label' => 'تدريبي'] : null,
                            $quiz->quiz_type === 'graded' ? ['icon' => 'fe-award', 'label' => 'مُقيّم'] : null,
                            $quiz->quiz_type === 'final_exam' ? ['icon' => 'fe-flag', 'label' => 'اختبار نهائي'] : null,
                        ])->filter()->values()->all(),
                        'showHistory' => true,
                        'completedAttempts' => $completedAttempts,
                        'attemptsAllowed' => $quiz->attempts_allowed,
                        'lastScore' => $lastAttempt ? number_format($lastAttempt->percentage_score ?? 0, 1) . '%' : null,
                        'lastPassed' => $lastAttempt ? ($lastAttempt->percentage_score ?? 0) >= ($quiz->passing_grade ?? 50) : false,
                        'reviewUrl' => ($lastAttempt && $lastAttempt->status === 'completed') ? route('student.quizzes.review.show', $lastAttempt->id) : null,
                        'inProgressAttempt' => $inProgressAttempt,
                        'continueUrl' => $inProgressAttempt ? route('student.quizzes.take', $inProgressAttempt->id) : null,
                        'canAttempt' => $canAttempt,
                        'startFormAction' => route('student.quizzes.start', $quiz->id),
                        'startFormHidden' => ['module_id' => $module->id],
                        'remainingAttempts' => $remainingAttempts,
                        'blockedLabel' => 'استنفدت جميع المحاولات',
                        'blockedHint' => $quiz->attempts_allowed
                            ? 'لقد استخدمت جميع المحاولات المسموحة (' . $quiz->attempts_allowed . ')'
                            : 'الاختبار غير متاح حالياً',
                        'tips' => [
                            ['icon' => 'fe-wifi', 'text' => 'اتصال إنترنت مستقر'],
                            ['icon' => 'fe-clock', 'text' => 'الوقت يبدأ فور البدء'],
                            ['icon' => 'fe-check-circle', 'text' => 'راجع إجاباتك قبل الإرسال'],
                        ],
                    ])
                @endif
