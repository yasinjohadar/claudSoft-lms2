@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-analytics.css') }}?v={{ @filemtime(public_path('assets/css/quiz-analytics.css')) ?: '1' }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/quiz-analytics-ui.js') }}?v={{ @filemtime(public_path('assets/js/quiz-analytics-ui.js')) ?: '1' }}" defer></script>
@endpush
