(function () {
    'use strict';

    function getThemeMode() {
        return document.documentElement.getAttribute('data-theme-mode') === 'dark' ? 'dark' : 'light';
    }

    function getCssRgbVar(name) {
        const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return value ? 'rgb(' + value + ')' : null;
    }

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

    function buildChartOptions(labels, values) {
        const isDark = getThemeMode() === 'dark';
        const primaryColor = getCssRgbVar('--primary-rgb') || 'rgb(1, 98, 232)';
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--default-text-color').trim()
            || (isDark ? 'rgba(255,255,255,0.85)' : '#334155');
        const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--default-border').trim()
            || (isDark ? '#2d3748' : '#e2e8f0');

        return {
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                },
            },
            theme: { mode: isDark ? 'dark' : 'light' },
            series: [{
                name: 'التحاقات',
                data: values,
            }],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '55%',
                },
            },
            xaxis: {
                categories: labels,
                labels: {
                    style: {
                        colors: textColor,
                        fontFamily: 'inherit',
                    },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor,
                        fontFamily: 'inherit',
                    },
                },
            },
            colors: [primaryColor],
            dataLabels: { enabled: false },
            grid: {
                strokeDashArray: 4,
                borderColor: borderColor,
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
            },
        };
    }

    function initEnrollmentsChart() {
        const el = document.getElementById('enrollments-chart');
        if (!el || typeof ApexCharts === 'undefined') {
            return null;
        }

        const labels = JSON.parse(el.dataset.labels || '[]');
        const values = JSON.parse(el.dataset.values || '[]');
        const chart = new ApexCharts(el, buildChartOptions(labels, values));
        chart.render();

        return chart;
    }

    function watchThemeChange(chart) {
        if (!chart) {
            return;
        }

        const observer = new MutationObserver(function () {
            const el = document.getElementById('enrollments-chart');
            if (!el) {
                return;
            }
            const labels = JSON.parse(el.dataset.labels || '[]');
            const values = JSON.parse(el.dataset.values || '[]');
            chart.updateOptions(buildChartOptions(labels, values), false, true);
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme-mode'],
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCountUp();
        const chart = initEnrollmentsChart();
        watchThemeChange(chart);
    });
})();
