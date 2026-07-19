@php
    /** @var \App\Models\QuestionBank $question */
    /** @var int $blankIndex */
    /** @var int $blankCount */
    /** @var array $savedAnswers */
    $blankChoices = $question->fillBlankSelectChoices($blankIndex, $blankCount);
    $selectedValue = isset($savedAnswers[$blankIndex]) ? (string) $savedAnswers[$blankIndex] : '';
@endphp
<select class="form-select d-inline-block fill-blank-input"
        style="width: auto; min-width: 9rem; max-width: 100%; display: inline-block !important; vertical-align: middle;"
        name="question_{{ $question->id }}[{{ $blankIndex }}]"
        data-question-id="{{ $question->id }}"
        data-blank-index="{{ $blankIndex }}"
        aria-label="اختر إجابة الفراغ {{ $blankIndex + 1 }}"
        @disabled($blankChoices->isEmpty())>
    <option value="">-- اختر --</option>
    @forelse($blankChoices as $choice)
        <option value="{{ $choice }}" @selected($selectedValue !== '' && $selectedValue === $choice)>{{ $choice }}</option>
    @empty
        <option value="" disabled>لا توجد خيارات</option>
    @endforelse
</select>
