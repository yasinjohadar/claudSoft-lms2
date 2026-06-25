/**
 * Platform review form UI
 */
(function () {
    'use strict';

    var ratingLabels = {
        1: 'ضعيف — نجمة واحدة',
        2: 'مقبول — نجمتان',
        3: 'جيد — 3 نجوم',
        4: 'جيد جداً — 4 نجوم',
        5: 'ممتاز — 5 نجوم',
    };

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function initCharCounter(textareaId, counterId, max) {
        var textarea = qs('#' + textareaId);
        var counter = qs('#' + counterId);
        if (!textarea || !counter) return;

        function update() {
            var len = textarea.value.length;
            counter.textContent = len;
            var wrap = counter.closest('.platform-review-field__counter');
            if (!wrap) return;
            wrap.classList.toggle('is-warning', len > max * 0.85 && len <= max);
            wrap.classList.toggle('is-danger', len > max);
        }

        update();
        textarea.addEventListener('input', update);
    }

    function initRatingCaption() {
        var group = qs('.pr-rating-input');
        var caption = qs('#pr-rating-caption');
        if (!group || !caption) return;

        function sync() {
            var checked = group.querySelector('input[type="radio"]:checked');
            caption.textContent = checked ? (ratingLabels[checked.value] || '') : 'اضغط على النجوم لاختيار التقييم';
        }

        group.querySelectorAll('input[type="radio"]').forEach(function (input) {
            input.addEventListener('change', sync);
        });
        sync();
    }

    function init() {
        if (!qs('.platform-review-page')) return;
        initCharCounter('review_text', 'char-count', 1000);
        initCharCounter('suggestion', 'suggestion-count', 500);
        initRatingCaption();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
