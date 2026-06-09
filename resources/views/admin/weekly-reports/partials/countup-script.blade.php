@push('scripts')
<script>
    (function () {
        function formatCountupNumber(value, withDecimals) {
            if (withDecimals) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        function initWeeklyReportsCountup(container) {
            const root = container || document;
            root.querySelectorAll('[data-countup]').forEach(function (el) {
                const target = parseFloat(el.dataset.countup || '0');
                const prefix = el.dataset.countupPrefix || '';
                const suffix = el.dataset.countupSuffix || '';
                const decimals = el.dataset.countupDecimals === '2' ? 2 : (el.dataset.countupDecimals === '1' ? 1 : 0);
                const duration = 800;
                const start = performance.now();

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const value = formatCountupNumber(target * eased, decimals > 0);
                    el.textContent = prefix + value + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initWeeklyReportsCountup();
        });
    })();
</script>
@endpush
