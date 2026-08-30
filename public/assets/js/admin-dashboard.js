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
            const suffix = el.dataset.countupSuffix || '';
            const duration = 900;
            const start = performance.now();

            const step = function (now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased) + suffix;
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    // نبضة ختامية لودجات لوحة التحكم (portal-kpi.css)
                    el.classList.add('stat-value-done');
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

    /**
     * موجة عند النقر على بطاقة اختصار (portal-shortcuts.css).
     * مفوَّضة على المستند بدل ربط كل بطاقة، فتعمل مع أي بطاقة تُضاف لاحقاً.
     */
    function initShortcutRipple() {
        document.addEventListener('click', function (e) {
            const card = e.target.closest('.shortcut-card');
            if (!card) {
                return;
            }

            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
            ripple.className = 'shortcut-ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            card.appendChild(ripple);
            ripple.addEventListener('animationend', function () {
                ripple.remove();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCountUp();
        initShortcutRipple();
    });
})();
