<!-- Favicon -->
<link rel="icon" href="{{ asset('assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

<!-- Preload Critical CSS for faster loading -->
<link rel="preload" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/libs/bootstrap/css/bootstrap.rtl.min.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/css/styles.min.css') }}" as="style">
<link rel="preload" href="{{ asset('assets/css/custom.css') }}?v={{ @filemtime(public_path('assets/css/custom.css')) ?: '1' }}" as="style">

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
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ @filemtime(public_path('assets/css/custom.css')) ?: '1' }}">

<!-- هيدر البوابات: شريط البحث وأيقونات الأدوات وزرّ طيّ القائمة -->
<link rel="stylesheet" href="{{ asset('assets/css/portal-header.css') }}?v={{ @filemtime(public_path('assets/css/portal-header.css')) ?: '1' }}">

@yield('styles')
@yield('css')
@stack('styles')
@stack('head-scripts')

<!-- Toastr CSS (after theme styles to prevent override) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS (defer for non-critical) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>

<!-- Wait for all stylesheets before revealing the page (prevents FOUC) -->
<script>
    (function () {
        var revealed = false;

        function reveal() {
            if (revealed) {
                return;
            }
            revealed = true;
            document.documentElement.classList.add('loaded');
        }

        var links = document.querySelectorAll('link[rel="stylesheet"]');
        if (!links.length) {
            reveal();
            return;
        }

        var pending = 0;

        links.forEach(function (link) {
            if (link.sheet) {
                return;
            }

            pending++;
            link.addEventListener('load', function () {
                if (--pending <= 0) {
                    reveal();
                }
            });
            link.addEventListener('error', function () {
                if (--pending <= 0) {
                    reveal();
                }
            });
        });

        if (pending === 0) {
            requestAnimationFrame(function () {
                requestAnimationFrame(reveal);
            });
            return;
        }

        window.addEventListener('load', reveal);
        setTimeout(reveal, 5000);
    })();
</script>
