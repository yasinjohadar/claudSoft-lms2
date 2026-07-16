@extends('admin.layouts.master')

@section('page-title')
    الكود الابتدائي — {{ $challenge->title }}
@stop

@push('styles')
    @include('admin.pages.programming-challenges.partials.form-styles')
    <style>
        .pch-langs__steps {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .pch-langs__step {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e2e8f0;
            color: #64748b;
        }

        .pch-langs__step.is-active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .pch-langs__step.is-done {
            background: #dcfce7;
            color: #15803d;
        }

        .pch-starter__footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-top: 1px solid var(--pf-border, #e2e8f0);
            background: var(--pf-soft, #f8fafc);
        }

        .pch-ide {
            border: 1px solid #1e293b;
            border-radius: 14px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.18);
        }

        .pch-ide__bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            background: #111827;
            border-bottom: 1px solid #1f2937;
        }

        .pch-ide__tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .pch-ide__tab {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.8rem;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .pch-ide__tab:hover {
            background: #1e293b;
            color: #e2e8f0;
        }

        .pch-ide__tab.is-active {
            background: #1e293b;
            color: #f8fafc;
        }

        .pch-ide__dot {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pch-ide__meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pch-ide__file {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            background: #1e293b;
            color: #cbd5e1;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
        }

        .pch-ide__file input {
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            width: 7.5rem;
            padding: 0;
            outline: none;
            direction: ltr;
            text-align: left;
        }

        .pch-ide__pane {
            display: none;
            min-height: 420px;
            height: min(58vh, 560px);
        }

        .pch-ide__pane.is-active {
            display: block;
        }

        .pch-ide__host {
            height: 100%;
            direction: ltr;
            text-align: left;
        }

        .pch-ide__host .cm-editor {
            height: 100%;
        }

        .pch-ide__fallback {
            width: 100%;
            height: 100%;
            min-height: 420px;
            border: 0;
            resize: vertical;
            padding: 1rem;
            background: #1e1e1e;
            color: #d4d4d4;
            font-family: Consolas, 'Cascadia Code', Monaco, monospace;
            font-size: 14px;
            line-height: 1.55;
            direction: ltr;
            text-align: left;
        }

        .pch-ide__fallback:focus {
            outline: none;
        }

        .pch-starter__note {
            margin-bottom: 1rem;
            padding: 0.75rem 0.95rem;
            border-radius: 12px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 0.82rem;
            line-height: 1.6;
        }

        [data-theme-mode="dark"] .pch-starter__note {
            background: rgba(37, 99, 235, 0.16);
            border-color: rgba(147, 197, 253, 0.35);
            color: #bfdbfe;
        }
    </style>
@endpush

@section('content')
    @php
        $isWeb = $challenge->isWebSandbox();
        $existingFiles = $challenge->files->keyBy('file_role');
        $webRoles = [
            'html' => [
                'label' => 'HTML',
                'lang' => 'html',
                'color' => '#E34F26',
                'filename' => 'index.html',
                'content' => "<!DOCTYPE html>\n<html lang=\"ar\" dir=\"rtl\">\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n  <title>التحدي</title>\n</head>\n<body>\n  <h1>مرحباً</h1>\n</body>\n</html>",
            ],
            'css' => [
                'label' => 'CSS',
                'lang' => 'css',
                'color' => '#1572B6',
                'filename' => 'style.css',
                'content' => "body {\n  font-family: sans-serif;\n  margin: 0;\n  padding: 1.5rem;\n}\n",
            ],
            'js' => [
                'label' => 'JS',
                'lang' => 'javascript',
                'color' => '#B8A000',
                'filename' => 'script.js',
                'content' => "// اكتب كود JavaScript هنا\n",
            ],
        ];
    @endphp

    <div class="main-content app-content pch-form">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="pch-form__hero">
                <div>
                    <nav>
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programming-challenges.index') }}">التحديات البرمجية</a></li>
                            <li class="breadcrumb-item active">الكود الابتدائي</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">الكود الابتدائي — {{ $challenge->title }}</h5>
                    <p>
                        @if($isWeb)
                            مشروع ويب واحد: اكتب نقطة البداية لملفات HTML وCSS وJavaScript معاً في محرر موحّد.
                        @else
                            عرّف الكود الابتدائي لكل لغة تنفيذ في هذا التحدي.
                        @endif
                    </p>
                    <div class="pch-langs__steps">
                        <span class="pch-langs__step is-done">1 · اللغات</span>
                        <span class="pch-langs__step is-active">2 · الكود الابتدائي</span>
                        <span class="pch-langs__step">3 · الاختبارات</span>
                    </div>
                </div>
                <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="pch-form__side-link">
                    <i class="fe fe-layers"></i> تعديل اللغات
                </a>
            </div>

            <form id="pch-starter-form" action="{{ route('programming-challenges.update-starter', $challenge->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="pch-form__panel">
                    <div class="pch-form__panel-head">
                        <span class="pch-form__panel-icon pch-form__panel-icon--slate"><i class="fe fe-terminal"></i></span>
                        <div>
                            <h6 class="pch-form__panel-title">
                                @if($isWeb) محرر مشروع الويب @else الكود الابتدائي @endif
                            </h6>
                            <p class="pch-form__panel-sub">
                                @if($isWeb)
                                    تبويبات HTML · CSS · JS داخل مساحة عمل واحدة — يظهر هذا الكود للطالب عند بدء المحاولة
                                @else
                                    ملف بداية لكل لغة مختارة
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="pch-form__panel-body">
                        @if($isWeb)
                            <div class="pch-starter__note">
                                الحزمة كاملة (HTML + CSS + JS). بدّل بين التبويبات دون فقدان المحتوى — عند الحفظ تُخزَّن الملفات الثلاثة معاً.
                            </div>

                            <div class="pch-ide" id="pch-web-ide" data-mode="web">
                                <div class="pch-ide__bar">
                                    <div class="pch-ide__tabs" role="tablist">
                                        @foreach($webRoles as $role => $meta)
                                            <button type="button"
                                                    class="pch-ide__tab {{ $loop->first ? 'is-active' : '' }}"
                                                    data-tab="{{ $role }}"
                                                    role="tab"
                                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                                <span class="pch-ide__dot" style="background: {{ $meta['color'] }}"></span>
                                                {{ $meta['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="pch-ide__meta">
                                        @foreach($webRoles as $role => $meta)
                                            @php $file = $existingFiles->get($role); @endphp
                                            <label class="pch-ide__file pch-ide__file-label" data-file-for="{{ $role }}" @if(! $loop->first) style="display: none" @endif>
                                                <i class="fe fe-file-text"></i>
                                                <input type="text"
                                                       name="files[{{ $loop->index }}][filename]"
                                                       value="{{ $file->filename ?? $meta['filename'] }}"
                                                       autocomplete="off"
                                                       spellcheck="false">
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                @foreach($webRoles as $role => $meta)
                                    @php
                                        $file = $existingFiles->get($role);
                                        $content = $file->content ?? $meta['content'];
                                    @endphp
                                    <input type="hidden" name="files[{{ $loop->index }}][file_role]" value="{{ $role }}">
                                    <div class="pch-ide__pane {{ $loop->first ? 'is-active' : '' }}"
                                         data-pane="{{ $role }}"
                                         data-lang="{{ $meta['lang'] }}">
                                        <textarea class="pch-ide__source d-none"
                                                  name="files[{{ $loop->index }}][content]"
                                                  data-role="{{ $role }}">{{ $content }}</textarea>
                                        <div class="pch-ide__host" data-host="{{ $role }}"></div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @forelse($challenge->languages as $i => $lang)
                                @php
                                    $file = $challenge->files->where('programming_language_id', $lang->id)->first();
                                    $cmLang = $lang->monaco_language_id ?: $lang->slug;
                                @endphp
                                <div class="mb-3">
                                    <input type="hidden" name="files[{{ $i }}][file_role]" value="starter">
                                    <input type="hidden" name="files[{{ $i }}][programming_language_id]" value="{{ $lang->id }}">
                                    <label class="pch-form__label">{{ $lang->display_name }}</label>
                                    <input type="text" name="files[{{ $i }}][filename]" class="form-control form-control-sm mb-2"
                                           value="{{ $file->filename ?? ($lang->default_filename ?? 'main.txt') }}"
                                           dir="ltr" style="text-align:left; max-width: 16rem;">
                                    <div class="pch-ide" data-mode="runner" data-lang="{{ $cmLang }}">
                                        <div class="pch-ide__bar">
                                            <span class="pch-ide__tab is-active" style="pointer-events:none">
                                                <span class="pch-ide__dot" style="background: {{ $lang->color ?: '#2563eb' }}"></span>
                                                {{ $lang->display_name }}
                                            </span>
                                        </div>
                                        <div class="pch-ide__pane is-active" style="display:block"
                                             data-pane="lang-{{ $lang->id }}"
                                             data-lang="{{ $cmLang }}">
                                            <textarea class="pch-ide__source d-none"
                                                      name="files[{{ $i }}][content]"
                                                      data-role="lang-{{ $lang->id }}">{{ $file->content ?? '' }}</textarea>
                                            <div class="pch-ide__host" data-host="lang-{{ $lang->id }}"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info mb-0">
                                    اختر اللغات أولاً من
                                    <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}">صفحة اللغات</a>
                                </div>
                            @endforelse
                        @endif
                    </div>

                    <div class="pch-starter__footer">
                        <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="pch-form__side-link">
                            <i class="fe fe-arrow-right"></i> رجوع للغات
                        </a>
                        <button type="submit" class="pch-form__submit" style="width: auto; min-width: 14rem;">
                            حفظ والمتابعة للاختبارات
                            <i class="fe fe-arrow-left"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@push('scripts')
    <script src="{{ asset('assets/js/challenge-ide-codemirror.bundle.js') }}?v={{ @filemtime(public_path('assets/js/challenge-ide-codemirror.bundle.js')) ?: '1' }}"></script>
    <script>
    (function () {
        var editors = {};

        function syncAll() {
            Object.keys(editors).forEach(function (role) {
                var ed = editors[role];
                var ta = document.querySelector('textarea.pch-ide__source[data-role="' + role + '"]');
                if (ed && ta) ta.value = ed.getValue();
            });
        }

        function activateTab(role) {
            document.querySelectorAll('#pch-web-ide .pch-ide__tab').forEach(function (btn) {
                var on = btn.getAttribute('data-tab') === role;
                btn.classList.toggle('is-active', on);
                btn.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            document.querySelectorAll('#pch-web-ide .pch-ide__pane').forEach(function (pane) {
                pane.classList.toggle('is-active', pane.getAttribute('data-pane') === role);
            });
            document.querySelectorAll('#pch-web-ide .pch-ide__file-label').forEach(function (label) {
                label.style.display = label.getAttribute('data-file-for') === role ? '' : 'none';
            });
            var ed = editors[role];
            if (ed && typeof ed.layout === 'function') {
                setTimeout(function () { ed.layout(); ed.focus(); }, 30);
            }
        }

        function mountEditor(host, role, language, initialDoc) {
            var create = window.__challengeCreateCodeMirror;
            if (!create || !host) {
                var ta = document.querySelector('textarea.pch-ide__source[data-role="' + role + '"]');
                if (ta) {
                    ta.classList.remove('d-none');
                    ta.classList.add('pch-ide__fallback');
                }
                return;
            }

            host.innerHTML = '';
            editors[role] = create(host, {
                doc: initialDoc || '',
                language: language || 'plaintext',
                onChange: function () {
                    var ta = document.querySelector('textarea.pch-ide__source[data-role="' + role + '"]');
                    if (ta && editors[role]) ta.value = editors[role].getValue();
                }
            });
        }

        function initAll() {
            document.querySelectorAll('.pch-ide__pane').forEach(function (pane) {
                var role = pane.getAttribute('data-pane');
                var lang = pane.getAttribute('data-lang');
                var ta = pane.querySelector('textarea.pch-ide__source');
                var host = pane.querySelector('.pch-ide__host');
                if (!role || !host || !ta) return;
                mountEditor(host, role, lang, ta.value);
            });
        }

        var webIde = document.getElementById('pch-web-ide');
        if (webIde) {
            webIde.querySelectorAll('.pch-ide__tab').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    activateTab(btn.getAttribute('data-tab'));
                });
            });
        }

        var form = document.getElementById('pch-starter-form');
        if (form) {
            form.addEventListener('submit', syncAll);
        }

        function boot() {
            if (window.__challengeCreateCodeMirror) {
                initAll();
                return;
            }
            window.addEventListener('challenge-codemirror-ready', function onReady() {
                window.removeEventListener('challenge-codemirror-ready', onReady);
                initAll();
            });
            setTimeout(function () {
                if (!Object.keys(editors).length) initAll();
            }, 800);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
    </script>
@endpush
