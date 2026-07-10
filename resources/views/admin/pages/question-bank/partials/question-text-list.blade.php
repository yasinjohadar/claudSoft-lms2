@props([
    'text' => '',
    'clamp' => false,
    'maxWidth' => '520px',
    'link' => null,
])

@php
    $previewHtml = mixed_bidi_html($text);
@endphp

@if($link)
    <a href="{{ $link }}" @class([
        'qb-question-preview qb-question-preview--list text-decoration-none',
        'qb-question-preview--clamp' => $clamp,
    ]) style="{{ $maxWidth ? 'max-width: ' . $maxWidth . ';' : '' }}" title="{{ strip_tags((string) $text) }}">
        {!! $previewHtml !!}
    </a>
@else
    <div @class([
        'qb-question-preview qb-question-preview--list',
        'qb-question-preview--clamp' => $clamp,
    ]) style="{{ $maxWidth ? 'max-width: ' . $maxWidth . ';' : '' }}" title="{{ strip_tags((string) $text) }}">
        {!! $previewHtml !!}
    </div>
@endif
