@php
    $isFillBlanksEdit = ($question->questionType->name ?? '') === 'fill_blanks'
        || ($question->questionType->name ?? '') === 'fill_blank';

    $existingDropdownOptions = old('dropdown_options');
    $existingBlankAnswers = old('blank_answers');

    if ($existingDropdownOptions === null) {
        $existingDropdownOptions = $question->options
            ->pluck('option_text')
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->unique()
            ->values()
            ->all();
        if (count($existingDropdownOptions) < 2) {
            $existingDropdownOptions = array_pad($existingDropdownOptions, 2, '');
        }
    }

    if ($existingBlankAnswers === null) {
        $existingBlankAnswers = [];
        $correctByOrder = $question->options
            ->where('is_correct', true)
            ->sortBy(['option_order', 'id'])
            ->groupBy(fn ($o) => (int) $o->option_order);

        $blankCount = max(
            substr_count((string) preg_replace('/_{3,}/', '[[blank]]', (string) $question->question_text), '[[blank]]'),
            0
        );

        for ($i = 0; $i < $blankCount; $i++) {
            $alts = $correctByOrder->get($i + 1, collect());
            $existingBlankAnswers[$i] = $alts->isNotEmpty()
                ? (string) $alts->first()->option_text
                : '';
        }
    }
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4 {{ $isFillBlanksEdit ? '' : 'd-none' }}"
     id="fill-blanks-edit-section"
     data-initial-blank-answers='@json($existingBlankAnswers)'>
    <input type="hidden" name="input_mode" value="dropdown" id="fillBlanksInputMode" @disabled(! $isFillBlanksEdit)>

    <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                <span class="assignments-section-icon"><i class="fe fe-list"></i></span>
                خيارات القائمة المنسدلة
            </h4>
            <p class="fs-12 text-muted mb-0">نفس القائمة تظهر في كل فراغ للطالب.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-transparent" id="editBlankCountChip">0 فراغ</span>
            <button type="button" class="btn btn-sm btn-primary" id="editAddOptionBtn">
                <i class="fe fe-plus me-1"></i>إضافة خيار
            </button>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="alert alert-info mb-3">
            <i class="fe fe-info me-1"></i>
            ضع <code>[[blank]]</code> في نص السؤال أعلاه، ثم حدّد الإجابة الصحيحة لكل فراغ من القائمة المشتركة.
        </div>

        <div id="editOptionsContainer">
            @foreach($existingDropdownOptions as $opt)
                <div class="fb-option-row d-flex gap-2 align-items-center mb-2">
                    <input type="text" name="dropdown_options[]" class="form-control edit-dropdown-option-input"
                           placeholder="نص الخيار..." value="{{ $opt }}" @disabled(! $isFillBlanksEdit)>
                    <button type="button" class="btn btn-outline-danger btn-sm edit-remove-option-btn">
                        <i class="fe fe-trash-2"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        <h6 class="fw-semibold mb-2">الإجابة الصحيحة لكل فراغ</h6>
        <p class="fs-12 text-muted mb-3">اختر من خيارات القائمة أعلاه.</p>
        <div id="editBlankAnswersContainer">
            <p class="text-muted mb-0" id="editBlankAnswersPlaceholder">أضف فراغات في نص السؤال أولاً.</p>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
    .fb-option-row,
    .fb-blank-answer-row {
        background: rgba(var(--primary-rgb), 0.03);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        border-radius: 12px;
        padding: 0.85rem 1rem;
    }
    .fb-blank-answer-row { margin-bottom: 0.75rem; }
</style>
@endpush
@endonce
