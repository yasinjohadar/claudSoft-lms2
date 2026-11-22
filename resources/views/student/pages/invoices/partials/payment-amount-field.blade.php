{{--
    حقل "المبلغ المدفوع فعلياً" — بدون قيمة افتراضية حتى لا يرسل الطالب المبلغ المتبقي كما هو دون انتباه.
    $inputId   : id الحقل
    $remaining : المبلغ المتبقي (قد يكون null في صفحة القائمة ويُعبّأ عبر JS من data-remaining)
--}}
@php $remainingValue = isset($remaining) ? number_format($remaining, 2, '.', '') : ''; @endphp

<div class="mb-3 js-pay-amount-field" data-remaining="{{ $remainingValue }}">
    <label for="{{ $inputId }}" class="form-label fw-semibold">
        المبلغ الذي دفعته فعلياً <span class="text-danger">*</span>
    </label>

    <div class="alert alert-danger small py-2 mb-2">
        <i class="fe fe-alert-triangle me-1"></i>
        اكتب <strong>قيمة المبلغ الذي حوّلته فعلاً</strong> كما هو في الإيصال، وليس المبلغ المتبقي على الفاتورة.
    </div>

    <div class="input-group">
        <span class="input-group-text">$</span>
        <input type="number"
               class="form-control form-control-lg js-pay-amount-input @error('amount') is-invalid @enderror"
               id="{{ $inputId }}"
               name="amount"
               step="0.01"
               min="0.01"
               @if($remainingValue !== '') max="{{ $remainingValue }}" @endif
               value="{{ old('amount') }}"
               placeholder="مثال: 50.00"
               inputmode="decimal"
               autocomplete="off"
               required>
        <button type="button" class="btn btn-outline-secondary js-pay-full-btn" title="تعبئة كامل المبلغ المتبقي">
            دفعت كامل المتبقي
        </button>
    </div>

    <div class="d-flex flex-wrap justify-content-between gap-2 mt-1">
        <small class="text-danger fw-semibold">المبلغ المتبقي على الفاتورة: $<span class="js-pay-remaining">{{ $remainingValue }}</span></small>
    </div>

    <div class="student-pay-summary js-pay-summary mt-2" hidden>
        <i class="fe fe-info"></i>
        <span>سيتم إرسال دفعة بقيمة <strong class="js-pay-summary-amount"></strong> للمراجعة.
            بعد قبولها يصبح المتبقي <strong class="js-pay-summary-after"></strong>.</span>
    </div>

    @error('amount')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    <script>
        (function () {
            function fmt(v) { return '$' + (Math.round(v * 100) / 100).toFixed(2); }

            function refresh(field) {
                var input = field.querySelector('.js-pay-amount-input');
                var summary = field.querySelector('.js-pay-summary');
                var remaining = parseFloat(field.dataset.remaining || '0');
                var amount = parseFloat(input.value);

                if (!summary) return;
                if (!amount || amount <= 0) { summary.hidden = true; return; }

                summary.hidden = false;
                summary.classList.toggle('is-invalid', remaining > 0 && amount > remaining);
                field.querySelector('.js-pay-summary-amount').textContent = fmt(amount);
                field.querySelector('.js-pay-summary-after').textContent =
                    remaining > 0 && amount > remaining ? 'أكبر من المتبقي!' : fmt(Math.max(0, remaining - amount));
            }

            // يُستدعى من صفحة القائمة عند فتح المودال لفاتورة معيّنة
            window.studentPayAmountReset = function (field, remaining) {
                var input = field.querySelector('.js-pay-amount-input');
                field.dataset.remaining = remaining || '';
                field.querySelector('.js-pay-remaining').textContent = remaining || '0.00';
                input.value = '';
                if (remaining) { input.max = remaining; } else { input.removeAttribute('max'); }
                refresh(field);
            };

            document.addEventListener('input', function (e) {
                var field = e.target.closest && e.target.closest('.js-pay-amount-field');
                if (field && e.target.classList.contains('js-pay-amount-input')) refresh(field);
            });

            document.addEventListener('click', function (e) {
                var btn = e.target.closest && e.target.closest('.js-pay-full-btn');
                if (!btn) return;
                var field = btn.closest('.js-pay-amount-field');
                var input = field.querySelector('.js-pay-amount-input');
                input.value = field.dataset.remaining || '';
                input.focus();
                refresh(field);
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.js-pay-amount-field').forEach(refresh);
            });
        })();
    </script>
@endonce
