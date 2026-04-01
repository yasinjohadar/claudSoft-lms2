@extends('student.layouts.master')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
@endpush

@section('page-title')
    {{ $module->title }}
@stop

@push('styles')
<style>
    .card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .sidebar-nav {
        position: sticky;
        top: 100px;
    }

    .student-learn-sidebar-accordion .accordion-button {
        font-size: 0.8125rem;
        line-height: 1.35;
    }

    .student-learn-sidebar-accordion .accordion-button:not(.collapsed) {
        box-shadow: none;
    }

    .student-learn-sidebar-curriculum .accordion-item {
        background: transparent;
    }

    .video-container iframe {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        border: 0 !important;
    }

    /* يُحدَّث من JS بارتفاع .app-header الفعلي حتى يثبت الفيديو تماماً تحت الناف بار */
    .student-learn-module-page {
        --student-learn-sticky-top: calc(3.75rem + 2px);
    }

    @media (max-width: 991.98px) {
        .student-learn-module-page.main-content {
            padding-inline: 5px !important;
        }

        .student-learn-module-page > .container-fluid {
            padding-inline: 0 !important;
        }

        .student-learn-module-page .row {
            --bs-gutter-x: 0;
            margin-inline: 0;
        }

        .student-learn-module-page .row > [class*="col-"] {
            padding-inline: 0;
        }

        .student-learn-module-page .student-learn-video-card {
            border-radius: 0;
            border-inline-width: 0;
            margin-bottom: 1rem;
        }

        .student-learn-module-page .student-learn-video-card .card-body {
            padding: 0 !important;
        }

        .student-learn-module-page .student-learn-video-card .video-container {
            border-radius: 0 !important;
        }

        /* تثبيت الفيديو أسفل الهيدر: top = ارتفاع الهيدر (يُضبط بـ JS) — z-index أقل من .app-header (100) */
        .student-learn-module-page .student-learn-video-sticky-wrap {
            position: sticky;
            top: var(--student-learn-sticky-top);
            z-index: 99;
            background: var(--body-bg, #fff);
        }

        /* بطاقة إكمال الدرس: حجم أنسب للجوال */
        .student-learn-module-page .student-learn-completion-card {
            border-radius: 5px !important;
        }

        .student-learn-module-page .student-learn-completion-card .card-body {
            padding: 0.65rem 0.75rem;
        }

        .student-learn-module-page .student-learn-completion-title {
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.35;
        }

        .student-learn-module-page .student-learn-completion-title .fa-graduation-cap {
            font-size: 0.9em;
        }

        .student-learn-module-page .student-learn-completion-sub {
            font-size: 0.75rem;
            line-height: 1.35;
        }

        .student-learn-module-page .student-learn-completion-badge-text {
            font-size: 0.8125rem;
        }

        .student-learn-module-page .student-learn-completion-btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            line-height: 1.35;
        }
    }

    @media (min-width: 992px) {
        .student-learn-module-page .student-learn-video-sticky-wrap {
            position: static;
            z-index: auto;
            background: transparent;
        }
    }
</style>
@endpush

