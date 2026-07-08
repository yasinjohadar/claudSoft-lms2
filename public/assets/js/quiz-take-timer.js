/**
 * Quiz take countdown — server-authoritative remaining seconds.
 * Uses performance.now() + initial remaining from the server so client clock skew
 * cannot immediately trigger "time up".
 */
(function () {
    'use strict';

    var intervalId = null;
    var warnedFiveMinutes = false;
    var anchorRemainingSeconds = null;
    var anchorPerfMs = null;

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function getContainer() {
        return document.getElementById('timer-container');
    }

    function readIntAttr(name) {
        var container = getContainer();
        if (!container) {
            return null;
        }

        var raw = container.getAttribute(name);
        if (raw === null || raw === '') {
            return null;
        }

        var value = parseInt(raw, 10);
        return isNaN(value) ? null : value;
    }

    function getInitialRemainingSeconds() {
        var remaining = readIntAttr('data-remaining-seconds');
        if (remaining !== null && remaining >= 0) {
            return remaining;
        }

        var endsAt = readIntAttr('data-ends-at');
        var serverNow = readIntAttr('data-server-now-ms');
        if (endsAt !== null && serverNow !== null) {
            return Math.max(0, Math.floor((endsAt - serverNow) / 1000));
        }

        if (endsAt !== null) {
            return Math.max(0, Math.floor((endsAt - Date.now()) / 1000));
        }

        return null;
    }

    function ensureAnchor() {
        if (anchorRemainingSeconds !== null && anchorPerfMs !== null) {
            return true;
        }

        var initial = getInitialRemainingSeconds();
        if (initial === null) {
            return false;
        }

        anchorRemainingSeconds = initial;
        anchorPerfMs = (typeof performance !== 'undefined' && performance.now)
            ? performance.now()
            : Date.now();

        return true;
    }

    function getRemainingSeconds() {
        if (!ensureAnchor()) {
            return 0;
        }

        var nowPerf = (typeof performance !== 'undefined' && performance.now)
            ? performance.now()
            : Date.now();
        var elapsed = Math.floor((nowPerf - anchorPerfMs) / 1000);

        return Math.max(0, anchorRemainingSeconds - elapsed);
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
            var timerContainer = getContainer();
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
        if (!getContainer()) {
            return;
        }

        if (!document.getElementById('timer-minutes') || !document.getElementById('timer-seconds')) {
            return;
        }

        if (!ensureAnchor()) {
            return;
        }

        stopTimer();
        warnedFiveMinutes = getRemainingSeconds() <= 300;
        tick();
        intervalId = setInterval(tick, 1000);
        window.timerInterval = intervalId;
    }

    function resetAnchorFromDom() {
        anchorRemainingSeconds = null;
        anchorPerfMs = null;
        ensureAnchor();
    }

    function boot() {
        if (!getContainer()) {
            return;
        }
        resetAnchorFromDom();
        startTimer();
    }

    window.QuizTakeTimer = {
        start: startTimer,
        stop: stopTimer,
        refresh: tick,
        getRemainingSeconds: getRemainingSeconds,
        resetAnchor: resetAnchorFromDom,
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
