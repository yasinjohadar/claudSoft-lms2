@php
    $languages = $languages ?? collect();
@endphp

@if($languages->isNotEmpty())
    <div class="d-flex flex-wrap gap-1 {{ $wrapperClass ?? '' }}">
        @foreach($languages as $language)
            <span class="qb-lang-chip" style="background-color: {{ $language->color ?? '#6c757d' }};" title="{{ $language->display_name ?? $language->name }}">
                @include('admin.pages.question-bank.partials.programming-language-icon', ['language' => $language])
                <span>{{ $language->display_name ?? $language->name }}</span>
            </span>
        @endforeach
    </div>
@elseif(!empty($emptyText))
    <span class="text-muted">{{ $emptyText }}</span>
@endif