@section('content')
<div class="main-content app-content student-learn-module-page">
    <div class="container-fluid">

        <div class="row">
            {{-- الجوال: الفيديو/المحتوى أولاً، ثم محتوى الكورس؛ سطح المكتب: الشريط ثم المحتوى كما كان --}}
            <div class="col-lg-9 order-1 order-lg-2">
                @include('student.courses.learning.module-main')
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 order-2 order-lg-1">
                <div class="sidebar-nav">
                    <div class="card">
                        <!-- Module Info Header -->
                        <div class="card-header" style="background: var(--primary-color); color: #fff;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white text-primary">
                                    <span id="js-learn-sidebar-type-content">
                                    @if($module->module_type == 'video')
                                        <i class="fas fa-play me-1"></i> فيديو
                                    @elseif($module->module_type == 'lesson')
                                        <i class="fas fa-book me-1"></i> درس
                                    @elseif($module->module_type == 'assignment')
                                        <i class="fas fa-file-alt me-1"></i> واجب
                                    @elseif($module->module_type == 'quiz')
                                        <i class="fas fa-question-circle me-1"></i> اختبار
                                    @elseif($module->module_type == 'question_module')
                                        <i class="fas fa-clipboard-question me-1"></i> اختبار
                                    @elseif($module->module_type == 'resource')
                                        <i class="fas fa-link me-1"></i> مورد
                                    @else
                                        <i class="fas fa-circle me-1"></i> محتوى
                                    @endif
                                    </span>
                                </span>
                                <span id="js-learn-sidebar-completed-wrap" class="{{ $isCompleted ? '' : 'd-none' }}">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> مكتمل
                                    </span>
                                </span>
                            </div>
                            <h5 id="js-learn-sidebar-title" class="mb-1 fw-bold">{{ $module->title }}</h5>
                            <p id="js-learn-sidebar-desc" class="mb-0 small opacity-75 {{ ($module->description ?? '') === '' ? 'd-none' : '' }}">{{ Str::limit($module->description ?? '', 80) }}</p>
                        </div>

                        <!-- Course Content - Hidden for Question Modules and Quizzes to avoid distracting students -->
                        @if($module->module_type != 'question_module' && $module->module_type != 'quiz')
                            <div class="card-header bg-light border-top">
                                <h6 class="mb-0 fw-semibold"><i class="fas fa-list me-2"></i>محتوى الكورس</h6>
                            </div>
                            <div class="card-body p-2 student-learn-sidebar-curriculum" style="max-height: 450px; overflow-y: auto;">
                                <div class="accordion accordion-customicon1 accordion-primary student-learn-sidebar-accordion" id="studentLearnSidebarAccordion">
                                    @foreach($module->course->sections as $section)
                                        @php
                                            $sectionModuleCount = $section->modules->count();
                                        @endphp
                                        <div class="accordion-item border-start-0 border-end-0">
                                            <h2 class="accordion-header" id="sidebar-section-heading-{{ $section->id }}">
                                                <button class="accordion-button collapsed px-2 py-2 shadow-none"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#sidebar-section-collapse-{{ $section->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="sidebar-section-collapse-{{ $section->id }}">
                                                    <div class="d-flex align-items-center w-100 justify-content-between gap-2 text-start">
                                                        <span class="small fw-semibold text-truncate mb-0">
                                                            <i class="fas fa-folder-open text-primary me-1"></i>{{ $section->title }}
                                                        </span>
                                                        <span class="badge bg-light text-default flex-shrink-0">{{ $sectionModuleCount }} {{ $sectionModuleCount === 1 ? 'درس' : 'دروس' }}</span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="sidebar-section-collapse-{{ $section->id }}"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="sidebar-section-heading-{{ $section->id }}"
                                                 data-bs-parent="#studentLearnSidebarAccordion">
                                                <div class="accordion-body p-2 pt-0">
                                                    @foreach($section->modules as $mod)
                                                        <a href="{{ route('student.learn.module', $mod->id) }}"
                                                           data-learn-sidebar-nav="1"
                                                           data-sidebar-module-id="{{ $mod->id }}"
                                                           class="d-flex align-items-center justify-content-between text-decoration-none mb-1 p-2 rounded {{ $mod->id == $module->id ? 'bg-primary text-white' : (in_array($mod->id, $completedModules) ? 'bg-success-transparent text-success' : 'bg-light text-dark') }}"
                                                           style="font-size: 0.8rem; border-right: 3px solid {{ $mod->id == $module->id ? '#7c3aed' : (in_array($mod->id, $completedModules) ? '#10b981' : 'transparent') }};">
                                                            <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                                                @if($mod->module_type == 'video')
                                                                    <i class="fas fa-play-circle me-2 flex-shrink-0"></i>
                                                                @elseif($mod->module_type == 'lesson')
                                                                    <i class="fas fa-book-open me-2 flex-shrink-0"></i>
                                                                @elseif($mod->module_type == 'assignment')
                                                                    <i class="fas fa-file-alt me-2 flex-shrink-0"></i>
                                                                @elseif($mod->module_type == 'quiz')
                                                                    <i class="fas fa-question-circle me-2 flex-shrink-0"></i>
                                                                @elseif($mod->module_type == 'question_module')
                                                                    <i class="fas fa-clipboard-question me-2 flex-shrink-0"></i>
                                                                @else
                                                                    <i class="fas fa-circle me-2 flex-shrink-0"></i>
                                                                @endif
                                                                <span class="text-truncate">{{ $mod->title }}</span>
                                                            </div>
                                                            <span class="d-flex align-items-center flex-shrink-0" data-sidebar-status>
                                                                @if(in_array($mod->id, $completedModules))
                                                                    <i class="fas fa-check-circle {{ $mod->id == $module->id ? 'text-white' : 'text-success' }} me-1"></i>
                                                                    <small class="{{ $mod->id == $module->id ? 'text-white' : 'text-success' }}">مكتمل</small>
                                                                @else
                                                                    <i class="fas fa-circle text-muted me-1" style="font-size: 0.7rem;"></i>
                                                                    <small class="text-muted">غير مكتمل</small>
                                                                @endif
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
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
            if (!f || !f.dataset.completedModules) {
                return [];
            }
            try {
                var raw = JSON.parse(f.dataset.completedModules);
                if (!Array.isArray(raw)) {
                    return [];
                }
                return raw.map(function (x) { return parseInt(x, 10); }).filter(function (n) { return !isNaN(n); });
            } catch (e) {
                return [];
            }
        }

        var TYPE_LABELS = {
            video: { icon: 'fa-play', text: 'فيديو' },
            lesson: { icon: 'fa-book', text: 'درس' },
            assignment: { icon: 'fa-file-alt', text: 'واجب' },
            quiz: { icon: 'fa-question-circle', text: 'اختبار' },
            question_module: { icon: 'fa-clipboard-question', text: 'اختبار' },
            resource: { icon: 'fa-link', text: 'مورد' },
            default: { icon: 'fa-circle', text: 'محتوى' },
        };

        function typeLabelHtml(type) {
            var t = TYPE_LABELS[type] || TYPE_LABELS.default;
            return '<i class="fas ' + t.icon + ' me-1"></i> ' + t.text;
        }

        function sidebarStatusHtml(isCompleted, isCurrent) {
            if (isCompleted && isCurrent) {
                return '<i class="fas fa-check-circle text-white me-1"></i><small class="text-white">مكتمل</small>';
            }
            if (isCompleted) {
                return '<i class="fas fa-check-circle text-success me-1"></i><small class="text-success">مكتمل</small>';
            }
            return '<i class="fas fa-circle text-muted me-1" style="font-size: 0.7rem;"></i><small class="text-muted">غير مكتمل</small>';
        }

        function applySidebarLinkState(link, moduleId, isCompleted, currentModuleId) {
            var isCurrent = moduleId === currentModuleId;
            var base = 'd-flex align-items-center justify-content-between text-decoration-none mb-1 p-2 rounded';
            var extra;
            var border;
            if (isCurrent) {
                extra = 'bg-primary text-white';
                border = '#7c3aed';
            } else if (isCompleted) {
                extra = 'bg-success-transparent text-success';
                border = '#10b981';
            } else {
                extra = 'bg-light text-dark';
                border = 'transparent';
            }
            link.className = base + ' ' + extra;
            link.style.fontSize = '0.8rem';
            link.style.borderRight = '3px solid ' + border;

            var statusEl = link.querySelector('[data-sidebar-status]');
            if (statusEl) {
                statusEl.className = 'd-flex align-items-center flex-shrink-0';
                statusEl.innerHTML = sidebarStatusHtml(isCompleted, isCurrent);
            }
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
                    descEl.textContent = '';
                    descEl.classList.add('d-none');
                }
            }

            var typeEl = document.getElementById('js-learn-sidebar-type-content');
            if (typeEl && frame.dataset.moduleType) {
                typeEl.innerHTML = typeLabelHtml(frame.dataset.moduleType);
            }

            var compWrap = document.getElementById('js-learn-sidebar-completed-wrap');
            if (compWrap) {
                compWrap.classList.toggle('d-none', frame.dataset.isCompleted !== '1');
            }

            var completedIds = parseCompletedModuleIds();
            var curId = getCurrentModuleId();

            document.querySelectorAll('a[data-sidebar-module-id]').forEach(function (link) {
                var mid = parseInt(link.getAttribute('data-sidebar-module-id'), 10);
                var isCompleted = completedIds.indexOf(mid) !== -1;
                applySidebarLinkState(link, mid, isCompleted, curId);
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
                        fire.shapeFromText({ text: '👏', scalar: scalar }),
                        fire.shapeFromText({ text: '🎉', scalar: scalar }),
                        fire.shapeFromText({ text: '✨', scalar: scalar }),
                        fire.shapeFromText({ text: '⭐', scalar: scalar }),
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
                    } else {
                        base.shapes = ['circle'];
                    }
                    return Object.assign(base, overrides || {});
                }
                fire(emojiBurst({ origin: { x: 0.5, y: 0.7 } }));
                setTimeout(function () {
                    fire(emojiBurst({
                        particleCount: 38,
                        angle: 58,
                        spread: 58,
                        origin: { x: 0.06, y: 0.78 },
                    }));
                }, 170);
                setTimeout(function () {
                    fire(emojiBurst({
                        particleCount: 38,
                        angle: 122,
                        spread: 58,
                        origin: { x: 0.94, y: 0.78 },
                    }));
                }, 310);
            }

            function tryMp3() {
                if (!applauseAudio) {
                    return Promise.reject();
                }
                applauseAudio.currentTime = 0;
                var scheduleStop = function () {
                    window.setTimeout(function () {
                        try {
                            applauseAudio.pause();
                            applauseAudio.currentTime = 0;
                        } catch (e2) {}
                    }, lessonApplauseMaxSeconds * 1000);
                };
                var p = applauseAudio.play();
                if (p && typeof p.then === 'function') {
                    p.then(scheduleStop).catch(function () {});
                } else {
                    scheduleStop();
                }
                return p || Promise.resolve();
            }
            tryMp3().catch(function () {});
        }

        function fadeAlertsInMain() {
            if (typeof window.$ !== 'undefined') {
                window.setTimeout(function () {
                    window.$('.alert').fadeOut();
                }, 5000);
            }
        }

        var sidebarAccordion = document.getElementById('studentLearnSidebarAccordion');
        if (sidebarAccordion) {
            sidebarAccordion.addEventListener('click', function (e) {
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

                var url = a.href;
                var frame = document.getElementById('student-learn-main');
                if (!frame) {
                    window.location.href = url;
                    return;
                }

                fetch(url, {
                    method: 'GET',
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
                    })
                    .catch(function () {});
            }, true);
        }

        window.addEventListener('popstate', function () {
            window.location.reload();
        });

        document.body.addEventListener('click', function (e) {
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
            if (!csrfToken) {
                alert('لم يتم العثور على رمز الأمان (CSRF). حدّث الصفحة.');
                return;
            }

            var applauseAudio = new Audio(lessonCompleteApplauseUrl);
            applauseAudio.volume = 0.42;
            applauseAudio.preload = 'auto';

            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>جارٍ التحديث...';

            fetch(url, {
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
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (!result.ok || !result.data.success) {
                        var msg = (result.data && result.data.message) ? result.data.message : 'تعذر تحديث حالة الدرس';
                        alert(msg);
                        return;
                    }

                    var mid = parseInt(result.data.module_id, 10);
                    var completed = !!result.data.is_completed;
                    if (completed) {
                        celebrateLessonComplete(applauseAudio);
                    }
                    setCompletionCardState(completed);

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
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    alert('حدث خطأ في الاتصال، حاول مرة أخرى.');
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
