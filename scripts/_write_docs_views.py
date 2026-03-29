# -*- coding: utf-8 -*-
from pathlib import Path

base = Path(__file__).resolve().parent.parent / "resources/views/frontend/docs"
base.mkdir(parents=True, exist_ok=True)

layout = r"""<!DOCTYPE html>
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
    @stack('scripts')
</body>

</html>
"""

index = r"""@extends('frontend.docs.layout')

@section('title', 'التوثيق')

@section('content')
    <div class="container">
        <header>
            <div class="header-tag">مركز المساعدة</div>
            <h1>التوثيق</h1>
            <p class="header-desc">تصفح الأدلة والشروحات حسب القسم.</p>
        </header>

        <section class="content-section" style="animation-delay: 0.1s;">
            @if ($categories->isEmpty())
                <div class="text-block">لا توجد أقسام توثيق منشورة حالياً.</div>
            @else
                <div class="course-grid">
                    @foreach ($categories as $cat)
                        <a href="{{ route('frontend.docs.show', ['categorySlug' => $cat->slug]) }}" class="lesson-card">
                            <div class="lesson-number">{{ $loop->iteration }}</div>
                            <h2 class="lesson-title">{{ $cat->name }}</h2>
                            @if ($cat->description)
                                <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($cat->description), 140) }}</p>
                            @endif
                            <div class="lesson-status">
                                <span></span>
                                <div class="start-btn">
                                    <span>فتح القسم</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <footer>
            <strong>التوثيق</strong> — مركز المساعدة
        </footer>
    </div>
@endsection
"""


show = r"""@extends('frontend.docs.layout')

@section('title')
    {{ $page->meta_title ?: $page->title . ' — ' . $category->name }}
@endsection

@push('meta')
    @if ($page->meta_description)
        <meta name="description" content="{{ e($page->meta_description) }}">
    @endif
@endpush

@section('content')
    <div class="container">
        <header>
            <div class="header-tag">{{ $category->name }}</div>
            <h1>{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="header-desc">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section class="content-section docs-content" style="animation-delay: 0.1s;">
            {!! $page->content !!}
        </section>

        <footer>
            <strong>{{ $category->name }}</strong> — {{ $page->title }}
        </footer>
    </div>
@endsection
"""
(base / "layout.blade.php").write_text(layout, encoding="utf-8")
(base / "index.blade.php").write_text(index, encoding="utf-8")
(base / "show.blade.php").write_text(show, encoding="utf-8")
print("ok", base)