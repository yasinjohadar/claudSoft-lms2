@php
    /** @var \App\Models\DocumentationPage $docPage */
    $btnClass = $btnClass ?? 'btn btn-outline-danger';
    $btnSizeClass = $btnSizeClass ?? '';
    $showIconOnly = $showIconOnly ?? false;
@endphp
@if($docPage->isPublished())
    <a href="{{ $docPage->pdfUrl() }}"
       class="{{ trim($btnClass.' '.$btnSizeClass) }}"
       target="_blank"
       rel="noopener"
       title="تصدير هذه الصفحة كـ PDF">
        <i class="fe fe-download{{ $showIconOnly ? '' : ' me-2' }}"></i>
        @unless($showIconOnly)
            تصدير PDF
        @endunless
    </a>
@endif
