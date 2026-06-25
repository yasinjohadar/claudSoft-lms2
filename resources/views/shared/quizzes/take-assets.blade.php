@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-take.css') }}?v={{ @filemtime(public_path('assets/css/quiz-take.css')) ?: '1' }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/quiz-take-ui.js') }}?v={{ @filemtime(public_path('assets/js/quiz-take-ui.js')) ?: '1' }}" defer></script>
@endpush
