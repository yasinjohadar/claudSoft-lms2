{{--
    Live batch poller shared by the create page and the batch detail page.

    Exposes window.DocAiBatch with attach()/startPolling()/renderBatch() so the
    host page only has to hand it a uuid (or an initial payload) and, on the
    create page, wire the submit button.
--}}
<script>
window.DocAiBatch = (function () {
    const statusUrlBase    = @json(route('admin.docs.ai-pages.batch.status',       ['uuid' => '__UUID__']));
    const cancelUrlBase    = @json(route('admin.docs.ai-pages.batch.cancel',       ['uuid' => '__UUID__']));
    const restartUrlBase   = @json(route('admin.docs.ai-pages.batch.restart',      ['uuid' => '__UUID__']));
    const resumeAllUrlBase = @json(route('admin.docs.ai-pages.batch.resume-all',   ['uuid' => '__UUID__']));
    const resumeUrlBase    = @json(route('admin.docs.ai-pages.batch.items.resume', ['uuid' => '__UUID__', 'item' => '__ITEM__']));
    const releaseUrlBase   = @json(route('admin.docs.ai-pages.batch.items.release',['uuid' => '__UUID__', 'item' => '__ITEM__']));
    const csrfToken        = @json(csrf_token());

    const STORAGE_KEY = 'docAiBatchUuid';
    const POLL_MS = 3000;

    let pollTimer = null;
    let currentUuid = null;
    let stopStreak = 0;
    // Previous status per item, so a transition into "completed" can be
    // announced once instead of on every tick.
    let lastStatuses = {};
    let mirrorUrl = true;

    function url(base, uuid, itemId) {
        let out = base.replace('__UUID__', encodeURIComponent(uuid));
        if (itemId !== undefined) out = out.replace('__ITEM__', encodeURIComponent(itemId));
        return out;
    }

    function toast(opts) {
        if (typeof Swal === 'undefined') {
            if (opts.icon === 'error') alert(opts.title + (opts.text ? '\n' + opts.text : ''));
            return;
        }
        Swal.fire(Object.assign({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
        }, opts));
    }

    function notify(message, icon) {
        if (!message) return;
        toast({ icon: icon || 'success', title: message });
    }

    function remember(uuid) {
        currentUuid = uuid;
        try { sessionStorage.setItem(STORAGE_KEY, uuid); } catch (e) {}
        if (!mirrorUrl) return;
        // Keep the uuid in the address bar so F5, bookmarking and the back
        // button all land on this same batch.
        try {
            const u = new URL(window.location.href);
            if (u.searchParams.get('batch') !== uuid) {
                u.searchParams.set('batch', uuid);
                window.history.replaceState({}, '', u.toString());
            }
        } catch (e) {}
    }

    function forget() {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
    }

    function badge(item) {
        let key = item.status;
        let label;

        if (item.status === 'failed') {
            key = item.is_incomplete ? 'incomplete' : 'failed';
            label = item.is_incomplete ? 'غير مكتملة' : 'فشلت';
        } else if (item.status === 'running' && item.is_stuck) {
            key = 'stuck';
            label = 'عالقة — لا استجابة';
        } else {
            label = {
                pending: 'قيد الانتظار',
                running: 'جاري التوليد…',
                resume_queued: 'بانتظار المتابعة',
                completed: 'مكتملة',
                skipped: 'متجاهَل',
            }[item.status] || item.status;
        }

        const el = document.createElement('span');
        el.className = 'doc-ai-batch-badge doc-ai-batch-badge--' + key;
        el.textContent = label;
        return el;
    }

    /** Progress block: an animated bar while working, a static one when stopped. */
    function progressBlock(item) {
        const active = item.status === 'running' || item.status === 'resume_queued';
        const hasSections = item.sections && item.sections.planned;

        if (!active && !hasSections) return null;

        const wrap = document.createElement('div');
        wrap.className = 'mt-2';

        const label = document.createElement('div');
        label.className = 'small text-muted mb-1';

        let text;
        if (active) {
            text = item.stage_label || 'جاري التوليد…';
            if (hasSections) {
                text += ' — تم توليد ' + item.sections.done + ' من ' + item.sections.planned + ' قسماً';
            }
        } else {
            text = item.progress_hint
                || ('تم توليد ' + item.sections.done + ' من ' + item.sections.planned + ' قسماً وحُفظت');
        }
        label.textContent = text;
        wrap.appendChild(label);

        const outer = document.createElement('div');
        outer.className = 'progress';
        outer.style.height = '6px';

        const inner = document.createElement('div');
        // While working, the bar tracks the pipeline's own percentage; once
        // stopped it shows how much of the page is already written and saved.
        const pct = active
            ? (item.progress || 0)
            : (item.sections_progress !== null && item.sections_progress !== undefined ? item.sections_progress : 0);
        inner.className = active
            ? 'progress-bar progress-bar-striped progress-bar-animated bg-success'
            : 'progress-bar bg-warning';
        inner.style.width = Math.max(0, Math.min(100, pct)) + '%';
        outer.appendChild(inner);
        wrap.appendChild(outer);

        if (!active && item.sections && item.sections.failed_headings && item.sections.failed_headings.length) {
            const heads = document.createElement('div');
            heads.className = 'doc-ai-sec-heads';
            const title = document.createElement('span');
            title.className = 'small text-muted';
            title.textContent = 'الأقسام الناقصة:';
            heads.appendChild(title);
            item.sections.failed_headings.forEach(function (h) {
                const chip = document.createElement('span');
                chip.className = 'doc-ai-sec-head';
                chip.textContent = h;
                heads.appendChild(chip);
            });
            wrap.appendChild(heads);
        }

        return wrap;
    }

    function actionButton(text, cls, icon, onClick) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm ' + cls + ' mt-2 me-1';
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1 d-none"></span>'
            + '<i class="fe ' + icon + ' me-1"></i><span class="btn-text"></span>';
        btn.querySelector('.btn-text').textContent = text;
        btn.addEventListener('click', function () { onClick(btn); });
        return btn;
    }

    function setBusy(btn, busy, busyText) {
        if (!btn) return;
        btn.disabled = busy;
        const sp = btn.querySelector('.spinner-border');
        if (sp) sp.classList.toggle('d-none', !busy);
        const label = btn.querySelector('.btn-text');
        if (label && busy && busyText) label.textContent = busyText;
    }

    function renderItems(batch) {
        const body = document.getElementById('batchItemsBody');
        if (!body) return;
        body.innerHTML = '';

        (batch.items || []).forEach(function (item) {
            const tr = document.createElement('tr');
            // Lets the detail page's "incomplete only" switch filter with CSS alone.
            tr.dataset.incomplete = item.is_incomplete ? '1' : '0';

            const num = document.createElement('td');
            num.textContent = item.position + 1;
            tr.appendChild(num);

            const topic = document.createElement('td');
            const topicText = document.createElement('div');
            topicText.className = 'doc-ai-item-topic';
            topicText.textContent = item.topic;
            topicText.title = item.topic;
            topic.appendChild(topicText);
            tr.appendChild(topic);

            const status = document.createElement('td');
            status.appendChild(badge(item));

            const bar = progressBlock(item);
            if (bar) status.appendChild(bar);

            if (item.status === 'failed' && item.error_message) {
                const msg = document.createElement('div');
                msg.className = 'text-danger small mt-1';
                msg.textContent = item.error_message;
                status.appendChild(msg);
            }

            // Announce a finish inline as well as via the toast, so the result
            // is still visible after the toast has gone.
            const prev = lastStatuses[item.id];
            if (item.status === 'completed' && prev && prev !== 'completed') {
                const done = document.createElement('div');
                done.className = 'doc-ai-item-done';
                done.textContent = '✔ اكتمل التوليد — حُفظت الصفحة';
                status.appendChild(done);
            }

            if (item.status === 'failed' && item.resumable) {
                status.appendChild(actionButton('متابعة التوليد', 'btn-primary', 'fe-play-circle', function (btn) {
                    setBusy(btn, true, 'جارٍ الإضافة…');
                    post(url(resumeUrlBase, currentUuid, item.id), btn);
                }));
            }

            if (item.can_release) {
                status.appendChild(actionButton('تحرير العنصر العالق', 'btn-outline-warning', 'fe-unlock', function (btn) {
                    if (!confirm('تحرير هذا الموضوع؟ افعل ذلك فقط إذا كنت متأكداً أن معالج الطابور توقف — وإلا قد يستمر التوليد في الخلفية.')) return;
                    setBusy(btn, true, 'جارٍ التحرير…');
                    post(url(releaseUrlBase, currentUuid, item.id), btn);
                }));
            }

            tr.appendChild(status);

            const actions = document.createElement('td');
            if (item.edit_url) {
                const a = document.createElement('a');
                a.href = item.edit_url;
                a.target = '_blank';
                a.className = 'btn btn-sm btn-outline-secondary';
                a.textContent = 'فتح الصفحة';
                actions.appendChild(a);
            }
            tr.appendChild(actions);

            body.appendChild(tr);
        });

        const next = {};
        (batch.items || []).forEach(function (i) { next[i.id] = i.status; });
        announceTransitions(batch, next);
        lastStatuses = next;
    }

    function announceTransitions(batch, next) {
        if (!Object.keys(lastStatuses).length) return;

        (batch.items || []).forEach(function (item) {
            const prev = lastStatuses[item.id];
            if (!prev || prev === item.status) return;

            if (item.status === 'completed') {
                toast({
                    icon: 'success',
                    title: 'اكتمل الموضوع',
                    text: 'حُفظت صفحة «' + String(item.topic).slice(0, 60) + '».',
                });
            } else if (item.status === 'failed' && prev === 'running') {
                toast({
                    icon: item.is_incomplete ? 'warning' : 'error',
                    title: item.is_incomplete ? 'توقّف قبل الاكتمال' : 'تعذّر التوليد',
                    text: item.error_message || '',
                    timer: 7000,
                });
            }
        });
    }

    function stat(text, kind) {
        const el = document.createElement('span');
        el.className = 'doc-ai-batch-stat' + (kind ? ' doc-ai-batch-stat--' + kind : '');
        el.textContent = text;
        return el;
    }

    function renderSummary(batch) {
        const box = document.getElementById('batchSummary');
        if (!box) return;
        box.innerHTML = '';
        box.appendChild(stat('الإجمالي: ' + batch.total));
        box.appendChild(stat('مكتملة: ' + batch.completed, 'done'));
        if (batch.incomplete) box.appendChild(stat('غير مكتملة: ' + batch.incomplete, 'incomplete'));
        const plainFailed = batch.failed - batch.incomplete;
        if (plainFailed > 0) box.appendChild(stat('فشلت: ' + plainFailed));
        if (batch.resume_queued) box.appendChild(stat('بانتظار المتابعة: ' + batch.resume_queued, 'queued'));
    }

    function renderBatch(batch) {
        const idle = document.getElementById('batchIdleMsg');
        const wrap = document.getElementById('batchProgressWrap');
        if (idle) idle.style.display = 'none';
        if (wrap) wrap.style.display = '';

        const done = (batch.completed || 0) + (batch.failed || 0);
        const pct = batch.total ? Math.round((done / batch.total) * 100) : 0;
        const bar = document.getElementById('batchProgressBar');
        if (bar) bar.style.width = pct + '%';

        const labelMap = {
            queued: 'في الطابور…',
            running: 'جاري المعالجة… (' + done + ' من ' + batch.total + ')',
            completed: 'اكتملت الدفعة بنجاح (' + batch.total + ' من ' + batch.total + ')',
            completed_with_errors: 'اكتملت الدفعة مع ' + batch.failed + ' موضوعاً غير مكتمل من ' + batch.total,
            cancelled: 'أُلغيت الدفعة',
        };
        const label = document.getElementById('batchProgressLabel');
        if (label) label.textContent = labelMap[batch.status] || batch.status;

        const cancelBtn = document.getElementById('batchCancelBtn');
        if (cancelBtn) cancelBtn.style.display = batch.finished ? 'none' : '';

        const resumeAllBtn = document.getElementById('batchResumeAllBtn');
        if (resumeAllBtn) {
            const n = batch.incomplete || 0;
            resumeAllBtn.classList.toggle('d-none', n === 0);
            const t = resumeAllBtn.querySelector('.btn-text');
            if (t && !resumeAllBtn.disabled) t.textContent = 'متابعة كل الناقص (' + n + ')';
        }

        const stalled = document.getElementById('batchStalledAlert');
        if (stalled) stalled.classList.toggle('d-none', !batch.stalled);

        renderSummary(batch);
        renderItems(batch);

        // Require the stop condition to hold twice. A freshly queued resume can
        // momentarily look finished if the worker has not claimed it yet, and
        // stopping there is exactly what used to make resume look like a no-op.
        if (batch.finished && !batch.any_running) {
            stopStreak++;
            if (stopStreak >= 2) stopPolling();
        } else {
            stopStreak = 0;
        }
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function tick() {
        if (!currentUuid) return;
        fetch(url(statusUrlBase, currentUuid), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) { if (res.success && res.batch) renderBatch(res.batch); })
            .catch(function () { /* transient blip: the next tick retries */ });
    }

    function startPolling(uuid) {
        remember(uuid);
        stopStreak = 0;
        stopPolling();
        tick();
        pollTimer = setInterval(tick, POLL_MS);
    }

    /** POST an action, render the batch it returns, and keep polling alive. */
    function post(actionUrl, btn) {
        return fetch(actionUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (res.body.success && res.body.batch) {
                    notify(res.body.message);
                    stopStreak = 0;
                    renderBatch(res.body.batch);
                    // The response already reports the topic as queued and the
                    // batch as running, so restarting the loop here is safe.
                    if (!pollTimer) startPolling(currentUuid);
                    return res.body;
                }
                setBusy(btn, false);
                toast({ icon: 'error', title: 'تعذّر تنفيذ الطلب', text: res.body.message || '', timer: 7000 });
                return null;
            })
            .catch(function () {
                setBusy(btn, false);
                toast({ icon: 'error', title: 'تعذّر الاتصال', text: 'تحقق من الاتصال ثم أعد المحاولة.' });
                return null;
            });
    }

    function wireControls() {
        const cancelBtn = document.getElementById('batchCancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                if (!currentUuid) return;
                if (!confirm('إلغاء الدفعة؟ المواضيع التي لم تبدأ بعد لن تُعالَج.')) return;
                post(url(cancelUrlBase, currentUuid), cancelBtn).then(function (body) {
                    if (body) forget();
                });
            });
        }

        const resumeAllBtn = document.getElementById('batchResumeAllBtn');
        if (resumeAllBtn) {
            resumeAllBtn.addEventListener('click', function () {
                if (!currentUuid) return;
                setBusy(resumeAllBtn, true, 'جارٍ الإضافة…');
                post(url(resumeAllUrlBase, currentUuid), resumeAllBtn).then(function () {
                    setBusy(resumeAllBtn, false);
                });
            });
        }

        const restartBtn = document.getElementById('batchRestartBtn');
        if (restartBtn) {
            restartBtn.addEventListener('click', function () {
                if (!currentUuid) return;
                setBusy(restartBtn, true);
                post(url(restartUrlBase, currentUuid), restartBtn).then(function () {
                    setBusy(restartBtn, false);
                });
            });
        }
    }

    /**
     * @param {object} opts
     * @param {object|null} opts.initialBatch server-rendered payload, if any
     * @param {boolean} [opts.mirrorUrl] keep ?batch=uuid in the address bar
     */
    function attach(opts) {
        opts = opts || {};
        if (opts.mirrorUrl === false) mirrorUrl = false;
        wireControls();

        const boot = opts.initialBatch;
        if (boot && boot.uuid) {
            currentUuid = boot.uuid;
            remember(boot.uuid);
            // Seed the status map first so restoring a finished batch does not
            // fire "just completed" toasts for work that ended hours ago.
            (boot.items || []).forEach(function (i) { lastStatuses[i.id] = i.status; });
            renderBatch(boot);
            if (!boot.finished || boot.any_running) startPolling(boot.uuid);
            return;
        }

        // Nothing from the server: fall back to a uuid remembered in this tab.
        let hinted = null;
        try { hinted = sessionStorage.getItem(STORAGE_KEY); } catch (e) {}
        if (hinted) startPolling(hinted);
    }

    return {
        attach: attach,
        startPolling: startPolling,
        renderBatch: renderBatch,
        remember: remember,
        forget: forget,
        notify: notify,
        toast: toast,
        resetHistory: function () { lastStatuses = {}; },
    };
})();
</script>
