<!-- Favicon -->
<link rel="icon" href="{{ asset('assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

<!-- Preload Critical CSS for faster loading -->
<link rel="preload" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/libs/bootstrap/css/bootstrap.rtl.min.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/css/styles.min.css') }}" as="style">

<!-- Bootstrap Css -->
<link id="style" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link id="style-rtl" href="{{ asset('assets/libs/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">

<!-- Style Css (already preloaded above) -->
<link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet">

<!-- Icons Css -->
<link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">

<!-- Node Waves Css -->
<link href="{{ asset('assets/libs/node-waves/waves.min.css') }}" rel="stylesheet">

<!-- Simplebar Css -->
<link href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">

<!-- Color Picker Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/@simonwep/pickr/themes/nano.min.css') }}">

<!-- Choices Css -->
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

<!-- Jsvector Maps -->
<link rel="stylesheet" href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}">

<!-- Custom Css -->
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

@yield('styles')

<!-- Toastr CSS (after theme styles to prevent override) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS (defer for non-critical) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>

<!-- Inline script to show page after CSS loads -->
<script>
    // Hide page until CSS is loaded (handled by inline CSS in master.blade.php)
    // This script ensures page shows even if CSS loads slowly
    (function() {
        // Check if CSS is loaded by testing a computed style
        function checkCSSLoaded() {
            const testEl = document.createElement('div');
            testEl.className = 'd-none';
            document.body.appendChild(testEl);
            const isLoaded = window.getComputedStyle(testEl).display === 'none';
            document.body.removeChild(testEl);
            return isLoaded;
        }
        
        // Try to show page after a short delay
        setTimeout(function() {
            if (checkCSSLoaded() || document.readyState === 'complete') {
                document.documentElement.classList.add('loaded');
            }
        }, 200);
        
        // Fallback: show page after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.documentElement.classList.add('loaded');
            }, 100);
        });
    })();
</script>
