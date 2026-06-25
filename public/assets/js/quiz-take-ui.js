/**
 * Quiz take UI — mobile drawer, bottom nav, option click-through
 */
(function () {
    'use strict';

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function qsa(sel, root) {
        return Array.from((root || document).querySelectorAll(sel));
    }

    function syncMobileNav() {
        var page = qs('.quiz-take-page');
        if (!page) return;

        var idx = typeof window.currentQuestionIndex !== 'undefined' ? window.currentQuestionIndex : 0;
        var total = typeof window.totalQuestions !== 'undefined' ? window.totalQuestions : 0;

        var prevBtn = qs('#quiz-take-mobile-prev');
        var nextBtn = qs('#quiz-take-mobile-next');
        var counter = qs('#quiz-take-mobile-counter');

        if (prevBtn) {
            prevBtn.disabled = idx <= 0;
        }
        if (nextBtn) {
            if (idx >= total - 1) {
                nextBtn.innerHTML = '<i class="fe fe-check me-1"></i>إرسال';
                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-success');
            } else {
                nextBtn.innerHTML = 'التالي<i class="fe fe-chevron-left ms-1"></i>';
                nextBtn.classList.remove('btn-success');
                nextBtn.classList.add('btn-primary');
            }
        }
        if (counter) {
            counter.textContent = (idx + 1) + ' / ' + total;
        }

        var timerMobile = qs('#quiz-take-mobile-timer');
        var timerMin = qs('#timer-minutes');
        var timerSec = qs('#timer-seconds');
        if (timerMobile && timerMin && timerSec) {
            timerMobile.textContent = timerMin.textContent + ':' + timerSec.textContent;
        }
    }

    function openSidebar() {
        var col = qs('#quiz-take-sidebar-col');
        var backdrop = qs('#quiz-take-sidebar-backdrop');
        if (col) col.classList.add('is-open');
        if (backdrop) backdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        var col = qs('#quiz-take-sidebar-col');
        var backdrop = qs('#quiz-take-sidebar-backdrop');
        if (col) col.classList.remove('is-open');
        if (backdrop) backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function toggleSidebar() {
        var col = qs('#quiz-take-sidebar-col');
        if (col && col.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function mobileNextOrSubmit() {
        var idx = typeof window.currentQuestionIndex !== 'undefined' ? window.currentQuestionIndex : 0;
        var total = typeof window.totalQuestions !== 'undefined' ? window.totalQuestions : 0;

        if (idx >= total - 1) {
            if (typeof window.showSubmitConfirmation === 'function') {
                window.showSubmitConfirmation();
            }
        } else if (typeof window.nextQuestion === 'function') {
            window.nextQuestion();
        }
        syncMobileNav();
    }

    function patchGoToQuestion() {
        if (typeof window.goToQuestion !== 'function' || window.goToQuestion.__quizTakePatched) {
            return;
        }
        var original = window.goToQuestion;
        window.goToQuestion = function (index) {
            original(index);
            syncMobileNav();
            closeSidebar();
        };
        window.goToQuestion.__quizTakePatched = true;
    }

    function bindOptionClicks() {
        qsa('.quiz-take-page .quiz-option-hit').forEach(function (label) {
            if (label.dataset.qtBound) return;
            label.dataset.qtBound = '1';
            label.addEventListener('click', function (e) {
                if (e.target.matches('input, textarea, select, a, button')) return;
                var input = label.querySelector('input[type="radio"], input[type="checkbox"]');
                if (!input || input.disabled) return;

                // Checkbox: <label> already toggles — manual toggle here double-fires and cancels selection
                if (input.type === 'checkbox') {
                    return;
                }

                if (input.type === 'radio' && !input.checked) {
                    e.preventDefault();
                    input.checked = true;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    function relocateSubmitModal() {
        var modal = qs('#submitModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    function init() {
        var page = qs('.quiz-take-page');
        if (!page) return;

        relocateSubmitModal();

        patchGoToQuestion();

        var openBtn = qs('#quiz-take-open-sidebar');
        var backdrop = qs('#quiz-take-sidebar-backdrop');
        var prevBtn = qs('#quiz-take-mobile-prev');
        var nextBtn = qs('#quiz-take-mobile-next');

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
        if (prevBtn) prevBtn.addEventListener('click', function () {
            if (typeof window.previousQuestion === 'function') window.previousQuestion();
            syncMobileNav();
        });
        if (nextBtn) nextBtn.addEventListener('click', mobileNextOrSubmit);

        qsa('.question-nav-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTimeout(closeSidebar, 50);
            });
        });

        bindOptionClicks();
        syncMobileNav();

        setInterval(function () {
            var timerMobile = qs('#quiz-take-mobile-timer');
            var timerMin = qs('#timer-minutes');
            var timerSec = qs('#timer-seconds');
            if (timerMobile && timerMin && timerSec) {
                timerMobile.textContent = timerMin.textContent + ':' + timerSec.textContent;
            }
        }, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.QuizTakeUI = {
        syncMobileNav: syncMobileNav,
        openSidebar: openSidebar,
        closeSidebar: closeSidebar,
    };
})();
