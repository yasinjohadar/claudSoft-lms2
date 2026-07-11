{{-- Shared ordering item row: up/down buttons (no drag) --}}
@php
    /** @var object $item */
    /** @var int $itemIndex */
    /** @var int $itemsCount */
    /** @var int|string $questionId */
@endphp
<div class="ordering-item"
     data-item-id="{{ $item->id }}"
     data-question-id="{{ $questionId }}">
    <div class="d-flex align-items-center gap-2 w-100">
        <span class="ordering-number">{{ $itemIndex + 1 }}</span>
        <span class="ordering-text flex-grow-1">{!! mixed_bidi_html($item->option_text) !!}</span>
        <div class="ordering-controls btn-group" role="group" aria-label="تحريك العنصر">
            <button type="button"
                    class="btn btn-sm btn-outline-primary ordering-move-up"
                    data-question-id="{{ $questionId }}"
                    aria-label="تحريك لأعلى"
                    {{ $itemIndex === 0 ? 'disabled' : '' }}>
                <i class="fas fa-arrow-up" aria-hidden="true"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-primary ordering-move-down"
                    data-question-id="{{ $questionId }}"
                    aria-label="تحريك لأسفل"
                    {{ $itemIndex >= $itemsCount - 1 ? 'disabled' : '' }}>
                <i class="fas fa-arrow-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
