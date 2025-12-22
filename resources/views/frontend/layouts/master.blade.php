<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

    @include("frontend.layouts.head")
</head>
<body data-bs-theme="LIGHT">
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="spinner">
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
            </div>
            <p class="loader-text">جاري التحميل...</p>
        </div>
    </div>

    @include("frontend.layouts.main-header")


    @yield("content")


    @include("frontend.layouts.footer")

    <!-- Page Loader Styles -->
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            text-align: center;
        }

        .spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .spinner-circle {
            width: 18px;
            height: 18px;
            background: var(--main-Color);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .spinner-circle:nth-child(1) {
            animation-delay: -0.32s;
        }

        .spinner-circle:nth-child(2) {
            animation-delay: -0.16s;
            background: var(--secondary-Color);
        }

        .spinner-circle:nth-child(3) {
            background: var(--main-Color);
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loader-text {
            color: var(--secondary-Color);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        /* Hide body scroll while loading */
        body.loading {
            overflow: hidden;
        }
    </style>

    <!-- Page Loader Script -->
    <script>
        (function() {
            const loader = document.getElementById('page-loader');

            // Function to hide loader
            function hideLoader() {
                if (loader) {
                    loader.classList.add('hidden');
                    document.body.classList.remove('loading');
                }
            }

            // Hide loader as soon as DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(hideLoader, 100);
                });
            } else {
                // DOM is already ready
                setTimeout(hideLoader, 100);
            }

            // Setup navigation loader after DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                // Show loader on link clicks
                const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])');

                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');

                        // Check if it's an internal link
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
                            loader.classList.remove('hidden');
                            document.body.classList.add('loading');
                        }
                    });
                });

                // Show loader on form submissions
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', function() {
                        loader.classList.remove('hidden');
                        document.body.classList.add('loading');
                    });
                });

                // Handle browser back/forward buttons
                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        hideLoader();
                    }
                });
            });
        })();
    </script>
</body>
</html>
