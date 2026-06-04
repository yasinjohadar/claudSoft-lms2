@if($attempt->is_completed && $attempt->percentage_score !== null)
    @php
        $pct = max(0, min(100, (float) $attempt->percentage_score));
        $passed = (bool) $attempt->passed;
    @endphp
    <div class="student-quizzes-result">
        <span class="student-quizzes-result__pct {{ $passed ? 'text-success' : 'text-danger' }}">
            {{ number_format($pct, 1) }}%
        </span>
        <div class="student-quizzes-result__track">
            <div class="student-quizzes-result__bar {{ $passed ? 'is-passed' : 'is-failed' }}"
                 style="width: {{ $pct }}%"
                 role="progressbar"
                 aria-valuenow="{{ $pct }}"
                 aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>
        <small class="student-quizzes-result__score">
            {{ number_format($attempt->total_score ?? 0, 1) }} / {{ number_format($attempt->max_score ?? 0, 1) }}
        </small>
    </div>
@else
    <span class="text-muted fs-13">غير مكتمل</span>
@endif
