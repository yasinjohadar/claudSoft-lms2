<script>
(function () {
    var url = @json(route('validate.phone-country'));

    function debounce(fn, ms) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }

    function getCsrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    /** عرض رسالة عربية واضحة فقط — لا نعرض أخطاء PHP/السيرفر الخام */
    function humanizePhoneMessage(msg) {
        if (msg === null || msg === undefined || typeof msg !== 'string') {
            return 'تعذر التحقق من الرقم. حاول مرة أخرى.';
        }
        var s = msg.trim();
        if (s === '') {
            return '';
        }
        if (s.length > 400) {
            return 'تعذر التحقق من الرقم. حاول مرة أخرى.';
        }
        var technical = /Class\s+[\"']|not found|Stack trace|Fatal error|Parse error|SQLSTATE|file_put_contents|vendor\\/i.test(s);
        if (technical) {
            return 'تعذر التحقق من الرقم الآن. حاول مرة أخرى، أو تأكد أن الرقم يطابق الدولة المختارة.';
        }
        return s;
    }

    function validateJson(countryEl, phoneEl, done) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                country_code: countryEl ? countryEl.value : '',
                phone: phoneEl ? phoneEl.value : ''
            })
        })
            .then(function (r) {
                return r.json().then(function (body) {
                    if (!r.ok) {
                        var firstErr = body.errors && body.errors[Object.keys(body.errors)[0]];
                        var raw = Array.isArray(firstErr) ? firstErr[0] : (body.message || 'خطأ في الطلب.');
                        done(null, { valid: false, message: humanizePhoneMessage(raw) });
                        return;
                    }
                    if (body && body.message) {
                        body.message = humanizePhoneMessage(body.message);
                    }
                    done(null, body);
                });
            })
            .catch(function () { done(new Error('network')); });
    }

    function setFeedback(el, text, state) {
        if (!el) return;
        el.textContent = text || '';
        el.classList.remove('text-success', 'text-danger', 'text-muted');
        if (state === 'loading') {
            el.classList.add('text-muted');
        } else if (state === 'ok') {
            el.classList.add('text-success');
        } else if (state === 'err') {
            el.classList.add('text-danger');
        }
    }

    function bindForm(form) {
        var countryEl = form.querySelector('select[name="country_code"]');
        var phoneEl = form.querySelector('input[name="phone"]');
        if (!countryEl || !phoneEl) return;

        var feedback = form.querySelector('[data-phone-ajax-feedback]');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.setAttribute('data-phone-ajax-feedback', '');
            feedback.className = 'small mt-1 phone-country-ajax-feedback';
            feedback.setAttribute('aria-live', 'polite');
            var row = phoneEl.closest('.row');
            if (row && row.parentNode) {
                row.parentNode.insertBefore(feedback, row.nextSibling);
            } else {
                phoneEl.parentNode.appendChild(feedback);
            }
        }

        var reqId = 0;

        function runLive() {
            var id = ++reqId;
            var cc = (countryEl.value || '').trim();
            var ph = (phoneEl.value || '').trim();
            if (cc === '' && ph === '') {
                setFeedback(feedback, '', null);
                return;
            }
            setFeedback(feedback, 'جاري التحقق…', 'loading');
            validateJson(countryEl, phoneEl, function (err, data) {
                if (id !== reqId) return;
                if (err) {
                    setFeedback(feedback, 'تعذر التحقق من الرقم.', 'err');
                    return;
                }
                if (data.valid) {
                    setFeedback(feedback, data.message || '', data.message ? 'ok' : null);
                } else {
                    setFeedback(feedback, data.message || 'رقم غير صالح.', 'err');
                }
            });
        }

        var debounced = debounce(runLive, 400);
        phoneEl.addEventListener('input', debounced);
        phoneEl.addEventListener('blur', function () {
            runLive();
        });
        countryEl.addEventListener('change', function () {
            runLive();
        });

        if (typeof jQuery !== 'undefined') {
            var $c = jQuery(countryEl);
            if ($c.data('select2')) {
                $c.on('change select2:select', function () {
                    runLive();
                });
            }
        }

        form.addEventListener('submit', function (e) {
            if (form.getAttribute('data-phone-ajax-bypass') === '1') {
                form.removeAttribute('data-phone-ajax-bypass');
                return;
            }
            var cc = (countryEl.value || '').trim();
            var ph = (phoneEl.value || '').trim();
            if (cc === '' && ph === '') {
                return;
            }
            e.preventDefault();
            setFeedback(feedback, 'جاري التحقق…', 'loading');
            validateJson(countryEl, phoneEl, function (err, data) {
                if (err) {
                    setFeedback(feedback, 'تعذر التحقق من الرقم.', 'err');
                    alert('تعذر التحقق من الرقم. حاول مرة أخرى.');
                    return;
                }
                if (data.valid) {
                    if (typeof form.requestSubmit === 'function') {
                        form.setAttribute('data-phone-ajax-bypass', '1');
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                } else {
                    setFeedback(feedback, data.message || 'رقم غير صالح.', 'err');
                    alert(data.message || 'تأكد من تطابق رقم الجوال مع رمز الدولة.');
                }
            });
        });
    }

    function boot() {
        document.querySelectorAll('form[data-phone-ajax-validate]').forEach(bindForm);
    }

    function scheduleBoot() {
        function run() {
            setTimeout(boot, 0);
        }
        if (typeof jQuery !== 'undefined') {
            jQuery(run);
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run();
        }
    }

    scheduleBoot();
})();
</script>
