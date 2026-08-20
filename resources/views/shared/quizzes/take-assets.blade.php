@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-take.css') }}?v={{ @filemtime(public_path('assets/css/quiz-take.css')) ?: '1' }}">
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-ordering.css') }}?v={{ @filemtime(public_path('assets/css/quiz-ordering.css')) ?: '1' }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/quiz-take-timer.js') }}?v={{ @filemtime(public_path('assets/js/quiz-take-timer.js')) ?: '1' }}"></script>
    <script src="{{ asset('assets/js/quiz-take-ui.js') }}?v={{ @filemtime(public_path('assets/js/quiz-take-ui.js')) ?: '1' }}" defer></script>
    <script src="{{ asset('assets/js/quiz-ordering.js') }}?v={{ @filemtime(public_path('assets/js/quiz-ordering.js')) ?: '1' }}"></script>
@endpush
