@php
    $video = $module->content; // Assuming polymorphic relationship
@endphp

<!-- Video Player -->
<div class="video-container" id="videoContainer">
    @if($video)
        @if($video->video_type == 'upload')
            <!-- Uploaded Video -->
            <video id="courseVideo" controls controlsList="nodownload" oncontextmenu="return false;">
                <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>

        @elseif($video->video_type == 'youtube')
            <!-- YouTube Video -->
            <iframe id="youtubePlayer"
                    src="https://www.youtube.com/embed/{{ $video->youtube_id }}?enablejsapi=1&rel=0"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>

        @elseif($video->video_type == 'vimeo')
            <!-- Vimeo Video -->
            <iframe src="https://player.vimeo.com/video/{{ $video->vimeo_id }}?title=0&byline=0&portrait=0"
                    frameborder="0"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen>
            </iframe>

        @elseif($video->video_type == 'url')
            <!-- External URL -->
            <video id="courseVideo" controls controlsList="nodownload" oncontextmenu="return false;">
                <source src="{{ $video->video_url }}" type="video/mp4">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>
        @endif

        <!-- Video Progress Indicator -->
        <div class="module-progress-indicator">
            <i class="fas fa-clock text-primary me-1"></i>
            <span id="videoProgress">0%</span> مكتمل
        </div>
    @else
        <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-white">
            <div class="text-center">
                <i class="fas fa-video-slash fa-3x mb-3 opacity-50"></i>
                <p>الفيديو غير متوفر حالياً</p>
            </div>
        </div>
    @endif
</div>

<!-- Video Information -->
@if($video)
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                <div class="fw-bold">{{ $video->duration ?? 'غير محدد' }}</div>
                <small class="text-muted">المدة</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-eye text-success fa-2x mb-2"></i>
                <div class="fw-bold">{{ $video->views_count ?? 0 }}</div>
                <small class="text-muted">المشاهدات</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-video text-danger fa-2x mb-2"></i>
                <div class="fw-bold">{{ $video->quality ?? 'HD' }}</div>
                <small class="text-muted">الجودة</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-3 bg-light rounded">
                <i class="fas fa-language text-info fa-2x mb-2"></i>
                <div class="fw-bold">{{ $video->language == 'ar' ? 'عربي' : 'إنجليزي' }}</div>
                <small class="text-muted">اللغة</small>
            </div>
        </div>
    </div>

    <!-- Video Description -->
    @if($video->description)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>حول هذا الفيديو</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $video->description }}</p>
            </div>
        </div>
    @endif

    <!-- Video Transcript (if available) -->
    @if($video->transcript)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-closed-captioning me-2"></i>النص المكتوب</h6>
                <button class="btn btn-sm btn-outline-primary" onclick="toggleTranscript()">
                    <i class="fas fa-chevron-down" id="transcriptToggle"></i>
                </button>
            </div>
            <div class="card-body d-none" id="transcriptContent">
                <div style="white-space: pre-wrap; line-height: 1.8;">{{ $video->transcript }}</div>
            </div>
        </div>
    @endif

    <!-- Video Resources/Attachments -->
    @if($video->attachments && count($video->attachments) > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>المرفقات</h6>
            </div>
            <div class="card-body">
                @foreach($video->attachments as $attachment)
                    <div class="resource-item">
                        <div class="d-flex align-items-center">
                            <div class="resource-icon bg-primary-transparent text-primary">
                                <i class="fas fa-file-{{ $attachment['type'] ?? 'pdf' }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $attachment['name'] }}</div>
                                <small class="text-muted">{{ $attachment['size'] ?? '' }}</small>
                            </div>
                        </div>
                        <a href="{{ route('student.videos.download-attachment', ['videoId' => $video->id, 'attachmentId' => $attachment['id']]) }}"
                           class="btn btn-sm btn-primary"
                           download>
                            <i class="fas fa-download me-1"></i>تحميل
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif

@push('scripts')
<script>
    // Video Progress Tracking
    let videoProgressInterval;
    let totalWatched = 0;

    @if($video && $video->video_type == 'upload')
        const video = document.getElementById('courseVideo');
        if (video) {
            // Track video progress every 10 seconds
            video.addEventListener('play', function() {
                videoProgressInterval = setInterval(() => {
                    updateVideoProgress();
                }, 10000); // Every 10 seconds
            });

            video.addEventListener('pause', function() {
                clearInterval(videoProgressInterval);
                updateVideoProgress(); // Save on pause
            });

            video.addEventListener('ended', function() {
                clearInterval(videoProgressInterval);
                updateVideoProgress();
                // Auto-mark as complete if 90% watched
                if (totalWatched / video.duration >= 0.9) {
                    document.getElementById('markCompleteForm')?.submit();
                }
            });

            video.addEventListener('timeupdate', function() {
                const progress = (video.currentTime / video.duration) * 100;
                document.getElementById('videoProgress').textContent = Math.round(progress) + '%';
            });
        }

        function updateVideoProgress() {
            if (!video) return;

            totalWatched = video.currentTime;
            const totalDuration = video.duration;

            // Send progress to server
            fetch('{{ route('student.modules.track-video-progress', $module->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    watched_seconds: Math.floor(totalWatched),
                    total_seconds: Math.floor(totalDuration)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.is_completed) {
                    console.log('Video auto-completed at 90%');
                }
            })
            .catch(error => console.error('Error tracking video progress:', error));
        }
    @endif

    // Toggle Transcript
    function toggleTranscript() {
        const content = document.getElementById('transcriptContent');
        const toggle = document.getElementById('transcriptToggle');

        content.classList.toggle('d-none');

        if (content.classList.contains('d-none')) {
            toggle.classList.remove('fa-chevron-up');
            toggle.classList.add('fa-chevron-down');
        } else {
            toggle.classList.remove('fa-chevron-down');
            toggle.classList.add('fa-chevron-up');
        }
    }
</script>
@endpush
