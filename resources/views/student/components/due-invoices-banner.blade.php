<div class="main-content app-content due-invoices-wrapper"
     id="dueInvoicesBanner"
     data-dismiss-key="{{ $alert['dismiss_key'] }}">
    <div class="container-fluid mt-2">
        <div class="due-invoices-banner shadow-sm" role="alert">
            <button type="button"
                    class="btn-close due-invoices-close"
                    id="dueInvoicesBannerClose"
                    aria-label="إغلاق"></button>

            <div class="due-invoices-main">
                <div class="due-invoices-title-row">
                    <h6 class="due-invoices-title mb-0">
                        <i class="fe fe-alert-triangle me-1"></i>فواتير مستحقة
                    </h6>
                    <span class="due-invoices-count">{{ $alert['count'] }}</span>
                </div>
                <p class="due-invoices-subtitle mb-0">
                    @if($alert['count'] === 1)
                        لديك فاتورة واحدة بمبلغ متبقٍ
                    @else
                        لديك {{ $alert['count'] }} فواتير بمبلغ متبقٍ
                    @endif
                    <strong>${{ number_format($alert['total_remaining'], 2) }}</strong>.
                    يرجى السداد في أقرب وقت.
                </p>
            </div>

            <a href="{{ $alert['invoices_url'] }}" class="btn btn-danger btn-sm due-invoices-cta">
                سداد الفواتير
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        var banner = document.getElementById('dueInvoicesBanner');
        var closeBtn = document.getElementById('dueInvoicesBannerClose');

        if (!banner || !closeBtn) {
            return;
        }

        var dismissKey = banner.getAttribute('data-dismiss-key');

        if (dismissKey && localStorage.getItem(dismissKey) === '1') {
            banner.remove();
            return;
        }

        closeBtn.addEventListener('click', function () {
            if (dismissKey) {
                localStorage.setItem(dismissKey, '1');
            }
            banner.remove();
        });
    })();
</script>
