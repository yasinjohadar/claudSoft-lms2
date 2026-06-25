@if($includeCss ?? true)
    <link rel="stylesheet" href="{{ asset('assets/css/challenge-ide.css') }}?v={{ @filemtime(public_path('assets/css/challenge-ide.css')) ?: '1' }}">
@endif

@if($includeJs ?? true)
    <script src="{{ asset('assets/js/challenge-ide.js') }}?v={{ @filemtime(public_path('assets/js/challenge-ide.js')) ?: '1' }}"></script>
@endif