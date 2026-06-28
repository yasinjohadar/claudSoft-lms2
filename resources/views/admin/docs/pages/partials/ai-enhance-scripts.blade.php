<script src="https://cdn.jsdelivr.net/npm/diff@5.2.0/dist/diff.min.js"></script>
<script>
(function () {
    const pages = @json($pagesJson);
    const initialPageId = @json($prefillPage?->id);
    const useLaravelAiEngineDefault = @json(!empty($useLaravelAiEngine));
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const refineUrl = @json(route('admin.docs.ai-pages.refine'));
    const csrf = @json(csrf_token());

    const pagePicker = document.getElementById('page_picker');
    const pageMeta = document.getElementById('pageMeta');
    const docOriginal = document.getElementById('doc_original');
    const userNotes = document.getElementById('user_notes');
    const enhanceBtn = document.getElementById('enhanceBtn');
    const reviewSection = document.getElementById('reviewSection');
    const enhanceStats = document.getElementById('enhanceStats');
    const previewOld = document.getElementById('previewOld');
    const previewNew = document.getElementById('previewNew');
    const diffOutput = document.getElementById('diffOutput');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');
    const btnRegenerate = document.getElementById('btnRegenerate');
    const saveForm = document.getElementById('enhanceSaveForm');
    const approveModalEl = document.getElementById('approveModal');
    const approvePageTitle = document.getElementById('approvePageTitle');
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');
    const enhanceHint = document.getElementById('enhanceHint');
    const editPageLink = document.getElementById('editPageLink');

    let currentPage = null;
    let originalHtml = '';

    function updateEditLink(page) {
        if (editPageLink) {
            if (page && page.edit_url) {
                editPageLink.href = page.edit_url;
                editPageLink.classList.remove('disabled');
                editPageLink.removeAttribute('aria-disabled');
            } else {
                editPageLink.href = '#';
                editPageLink.classList.add('disabled');
                editPageLink.setAttribute('aria-disabled', 'true');
            }
        }
        const bannerLink = document.getElementById('editPageLinkBanner');
        if (bannerLink) {
            if (page && page.edit_url) {
                bannerLink.href = page.edit_url;
                bannerLink.style.display = '';
            } else {
                bannerLink.style.display = 'none';
            }
        }
    }

    function updateHint() {
        if (!enhanceHint) return;
        const hasPage = pagePicker && pagePicker.value;
        const notes = userNotes ? userNotes.value.trim().length : 0;
        const hasContent = docOriginal && docOriginal.value.trim().length > 0;
        if (!hasPage) {
            enhanceHint.textContent = 'اختر صفحة توثيق للبدء.';
        } else if (!hasContent) {
            enhanceHint.textContent = 'جاري تحميل محتوى الصفحة…';
        } else if (notes < 10) {
            enhanceHint.textContent = 'اكتب وصف الأفكار (10 أحرف على الأقل).';
        } else {
            enhanceHint.textContent = 'جاهز — اضغط «تطبيق الأفكار».';
        }
    }

    function findPage(id) {
        return pages.find(function (p) { return String(p.id) === String(id); }) || null;
    }

    function syncDocsEngineModelVisibility() {
        if (!docsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('docs_engine_laravel_ai')?.checked;
        const wL = document.getElementById('docs_engine_laravel_wrap');
        const wG = document.getElementById('docs_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    function getEditorHtml(id) {
        if (typeof tinymce !== 'undefined') {
            const ed = tinymce.get(id);
            if (ed) return ed.getContent();
        }
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    function setEditorHtml(id, html) {
        if (typeof tinymce !== 'undefined') {
            const ed = tinymce.get(id);
            if (ed) {
                ed.setContent(html || '');
                return;
            }
        }
        const el = document.getElementById(id);
        if (el) el.value = html || '';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function stripHtmlForDiff(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        const text = tmp.textContent || tmp.innerText || '';
        return text.replace(/\s+/g, ' ').trim();
    }

    function renderDiff(oldHtml, newHtml) {
        if (typeof Diff === 'undefined') {
            diffOutput.textContent = 'تعذّر تحميل مكتبة المقارنة.';
            return;
        }
        const oldText = stripHtmlForDiff(oldHtml);
        const newText = stripHtmlForDiff(newHtml);
        const parts = Diff.diffWords(oldText, newText);
        let html = '';
        parts.forEach(function (part) {
            const val = escapeHtml(part.value);
            if (part.added) {
                html += '<ins>' + val + '</ins>';
            } else if (part.removed) {
                html += '<del>' + val + '</del>';
            } else {
                html += '<span class="diff-unchanged">' + val + '</span>';
            }
        });
        diffOutput.innerHTML = html || '<span class="text-muted">لا توجد فروقات نصية واضحة.</span>';
    }

    function renderPreview(oldHtml, newHtml) {
        previewOld.innerHTML = oldHtml || '<p class="text-muted">—</p>';
        previewNew.innerHTML = newHtml || '<p class="text-muted">—</p>';
    }

    function renderStats(stats) {
        if (!stats) {
            enhanceStats.innerHTML = '';
            return;
        }
        const items = [
            { label: 'طول القديم', value: Number(stats.old_length || 0).toLocaleString() },
            { label: 'طول الجديد', value: Number(stats.new_length || 0).toLocaleString() },
            { label: 'أقسام قديمة', value: stats.old_sections ?? 0 },
            { label: 'أقسام جديدة', value: stats.new_sections ?? 0 },
        ];
        enhanceStats.innerHTML = items.map(function (item) {
            return '<div class="doc-enhance-stat"><span class="doc-enhance-stat__value">' + item.value + '</span><span class="doc-enhance-stat__label">' + item.label + '</span></div>';
        }).join('');
    }

    function updatePageMeta(page) {
        if (!page || !pageMeta) return;

        pageMeta.replaceChildren();

        const slugEl = document.createElement('span');
        slugEl.className = 'doc-cat-slug';
        slugEl.textContent = page.slug || '';
        pageMeta.appendChild(slugEl);

        const statusEl = document.createElement('span');
        statusEl.className = page.status === 'published'
            ? 'doc-cat-status doc-cat-status--published'
            : 'doc-cat-status doc-cat-status--draft';
        if (page.status === 'published') {
            const dot = document.createElement('span');
            dot.className = 'doc-cat-status__dot';
            statusEl.appendChild(dot);
            statusEl.appendChild(document.createTextNode('منشور'));
        } else {
            statusEl.textContent = 'مسودة';
        }
        pageMeta.appendChild(statusEl);

        const catEl = document.createElement('span');
        catEl.className = 'doc-cat-chip doc-cat-chip--section';
        catEl.innerHTML = '<i class="fe fe-folder"></i>' + escapeHtml(page.category_name || '—');
        pageMeta.appendChild(catEl);

        pageMeta.style.display = '';
    }

    function populateSaveFields(page) {
        document.getElementById('save_category_id').value = page.category_id || '';
        document.getElementById('save_parent_id').value = page.parent_id || '';
        document.getElementById('save_sort_order').value = page.sort_order ?? 0;
        document.getElementById('save_status').value = page.status || 'draft';
        document.getElementById('save_published_at').value = page.published_at || '';
        document.getElementById('save_meta_title').value = page.meta_title || '';
        document.getElementById('save_meta_description').value = page.meta_description || '';
        document.getElementById('save_is_indexable').value = page.is_indexable ? '1' : '0';
        document.getElementById('save_title').value = page.title || '';
        document.getElementById('save_slug').value = page.slug || '';
        document.getElementById('save_excerpt').value = page.excerpt || '';
        saveForm.action = page.update_url;
    }

    function hideReview() {
        reviewSection.classList.remove('is-visible');
        btnApprove.disabled = true;
        setEditorHtml('doc_result', '');
    }

    function showReview(oldHtml, newHtml, stats) {
        originalHtml = oldHtml;
        renderStats(stats);
        renderPreview(oldHtml, newHtml);
        renderDiff(oldHtml, newHtml);
        setEditorHtml('doc_result', newHtml);
        reviewSection.classList.add('is-visible');
        btnApprove.disabled = false;
        reviewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function validateReady() {
        const hasPage = pagePicker && pagePicker.value;
        const notes = userNotes ? userNotes.value.trim() : '';
        const notesOk = notes.length >= 10;
        const hasContent = docOriginal && docOriginal.value.trim().length > 0;
        if (enhanceBtn) {
            enhanceBtn.disabled = !(hasPage && notesOk && hasContent);
        }
        updateHint();
    }

    function loadPage(pageId) {
        const page = findPage(pageId);
        currentPage = page;
        hideReview();

        if (!page) {
            docOriginal.value = '';
            pageMeta.style.display = 'none';
            updateEditLink(null);
            validateReady();
            return;
        }

        updatePageMeta(page);
        populateSaveFields(page);
        updateEditLink(page);
        validateReady();

        const sourceUrl = page.source_url;
        if (!sourceUrl) {
            docOriginal.value = '';
            validateReady();
            return;
        }

        docOriginal.value = '';
        enhanceBtn.disabled = true;
        updateHint();

        fetch(sourceUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('fetch failed');
                return r.json();
            })
            .then(function (data) {
                docOriginal.value = data.content || '';
                originalHtml = data.content || '';
                if (data.excerpt) {
                    document.getElementById('save_excerpt').value = data.excerpt;
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'تعذّر تحميل محتوى الصفحة.' });
                } else {
                    alert('تعذّر تحميل محتوى الصفحة.');
                }
            })
            .finally(function () {
                validateReady();
            });
    }

    function resolveDocsEngine() {
        if (docsEngineChoiceAvailable) {
            const r = document.querySelector('input[name="docs_engine"]:checked');
            if (r) return r.value;
        }
        if (document.getElementById('laravel_ai_model_id') && !document.getElementById('docs_engine_legacy_wrap')) {
            return 'laravel_ai';
        }
        if (document.getElementById('ai_model_id') && !document.getElementById('docs_engine_laravel_wrap')) {
            return 'legacy';
        }
        return useLaravelAiEngineDefault ? 'laravel_ai' : 'legacy';
    }

    function buildPayload() {
        const engine = resolveDocsEngine();
        const laravelEl = document.getElementById('laravel_ai_model_id');
        const legacyEl = document.getElementById('ai_model_id');

        return {
            source_html: docOriginal.value,
            user_notes: userNotes.value.trim(),
            mode: 'enhance',
            docs_engine: engine,
            ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
            laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
            tone: document.getElementById('tone').value,
            language: document.getElementById('language').value,
            update_excerpt: document.getElementById('update_excerpt').checked,
            _token: csrf,
        };
    }

    function runEnhance() {
        if (!currentPage) {
            alert('اختر صفحة أولاً');
            return;
        }
        const notes = userNotes.value.trim();
        if (notes.length < 10) {
            alert('صف الأفكار (10 أحرف على الأقل)');
            return;
        }
        const sourceHtml = docOriginal.value;
        if (!sourceHtml.trim()) {
            alert('الصفحة المختارة لا تحتوي محتوى');
            return;
        }

        const btn = enhanceBtn;
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.loading-spinner');
        btn.disabled = true;
        if (btnText) btnText.textContent = 'جاري التطبيق…';
        spinner.classList.add('active');

        fetch(refineUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(buildPayload()),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (res.body.success && res.body.data && res.body.data.content !== undefined) {
                    const newHtml = res.body.data.content;
                    showReview(sourceHtml, newHtml, res.body.data.stats);
                    if (res.body.data.excerpt) {
                        document.getElementById('save_excerpt').value = res.body.data.excerpt;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'تم', text: 'راجع المقارنة ثم وافق للحفظ.', timer: 2500 });
                    }
                } else {
                    const msg = res.body.message || 'فشل التطبيق';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الاتصال بالخادم' });
                } else {
                    alert('فشل الاتصال');
                }
            })
            .finally(function () {
                spinner.classList.remove('active');
                if (btnText) btnText.textContent = 'تطبيق الأفكار';
                validateReady();
            });
    }

    if (pagePicker) {
        pagePicker.addEventListener('change', function () {
            loadPage(this.value);
            const url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('documentation_page_id', this.value);
            } else {
                url.searchParams.delete('documentation_page_id');
            }
            window.history.replaceState({}, '', url.toString());
        });
    }

    if (userNotes) {
        userNotes.addEventListener('input', validateReady);
    }

    if (enhanceBtn) {
        enhanceBtn.addEventListener('click', runEnhance);
    }

    if (btnRegenerate) {
        btnRegenerate.addEventListener('click', function () {
            hideReview();
            runEnhance();
        });
    }

    if (btnReject) {
        btnReject.addEventListener('click', function () {
            hideReview();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'info', title: 'تم الرفض', text: 'لم يُحفظ أي تغيير.', timer: 2000, showConfirmButton: false });
            }
        });
    }

    if (btnApprove) {
        btnApprove.addEventListener('click', function () {
            const content = getEditorHtml('doc_result').trim();
            if (!content) {
                alert('لا يوجد محتوى للحفظ');
                return;
            }
            if (currentPage && approvePageTitle) {
                approvePageTitle.textContent = currentPage.title;
            }
            if (approveModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(approveModalEl).show();
            } else if (confirm('الموافقة على حفظ التغييرات؟')) {
                submitSave();
            }
        });
    }

    function submitSave() {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        const content = getEditorHtml('doc_result').trim();
        if (!content) {
            alert('لا يوجد محتوى للحفظ');
            return;
        }
        if (!currentPage || saveForm.action === '#') {
            alert('اختر صفحة صالحة');
            return;
        }
        saveForm.submit();
    }

    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener('click', function () {
            if (approveModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(approveModalEl).hide();
            }
            submitSave();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncDocsEngineModelVisibility();
        document.querySelectorAll('input[name="docs_engine"]').forEach(function (el) {
            el.addEventListener('change', syncDocsEngineModelVisibility);
        });

        if (initialPageId) {
            loadPage(initialPageId);
        } else if (pagePicker && pagePicker.value) {
            loadPage(pagePicker.value);
        }
        validateReady();
    });

    document.getElementById('tab-edit-btn')?.addEventListener('shown.bs.tab', function () {
        if (typeof tinymce !== 'undefined' && !tinymce.get('doc_result')) {
            setTimeout(function () {
                if (typeof initDocTinyMCE === 'function') {
                    initDocTinyMCE();
                }
            }, 100);
        }
    });
})();
</script>
