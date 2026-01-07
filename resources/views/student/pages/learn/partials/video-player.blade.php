@php
    $video = $module->modulable;
    
    // Check Bunny URL
    $isBunnyUrl = false;
    $videoUrl = '';
    
    if ($video) {
        $videoUrl = $video->video_url ?? '';
        $isBunnyUrl = !empty($videoUrl) && (
            str_contains($videoUrl, 'mediadelivery.net') ||
            str_contains($videoUrl, 'bunny.net') ||
            str_contains($videoUrl, 'b-cdn.net') ||
            str_contains($videoUrl, 'iframe.mediadelivery')
        );
    }
@endphp

<!-- Video Player Container - FULL WIDTH -->
<div style="width: 100%; max-width: 100%; margin-bottom: 1.5rem;">
    <div style="position: relative; width: 100%; padding-top: 56.25%; background: #000; border-radius: 12px; overflow: hidden;">
        @if($video)
            @if($isBunnyUrl)
                {{-- Bunny.net Video - Full Width --}}
                <iframe 
                    id="bunny-video-iframe-{{ $module->id ?? 'default' }}"
                    class="bunny-video-iframe"
                    src="{{ $videoUrl }}"
                    style="position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; max-width: none !important; max-height: none !important; border: 0 !important; margin: 0 !important; padding: 0 !important; transform: scale(1) !important;"
                    frameborder="0"
                    allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            @elseif($video->video_type == 'youtube')
                {{-- YouTube Video --}}
                <iframe 
                    src="https://www.youtube.com/embed/{{ $video->youtube_id }}?rel=0"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            @elseif($video->video_type == 'vimeo')
                {{-- Vimeo Video --}}
                <iframe 
                    src="https://player.vimeo.com/video/{{ $video->vimeo_id }}"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                    frameborder="0"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen>
                </iframe>
            @elseif($video->video_type == 'upload' && $video->video_path)
                {{-- Uploaded Video --}}
                <video 
                    id="courseVideo" 
                    controls 
                    controlsList="nodownload"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                </video>
            @elseif(!empty($videoUrl))
                {{-- External URL --}}
                <video 
                    id="courseVideo" 
                    controls 
                    controlsList="nodownload"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    <source src="{{ $videoUrl }}" type="video/mp4">
                </video>
            @else
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white;">
                    <div class="text-center">
                        <i class="fas fa-video-slash fa-3x mb-3"></i>
                        <p>الفيديو غير متوفر</p>
                    </div>
                </div>
            @endif
        @else
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white;">
                <div class="text-center">
                    <i class="fas fa-video-slash fa-3x mb-3"></i>
                    <p>الفيديو غير متوفر</p>
                </div>
            </div>
        @endif
    </div>
</div>

@if($video)
<!-- Video Info -->
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
            <div class="fw-bold">{{ ($video->language ?? 'ar') == 'ar' ? 'عربي' : 'إنجليزي' }}</div>
            <small class="text-muted">اللغة</small>
        </div>
    </div>
</div>

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
@endif

@push('styles')
<style>
    .bunny-video-iframe {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        min-width: 100% !important;
        min-height: 100% !important;
        max-width: none !important;
        max-height: none !important;
        border: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: scale(1) !important;
        box-sizing: border-box !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        function resizeBunnyIframes() {
            $('.bunny-video-iframe').each(function() {
                const $iframe = $(this);
                
                // Force iframe size
                $iframe.css({
                    'position': 'absolute',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'min-width': '100%',
                    'min-height': '100%',
                    'max-width': 'none',
                    'max-height': 'none',
                    'border': '0',
                    'margin': '0',
                    'padding': '0',
                    'transform': 'scale(1)',
                    'box-sizing': 'border-box'
                });
                
                // Try to access iframe content (may fail due to CORS)
                try {
                    const iframeDoc = this.contentDocument || this.contentWindow.document;
                    if (iframeDoc) {
                        const iframeBody = iframeDoc.body;
                        if (iframeBody) {
                            iframeBody.style.margin = '0';
                            iframeBody.style.padding = '0';
                            iframeBody.style.overflow = 'hidden';
                            
                            const video = iframeDoc.querySelector('video');
                            if (video) {
                                video.style.width = '100%';
                                video.style.height = '100%';
                                video.style.objectFit = 'contain';
                            }
                        }
                    }
                } catch (e) {
                    // Cross-origin restriction, ignore
                }
            });
        }
        
        resizeBunnyIframes();
        
        $('.bunny-video-iframe').on('load', function() {
            resizeBunnyIframes();
        });
        
        $(window).on('resize', function() {
            resizeBunnyIframes();
        });
        
        setInterval(resizeBunnyIframes, 1000);
    });
</script>
@endpush
