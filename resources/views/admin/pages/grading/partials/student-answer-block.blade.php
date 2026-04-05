@php
    /** @var \App\Models\QuizResponse $response */
    $presenter = app(\App\Support\QuizGradingAnswerPresenter::class);
    $studentPlain = $presenter->studentAnswerPlain($response);
@endphp
<div class="p-3 bg-light rounded">
    @if($studentPlain === \App\Support\QuizGradingAnswerPresenter::NO_ANSWER)
        <span class="text-muted">{{ $studentPlain }}</span>
    @else
        <div class="mb-0">{!! nl2br(e($studentPlain)) !!}</div>
    @endif
</div>
