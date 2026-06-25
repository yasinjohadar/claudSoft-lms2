<div class="quiz-review-filters" role="tablist" aria-label="تصفية الأسئلة">
    <button type="button" class="quiz-review-filter is-active" data-filter="all" role="tab" aria-selected="true">
        <i class="fe fe-layers"></i>
        <span>الكل</span>
        <span class="quiz-review-filter__count" data-count-all>{{ $stats['total_questions'] ?? 0 }}</span>
    </button>
    <button type="button" class="quiz-review-filter quiz-review-filter--success" data-filter="correct" role="tab">
        <i class="fe fe-check-circle"></i>
        <span>صحيح</span>
        <span class="quiz-review-filter__count">{{ $stats['correct'] ?? 0 }}</span>
    </button>
    <button type="button" class="quiz-review-filter quiz-review-filter--danger" data-filter="wrong" role="tab">
        <i class="fe fe-x-circle"></i>
        <span>خطأ</span>
        <span class="quiz-review-filter__count">{{ $stats['incorrect'] ?? 0 }}</span>
    </button>
    <button type="button" class="quiz-review-filter quiz-review-filter--warning" data-filter="pending" role="tab">
        <i class="fe fe-clock"></i>
        <span>قيد التصحيح</span>
        <span class="quiz-review-filter__count">{{ $stats['ungraded'] ?? 0 }}</span>
    </button>
</div>
