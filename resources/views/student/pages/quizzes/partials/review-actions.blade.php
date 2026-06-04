@if($attempt->is_completed)
    <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" class="btn btn-sm btn-primary rounded-pill">
        <i class="fe fe-eye me-1"></i>مراجعة
    </a>
@else
    <a href="{{ route('student.quizzes.take', $attempt->id) }}" class="btn btn-sm btn-warning rounded-pill">
        <i class="fe fe-play me-1"></i>متابعة
    </a>
@endif
