{{-- Shared ordering item row: up/down buttons (no drag) --}}
@php
    /** @var object $item */
    /** @var int $itemIndex */
    /** @var int $itemsCount */
    /** @var int|string $questionId */

    $isFirst = $itemIndex === 0;
    $isLast = $itemIndex >= $itemsCount - 1;

    // حدّ القائمة يُعلَّم بـ aria-disabled لا disabled: الزر المعطّل فعلياً لا يُطلق
    // حدث نقر، فلا يمكن شرح سبب عدم الحركة للطالب (انظر quiz-ordering.js).
    $upLabel = $isFirst ? 'تحريك لأعلى (العنصر في أعلى القائمة)' : 'تحريك لأعلى إلى الموضع ' . $itemIndex;
    $downLabel = $isLast ? 'تحريك لأسفل (العنصر في أسفل القائمة)' : 'تحريك لأسفل إلى الموضع ' . ($itemIndex + 2);
@endphp
<div class="ordering-item"
     data-item-id="{{ $item->id }}"
     data-question-id="{{ $questionId }}"
     data-position="{{ $itemIndex + 1 }}">
    <div class="d-flex align-items-center gap-2 w-100">
        <span class="ordering-number">{{ $itemIndex + 1 }}</span>
        <span class="ordering-text flex-grow-1">{!! mixed_bidi_html($item->option_text) !!}</span>
        <div class="ordering-controls btn-group" role="group" aria-label="تحريك العنصر">
            <button type="button"
                    class="btn btn-sm btn-outline-primary ordering-move-up"
                    data-question-id="{{ $questionId }}"
                    data-direction="up"
                    aria-disabled="{{ $isFirst ? 'true' : 'false' }}"
                    aria-label="{{ $upLabel }}"
                    title="{{ $upLabel }}">
                <i class="fas fa-arrow-up" aria-hidden="true"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-primary ordering-move-down"
                    data-question-id="{{ $questionId }}"
                    data-direction="down"
                    aria-disabled="{{ $isLast ? 'true' : 'false' }}"
                    aria-label="{{ $downLabel }}"
                    title="{{ $downLabel }}">
                <i class="fas fa-arrow-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>
