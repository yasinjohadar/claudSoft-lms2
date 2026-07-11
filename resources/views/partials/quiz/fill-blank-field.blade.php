@php
    /** @var \App\Models\QuestionBank $question */
    /** @var int $blankIndex */
    /** @var int $blankCount */
    /** @var array $savedAnswers */
    $blankChoices = $question->fillBlankSelectChoices($blankIndex, $blankCount);
    $selectedValue = isset($savedAnswers[$blankIndex]) ? (string) $savedAnswers[$blankIndex] : '';
@endphp
@if($blankChoices->isNotEmpty())
    <select class="form-select d-inline-block fill-blank-input"
            style="width: auto; min-width: 9rem; max-width: 100%; display: inline-block !important; vertical-align: middle;"
            name="question_{{ $question->id }}[{{ $blankIndex }}]"
            data-question-id="{{ $question->id }}"
            data-blank-index="{{ $blankIndex }}"
            aria-label="اختر إجابة الفراغ {{ $blankIndex + 1 }}">
        <option value="">-- اختر --</option>
        @foreach($blankChoices as $choice)
            <option value="{{ $choice }}" @selected($selectedValue !== '' && $selectedValue === $choice)>{{ $choice }}</option>
        @endforeach
    </select>
@else
    <input type="text"
           class="form-control d-inline-block fill-blank-input"
           style="width: 150px; display: inline-block !important;"
           name="question_{{ $question->id }}[{{ $blankIndex }}]"
           value="{{ $selectedValue }}"
           data-question-id="{{ $question->id }}"
           data-blank-index="{{ $blankIndex }}"
           placeholder="...">
@endif
