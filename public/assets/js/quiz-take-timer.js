/**
 * Quiz take countdown — wall-clock based, independent of jQuery / quiz init.
 */
(function () {
    'use strict';

    var intervalId = null;
    var warnedFiveMinutes = false;

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function getEndsAtMs() {
        var container = document.getElementById('timer-container');
        if (!container) {
            return null;
        }

        var raw = container.getAttribute('data-ends-at');
        if (!raw) {
            return null;
        }

        var endsAt = parseInt(raw, 10);
        return isNaN(endsAt) || endsAt <= 0 ? null : endsAt;
    }

    function getRemainingSeconds() {
        var endsAt = getEndsAtMs();
        if (!endsAt) {
            return 0;
        }

        return Math.max(0, Math.floor((endsAt - Date.now()) / 1000));
    }

    function updateDisplay(seconds) {
        var minutes = Math.floor(seconds / 60);
        var secs = seconds % 60;
        var minutesStr = pad2(minutes);
        var secondsStr = pad2(secs);

        var minEl = document.getElementById('timer-minutes');
        var secEl = document.getElementById('timer-seconds');
        if (minEl) {
            minEl.textContent = minutesStr;
        }
        if (secEl) {
            secEl.textContent = secondsStr;
        }

        var mobileTimer = document.getElementById('quiz-take-mobile-timer');
        if (mobileTimer) {
            mobileTimer.textContent = minutesStr + ':' + secondsStr;
        }

        if (typeof window.remainingTimeSeconds !== 'undefined') {
            window.remainingTimeSeconds = seconds;
        }
    }

    function stopTimer() {
        if (intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
        if (typeof window.timerInterval !== 'undefined') {
            window.timerInterval = null;
        }
    }

    function handleTimeUp() {
        stopTimer();

        if (typeof window.timeUp === 'function') {
            window.timeUp();
            return;
        }

        if (typeof window.submitQuiz === 'function') {
            window.submitQuiz(true);
        }
    }

    function tick() {
        var remaining = getRemainingSeconds();
        updateDisplay(remaining);

        if (remaining === 300 && !warnedFiveMinutes) {
            warnedFiveMinutes = true;
            var timerContainer = document.getElementById('timer-container');
            if (timerContainer) {
                timerContainer.classList.add('time-warning');
            }
            if (typeof window.showToast === 'function') {
                window.showToast('تحذير: تبقى 5 دقائق فقط!', 'warning');
            }
        }

        if (remaining <= 0) {
            handleTimeUp();
        }
    }

    function startTimer() {
        if (!getEndsAtMs()) {
            return;
        }

        if (!document.getElementById('timer-minutes') || !document.getElementById('timer-seconds')) {
            return;
        }

        stopTimer();
        warnedFiveMinutes = getRemainingSeconds() <= 300;
        tick();
        intervalId = setInterval(tick, 1000);
        window.timerInterval = intervalId;
    }

    function boot() {
        if (!document.getElementById('timer-container')) {
            return;
        }
        startTimer();
    }

    window.QuizTakeTimer = {
        start: startTimer,
        stop: stopTimer,
        refresh: tick,
        getRemainingSeconds: getRemainingSeconds,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('turbo:load', boot);
    document.addEventListener('turbo:before-render', stopTimer);

    if (typeof window.__quizTimerBoot === 'function') {
        window.__quizTimerBoot();
    }

    document.dispatchEvent(new CustomEvent('quiz-take-timer:ready'));
})();
