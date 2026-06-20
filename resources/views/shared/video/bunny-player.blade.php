@php
    /** @var \App\Models\Video $video */
    $nativeUrl = $video->getBunnyNativePlaybackUrl();
    // Never use stored Bunny iframe embed — it loads RUM metrics.
    $iframeSrc = $nativeUrl ? null : $video->getBunnyIframeSrc();
    $frameStyle = $frameStyle ?? 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;';
@endphp

@if($nativeUrl)
    <video
        controls
        controlsList="nodownload"
        playsinline
        preload="metadata"
        style="{{ $frameStyle }} background: #000;">
        <source src="{{ $nativeUrl }}" type="video/mp4">
    </video>
@elseif($iframeSrc)
    <iframe
        src="{{ $iframeSrc }}"
        style="{{ $frameStyle }}"
        frameborder="0"
        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
        allowfullscreen
        loading="lazy">
    </iframe>
@endif
