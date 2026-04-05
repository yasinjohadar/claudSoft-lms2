@php
    /** @var \App\Models\QuestionBank|null $question */
    $presenter = app(\App\Support\QuizGradingAnswerPresenter::class);
    $correctPlain = $presenter->correctAnswerPlain($question);
@endphp
<div class="mb-3">
    <h6 class="fw-bold mb-2 text-success">
        <i class="fas fa-check-circle me-1"></i>الإجابة الصحيحة المتوقعة:
    </h6>
    <div class="alert alert-light border mb-0">{!! nl2br(e($correctPlain)) !!}</div>
    @if($question && trim((string) ($question->explanation ?? '')) !== '')
        <h6 class="fw-bold mb-2 mt-3 text-success">
            <i class="fas fa-lightbulb me-1"></i>شرح / ملاحظات إضافية:
        </h6>
        <div class="alert alert-success mb-0">
            {{ $question->explanation }}
        </div>
    @endif
</div>
