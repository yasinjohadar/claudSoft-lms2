@extends('student.layouts.master')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
@endpush

@section('page-title')
    {{ $module->title }}
@stop

@section('content')
<div class="main-content app-content student-learn-module-page">
    <div class="container-fluid pb-3">

        <div class="my-4 page-header-breadcrumb dashboard-fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    @if($module->course)
                        <li class="breadcrumb-item">
                            <a href="{{ route('student.courses.show', $module->course_id) }}">
                                {{ Str::limit($module->course->title, 36) }}
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">{{ Str::limit($module->title, 40) }}</li>
                </ol>
            </nav>
        </div>

        <div class="row align-items-start g-4 student-learn-module-layout">
            <div class="col-lg-9 order-1 order-lg-2">
                @include('student.courses.learning.module-main')
            </div>

            <div class="col-lg-3 order-2 order-lg-1">
                @include('student.courses.learning.partials.learn-sidebar')
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
    (function () {
        'use strict';

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        var lessonCompleteApplauseUrl = @json(asset('assets/sounds/lesson-complete-applause.mp3'));
        var lessonApplauseMaxSeconds = 5;

        function syncLearnStickyVideoTop() {
            var page = document.querySelector('.student-learn-module-page');
            var header = document.querySelector('.app-header');
            if (!page) {
                return;
            }
            if (header && header.offsetHeight > 0) {
                page.style.setProperty('--student-learn-sticky-top', header.offsetHeight + 'px');
            }
        }

        function getLearnFrame() {
            return document.getElementById('student-learn-main');
        }

        function getCurrentModuleId() {
            var f = getLearnFrame();
            if (!f || f.dataset.currentModuleId === undefined) {
                return 0;
            }
            return parseInt(f.dataset.currentModuleId, 10) || 0;
        }

        function parseCompletedModuleIds() {
            var f = getLearnFrame();
            if (!f) {
                return [];
            }

            var completedIds = [];
            if (f.dataset.completedModules) {
                try {
                    var raw = JSON.parse(f.dataset.completedModules);
                    if (Array.isArray(raw)) {
                        completedIds = raw.map(function (x) { return parseInt(x, 10); }).filter(function (n) { return !isNaN(n); });
                    }
                } catch (e) {
                    completedIds = [];
                }
            }

            if (f.dataset.isCompleted === '1') {
                var currentId = getCurrentModuleId();
                if (currentId && completedIds.indexOf(currentId) === -1) {
                    completedIds.push(currentId);
                }
            }

            return completedIds;
        }

        var TYPE_LABELS = {
            video: { icon: 'fe-play-circle', text: 'فيديو', color: 'danger' },
            lesson: { icon: 'fe-book-open', text: 'درس', color: 'primary' },
            assignment: { icon: 'fe-clipboard', text: 'واجب', color: 'warning' },
            quiz: { icon: 'fe-help-circle', text: 'اختبار', color: 'success' },
            question_module: { icon: 'fe-file-text', text: 'اختبار', color: 'info' },
            resource: { icon: 'fe-link', text: 'مورد', color: 'secondary' },
            documentation: { icon: 'fe-book', text: 'توثيق', color: 'info' },
            simulator: { icon: 'fe-cpu', text: 'محاكاة', color: 'info' },
            default: { icon: 'fe-file', text: 'محتوى', color: 'secondary' },
        };

        function typeLabelHtml(type) {
            var t = TYPE_LABELS[type] || TYPE_LABELS.default;
            return '<i class="fe ' + t.icon + ' me-1"></i>' + t.text;
        }

        function sidebarCompletionBtnHtml(isCompleted) {
            if (isCompleted) {
                return '<i class="fe fe-check"></i><span>مكتمل</span>';
            }
            return '<i class="fe fe-circle"></i><span>غير مكتمل</span>';
        }

        function applySidebarRowState(item, moduleId, isCompleted, currentModuleId) {
            var isCurrent = moduleId === currentModuleId;
            var lesson = item.querySelector('.student-learn-sidebar-lesson');
            if (lesson) {
                lesson.className = 'student-learn-sidebar-lesson';
                if (isCurrent) {
                    lesson.classList.add('is-active');
                } else if (isCompleted) {
                    lesson.classList.add('is-completed');
                }
            }

            var toggleBtn = item.querySelector('.js-sidebar-completion-toggle');
            if (toggleBtn) {
                toggleBtn.dataset.isCompleted = isCompleted ? '1' : '0';
                toggleBtn.classList.toggle('is-done', isCompleted);
                toggleBtn.classList.toggle('is-pending', !isCompleted);
                toggleBtn.innerHTML = sidebarCompletionBtnHtml(isCompleted);
                toggleBtn.title = isCompleted ? 'إلغاء الإكمال' : 'تحديد كمكتمل';
                toggleBtn.setAttribute('aria-label', isCompleted ? 'إلغاء إكمال الدرس' : 'تحديد الدرس كمكتمل');
            }
        }

        function requestModuleCompletion(url, triggerBtn, moduleId, options) {
            options = options || {};
            if (!csrfToken) {
                alert('لم يتم العثور على رمز الأمان (CSRF). حدّث الصفحة.');
                return Promise.reject();
            }

            var applauseAudio = options.celebrate
                ? (function () {
                    var audio = new Audio(lessonCompleteApplauseUrl);
                    audio.volume = 0.42;
                    audio.preload = 'auto';
                    return audio;
                })()
                : null;

            var originalHtml = triggerBtn.innerHTML;
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            return fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            })
                .then(function (response) {
                    var ct = response.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) {
                        return { ok: false, status: response.status, data: { success: false, message: 'تعذر قراءة الرد، حدّث الصفحة وحاول مجددًا.' } };
                    }
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data || {} };
                    });
                })
                .then(function (result) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = originalHtml;

                    if (!result.ok || !result.data.success) {
                        var msg = (result.data && result.data.message) ? result.data.message : 'تعذر تحديث حالة الدرس';
                        alert(msg);
                        return;
                    }

                    var mid = parseInt(result.data.module_id, 10) || moduleId;
                    var completed = !!result.data.is_completed;
                    if (completed && options.celebrate) {
                        celebrateLessonComplete(applauseAudio);
                    }

                    if (mid === getCurrentModuleId()) {
                        setCompletionCardState(completed);
                    }

                    var frame = getLearnFrame();
                    if (frame) {
                        var arr = parseCompletedModuleIds();
                        var ix = arr.indexOf(mid);
                        if (completed && ix === -1) {
                            arr.push(mid);
                        }
                        if (!completed && ix !== -1) {
                            arr.splice(ix, 1);
                        }
                        frame.dataset.completedModules = JSON.stringify(arr);
                        if (mid === getCurrentModuleId()) {
                            frame.dataset.isCompleted = completed ? '1' : '0';
                            var compWrap = document.getElementById('js-learn-sidebar-completed-wrap');
                            if (compWrap) {
                                compWrap.classList.toggle('d-none', !completed);
                            }
                        }
                    }

                    window.syncStudentLearnSidebarFromFrame();
                })
                .catch(function () {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = originalHtml;
                    alert('حدث خطأ في الاتصال، حاول مرة أخرى.');
                });
        }

        window.syncStudentLearnSidebarFromFrame = function () {
            var frame = getLearnFrame();
            if (!frame) {
                return;
            }

            var titleEl = document.getElementById('js-learn-sidebar-title');
            if (titleEl && frame.dataset.moduleTitle !== undefined) {
                titleEl.textContent = frame.dataset.moduleTitle;
            }

            var descEl = document.getElementById('js-learn-sidebar-desc');
            if (descEl) {
                if (frame.dataset.hasDescription === '1' && frame.dataset.moduleDescription) {
                    descEl.textContent = frame.dataset.moduleDescription;
                    descEl.classList.remove('d-none');
                } else {
                    descEl.classList.add('d-none');
                }
            }

            var typeEl = document.getElementById('js-learn-sidebar-type-content');
            if (typeEl && frame.dataset.moduleType) {
                var t = TYPE_LABELS[frame.dataset.moduleType] || TYPE_LABELS.default;
                typeEl.className = 'badge bg-' + t.color + '-transparent text-' + t.color;
                typeEl.innerHTML = '<i class="fe ' + t.icon + ' me-1"></i>' + t.text;
            }

            var compWrap = document.getElementById('js-learn-sidebar-completed-wrap');
            if (compWrap) {
                compWrap.classList.toggle('d-none', frame.dataset.isCompleted !== '1');
            }

            var completedIds = parseCompletedModuleIds();
            var curId = getCurrentModuleId();

            document.querySelectorAll('[data-sidebar-row-module-id]').forEach(function (item) {
                var mid = parseInt(item.getAttribute('data-sidebar-row-module-id'), 10);
                var isCompleted = completedIds.indexOf(mid) !== -1;
                applySidebarRowState(item, mid, isCompleted, curId);
            });

            if (frame.dataset.pageTitle) {
                document.title = frame.dataset.pageTitle;
            }
        };

        function setCompletionCardState(isCompleted) {
            var main = getLearnFrame();
            if (!main) {
                return;
            }
            var badge = main.querySelector('#module-completion-badge');
            var btnComplete = main.querySelector('.js-module-completion-btn[data-action="complete"]');
            var btnIncomplete = main.querySelector('.js-module-completion-btn[data-action="incomplete"]');
            if (badge) {
                badge.classList.toggle('d-none', !isCompleted);
            }
            if (btnComplete) {
                btnComplete.classList.toggle('d-none', isCompleted);
            }
            if (btnIncomplete) {
                btnIncomplete.classList.toggle('d-none', !isCompleted);
            }
        }

        function celebrateLessonComplete(applauseAudio) {
            var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!reducedMotion && typeof window.confetti === 'function') {
                var fire = window.confetti;
                var scalar = 1.08;
                var emojiShapes = [];
                if (typeof fire.shapeFromText === 'function') {
                    emojiShapes = [
                        fire.shapeFromText({ text: '👏', scalar: scalar }),
                        fire.shapeFromText({ text: '🎉', scalar: scalar }),
                        fire.shapeFromText({ text: '✨', scalar: scalar }),
                    ];
                }
                function emojiBurst(overrides) {
                    var base = {
                        scalar: scalar,
                        particleCount: 52,
                        spread: 72,
                        gravity: 0.9,
                        ticks: 260,
                        startVelocity: 46,
                    };
                    if (emojiShapes.length) {
                        base.shapes = emojiShapes;
                    }
                    return Object.assign(base, overrides || {});
                }
                fire(emojiBurst({ origin: { x: 0.5, y: 0.7 } }));
            }

            if (applauseAudio) {
                applauseAudio.currentTime = 0;
                var p = applauseAudio.play();
                if (p && typeof p.catch === 'function') {
                    p.catch(function () {});
                }
                window.setTimeout(function () {
                    try {
                        applauseAudio.pause();
                        applauseAudio.currentTime = 0;
                    } catch (e2) {}
                }, lessonApplauseMaxSeconds * 1000);
            }
        }

        function fadeAlertsInMain() {
            if (typeof window.$ !== 'undefined') {
                window.setTimeout(function () {
                    window.$('.alert').fadeOut();
                }, 5000);
            }
        }

        function setLearnMainLoading(isLoading) {
            var frame = getLearnFrame();
            if (frame) {
                frame.classList.toggle('is-loading', isLoading);
            }
        }

        var sidebarNavLoading = false;
        var sidebarNavController = null;

        var sidebarAccordion = document.getElementById('studentLearnSidebarAccordion');
        if (sidebarAccordion) {
            sidebarAccordion.addEventListener('click', function (e) {
                if (e.target.closest('.js-sidebar-completion-toggle')) {
                    return;
                }

                var a = e.target.closest('a[data-learn-sidebar-nav][data-sidebar-module-id]');
                if (!a || !a.getAttribute('href')) {
                    return;
                }
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                    return;
                }
                if (a.getAttribute('target') === '_blank') {
                    return;
                }
                e.preventDefault();

                var item = a.closest('[data-sidebar-row-module-id]');
                var targetId = parseInt(a.getAttribute('data-sidebar-module-id'), 10);
                if (targetId === getCurrentModuleId()) {
                    return;
                }
                if (sidebarNavLoading) {
                    return;
                }

                var url = a.href;
                var frame = document.getElementById('student-learn-main');
                if (!frame) {
                    window.location.href = url;
                    return;
                }

                if (sidebarNavController) {
                    sidebarNavController.abort();
                }
                sidebarNavController = new AbortController();
                var navTimeoutId = window.setTimeout(function () {
                    sidebarNavController.abort();
                }, 15000);

                sidebarNavLoading = true;
                if (item) {
                    item.classList.add('is-loading');
                }
                setLearnMainLoading(true);

                fetch(url, {
                    method: 'GET',
                    signal: sidebarNavController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                        'Turbo-Frame': 'student-learn-main',
                        'X-Learn-Partial': 'main',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        if (!response.ok) {
                            window.location.href = url;
                            return Promise.reject();
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        if (!html || (html.indexOf('id="student-learn-main"') === -1 && html.indexOf("id='student-learn-main'") === -1)) {
                            window.location.href = url;
                            return;
                        }
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var newFrame = doc.getElementById('student-learn-main');
                        if (!newFrame) {
                            window.location.href = url;
                            return;
                        }
                        frame.replaceWith(newFrame);
                        try {
                            history.pushState({ studentLearn: true }, '', url);
                        } catch (err2) {
                            history.pushState({}, '', url);
                        }
                        window.syncStudentLearnSidebarFromFrame();
                        fadeAlertsInMain();
                        syncLearnStickyVideoTop();
                        var sticky = document.querySelector('.student-learn-video-sticky-wrap');
                        if (sticky) {
                            sticky.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    })
                    .catch(function () {
                        window.location.href = url;
                    })
                    .finally(function () {
                        window.clearTimeout(navTimeoutId);
                        sidebarNavLoading = false;
                        if (item) {
                            item.classList.remove('is-loading');
                        }
                        setLearnMainLoading(false);
                        sidebarNavController = null;
                    });
            }, true);
        }

        window.addEventListener('popstate', function () {
            window.location.reload();
        });

        document.body.addEventListener('click', function (e) {
            var sidebarToggle = e.target.closest('.js-sidebar-completion-toggle');
            if (sidebarToggle) {
                e.preventDefault();
                e.stopPropagation();

                var isCompleted = sidebarToggle.dataset.isCompleted === '1';
                var url = isCompleted
                    ? sidebarToggle.dataset.urlIncomplete
                    : sidebarToggle.dataset.urlComplete;
                var moduleId = parseInt(sidebarToggle.dataset.moduleId, 10);

                requestModuleCompletion(url, sidebarToggle, moduleId, {
                    celebrate: !isCompleted,
                });
                return;
            }

            var btn = e.target.closest('.js-module-completion-btn');
            if (!btn) {
                return;
            }
            var main = getLearnFrame();
            if (!main || !main.contains(btn)) {
                return;
            }

            var url = btn.getAttribute('data-url');
            if (!url) {
                return;
            }

            var action = btn.getAttribute('data-action');
            requestModuleCompletion(url, btn, getCurrentModuleId(), {
                celebrate: action === 'complete',
            });
        });

        function onReady() {
            syncLearnStickyVideoTop();
            window.syncStudentLearnSidebarFromFrame();
            fadeAlertsInMain();
        }

        window.addEventListener('resize', syncLearnStickyVideoTop);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onReady);
        } else {
            onReady();
        }
    })();
</script>
@stop
