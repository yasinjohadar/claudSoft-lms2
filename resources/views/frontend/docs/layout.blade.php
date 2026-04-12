<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'التوثيق')</title>
    @stack('meta')

    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">

    <link href="{{ asset('docs/css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>

    <button class="theme-toggle" type="button" onclick="toggleTheme()" aria-label="تبديل الوضع">
        <span class="icon moon">🌙</span>
        <span class="icon sun">☀️</span>
        <span class="text">الوضع</span>
    </button>

    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>
    <div class="glow-blob blob-3"></div>

    @yield('content')

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
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('claudsoft-docs-theme', newTheme);
        }

        (function () {
            const savedTheme = localStorage.getItem('claudsoft-docs-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();

        function wrapDocsContentPreBlocks() {
            document.querySelectorAll('.docs-content pre').forEach(function (pre) {
                if (pre.closest('.code-block')) {
                    return;
                }
                const block = document.createElement('div');
                block.className = 'code-block';
                const header = document.createElement('div');
                header.className = 'code-header';
                header.innerHTML = '<div class="code-dots"><span></span><span></span><span></span></div><span class="code-lang">CODE</span>';
                const codeEl = pre.querySelector('code');
                if (codeEl) {
                    const m = codeEl.className.match(/language-(\w+)/);
                    if (m) {
                        header.querySelector('.code-lang').textContent = m[1].toUpperCase();
                    }
                }
                pre.parentNode.insertBefore(block, pre);
                block.appendChild(header);
                block.appendChild(pre);
            });
        }

        function initCodeBlocks() {
            wrapDocsContentPreBlocks();
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
                copyBtn.innerHTML = '<span class="copy-icon">📋</span> <span class="btn-text">نسخ</span>';
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
                        copyBtn.innerHTML = '<span class="copy-icon">✅</span> <span class="btn-text">تم النسخ</span>';
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
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCodeBlocks);
        } else {
            initCodeBlocks();
        }
    </script>
    {{-- إبقاء ?token= على روابط /docs للتنقل داخل WebView (Flutter) --}}
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
    @stack('scripts')
</body>

</html>
