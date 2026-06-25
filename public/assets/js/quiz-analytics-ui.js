/**
 * Admin quiz analytics — charts, filters, accordions
 */
(function () {
    'use strict';

    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

    function formatNumber(value, decimals) {
        return new Intl.NumberFormat('ar-EG', {
            minimumFractionDigits: decimals ? 1 : 0,
            maximumFractionDigits: decimals ? 1 : 0,
        }).format(value);
    }

    function initCountup() {
        qsa('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var suffix = el.dataset.countupSuffix || '';
            var decimals = el.dataset.countupDecimals === '1';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased, decimals);
                el.textContent = value + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    }

    function initGradeBars() {
        if (!('IntersectionObserver' in window)) {
            qsa('.quiz-analytics-grade-row').forEach(function (row) {
                row.classList.add('is-animated');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        qsa('.quiz-analytics-grade-row').forEach(function (row) {
            observer.observe(row);
        });
    }

    function initQuestionFilters() {
        qsa('.quiz-analytics-q-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var filter = btn.getAttribute('data-filter') || 'all';

                qsa('.quiz-analytics-q-filter').forEach(function (b) {
                    b.classList.toggle('is-active', b === btn);
                });

                qsa('.quiz-analytics-question').forEach(function (card) {
                    var difficulty = card.getAttribute('data-difficulty') || 'medium';
                    var show = filter === 'all' || difficulty === filter;
                    card.classList.toggle('is-hidden', !show);
                });
            });
        });
    }

    function initQuestionAccordions() {
        qsa('.quiz-analytics-question__header').forEach(function (header) {
            function toggle() {
                var card = header.closest('.quiz-analytics-question');
                var detail = card && qs('.quiz-analytics-question__detail', card);
                if (!card || !detail) return;

                var isOpen = card.classList.contains('is-open');

                qsa('.quiz-analytics-question.is-open').forEach(function (openCard) {
                    if (openCard === card) return;
                    openCard.classList.remove('is-open');
                    var openDetail = qs('.quiz-analytics-question__detail', openCard);
                    var openHeader = qs('.quiz-analytics-question__header', openCard);
                    if (openDetail) openDetail.hidden = true;
                    if (openHeader) openHeader.setAttribute('aria-expanded', 'false');
                });

                card.classList.toggle('is-open', !isOpen);
                detail.hidden = isOpen;
                header.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            }

            header.addEventListener('click', toggle);
            header.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle();
                }
            });
        });
    }

    function initTrendsChart() {
        var el = qs('#quizAnalyticsTrendsChart');
        var dataEl = qs('#quiz-analytics-trends-data');
        if (!el || !dataEl || typeof ApexCharts === 'undefined') return;

        var data;
        try {
            data = JSON.parse(dataEl.textContent || '[]');
        } catch (e) {
            return;
        }

        if (!data.length) return;

        var isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
        var textColor = isDark ? '#94a3b8' : '#64748b';

        new ApexCharts(el, {
            chart: {
                type: 'area',
                height: 260,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            series: [
                { name: 'متوسط الدرجة', data: data.map(function (d) { return Math.round((d.avg_score || 0) * 10) / 10; }) },
                { name: 'المحاولات', data: data.map(function (d) { return d.count || 0; }) },
            ],
            colors: ['rgb(var(--primary-rgb))', 'rgba(var(--bs-success-rgb), 0.8)'],
            stroke: { curve: 'smooth', width: [3, 2] },
            fill: {
                type: ['gradient', 'solid'],
                gradient: { opacityFrom: 0.35, opacityTo: 0.05 },
                opacity: [0.35, 0.1],
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: data.map(function (d) { return d.date; }),
                labels: { style: { colors: textColor, fontSize: '11px' } },
            },
            yaxis: [
                {
                    title: { text: '%', style: { color: textColor } },
                    labels: { style: { colors: textColor } },
                    max: 100,
                },
                {
                    opposite: true,
                    title: { text: 'محاولات', style: { color: textColor } },
                    labels: { style: { colors: textColor } },
                },
            ],
            grid: { borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)' },
            legend: { position: 'top', labels: { colors: textColor } },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        }).render();
    }

    function init() {
        if (!qs('.quiz-analytics-page')) return;
        initCountup();
        initGradeBars();
        initQuestionFilters();
        initQuestionAccordions();
        initTrendsChart();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
