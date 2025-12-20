@php
    $lesson = $module->content; // Assuming polymorphic relationship
@endphp

@if($lesson)
    <!-- Lesson Content -->
    <div class="lesson-content">
        <!-- Lesson Main Content -->
        <div class="mb-5">
            {!! $lesson->content !!}
        </div>

        <!-- Lesson Objectives (if available) -->
        @if($lesson->objectives && count($lesson->objectives) > 0)
            <div class="card bg-primary-transparent border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bullseye me-2"></i>أهداف الدرس
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @foreach($lesson->objectives as $objective)
                            <li class="mb-2">{{ $objective }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Key Points (if available) -->
        @if($lesson->key_points && count($lesson->key_points) > 0)
            <div class="card bg-warning-transparent border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-star me-2"></i>النقاط الرئيسية
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($lesson->key_points as $point)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 30px; height: 30px;">
                                    <i class="fas fa-check text-dark"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                {{ $point }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Code Examples (if available) -->
        @if($lesson->code_examples && count($lesson->code_examples) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-code me-2"></i>أمثلة برمجية
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($lesson->code_examples as $example)
                        <div class="mb-4">
                            @if(isset($example['title']))
                                <h6 class="mb-2">{{ $example['title'] }}</h6>
                            @endif
                            @if(isset($example['description']))
                                <p class="text-muted mb-2">{{ $example['description'] }}</p>
                            @endif
                            <pre class="bg-dark text-white p-3 rounded" style="overflow-x: auto;"><code>{{ $example['code'] }}</code></pre>
                            @if(isset($example['output']))
                                <div class="alert alert-secondary mt-2">
                                    <strong>النتيجة:</strong>
                                    <pre class="mb-0 mt-2">{{ $example['output'] }}</pre>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Additional Resources -->
        @if($lesson->additional_resources && count($lesson->additional_resources) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-link me-2"></i>موارد إضافية
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($lesson->additional_resources as $resource)
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-external-link-alt text-primary me-3"></i>
                            <div>
                                <a href="{{ $resource['url'] }}" target="_blank" class="fw-semibold text-decoration-none">
                                    {{ $resource['title'] }}
                                </a>
                                @if(isset($resource['description']))
                                    <br><small class="text-muted">{{ $resource['description'] }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Lesson Attachments -->
        @if($lesson->attachments && count($lesson->attachments) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>مرفقات الدرس
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($lesson->attachments as $attachment)
                            <div class="col-md-6">
                                <div class="resource-item">
                                    <div class="d-flex align-items-center">
                                        <div class="resource-icon
                                            {{ str_contains($attachment['type'] ?? '', 'pdf') ? 'bg-danger-transparent text-danger' : '' }}
                                            {{ str_contains($attachment['type'] ?? '', 'doc') ? 'bg-primary-transparent text-primary' : '' }}
                                            {{ str_contains($attachment['type'] ?? '', 'image') ? 'bg-success-transparent text-success' : '' }}
                                            {{ !str_contains($attachment['type'] ?? '', 'pdf') && !str_contains($attachment['type'] ?? '', 'doc') && !str_contains($attachment['type'] ?? '', 'image') ? 'bg-info-transparent text-info' : '' }}">
                                            @if(str_contains($attachment['type'] ?? '', 'pdf'))
                                                <i class="fas fa-file-pdf"></i>
                                            @elseif(str_contains($attachment['type'] ?? '', 'doc'))
                                                <i class="fas fa-file-word"></i>
                                            @elseif(str_contains($attachment['type'] ?? '', 'image'))
                                                <i class="fas fa-file-image"></i>
                                            @else
                                                <i class="fas fa-file"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $attachment['name'] }}</div>
                                            <small class="text-muted">{{ $attachment['size'] ?? '' }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.lessons.download-attachment', ['lessonId' => $lesson->id, 'attachmentId' => $attachment['id']]) }}"
                                       class="btn btn-sm btn-primary"
                                       download>
                                        <i class="fas fa-download me-1"></i>تحميل
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Box -->
        @if($lesson->summary)
            <div class="card bg-success-transparent border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>ملخص الدرس
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $lesson->summary }}</p>
                </div>
            </div>
        @endif

        <!-- Practice Exercise (if available) -->
        @if($lesson->practice_exercise)
            <div class="card mt-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-dumbbell me-2"></i>تمرين عملي
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>التمرين:</strong>
                        <p class="mt-2">{{ $lesson->practice_exercise['question'] ?? '' }}</p>
                    </div>

                    @if(isset($lesson->practice_exercise['hints']))
                        <div class="alert alert-info">
                            <strong><i class="fas fa-lightbulb me-2"></i>تلميحات:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($lesson->practice_exercise['hints'] as $hint)
                                    <li>{{ $hint }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($lesson->practice_exercise['solution']))
                        <button class="btn btn-outline-success" onclick="toggleSolution()">
                            <i class="fas fa-eye me-2"></i>عرض الحل
                        </button>
                        <div id="exerciseSolution" class="mt-3 d-none">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <strong>الحل:</strong>
                                </div>
                                <div class="card-body">
                                    <pre class="mb-0">{{ $lesson->practice_exercise['solution'] }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-book-open fa-4x mb-3 opacity-25"></i>
        <h5>محتوى الدرس غير متوفر</h5>
        <p>يرجى المحاولة لاحقاً أو التواصل مع الدعم الفني</p>
    </div>
@endif

@push('scripts')
<script>
    function toggleSolution() {
        const solution = document.getElementById('exerciseSolution');
        solution.classList.toggle('d-none');
        event.target.innerHTML = solution.classList.contains('d-none')
            ? '<i class="fas fa-eye me-2"></i>عرض الحل'
            : '<i class="fas fa-eye-slash me-2"></i>إخفاء الحل';
    }

    // Auto-scroll to top when lesson loads
    window.scrollTo(0, 0);
</script>
@endpush
