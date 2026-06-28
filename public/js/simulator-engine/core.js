(function (global) {
    'use strict';

    const WIDGETS = {};
    const SECTION_RENDERERS = {};

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function sectionTitle(section, index) {
        if (section.title) return section.title;
        const labels = {
            hero: 'مقدمة',
            concept_cards: 'المفاهيم',
            code_tabs: 'أمثلة الكود',
            interactive: 'تجربة تفاعلية',
            reference_table: 'مرجع',
            checklist: 'قائمة التعلّم',
            mini_quiz: 'اختبار قصير',
            callout: 'ملاحظة',
            comparison: 'مقارنة',
        };
        return labels[section.type] || 'قسم ' + (index + 1);
    }

    SECTION_RENDERERS.hero = function (section) {
        return '<div class="simulator-section simulator-section-hero" id="section-hero">' +
            '<h2>' + escapeHtml(section.title || '') + '</h2>' +
            (section.summary ? '<p class="simulator-hero-summary">' + escapeHtml(section.summary) + '</p>' : '') +
            '</div>';
    };

    SECTION_RENDERERS.concept_cards = function (section, index) {
        let cards = (section.items || []).map(function (item) {
            return '<div class="simulator-card-item">' +
                '<h3>' + escapeHtml(item.title || '') + '</h3>' +
                '<p>' + escapeHtml(item.body || '') + '</p>' +
                '</div>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '') +
            '<div class="simulator-cards">' + cards + '</div></div>';
    };

    SECTION_RENDERERS.code_tabs = function (section, index) {
        const snippets = section.snippets || [];
        const btns = snippets.map(function (s, i) {
            return '<button type="button" class="simulator-code-tab-btn' + (i === 0 ? ' active' : '') + '" data-tab="' + index + '-' + i + '">' +
                escapeHtml(s.label || s.lang) + '</button>';
        }).join('');
        const panels = snippets.map(function (s, i) {
            const lang = s.lang || 'javascript';
            return '<div class="simulator-code-panel' + (i === 0 ? ' active' : '') + '" data-panel="' + index + '-' + i + '">' +
                '<pre><code class="language-' + escapeHtml(lang) + '">' + escapeHtml(s.code || '') + '</code></pre></div>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '<h2>أمثلة الكود</h2>') +
            '<div class="simulator-code-tabs" data-code-tabs="' + index + '">' +
            '<div class="simulator-code-tab-btns">' + btns + '</div>' + panels + '</div></div>';
    };

    SECTION_RENDERERS.interactive = function (section, index) {
        const widgetId = 'widget-' + index;
        const html = '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '<h2>تجربة تفاعلية</h2>') +
            '<div class="simulator-interactive" id="' + widgetId + '" data-widget="' + escapeHtml(section.widget || '') + '"></div></div>';
        setTimeout(function () {
            const el = document.getElementById(widgetId);
            const fn = WIDGETS[section.widget];
            if (el && fn) fn(el, section.config || {});
        }, 0);
        return html;
    };

    SECTION_RENDERERS.reference_table = function (section, index) {
        const cols = section.columns || [];
        const head = cols.map(function (c) { return '<th>' + escapeHtml(c) + '</th>'; }).join('');
        const rows = (section.rows || []).map(function (row) {
            const cells = row.map(function (cell) { return '<td>' + escapeHtml(cell) + '</td>'; }).join('');
            return '<tr>' + cells + '</tr>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            '<h2>' + escapeHtml(section.title || 'جدول مرجعي') + '</h2>' +
            '<div class="simulator-table-wrap"><table class="simulator-table"><thead><tr>' + head + '</tr></thead><tbody>' +
            rows + '</tbody></table></div></div>';
    };

    SECTION_RENDERERS.checklist = function (section, index) {
        const items = (section.items || []).map(function (item) {
            return '<li>' + escapeHtml(item) + '</li>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '<h2>قائمة التعلّم</h2>') +
            '<ul class="simulator-checklist">' + items + '</ul></div>';
    };

    SECTION_RENDERERS.mini_quiz = function (section, index) {
        const questions = (section.questions || []).map(function (q, qi) {
            const opts = (q.options || []).map(function (opt, oi) {
                return '<label><input type="radio" name="quiz-' + index + '-' + qi + '" value="' + oi + '"> ' +
                    escapeHtml(opt) + '</label>';
            }).join('');
            return '<div class="simulator-quiz-q" data-answer="' + (q.answer ?? 0) + '">' +
                '<p><strong>' + (qi + 1) + '.</strong> ' + escapeHtml(q.prompt || '') + '</p>' +
                '<div class="simulator-quiz-options">' + opts + '</div></div>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '<h2>اختبار قصير</h2>') +
            questions +
            '<button type="button" class="simulator-btn" data-quiz-check="' + index + '">تحقق من الإجابات</button>' +
            '<p class="simulator-quiz-result" id="quiz-result-' + index + '"></p></div>';
    };

    SECTION_RENDERERS.callout = function (section, index) {
        const variant = section.variant || 'info';
        return '<div class="simulator-section" id="section-' + index + '">' +
            '<div class="simulator-callout ' + escapeHtml(variant) + '">' + escapeHtml(section.body || '') + '</div></div>';
    };

    SECTION_RENDERERS.comparison = function (section, index) {
        const pairs = (section.pairs || []).map(function (pair) {
            return '<div class="simulator-card-item"><h3>' + escapeHtml(pair.title || '') + '</h3>' +
                '<p>' + escapeHtml(pair.body || '') + '</p></div>';
        }).join('');
        return '<div class="simulator-section" id="section-' + index + '">' +
            (section.title ? '<h2>' + escapeHtml(section.title) + '</h2>' : '') +
            '<div class="simulator-cards">' + pairs + '</div></div>';
    };

    function bindCodeTabs() {
        document.querySelectorAll('.simulator-code-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-tab');
                const wrap = btn.closest('.simulator-code-tabs');
                wrap.querySelectorAll('.simulator-code-tab-btn').forEach(function (b) { b.classList.remove('active'); });
                wrap.querySelectorAll('.simulator-code-panel').forEach(function (p) { p.classList.remove('active'); });
                btn.classList.add('active');
                const panel = wrap.querySelector('[data-panel="' + id + '"]');
                if (panel) panel.classList.add('active');
            });
        });
    }

    function bindQuizButtons() {
        document.querySelectorAll('[data-quiz-check]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const sectionIndex = btn.getAttribute('data-quiz-check');
                const section = document.getElementById('section-' + sectionIndex);
                if (!section) return;
                let correct = 0;
                let total = 0;
                section.querySelectorAll('.simulator-quiz-q').forEach(function (qEl) {
                    total++;
                    const answer = parseInt(qEl.getAttribute('data-answer'), 10);
                    const selected = qEl.querySelector('input[type="radio"]:checked');
                    if (selected && parseInt(selected.value, 10) === answer) correct++;
                });
                const result = document.getElementById('quiz-result-' + sectionIndex);
                if (result) {
                    result.textContent = 'النتيجة: ' + correct + ' / ' + total;
                }
            });
        });
    }

    function buildNav(sections) {
        const nav = document.getElementById('simulator-nav');
        if (!nav) return;
        nav.innerHTML = sections.map(function (section, i) {
            const id = section.type === 'hero' ? 'section-hero' : 'section-' + i;
            return '<a href="#' + id + '" class="simulator-nav-link">' + escapeHtml(sectionTitle(section, i)) + '</a>';
        }).join('');
        nav.querySelectorAll('.simulator-nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function updateProgress() {
        const bar = document.getElementById('simulator-progress-bar');
        if (!bar) return;
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const pct = docHeight > 0 ? Math.min(100, (scrollTop / docHeight) * 100) : 0;
        bar.style.width = pct + '%';
    }

    const SimulatorEngine = {
        registerWidget: function (name, fn) {
            WIDGETS[name] = fn;
        },

        init: function (spec) {
            const container = document.getElementById('simulator-sections');
            if (!container || !spec || !spec.sections) return;

            const html = spec.sections.map(function (section, index) {
                const renderer = SECTION_RENDERERS[section.type];
                return renderer ? renderer(section, index) : '';
            }).join('');

            container.innerHTML = html;
            buildNav(spec.sections);
            bindCodeTabs();
            bindQuizButtons();

            if (global.Prism) {
                global.Prism.highlightAllUnder(container);
            }

            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();
        },
    };

    global.SimulatorEngine = SimulatorEngine;
})(window);
