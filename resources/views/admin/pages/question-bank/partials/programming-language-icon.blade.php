@php
    $slug = $language->slug ?? '';
    $icon = $language->icon ?? '';
@endphp

@if($slug === 'dart' || $icon === 'qb-lang-icon--dart')
    <span class="qb-lang-chip__svg" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" role="img" focusable="false">
            <path d="M4.105 4.105h7.253v7.253L4.105 4.105zm2.303 2.303v2.647l2.647 2.647h2.647L6.408 6.408zm11.184 0 2.303 2.303-9.184 9.184h-2.303v-2.303l9.184-9.184z"/>
        </svg>
    </span>
@else
    <i class="{{ $icon ?: 'fe fe-code' }}" aria-hidden="true"></i>
@endif
