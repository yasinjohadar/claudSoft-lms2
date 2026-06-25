@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-review.css') }}?v={{ @filemtime(public_path('assets/css/quiz-review.css')) ?: '1' }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/quiz-review-ui.js') }}?v={{ @filemtime(public_path('assets/js/quiz-review-ui.js')) ?: '1' }}" defer></script>
@endpush
