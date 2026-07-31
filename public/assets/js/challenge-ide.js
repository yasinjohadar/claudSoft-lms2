(function () {
    'use strict';

    const editors = {};
    let config = null;
    let activeTab = 0;
    let autoSaveTimer = null;
    let previewDebounce = null;
    let initialized = false;
    let resizeObserver = null;

    function init(cfg) {
        if (initialized || !cfg) return;
        initialized = true;
        config = cfg;
        if (!config.files || !config.files.length) {
            config.files = defaultWebFiles();
        }
        buildTabs();
        (config.files || []).forEach(function (_file, i) {
            createTextEditorForTab(i);
        });
        updateActiveFilename(0);
        updatePreviewFromConfig();
        bindToolbar();
        bindLayoutToggle();
        bindResizer();
        observeResize();
        startAutoSave();
        window.addEventListener('resize', layoutAllEditors);
        enhanceWithCodeMirror();
    }

    function tabDotColor(kind) {
        if (kind === 'html') return '#E34F26';
        if (kind === 'css') return '#1572B6';
        if (kind === 'js') return '#B8A000';
        if (kind === 'py') return '#3776AB';
        return '#64748b';
    }

    function updateActiveFilename(index) {
        const file = (config.files || [])[index];
        const el = document.getElementById('challenge-active-filename');
        if (!el) return;
        el.textContent = file ? (file.filename || file.tab_label || 'file') : '—';
    }

    function defaultWebFiles() {
        return [
            { file_role: 'html', filename: 'index.html', content: '<h1>مرحباً</h1>\n<p>ابدأ التعديل هنا</p>', monaco_language_id: 'html', tab_label: 'HTML' },
            { file_role: 'css', filename: 'style.css', content: 'body { font-family: sans-serif; padding: 1rem; }', monaco_language_id: 'css', tab_label: 'CSS' },
            { file_role: 'js', filename: 'script.js', content: '', monaco_language_id: 'javascript', tab_label: 'JS' },
        ];
    }

    function brandIconSvg(kind) {
        if (kind === 'html') {
            return '<svg class="challenge-ide__brand-icon" viewBox="0 0 128 128" aria-hidden="true" focusable="false">' +
                '<path fill="#E44D26" d="M19 0l9 103 36 10 36-10 9-103z"/>' +
                '<path fill="#F16529" d="M64 117l29-8 8-88H64z"/>' +
                '<path fill="#EBEBEB" d="M64 52H45l-1-14h20V25H30l4 45h30zm0 40l-.1.1-15.4-4.1-1-11H34l2 23 28 7.7.1-.1z"/>' +
                '<path fill="#FFF" d="M64 52v13h16.8l-1.6 18-15.2 4.1V100l28-7.7 2-33.3.4-4.5L97 25H64z"/></svg>';
        }
        if (kind === 'css') {
            return '<svg class="challenge-ide__brand-icon" viewBox="0 0 128 128" aria-hidden="true" focusable="false">' +
                '<path fill="#1572B6" d="M19 0l9 103 36 10 36-10 9-103z"/>' +
                '<path fill="#33A9DC" d="M64 117l29-8 8-88H64z"/>' +
                '<path fill="#EBEBEB" d="M64 52H45.5l-1.2-14H64V25H31.2l.5 6 3.2 36H64zm-.1 40.1l-.1.1-15.3-4.1-.9-11H34l1.7 22.5 28.1 7.8.1-.1z"/>' +
                '<path fill="#FFF" d="M64 52v13h16.9l-1.6 17.9-15.3 4.1v13.1l28.1-7.8 2.1-33.8.3-4.5L97 25H64v13h32.3z"/></svg>';
        }
        if (kind === 'js') {
            return '<svg class="challenge-ide__brand-icon" viewBox="0 0 128 128" aria-hidden="true" focusable="false">' +
                '<path fill="#F7DF1E" d="M2 2h124v124H2z"/>' +
                '<path d="M67.3 97.4c2 3.4 4.6 5.9 9.3 5.9 3.9 0 6.4-2 6.4-4.7 0-3.3-2.6-4.4-6.9-6.3l-2.4-1c-6.9-2.9-11.5-6.6-11.5-14.3 0-7.1 5.4-12.6 13.9-12.6 6 0 10.4 2.1 13.5 7.6l-7.4 4.7c-1.6-2.9-3.4-4-6.1-4-2.8 0-4.5 1.8-4.5 4 0 2.8 1.7 3.9 5.7 5.6l2.4 1c8.1 3.5 12.7 7 12.7 15 0 8.6-6.7 13.3-15.7 13.3-8.8 0-14.5-4.2-17.3-9.7zm-29 1.5c1.5 2.7 2.9 4.9 6.2 4.9 3.2 0 5.2-1.2 5.2-6.1V61.3h9.3v36.6c0 9.6-5.6 14-13.8 14-7.4 0-11.7-3.8-13.9-8.5z"/></svg>';
        }
        if (kind === 'py') {
            return '<svg class="challenge-ide__brand-icon" viewBox="0 0 128 128" aria-hidden="true" focusable="false">' +
                '<path fill="#3776AB" d="M63.4 16c-17 0-15.9 7.4-15.9 7.4l.01 7.7h16.2v2.3H35S22 32.2 22 58.7s12.5 25.6 12.5 25.6h7.5V69.7s-.4-12.5 12.3-12.5h21.2s11.9.2 11.9-11.5V24.9S89.6 16 63.4 16zm-9.3 6.8a3.3 3.3 0 110 6.6 3.3 3.3 0 010-6.6z"/>' +
                '<path fill="#FFD43B" d="M64.6 112c17 0 15.9-7.4 15.9-7.4l-.01-7.7H64.3v-2.3H93s13 1.2 13-25.3-12.5-25.6-12.5-25.6h-7.5v14.6s.4 12.5-12.3 12.5H52.5S40.6 70.6 40.6 82.3v20.8S38.4 112 64.6 112zm9.3-6.8a3.3 3.3 0 110-6.6 3.3 3.3 0 010 6.6z"/></svg>';
        }
        return '<svg class="challenge-ide__brand-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="#9cdcfe" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zm0 2l4 4h-4z"/></svg>';
    }

    function tabMeta(file) {
        const role = String(file.file_role || file.monaco_language_id || '').toLowerCase();
        const label = file.tab_label || file.filename || file.file_role || 'File';
        if (role === 'html' || role === 'xml' || (file.filename || '').endsWith('.html')) {
            return { label: label, kind: 'html' };
        }
        if (role === 'css' || (file.filename || '').endsWith('.css')) {
            return { label: label, kind: 'css' };
        }
        if (role === 'js' || role === 'javascript' || role === 'typescript' || (file.filename || '').endsWith('.js')) {
            return { label: label, kind: 'js' };
        }
        if (role === 'python' || role === 'py') {
            return { label: label, kind: 'py' };
        }
        return { label: label, kind: 'file' };
    }

    function buildTabs() {
        const tabsEl = document.getElementById('challenge-editor-tabs');
        const editorsEl = document.getElementById('challenge-editors');
        if (!tabsEl || !editorsEl) return;

        tabsEl.innerHTML = '';
        editorsEl.innerHTML = '';

        (config.files || []).forEach(function (file, i) {
            const tabId = 'challenge-tab-' + i;
            const meta = tabMeta(file);
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML =
                '<button class="nav-link challenge-ide__tab challenge-ide__tab--' + meta.kind +
                (i === 0 ? ' active' : '') + '" type="button" data-tab="' + i + '" title="' +
                (file.filename || meta.label) + '">' +
                '<span class="challenge-ide__tab-dot" style="background:' + tabDotColor(meta.kind) + '"></span>' +
                '<span class="challenge-ide__tab-label">' + meta.label + '</span>' +
                '</button>';
            tabsEl.appendChild(li);

            const pane = document.createElement('div');
            pane.className = 'challenge-ide__editor-pane' + (i === 0 ? ' active' : '');
            pane.id = tabId;
            pane.innerHTML = '<div class="challenge-ide__monaco" data-index="' + i + '"></div>';
            editorsEl.appendChild(pane);
        });

        tabsEl.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-tab]');
            if (!btn) return;
            switchTab(parseInt(btn.dataset.tab, 10));
        });
    }

    function switchTab(index) {
        activeTab = index;
        document.querySelectorAll('#challenge-editor-tabs .nav-link').forEach(function (el, i) {
            el.classList.toggle('active', i === index);
        });
        document.querySelectorAll('.challenge-ide__editor-pane').forEach(function (el, i) {
            el.classList.toggle('active', i === index);
        });
        updateActiveFilename(index);
        if (editors[index] && typeof editors[index].focus === 'function') {
            setTimeout(function () { editors[index].focus(); }, 30);
        }
        setTimeout(layoutAllEditors, 50);
    }

    function getEditorContainer(index) {
        return document.querySelector('.challenge-ide__monaco[data-index="' + index + '"]');
    }

    function createTextEditorForTab(index) {
        if (editors[index]) return;

        const file = config.files[index];
        const container = getEditorContainer(index);
        if (!container || !file) return;

        container.setAttribute('dir', 'ltr');
        container.style.direction = 'ltr';

        const ta = document.createElement('textarea');
        ta.className = 'challenge-ide__code-editor';
        ta.value = file.content || '';
        ta.spellcheck = false;
        ta.setAttribute('autocomplete', 'off');
        ta.setAttribute('autocapitalize', 'off');
        ta.setAttribute('autocorrect', 'off');
        ta.setAttribute('wrap', 'off');
        ta.setAttribute('aria-label', file.tab_label || file.filename || 'editor');
        ta.addEventListener('input', schedulePreview);
        ta.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            e.preventDefault();
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            ta.value = ta.value.slice(0, start) + '  ' + ta.value.slice(end);
            ta.selectionStart = ta.selectionEnd = start + 2;
            schedulePreview();
        });

        container.innerHTML = '';
        container.appendChild(ta);

        editors[index] = {
            type: 'textarea',
            getValue: function () { return ta.value; },
            setValue: function (v) { ta.value = v; },
            layout: function () {},
            focus: function () { ta.focus(); },
        };

        if (index === activeTab) {
            setTimeout(function () { ta.focus(); }, 50);
        }
    }

    function enhanceWithCodeMirror() {
        function apply(createFn) {
            if (typeof createFn !== 'function') return false;
            (config.files || []).forEach(function (_file, i) {
                upgradeTabToCodeMirror(i, createFn);
            });
            layoutAllEditors();
            if (editors[activeTab] && typeof editors[activeTab].focus === 'function') {
                editors[activeTab].focus();
            }
            return true;
        }

        if (apply(window.__challengeCreateCodeMirror)) return;

        window.addEventListener('challenge-codemirror-ready', function onReady() {
            window.removeEventListener('challenge-codemirror-ready', onReady);
            apply(window.__challengeCreateCodeMirror);
        }, { once: true });

        setTimeout(function () {
            apply(window.__challengeCreateCodeMirror);
        }, 0);
    }

    function upgradeTabToCodeMirror(index, createCodeMirrorEditor) {
        const current = editors[index];
        if (!current || current.type === 'codemirror') return;

        const file = config.files[index];
        const container = getEditorContainer(index);
        if (!container || !file) return;

        const value = current.getValue();
        container.innerHTML = '';
        container.setAttribute('dir', 'ltr');
        container.style.direction = 'ltr';

        try {
            const cm = createCodeMirrorEditor(container, {
                doc: value,
                language: file.monaco_language_id || file.file_role || 'plaintext',
                onChange: schedulePreview,
            });

            editors[index] = {
                type: 'codemirror',
                getValue: cm.getValue,
                setValue: cm.setValue,
                layout: cm.layout,
                focus: cm.focus,
                destroy: cm.destroy,
            };
        } catch (err) {
            console.warn('CodeMirror upgrade failed for tab', index, err);
            delete editors[index];
            createTextEditorForTab(index);
            if (editors[index]) editors[index].setValue(value);
        }
    }

    function layoutAllEditors() {
        Object.values(editors).forEach(function (ed) {
            if (ed && typeof ed.layout === 'function') {
                ed.layout();
            }
        });
    }

    function observeResize() {
        const workspace = document.querySelector('.challenge-ide__workspace');
        if (!workspace || typeof ResizeObserver === 'undefined') return;

        if (resizeObserver) resizeObserver.disconnect();
        resizeObserver = new ResizeObserver(function () {
            layoutAllEditors();
        });
        resizeObserver.observe(workspace);
    }

    function getFilesPayload() {
        return (config.files || []).map(function (file, i) {
            return {
                file_role: file.file_role,
                filename: file.filename,
                content: editors[i] ? editors[i].getValue() : (file.content || ''),
                programming_language_id: file.programming_language_id || null,
            };
        });
    }

    function extractWebParts(files) {
        let html = '', css = '', js = '';
        files.forEach(function (f) {
            const role = (f.file_role || '').toLowerCase();
            const name = (f.filename || '').toLowerCase();
            if (role === 'html' || name.endsWith('.html')) html = f.content || '';
            else if (role === 'css' || name.endsWith('.css')) css = f.content || '';
            else if (role === 'js' || role === 'javascript' || name.endsWith('.js')) js = f.content || '';
        });
        return { html: html, css: css, js: js };
    }

    function buildSrcdoc(parts) {
        const html = (parts.html || '').trim();
        const css = parts.css || '';
        const js = parts.js || '';

        if (/^\s*<!DOCTYPE/i.test(html) || /^\s*<html/i.test(html)) {
            let doc = html;
            if (css) {
                if (/<\/head>/i.test(doc)) {
                    doc = doc.replace(/<\/head>/i, '<style>' + css + '</style></head>');
                } else {
                    doc = doc.replace(/<html[^>]*>/i, function (m) { return m + '<head><style>' + css + '</style></head>'; });
                }
            }
            if (js) {
                if (/<\/body>/i.test(doc)) {
                    doc = doc.replace(/<\/body>/i, '<script>' + js + '<\/script></body>');
                } else {
                    doc += '<script>' + js + '<\/script>';
                }
            }
            return doc;
        }

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>' +
            css + '</style></head><body>' + html +
            '<script>' + js + '<\/script></body></html>';
    }

    function updatePreviewFromConfig() {
        if (config.challengeType !== 'web_sandbox') return;
        const parts = extractWebParts(getFilesPayload());
        const frame = document.getElementById('challenge-preview-frame');
        if (frame) frame.srcdoc = buildSrcdoc(parts);
    }

    function openPreviewInNewTab() {
        if (config.challengeType !== 'web_sandbox') return;

        const html = buildSrcdoc(extractWebParts(getFilesPayload()));
        const storeUrl = config.previewStoreUrl;

        if (!storeUrl) {
            alert('رابط حفظ المعاينة غير متاح.');
            return;
        }

        // Open immediately to keep the user-gesture (avoids popup blockers).
        const win = window.open('about:blank', '_blank');
        if (!win) {
            alert('تعذر فتح التاب الجديد. تحقق من إعدادات مانع النوافذ المنبثقة.');
            return;
        }

        try {
            win.document.open();
            win.document.write(
                '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8">' +
                '<title>جاري تجهيز المعاينة</title>' +
                '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;' +
                'background:#0f172a;color:#e2e8f0;font-family:system-ui,sans-serif}</style></head>' +
                '<body><p>جاري تجهيز المعاينة…</p></body></html>'
            );
            win.document.close();
        } catch (e) { /* ignore */ }

        fetch(storeUrl, {
            method: 'POST',
            headers: buildRequestHeaders(),
            credentials: 'same-origin',
            body: JSON.stringify({ html: html }),
        })
            .then(function (r) { return parseJsonResponse(r); })
            .then(function (res) {
                if (isCsrfFailure(res)) {
                    return refreshCsrfToken().then(function () {
                        return fetch(storeUrl, {
                            method: 'POST',
                            headers: buildRequestHeaders(),
                            credentials: 'same-origin',
                            body: JSON.stringify({ html: html }),
                        }).then(parseJsonResponse);
                    });
                }
                return res;
            })
            .then(function (res) {
                if (!res.ok || !res.data.url) {
                    throw new Error(csrfFriendlyMessage(res) || 'فشل حفظ المعاينة');
                }
                win.location = res.data.url;
            })
            .catch(function (err) {
                try { win.close(); } catch (e) { /* ignore */ }
                alert(err.message || 'تعذر إنشاء رابط المعاينة الفريد.');
            });
    }

    function schedulePreview() {
        if (config.challengeType !== 'web_sandbox') return;
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(updatePreviewFromConfig, 250);
    }

    function setSaveStatus(text, cls) {
        const el = document.getElementById('challenge-save-status');
        if (!el) return;
        el.textContent = text;
        el.className = 'challenge-ide__save-status ' + (cls || '');
    }

    function setWorkspaceLayout(mode) {
        const app = document.getElementById('challenge-ide-app');
        if (!app) return;
        const next = mode === 'bottom' ? 'bottom' : 'side';
        app.setAttribute('data-layout', next);
        document.querySelectorAll('[data-layout-set]').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-layout-set') === next);
        });
        const editorPanel = document.querySelector('.challenge-ide__editor-panel');
        if (editorPanel) editorPanel.style.flex = '';
        setTimeout(layoutAllEditors, 40);
    }

    function bindLayoutToggle() {
        document.querySelectorAll('[data-layout-set]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setWorkspaceLayout(btn.getAttribute('data-layout-set'));
            });
        });
    }

    function readCookie(name) {
        const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1');
        const match = document.cookie.match(new RegExp('(?:^|; )' + escaped + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        return (config && config.csrf) || '';
    }

    function setCsrfToken(token) {
        if (!token) return;
        if (config) config.csrf = token;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', token);
    }

    function buildRequestHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        const csrf = getCsrfToken();
        if (csrf) headers['X-CSRF-TOKEN'] = csrf;
        // Prefer cookie token — stays in sync with the active session longer than a page-load snapshot.
        const xsrf = readCookie('XSRF-TOKEN');
        if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;
        return headers;
    }

    function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (contentType.indexOf('application/json') !== -1) {
            return response.json().then(function (data) {
                return { ok: response.ok, status: response.status, data: data || {} };
            });
        }
        return response.text().then(function (text) {
            return {
                ok: response.ok,
                status: response.status,
                data: { message: (text || response.statusText || '').slice(0, 240) },
            };
        });
    }

    function isCsrfFailure(res) {
        if (!res) return false;
        if (res.status === 419) return true;
        const msg = String((res.data && res.data.message) || '');
        return /csrf/i.test(msg) || /token mismatch/i.test(msg);
    }

    function csrfFriendlyMessage(res) {
        if (isCsrfFailure(res)) {
            return 'انتهت صلاحية الجلسة الأمنية. حدّث الصفحة ثم أعد المحاولة.';
        }
        return (res && res.data && res.data.message) || '';
    }

    function refreshCsrfToken() {
        const url = (config && config.api && config.api.csrf) || '/student/challenges/csrf-token';
        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.token) setCsrfToken(data.token);
            })
            .catch(function () { /* keep previous token */ });
    }

    function apiPost(url, body, options) {
        options = options || {};
        const attempt = options._attempt || 0;

        return fetch(url, {
            method: 'POST',
            headers: buildRequestHeaders(),
            credentials: 'same-origin',
            body: JSON.stringify(body),
        })
            .then(parseJsonResponse)
            .then(function (res) {
                if (isCsrfFailure(res) && attempt < 1) {
                    return refreshCsrfToken().then(function () {
                        return apiPost(url, body, { _attempt: attempt + 1 });
                    });
                }
                if (isCsrfFailure(res)) {
                    res.data = res.data || {};
                    res.data.message = csrfFriendlyMessage(res);
                }
                return res;
            });
    }

    function saveDraft() {
        setSaveStatus('جاري الحفظ...', 'saving');
        return apiPost(config.api.saveDraft, {
            files: getFilesPayload(),
            attempt_id: config.attemptId,
            module_id: config.moduleId,
        }).then(function (res) {
            if (res.ok && res.data.success) setSaveStatus('تم الحفظ', 'saved');
            else setSaveStatus(csrfFriendlyMessage(res) || res.data.message || 'خطأ في الحفظ', 'error');
        }).catch(function () { setSaveStatus('خطأ في الاتصال', 'error'); });
    }

    function submitChallenge() {
        if (!confirm('تسليم التحدي؟ لن تتمكن من التعديل بعد التسليم.')) return;
        // Refresh CSRF right before submit — students often keep the IDE open a long time.
        refreshCsrfToken().then(function () {
            return apiPost(config.api.submit, {
                files: getFilesPayload(),
                attempt_id: config.attemptId,
                module_id: config.moduleId,
            });
        }).then(function (res) {
            if (res.ok && res.data.success) {
                alert(res.data.message || 'تم التسليم بنجاح');
                window.location.href = config.backUrl;
            } else {
                alert(csrfFriendlyMessage(res) || res.data.message || 'فشل التسليم');
            }
        }).catch(function () {
            alert('تعذر الاتصال بالخادم. تحقق من الاتصال ثم أعد المحاولة.');
        });
    }

    function runCode() {
        const consoleEl = document.getElementById('challenge-console');
        if (consoleEl) consoleEl.textContent = 'جاري التشغيل...';
        apiPost(config.api.run, {
            files: getFilesPayload(),
            attempt_id: config.attemptId,
            module_id: config.moduleId,
        }).then(function (res) {
            if (!consoleEl) return;
            const d = res.data.data || {};
            consoleEl.textContent = (res.data.message || '') + '\n\n' + (d.stdout || '') + (d.stderr ? '\n[stderr]\n' + d.stderr : '');
        });
    }

    function bindToolbar() {
        document.getElementById('challenge-btn-save')?.addEventListener('click', saveDraft);
        document.getElementById('challenge-btn-submit')?.addEventListener('click', submitChallenge);
        document.getElementById('challenge-btn-run')?.addEventListener('click', runCode);
        document.getElementById('challenge-btn-refresh-preview')?.addEventListener('click', updatePreviewFromConfig);
        document.getElementById('challenge-btn-open-preview')?.addEventListener('click', openPreviewInNewTab);
    }

    function bindResizer() {
        const resizer = document.getElementById('challenge-resizer');
        const workspace = document.querySelector('.challenge-ide__workspace');
        const editorPanel = document.querySelector('.challenge-ide__editor-panel');
        const app = document.getElementById('challenge-ide-app');
        if (!resizer || !workspace || !editorPanel) return;
        let dragging = false;
        resizer.addEventListener('mousedown', function (e) {
            e.preventDefault();
            dragging = true;
            const bottom = app && app.getAttribute('data-layout') === 'bottom';
            document.body.style.cursor = bottom ? 'row-resize' : 'col-resize';
            document.body.style.userSelect = 'none';
        });
        document.addEventListener('mouseup', function () {
            dragging = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        });
        document.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            const rect = workspace.getBoundingClientRect();
            const bottom = app && app.getAttribute('data-layout') === 'bottom';
            if (bottom) {
                const pct = ((e.clientY - rect.top) / rect.height) * 100;
                editorPanel.style.flex = '0 0 ' + Math.min(80, Math.max(30, pct)) + '%';
            } else {
                const pct = ((e.clientX - rect.left) / rect.width) * 100;
                editorPanel.style.flex = '0 0 ' + Math.min(75, Math.max(25, pct)) + '%';
            }
            layoutAllEditors();
        });
    }

    function startAutoSave() {
        const interval = (config.autoSaveInterval || 30) * 1000;
        autoSaveTimer = setInterval(saveDraft, interval);
    }

    window.ChallengeIDE = { init: init };
})();
