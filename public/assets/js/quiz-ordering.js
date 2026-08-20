/**
 * محرّك ترتيب العناصر (ordering) المشترك لصفحات حل الاختبار.
 *
 * يعالج المشاكل التي جعلت بعض الطلاب يظنون أن الأسهم لا تعمل:
 *  - لا توجد أي إشارة بصرية بعد النقر: العنصر يتبادل مكانه مع جاره فقط، والزر
 *    ينتقل مع العنصر فيصبح المؤشر فوق زر الجار — فالنقر مرتين في نفس المكان
 *    يرجع العنصرين لمكانهما (فيبدو كأن شيئاً لم يحدث).
 *  - النقر على سهم عند حدّ القائمة كان صامتاً تماماً.
 *  - فقدان التركيز (focus) عند نقل العنصر يمنع طلاب الكيبورد من التكرار.
 *  - لا مؤشّر على نجاح/فشل حفظ الترتيب على السيرفر.
 *
 * لذلك يوفّر: حركة FLIP متحركة، تظليل العنصر المنقول + شريحة «تم النقل»،
 * نبض رقم الموضع، سطر حالة (آخر حركة + حالة الحفظ)، منطقة aria-live للنطق،
 * إبقاء التركيز على السهم المستخدم، شرح نقرات الحدود، وتسلسل طلبات الحفظ.
 *
 * ربط الصفحة:
 *   QuizOrdering.configure({
 *       save: function (questionId, order) { return $.ajax(...); }, // promise/jqXHR
 *       onChange: function (questionId, order) { ... }              // تحديث التقدم
 *   });
 *   QuizOrdering.init();
 */
