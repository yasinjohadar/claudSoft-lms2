<!DOCTYPE html>
<html lang="ar" dir="rtl"@if($pdfExport ?? false) data-theme="light"@endif>
@php
    $pdfExport = $pdfExport ?? false;
    $forcedTheme = $forcedTheme ?? null;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @unless($pdfExport)
    {{-- تطبيق الثيم قبل أي CSS لتجنب وميض الوضع الليلي/النهاري --}}
    <script>
        (function () {
            var forcedTheme = @json($forcedTheme);
            var theme = forcedTheme || 'dark';
            if (!forcedTheme) {
                try {
                    theme = localStorage.getItem('claudsoft-docs-theme') || 'dark';
                } catch (e) { /* ignore */ }
            }
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.classList.add('docs-theme-init');
        })();
    </script>
    @endunless
    <style>
        html.docs-theme-init { color-scheme: dark; }
        html.docs-theme-init[data-theme="light"] { color-scheme: light; }
        html.docs-theme-init,
        html.docs-theme-init body {
            background-color: #0a0e17;
            color: #e8eaf0;
        }
        html.docs-theme-init[data-theme="light"],
        html.docs-theme-init[data-theme="light"] body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        html.docs-theme-init *,
        html.docs-theme-init *::before,
        html.docs-theme-init *::after {
            transition: none !important;
        }
        html.docs-theme-init .content-section {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }
    </style>

    <title>@yield('title', 'التوثيق')</title>
    @stack('meta')

    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <style>
        .docs-content pre[class*="language-"]:not(.code-block pre),
        .docs-content code[class*="language-"]:not(.code-block code) {
            background: transparent;
        }
    </style>

    <link href="{{ asset('docs/css/style.css') }}?v={{ filemtime(public_path('docs/css/style.css')) }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
</head>

