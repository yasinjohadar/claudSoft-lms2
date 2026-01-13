<!-- Scroll To Top -->
<div class="scrollToTop">
    <span class="arrow"><i class="las la-angle-double-up"></i></span>
</div>
<div id="responsive-overlay"></div>
<!-- Scroll To Top -->

<!-- Popper JS -->
<script src="{{ asset('assets/libs/@popperjs/core/umd/popper.min.js') }}" defer></script>
{{-- <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>


<!-- Defaultmenu JS -->
<script src="{{ asset('assets/js/defaultmenu.min.js') }}" defer></script>

<!-- Node Waves JS -->
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}" defer></script>

<!-- Sticky JS -->
<script src="{{ asset('assets/js/sticky.js') }}" defer></script>

<!-- Simplebar JS -->
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}" defer></script>
<script src="{{ asset('assets/js/simplebar.js') }}" defer></script>

<!-- Color Picker JS -->
<script src="{{ asset('assets/libs/@simonwep/pickr/pickr.es5.min.js') }}" defer></script>

<!-- Apex Charts JS -->
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}" defer></script>

<!-- JSVector Maps JS -->
<script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}" defer></script>

<!-- JSVector Maps MapsJS -->
<script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}" defer></script>
<script src="{{ asset('assets/js/us-merc-en.js') }}" defer></script>

<!-- Chartjs Chart JS (Dashboard Only) -->
@stack('dashboard-scripts')

<!-- Custom-Switcher JS -->
<script src="{{ asset('assets/js/custom-switcher.min.js') }}" defer></script>

<!-- Custom JS -->
<script src="{{ asset('assets/js/custom.js') }}" defer></script>

<!-- Show page after all resources load -->
<script>
    // Mark page as loaded when all resources are ready
    window.addEventListener('load', function() {
        document.documentElement.classList.add('loaded');
        // Hide loader
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.display = 'none';
        }
    });
</script>

<!-- Page Specific Scripts -->
@yield('script')
@yield('scripts')

<script>
    // حفظ موضع السكرول في السايدبار واسترجاعه
    (function () {
        const sidebar = document.getElementById('sidebar-scroll');
        if (!sidebar) return;

        // استرجاع الموضع
        const savedScroll = localStorage.getItem('admin_sidebar_scroll');
        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10) || 0;
        }

        // حفظ الموضع عند التحريك
        sidebar.addEventListener('scroll', function () {
            localStorage.setItem('admin_sidebar_scroll', sidebar.scrollTop);
        });
    })();
</script>
@yield('scripts')
