(function () {
    'use strict';

    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function initCountUp() {
        const elements = document.querySelectorAll('[data-countup]');
        if (!elements.length) {
            return;
        }

        const animate = function (el) {
            if (el.dataset.countupAnimated === 'true') {
                return;
            }
            el.dataset.countupAnimated = 'true';

            const target = parseFloat(el.dataset.countup || '0');
            const duration = 900;
            const start = performance.now();

            const step = function (now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased);
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };

            requestAnimationFrame(step);
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animate(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            elements.forEach(function (el) {
                observer.observe(el);
            });
        } else {
            elements.forEach(animate);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCountUp();
    });
})();
