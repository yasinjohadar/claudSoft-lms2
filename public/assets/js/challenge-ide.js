(function () {
    'use strict';

    const editors = {};
    let config = null;
    let activeTab = 0;
    let autoSaveTimer = null;
    let previewDebounce = null;
    let initialized = false;
    let monacoReady = false;
    let useFallback = false;
    let resizeObserver = null;
    const MONACO_VS = 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs';

    function init(cfg) {
        if (initialized || !cfg) return;
        initialized = true;
        config = cfg;
        if (!config.files || !config.files.length) {
            config.files = defaultWebFiles();
        }
        buildTabs();
        updatePreviewFromConfig();
        loadMonaco()
            .then(function () {
                monacoReady = true;
                createEditorForTab(activeTab);
                observeResize();
                setTimeout(function () { layoutAllEditors(); }, 150);
            })
            .catch(function (err) {
                console.error('Monaco failed:', err);
                useFallback = true;
                createFallbackEditorForTab(activeTab);
                setSaveStatus('محرر بديل — تحقق من الاتصال', 'error');
            });
        bindToolbar();
        bindResizer();
        startAutoSave();
        window.addEventListener('resize', layoutAllEditors);
    }

    function defaultWebFiles() {
        return [
            { file_role: 'html', filename: 'index.html', content: '<h1>مرحباً</h1>\n<p>ابدأ التعديل هنا</p>', monaco_language_id: 'html', tab_label: 'HTML' },
            { file_role: 'css', filename: 'style.css', content: 'body { font-family: sans-serif; padding: 1rem; }', monaco_language_id: 'css', tab_label: 'CSS' },
            { file_role: 'js', filename: 'script.js', content: '', monaco_language_id: 'javascript', tab_label: 'JS' },
        ];
    }

    function buildTabs() {
        const tabsEl = document.getElementById('challenge-editor-tabs');
        const editorsEl = document.getElementById('challenge-editors');
        if (!tabsEl || !editorsEl) return;

        tabsEl.innerHTML = '';
        editorsEl.innerHTML = '';

        (config.files || []).forEach(function (file, i) {
            const tabId = 'challenge-tab-' + i;
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.innerHTML = '<button class="nav-link ' + (i === 0 ? 'active' : '') + '" type="button" data-tab="' + i + '">' + (file.tab_label || file.filename || file.file_role) + '</button>';
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
        if (useFallback) {
            createFallbackEditorForTab(index);
        } else if (monacoReady) {
            createEditorForTab(index);
        }
        setTimeout(layoutAllEditors, 50);
    }

    function loadMonaco() {
        return new Promise(function (resolve, reject) {
            if (window.monaco && window.monaco.editor) {
                resolve();
                return;
            }

            function boot(amdRequire) {
                amdRequire.config({
                    paths: { vs: MONACO_VS },
                    'vs/nls': { availableLanguages: { '*': 'en' } },
                });
                amdRequire(['vs/editor/editor.main'], function () {
                    resolve();
                }, reject);
            }

            const script = document.createElement('script');
            script.src = MONACO_VS + '/loader.js';
            script.async = false;
            script.onload = function () {
                const amdRequire = window.require;
                if (!amdRequire || typeof amdRequire.config !== 'function') {
                    reject(new Error('Monaco AMD loader unavailable'));
                    return;
                }
                window.__monacoAmdRequire = amdRequire;
                boot(amdRequire);
            };
            script.onerror = function () { reject(new Error('Failed to load Monaco loader')); };
            document.head.appendChild(script);
        });
    }

    function getEditorContainer(index) {
        return document.querySelector('.challenge-ide__editor-pane.active .challenge-ide__monaco[data-index="' + index + '"]')
            || document.querySelector('.challenge-ide__monaco[data-index="' + index + '"]');
    }

    function createEditorForTab(index) {
        if (editors[index] || !window.monaco) return;

        const file = config.files[index];
        const container = getEditorContainer(index);
        if (!container || !file) return;

        container.innerHTML = '';
        const isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';

        editors[index] = monaco.editor.create(container, {
            value: file.content || '',
            language: file.monaco_language_id || 'plaintext',
            theme: isDark ? 'vs-dark' : 'vs',
            automaticLayout: true,
            readOnly: false,
            domReadOnly: false,
            fontSize: 14,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            wordWrap: 'on',
        });

        editors[index].onDidChangeModelContent(schedulePreview);
        editors[index].focus();
        layoutAllEditors();
    }

    function createFallbackEditorForTab(index) {
        if (editors[index]) return;

        const file = config.files[index];
        const container = getEditorContainer(index);
        if (!container || !file) return;

        const ta = document.createElement('textarea');
        ta.className = 'challenge-ide__fallback-editor';
        ta.value = file.content || '';
        ta.spellcheck = false;
        ta.addEventListener('input', schedulePreview);
        container.innerHTML = '';
        container.appendChild(ta);

        editors[index] = {
            getValue: function () { return ta.value; },
            layout: function () {},
            focus: function () { ta.focus(); },
        };
        ta.focus();
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

    function schedulePreview() {
        if (config.challengeType !== 'web_sandbox') return;
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(updatePreviewFromConfig, 250);
    }

    function setSaveStatus(text, cls) {
        const el = document.getElementById('challenge-save-status');
        if (!el) return;
        el.textContent = text;
        el.className = 'challenge-ide__save-status text-muted small ' + (cls || '');
    }

    function apiPost(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': config.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); });
    }

    function saveDraft() {
        setSaveStatus('جاري الحفظ...', 'saving');
        return apiPost(config.api.saveDraft, {
            files: getFilesPayload(),
            attempt_id: config.attemptId,
            module_id: config.moduleId,
        }).then(function (res) {
            if (res.ok && res.data.success) setSaveStatus('تم الحفظ', 'saved');
            else setSaveStatus(res.data.message || 'خطأ في الحفظ', 'error');
        }).catch(function () { setSaveStatus('خطأ في الاتصال', 'error'); });
    }

    function submitChallenge() {
        if (!confirm('تسليم التحدي؟ لن تتمكن من التعديل بعد التسليم.')) return;
        apiPost(config.api.submit, {
            files: getFilesPayload(),
            attempt_id: config.attemptId,
            module_id: config.moduleId,
        }).then(function (res) {
            if (res.ok && res.data.success) {
                alert(res.data.message || 'تم التسليم بنجاح');
                window.location.href = config.backUrl;
            } else {
                alert(res.data.message || 'فشل التسليم');
            }
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
    }

    function bindResizer() {
        const resizer = document.getElementById('challenge-resizer');
        const workspace = document.querySelector('.challenge-ide__workspace');
        const editorPanel = document.querySelector('.challenge-ide__editor-panel');
        if (!resizer || !workspace || !editorPanel) return;
        let dragging = false;
        resizer.addEventListener('mousedown', function () { dragging = true; document.body.style.cursor = 'col-resize'; });
        document.addEventListener('mouseup', function () { dragging = false; document.body.style.cursor = ''; });
        document.addEventListener('mousemove', function (e) {
            if (!dragging) return;
            const rect = workspace.getBoundingClientRect();
            const pct = ((e.clientX - rect.left) / rect.width) * 100;
            editorPanel.style.flex = '0 0 ' + Math.min(75, Math.max(25, pct)) + '%';
            layoutAllEditors();
        });
    }

    function startAutoSave() {
        const interval = (config.autoSaveInterval || 30) * 1000;
        autoSaveTimer = setInterval(saveDraft, interval);
    }

    window.ChallengeIDE = { init: init };
})();