<body @class([
    'docs-pdf-export' => $pdfExport,
])>

    @if($pdfExport ?? false)
        @include('frontend.docs.partials.pdf-running-header')
    @endif

    @unless($pdfExport)
    <div class="docs-top-toolbar">
        <button class="theme-toggle" type="button" onclick="toggleTheme()" aria-label="تبديل الوضع">
            <span class="icon moon">🌙</span>
            <span class="icon sun">☀️</span>
            <span class="text">الوضع</span>
        </button>
        @stack('docs-toolbar')
    </div>
    @endunless

    @unless($pdfExport)
    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>
    <div class="glow-blob blob-3"></div>
    @endunless

    @yield('content')

    @if($pdfExport ?? false)
        @include('frontend.docs.partials.pdf-document-footer')
    @endif

    {{-- تغليف أكواد pre فوراً بعد المحتوى قبل Prism لتقليل وميض التنسيق --}}
    <script>
        function detectCodeLanguage(codeEl, pre) {
            var className = (codeEl && codeEl.className) || (pre && pre.className) || '';
            var match = className.match(/language-([\w-]+)/);
            var lang = match ? match[1].toLowerCase() : '';
            var sample = ((codeEl && codeEl.textContent) || (pre && pre.textContent) || '').trim().slice(0, 1200);
            var languageNames = {
                'php': 'PHP', 'javascript': 'JavaScript', 'js': 'JavaScript',
                'typescript': 'TypeScript', 'ts': 'TypeScript', 'python': 'Python',
                'java': 'Java', 'cpp': 'C++', 'c': 'C', 'csharp': 'C#', 'cs': 'C#',
                'sql': 'SQL', 'json': 'JSON', 'bash': 'Bash', 'shell': 'Shell', 'sh': 'Shell',
                'html': 'HTML', 'css': 'CSS', 'markup': 'HTML', 'xml': 'XML', 'dart': 'Dart',
                'ruby': 'Ruby', 'go': 'Go', 'rust': 'Rust', 'swift': 'Swift', 'kotlin': 'Kotlin',
                'yaml': 'YAML', 'markdown': 'Markdown', 'text': 'Text', 'plaintext': 'Text'
            };
            // An explicit language- class is the author's answer; sniffing over the
            // top of it re-tagged language-html blocks containing the word
            // "function" as JavaScript and highlighted them wrongly.
            if (lang) {
                return languageNames[lang] || lang.replace(/-/g, ' ').toUpperCase();
            }

            var detectedLang = '';
            if (/\b(const|let|var|function|=>|console\.log|document\.|window\.)\b/.test(sample)) {
                detectedLang = 'javascript';
            } else if (/^\s*<\?php/m.test(sample) || (/\$\w+/.test(sample) && /\b(echo|function|namespace|use |public |private )\b/.test(sample))) {
                detectedLang = 'php';
            } else if (/^\s*def \w+\(/m.test(sample) || /\bprint\s*\(/.test(sample)) {
                detectedLang = 'python';
            } else if (/\bvoid main\s*\(/.test(sample) || /\bimport\s+'package:/.test(sample)) {
                detectedLang = 'dart';
            } else if (/^\s*<!DOCTYPE|<html|<div|<span/i.test(sample)) {
                detectedLang = 'markup';
            } else if (/^\s*[\w-]+\s*:\s*[^;]+;/m.test(sample) && /\{|\}/.test(sample) && !/\bfunction\b/.test(sample)) {
                detectedLang = 'css';
            } else if (/^\s*\{[\s\S]*"[\w-]+"\s*:/m.test(sample)) {
                detectedLang = 'json';
            } else if (lang) {
                detectedLang = lang;
            }
            if (codeEl && detectedLang) {
                codeEl.className = codeEl.className.replace(/\blanguage-[\w-]+\b/g, '').trim();
                codeEl.className = (codeEl.className + ' language-' + detectedLang).trim();
            }
            return languageNames[detectedLang] || (detectedLang ? detectedLang.replace(/-/g, ' ').toUpperCase() : 'CODE');
        }

        function wrapDocsContentPreBlocks() {
            document.querySelectorAll('.docs-content pre').forEach(function (pre) {
                if (pre.closest('.code-block')) return;
                var codeEl = pre.querySelector('code');
                if (!codeEl) {
                    codeEl = document.createElement('code');
                    codeEl.textContent = pre.textContent;
                    pre.textContent = '';
                    pre.appendChild(codeEl);
                }
                if (!codeEl.className.match(/language-/)) {
                    codeEl.className = (codeEl.className + ' language-javascript').trim();
                }
                var block = document.createElement('div');
                block.className = 'code-block';
                var header = document.createElement('div');
                header.className = 'code-header';
                header.innerHTML = '<div class="code-dots"><span></span><span></span><span></span></div><span class="code-lang">CODE</span>';
                header.querySelector('.code-lang').textContent = detectCodeLanguage(codeEl, pre);
                pre.parentNode.insertBefore(block, pre);
                block.appendChild(header);
                block.appendChild(pre);
            });
        }

        wrapDocsContentPreBlocks();
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
        if (typeof Prism !== 'undefined' && Prism.plugins && Prism.plugins.autoloader) {
            Prism.plugins.autoloader.languages_path =
                'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/';
        }
    </script>

    <script>
        function toggleTheme() {
            if (@json($pdfExport)) {
                return;
            }

            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('claudsoft-docs-theme', newTheme);
        }

        function initCodeBlocks() {
            try {
                if (typeof Prism !== 'undefined') {
                    Prism.highlightAll();
                }
            } catch (e) {
                console.error('Prism error:', e);
            }

            document.querySelectorAll('.code-block').forEach(function (block) {
                if (block.querySelector('.copy-btn')) {
                    return;
                }

                const header = block.querySelector('.code-header');
                const target = header || block;

                const copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'copy-btn';
                copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> <span class="btn-text">نسخ</span>';
                target.appendChild(copyBtn);

                copyBtn.addEventListener('click', async function () {
                    const codeEl = block.querySelector('code');
                    if (!codeEl) {
                        return;
                    }
                    const code = codeEl.innerText.trim();

                    try {
                        await navigator.clipboard.writeText(code);
                        const originalHTML = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> <span class="btn-text">تم</span>';
                        copyBtn.classList.add('copied');

                        setTimeout(function () {
                            copyBtn.innerHTML = originalHTML;
                            copyBtn.classList.remove('copied');
                        }, 2000);
                    } catch (err) {
                        console.error('Failed to copy: ', err);
                    }
                });
            });

            document.documentElement.classList.remove('docs-theme-init');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCodeBlocks);
        } else {
            initCodeBlocks();
        }
    </script>
    {{-- إبقاء ?token= على روابط /docs للتنقل داخل WebView (Flutter) --}}
    @unless($pdfExport)
    <script>
        (function () {
            function pathIsUnderDocs(path) {
                if (path.indexOf('/docs') === 0) {
                    return true;
                }
                return /^\/[a-z]{2}(-[a-z]{2})?\/docs(\/|$)/i.test(path);
            }
            function appendTokenToDocsLinks() {
                var params = new URLSearchParams(window.location.search);
                var token = params.get('token');
                if (!token) {
                    return;
                }
                document.querySelectorAll('a[href]').forEach(function (a) {
                    try {
                        var href = a.getAttribute('href');
                        if (!href || href.charAt(0) === '#' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
                            return;
                        }
                        var u = new URL(href, window.location.origin);
                        if (!pathIsUnderDocs(u.pathname)) {
                            return;
                        }
                        if (u.searchParams.has('token')) {
                            return;
                        }
                        u.searchParams.set('token', token);
                        if (href.indexOf('://') === -1) {
                            a.setAttribute('href', u.pathname + u.search + u.hash);
                        } else {
                            a.setAttribute('href', u.toString());
                        }
                    } catch (e) { /* ignore */ }
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', appendTokenToDocsLinks);
            } else {
                appendTokenToDocsLinks();
            }
        })();
    </script>
    @endunless
    @stack('scripts')
</body>

</html>
