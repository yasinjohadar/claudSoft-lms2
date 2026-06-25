/**
 * Quiz review page — filters, scroll, reveal animation
 */
(function () {
    'use strict';

    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

    function applyFilter(status) {
        qsa('.quiz-review-card').forEach(function (card) {
            var cardStatus = card.getAttribute('data-review-status') || 'pending';
            var show = status === 'all' || cardStatus === status;
            card.classList.toggle('is-filtered-out', !show);
            card.style.display = show ? '' : 'none';
        });

        qsa('.quiz-review-filter').forEach(function (btn) {
            var active = btn.getAttribute('data-filter') === status;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    function initFilters() {
        qsa('.quiz-review-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyFilter(btn.getAttribute('data-filter') || 'all');
            });
        });
    }

    function initReveal() {
        if (!('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        qsa('.quiz-review-card').forEach(function (card, i) {
            card.style.setProperty('--qr-delay', Math.min(i * 0.04, 0.4) + 's');
            observer.observe(card);
        });
    }

    function init() {
        if (!qs('.student-quiz-review-page')) return;
        initFilters();
        initReveal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
