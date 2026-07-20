@php
    /** @var \App\Models\Video $video */
    // Embed view token auth: always use signed iframe — never direct MP4.
    $iframeSrc = $video->getBunnyIframeSrc();
    $frameStyle = $frameStyle ?? 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;';
@endphp

@if($iframeSrc)
    <iframe
        src="{{ $iframeSrc }}"
        style="{{ $frameStyle }}"
        frameborder="0"
        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
        allowfullscreen
        loading="lazy">
    </iframe>
@else
    <div class="d-flex align-items-center justify-content-center h-100 text-white" style="{{ $frameStyle }} background: #000;">
        <div class="text-center px-3">
            <i class="fas fa-exclamation-triangle fs-1 mb-3"></i>
            <p class="mb-0">تعذر تجهيز مشغل الفيديو. تحقق من إعدادات Bunny Stream Token.</p>
        </div>
    </div>
@endif