(function (window, document) {
    'use strict';

    // حماية من تحميل الملف مرتين: نسختان تعنيان معالِجَي نقر ⇒ تحريك مزدوج.
    if (window.QuizOrdering && window.QuizOrdering.__loaded) {
        return;
    }

    var MOVE_MS = 220;
    var FLAG_MS = 1900;
    var EDGE_MS = 400;
    var HIGHLIGHT_MS = 1600;
    var SAVE_DEBOUNCE_MS = 300;
    var SAVE_RETRY_MS = 2500;

    var config = { save: null, onChange: null };
    var saveStates = {};
    var timers = {};
    var bound = false;

    function slice(nodes) {
        return Array.prototype.slice.call(nodes);
    }

    function reducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function listFor(questionId) {
        return document.getElementById('ordering-list-' + questionId);
    }

    function itemsIn(list) {
        return list ? slice(list.querySelectorAll('.ordering-item')) : [];
    }

    function questionIdOf(el) {
        var item = el.closest('.ordering-item, .ordering-container');
        return item ? item.getAttribute('data-question-id') : null;
    }

    /**
     * نصوص عناصر الترتيب قد تكون تاغات HTML نفسها (مثل <body>)، فلا يجوز
     * إدراجها في innerHTML كما هي وإلا اختفت من الرسالة تماماً.
     */
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function itemLabel(item) {
        var text = item.querySelector('.ordering-text');
        var value = (text ? text.textContent : '').replace(/\s+/g, ' ').trim();
        return value.length > 45 ? value.slice(0, 45) + '…' : value;
    }

    function itemLabelHtml(item) {
        return escapeHtml(itemLabel(item));
    }

    /* ── الحالة المرئية للأسهم والأرقام ──────────────────────────────── */

    function refreshControls(list) {
        var items = itemsIn(list);
        var last = items.length - 1;

        items.forEach(function (item, index) {
            var number = item.querySelector('.ordering-number');
            if (number) {
                number.textContent = index + 1;
            }
            item.setAttribute('data-position', index + 1);

            setEdgeState(item.querySelector('.ordering-move-up'), index === 0, index, items.length, 'up');
            setEdgeState(item.querySelector('.ordering-move-down'), index === last, index, items.length, 'down');
        });
    }

    /**
     * الحدّ يُعلَّم بـ aria-disabled لا disabled: الزر المعطّل فعلياً لا يُرسل حدث
     * نقر أصلاً، فلا نستطيع شرح سبب عدم الحركة للطالب.
     */
    function setEdgeState(button, isEdge, index, total, direction) {
        if (!button) {
            return;
        }

        button.disabled = false;
        button.removeAttribute('disabled');
        button.setAttribute('aria-disabled', isEdge ? 'true' : 'false');

        var target = direction === 'up' ? index : index + 2;
        var label = direction === 'up' ? 'تحريك لأعلى' : 'تحريك لأسفل';

        if (isEdge) {
            label += direction === 'up' ? ' (العنصر في أعلى القائمة)' : ' (العنصر في أسفل القائمة)';
        } else {
            label += ' إلى الموضع ' + target + ' من ' + total;
        }

        button.setAttribute('aria-label', label);
        button.setAttribute('title', label);
    }

    /* ── سطر الحالة ومنطقة النطق ─────────────────────────────────────── */

    function feedbackFor(questionId) {
        var list = listFor(questionId);
        var container = list ? list.closest('.ordering-container') : null;
        if (!container) {
            return null;
        }

        var box = container.querySelector('.ordering-feedback');
        if (!box) {
            box = document.createElement('div');
            box.className = 'ordering-feedback';
            box.setAttribute('role', 'status');
            box.setAttribute('aria-live', 'polite');
            box.innerHTML = '<span class="ordering-feedback__move"></span>'
                + '<span class="ordering-feedback__save"></span>';
            list.parentNode.insertBefore(box, list.nextSibling);
        }

        return box;
    }

    function setMoveMessage(questionId, html) {
        var box = feedbackFor(questionId);
        if (!box) {
            return;
        }

        var el = box.querySelector('.ordering-feedback__move');
        el.classList.remove('is-hint');
        el.innerHTML = html;
    }

    function setSaveMessage(questionId, html, stateClass) {
        var box = feedbackFor(questionId);
        if (!box) {
            return;
        }

        var el = box.querySelector('.ordering-feedback__save');
        el.className = 'ordering-feedback__save' + (stateClass ? ' ' + stateClass : '');
        el.innerHTML = html;
    }

    /* ── التحريك ─────────────────────────────────────────────────────── */

    function flagMoved(item, to) {
        slice(item.querySelectorAll('.ordering-move-flag')).forEach(function (old) {
            old.parentNode.removeChild(old);
        });

        var flag = document.createElement('span');
        flag.className = 'ordering-move-flag';
        flag.setAttribute('aria-hidden', 'true');
        flag.innerHTML = '<i class="fas fa-check"></i> تم النقل إلى الموضع ' + to;
        item.appendChild(flag);

        window.setTimeout(function () {
            flag.classList.add('is-leaving');
            window.setTimeout(function () {
                if (flag.parentNode) {
                    flag.parentNode.removeChild(flag);
                }
            }, 320);
        }, FLAG_MS);
    }

    function highlightMoved(item, neighbour) {
        var number = item.querySelector('.ordering-number');

        item.classList.remove('ordering-item--moved');
        if (number) {
            number.classList.remove('ordering-number--moved');
        }
        if (neighbour) {
            neighbour.classList.remove('ordering-item--shifted');
        }

        // إعادة تشغيل الأنيميشن بعد إزالة الصنف
        void item.offsetWidth;

        item.classList.add('ordering-item--moved');
        if (number) {
            number.classList.add('ordering-number--moved');
        }
        if (neighbour) {
            neighbour.classList.add('ordering-item--shifted');
        }

        window.setTimeout(function () {
            item.classList.remove('ordering-item--moved');
            if (number) {
                number.classList.remove('ordering-number--moved');
            }
            if (neighbour) {
                neighbour.classList.remove('ordering-item--shifted');
            }
        }, HIGHLIGHT_MS);
    }

    /**
     * حركة FLIP: يتابع الطالب العنصر بعينه بدل قفزة مفاجئة.
     * `snapshot` مصفوفة { el, top } مأخوذة قبل تعديل الـ DOM.
     */
    function animateSwap(snapshot) {
        if (reducedMotion()) {
            return;
        }

        snapshot.forEach(function (entry) {
            var delta = entry.top - entry.el.getBoundingClientRect().top;
            if (!delta) {
                return;
            }

            entry.el.style.transition = 'none';
            entry.el.style.transform = 'translateY(' + delta + 'px)';

            window.requestAnimationFrame(function () {
                entry.el.style.transition = 'transform ' + MOVE_MS + 'ms ease';
                entry.el.style.transform = '';

                window.setTimeout(function () {
                    entry.el.style.transition = '';
                    entry.el.style.transform = '';
                }, MOVE_MS + 40);
            });
        });
    }

    function keepInView(item) {
        var rect = item.getBoundingClientRect();
        var height = window.innerHeight || document.documentElement.clientHeight;

        if (rect.top < 72 || rect.bottom > height - 72) {
            item.scrollIntoView({
                block: 'center',
                behavior: reducedMotion() ? 'auto' : 'smooth'
            });
        }
    }

    function flashEdge(item, direction) {
        item.classList.remove('ordering-item--edge');
        void item.offsetWidth;
        item.classList.add('ordering-item--edge');

        window.setTimeout(function () {
            item.classList.remove('ordering-item--edge');
        }, EDGE_MS);

        var questionId = item.getAttribute('data-question-id');
        setMoveMessage(questionId,
            '<i class="fas fa-info-circle"></i> «' + itemLabelHtml(item) + '» '
            + (direction === 'up' ? 'في أعلى القائمة بالفعل' : 'في أسفل القائمة بالفعل'));
    }

    function move(item, direction, button) {
        var list = item.parentNode;
        if (!list || !list.classList.contains('ordering-list')) {
            return;
        }

        var neighbour = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
        if (!neighbour || !neighbour.classList.contains('ordering-item')) {
            flashEdge(item, direction);
            return;
        }

        var questionId = item.getAttribute('data-question-id') || questionIdOf(item);
        var snapshot = itemsIn(list).map(function (el) {
            return { el: el, top: el.getBoundingClientRect().top };
        });
        var from = itemsIn(list).indexOf(item) + 1;

        if (direction === 'up') {
            list.insertBefore(item, neighbour);
        } else {
            list.insertBefore(neighbour, item);
        }

        var to = itemsIn(list).indexOf(item) + 1;

        animateSwap(snapshot);
        refreshControls(list);
        highlightMoved(item, neighbour);
        flagMoved(item, to);
        keepInView(item);

        setMoveMessage(questionId,
            '<i class="fas fa-arrows-alt-v"></i> تم نقل «' + itemLabelHtml(item)
            + '» من الموضع ' + from + ' إلى الموضع ' + to);

        // نقل العنصر يفقد التركيز في بعض المتصفحات، وإرجاعه يجعل حلقة
        // «انقر ← تحرّك» واضحة ومتاحة لطلاب الكيبورد.
        if (button) {
            try {
                button.focus({ preventScroll: true });
            } catch (error) {
                button.focus();
            }
        }

        commit(questionId);
    }

    /* ── الحفظ ───────────────────────────────────────────────────────── */

    function getOrder(questionId) {
        return itemsIn(listFor(questionId)).map(function (item) {
            var value = item.getAttribute('data-item-id');
            var numeric = parseInt(value, 10);
            return isNaN(numeric) ? value : numeric;
        });
    }

    function syncHiddenInput(questionId, order) {
        var input = document.getElementById('ordering-input-' + questionId);
        if (input) {
            input.value = JSON.stringify(order);
        }
    }

    function stateFor(questionId) {
        if (!saveStates[questionId]) {
            saveStates[questionId] = { inflight: false, dirty: false, retried: false };
        }
        return saveStates[questionId];
    }

    function commit(questionId, options) {
        var silent = !!(options && options.silent);
        var order = getOrder(questionId);

        syncHiddenInput(questionId, order);

        if (typeof config.onChange === 'function') {
            try {
                config.onChange(questionId, order);
            } catch (error) {
                window.console && console.error('QuizOrdering onChange failed', error);
            }
        }

        if (typeof config.save !== 'function' || order.length === 0) {
            return;
        }

        if (!silent) {
            setSaveMessage(questionId,
                '<span class="spinner-border spinner-border-sm"></span> جارٍ حفظ الترتيب…');
        }

        window.clearTimeout(timers[questionId]);
        timers[questionId] = window.setTimeout(function () {
            flush(questionId, silent);
        }, SAVE_DEBOUNCE_MS);
    }

    function flush(questionId, silent) {
        var state = stateFor(questionId);

        if (state.inflight) {
            state.dirty = true;
            return;
        }

        var order = getOrder(questionId);
        if (order.length === 0) {
            return;
        }

        state.inflight = true;

        var request;
        try {
            request = config.save(questionId, order);
        } catch (error) {
            state.inflight = false;
            saveFailed(questionId, silent);
            return;
        }

        Promise.resolve(request).then(function () {
            state.inflight = false;
            state.retried = false;

            if (state.dirty) {
                state.dirty = false;
                flush(questionId, silent);
                return;
            }

            if (!silent) {
                setSaveMessage(questionId, '<i class="fas fa-check-circle"></i> تم حفظ الترتيب', 'is-saved');
            }
        }, function () {
            state.inflight = false;
            state.dirty = false;

            if (!state.retried) {
                state.retried = true;
                window.setTimeout(function () {
                    flush(questionId, silent);
                }, SAVE_RETRY_MS);
                return;
            }

            saveFailed(questionId, silent);
        });
    }

    function saveFailed(questionId, silent) {
        if (silent) {
            return;
        }

        setSaveMessage(questionId,
            '<i class="fas fa-exclamation-triangle"></i> تعذّر حفظ الترتيب — سيُرسل عند التسليم',
            'is-error');
    }

    /**
     * يُنهي أي حفظ مؤجّل فوراً (يُستخدم قبل تسليم الاختبار).
     */
    function flushAll() {
        Object.keys(timers).forEach(function (questionId) {
            window.clearTimeout(timers[questionId]);
            flush(questionId, true);
        });
    }

    /* ── التهيئة ─────────────────────────────────────────────────────── */

    function syncAll(options) {
        slice(document.querySelectorAll('.ordering-container')).forEach(function (container) {
            var questionId = container.getAttribute('data-question-id');
            var list = listFor(questionId);
            if (!list) {
                return;
            }

            refreshControls(list);

            // تجهيز سطر الحالة مسبقاً: يحجز مكانه (لا اهتزاز في التخطيط) ويُخبر
            // الطالب أن كل حركة ستُؤكَّد له.
            var box = feedbackFor(questionId);
            var move = box ? box.querySelector('.ordering-feedback__move') : null;
            if (move && !move.innerHTML) {
                move.classList.add('is-hint');
                move.innerHTML = '<i class="fas fa-hand-pointer"></i> اضغط الأسهم لتغيير الترتيب — سيظهر تأكيد لكل حركة';
            }

            commit(questionId, options);
        });
    }

    function onClick(event) {
        if (!event.target || typeof event.target.closest !== 'function') {
            return;
        }

        var button = event.target.closest('.ordering-move-up, .ordering-move-down');
        if (!button) {
            return;
        }

        var item = button.closest('.ordering-item');
        if (!item) {
            return;
        }

        event.preventDefault();

        var direction = button.classList.contains('ordering-move-up') ? 'up' : 'down';

        if (button.getAttribute('aria-disabled') === 'true') {
            flashEdge(item, direction);
            return;
        }

        move(item, direction, button);
    }

    function configure(options) {
        options = options || {};

        if (typeof options.save === 'function') {
            config.save = options.save;
        }
        if (typeof options.onChange === 'function') {
            config.onChange = options.onChange;
        }
    }

    function bindOnce() {
        if (bound) {
            return;
        }

        document.addEventListener('click', onClick);
        bound = true;
    }

    function init(options) {
        configure(options);
        bindOnce();

        // أول مزامنة تحفظ الترتيب الأولي بصمت (بدون رسائل «تم الحفظ»).
        syncAll({ silent: true });
    }

    window.QuizOrdering = {
        __loaded: true,
        init: init,
        configure: configure,
        syncAll: syncAll,
        refreshControls: refreshControls,
        getOrder: getOrder,
        flushAll: flushAll
    };

    // ربط ذاتي: الأسهم يجب أن تحرّك العناصر حتى لو فشل سكربت الصفحة أو jQuery.
    // الحفظ يُفعَّل لاحقاً عند نداء init() من الصفحة.
    bindOnce();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            syncAll({ silent: true });
        });
    } else {
        syncAll({ silent: true });
    }
})(window, document);
