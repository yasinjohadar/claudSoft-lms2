@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/platform-review.css') }}?v={{ @filemtime(public_path('assets/css/platform-review.css')) ?: '1' }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/platform-review-ui.js') }}?v={{ @filemtime(public_path('assets/js/platform-review-ui.js')) ?: '1' }}" defer></script>
@endpush
