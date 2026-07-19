{{-- Shared async documentation AI job polling --}}
<script>
window.DocAiJobPoller = (function () {
    const statusUrlTemplate = @json(route('admin.docs.ai-pages.jobs.show', ['uuid' => '__UUID__']));

    function statusUrl(uuid) {
        return statusUrlTemplate.replace('__UUID__', encodeURIComponent(uuid));
    }

    /**
     * @param {object} opts
     * @param {string} opts.uuid
     * @param {string} opts.storageKey
     * @param {function(object): void} opts.onProgress
     * @param {function(object): void} opts.onComplete  receives job.result
     * @param {function(string): void} opts.onError
     * @param {number} [opts.intervalMs]
     */
    function poll(opts) {
        const uuid = opts.uuid;
        const storageKey = opts.storageKey || null;
        const intervalMs = opts.intervalMs || 2000;
        let stopped = false;
        let timer = null;

        if (storageKey) {
            try { sessionStorage.setItem(storageKey, uuid); } catch (e) {}
        }

        function finishStorage() {
            if (storageKey) {
                try { sessionStorage.removeItem(storageKey); } catch (e) {}
            }
        }

        function tick() {
            if (stopped) return;
            fetch(statusUrl(uuid), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    if (stopped) return;
                    const job = (data && data.job) ? data.job : null;
                    if (!job) throw new Error('استجابة غير صالحة');

                    if (typeof opts.onProgress === 'function') {
                        opts.onProgress(job);
                    }

                    if (job.status === 'completed') {
                        stopped = true;
                        finishStorage();
                        if (typeof opts.onComplete === 'function') {
                            opts.onComplete(job.result || {});
                        }
                        return;
                    }

                    if (job.status === 'failed' || job.status === 'cancelled') {
                        stopped = true;
                        finishStorage();
                        if (typeof opts.onError === 'function') {
                            opts.onError(job.error_message || 'فشلت المهمة');
                        }
                        return;
                    }

                    // queued with queue_hint: still poll — worker may pick it up
                    timer = setTimeout(tick, intervalMs);
                })
                .catch(function (err) {
                    if (stopped) return;
                    // transient network blip: retry a few times via interval
                    timer = setTimeout(tick, Math.max(intervalMs, 3000));
                    if (typeof opts.onProgress === 'function') {
                        opts.onProgress({
                            status: 'running',
                            progress: opts._lastProgress || 0,
                            stage_label: 'إعادة الاتصال…',
                            _poll_error: String(err && err.message ? err.message : err)
                        });
                    }
                });
        }

        tick();

        return {
            stop: function () {
                stopped = true;
                if (timer) clearTimeout(timer);
            }
        };
    }

    function resumeIfAny(storageKey, handlers) {
        let uuid = null;
        try { uuid = sessionStorage.getItem(storageKey); } catch (e) {}
        if (!uuid) return null;
        return poll(Object.assign({ uuid: uuid, storageKey: storageKey }, handlers));
    }

    return { poll: poll, resumeIfAny: resumeIfAny, statusUrl: statusUrl };
})();
</script>
