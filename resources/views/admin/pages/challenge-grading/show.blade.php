@extends('admin.layouts.master')

@php
    $isWeb = $attempt->challenge->isWebSandbox();
    $files = $submission?->files ?? collect();

    $iconFor = function ($file) {
        $role = strtolower((string) ($file->file_role ?? ''));
        $name = strtolower((string) ($file->filename ?? ''));
        if ($role === 'html' || str_ends_with($name, '.html')) return 'html';
        if ($role === 'css' || str_ends_with($name, '.css')) return 'css';
        if (in_array($role, ['js', 'javascript'], true) || str_ends_with($name, '.js')) return 'js';
        return 'file';
    };
@endphp

@section('page-title')
    تقييم تسليم — {{ $attempt->challenge->title }}
@stop

@push('styles')
    <style>
        .cg-grade-page {
            --cg-border: var(--default-border, #e5e7eb);
            --cg-muted: var(--text-muted, #6b7280);
            --cg-surface: var(--custom-white, #fff);
            --cg-code-bg: #1e1e1e;
        }

        .cg-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin: 1.25rem 0 1.5rem;
        }

        .cg-hero h5 { margin: 0 0 0.35rem; }
        .cg-hero p { margin: 0; color: var(--cg-muted); }

        .cg-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 1199.98px) {
            .cg-workspace { grid-template-columns: 1fr; }
        }

        .cg-panel {
            border: 1px solid var(--cg-border);
            border-radius: 14px;
            background: var(--cg-surface);
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            min-height: 520px;
            display: flex;
            flex-direction: column;
        }

        .cg-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--cg-border);
            background: rgba(15, 23, 42, 0.02);
        }

        .cg-panel__title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .cg-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid var(--cg-border);
            background: #252526;
            margin: 0;
            list-style: none;
        }

        .cg-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #cfcfcf;
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .cg-tab.active {
            background: #1e1e1e;
            color: #fff;
            box-shadow: inset 0 -2px 0 0 #3b82f6;
        }

        .cg-tab-icon { width: 14px; height: 14px; display: block; }

        .cg-code-wrap {
            flex: 1;
            min-height: 0;
            background: var(--cg-code-bg);
        }

        .cg-code {
            margin: 0;
            padding: 1rem 1.1rem;
            max-height: 560px;
            overflow: auto;
            color: #d4d4d4;
            font-size: 0.85rem;
            line-height: 1.55;
            direction: ltr;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .cg-preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.55rem 0.85rem;
            border-bottom: 1px solid var(--cg-border);
            background: rgba(15, 23, 42, 0.04);
            color: inherit;
        }

        [data-theme-mode="dark"] .cg-preview-toolbar {
            background: #0f172a;
            border-bottom-color: rgba(148, 163, 184, 0.25);
            color: #e2e8f0;
        }

        .cg-preview-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .cg-preview-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1;
            padding: 0.4rem 0.7rem;
            border-radius: 0.45rem;
            border: 1px solid var(--cg-border);
            background: var(--cg-surface);
            color: inherit;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .cg-preview-btn:hover,
        .cg-preview-btn:focus-visible {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            color: inherit;
            outline: none;
        }

        [data-theme-mode="dark"] .cg-preview-btn {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(226, 232, 240, 0.35);
            color: #e2e8f0;
        }

        [data-theme-mode="dark"] .cg-preview-btn:hover,
        [data-theme-mode="dark"] .cg-preview-btn:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(226, 232, 240, 0.55);
            color: #fff;
        }

        .cg-preview-frame {
            flex: 1;
            width: 100%;
            min-height: 460px;
            border: 0;
            background: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="main-content app-content cg-grade-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="cg-hero">
                <div>
                    <h5 class="page-title fs-21">تقييم: {{ $attempt->challenge->title }}</h5>
                    <p>الطالب: {{ $attempt->student->name }} — محاولة #{{ $attempt->attempt_number }}</p>
                </div>
                <a href="{{ route('admin.challenge-grading.index') }}" class="btn btn-light btn-sm">العودة للقائمة</a>
            </div>

            <div class="cg-workspace">
                <div class="cg-panel">
                    <div class="cg-panel__header">
                        <h6 class="cg-panel__title">كود الطالب</h6>
                    </div>

                    @if($files->count())
                        <ul class="cg-tabs" role="tablist">
                            @foreach($files as $i => $file)
                                @php $kind = $iconFor($file); @endphp
                                <li>
                                    <button class="cg-tab @if($i === 0) active @endif" type="button"
                                            data-bs-toggle="tab" data-bs-target="#file-{{ $i }}">
                                        @if($kind === 'html')
                                            <svg class="cg-tab-icon" viewBox="0 0 128 128" aria-hidden="true"><path fill="#E44D26" d="M19 0l9 103 36 10 36-10 9-103z"/><path fill="#F16529" d="M64 117l29-8 8-88H64z"/><path fill="#EBEBEB" d="M64 52H45l-1-14h20V25H30l4 45h30zm0 40l-.1.1-15.4-4.1-1-11H34l2 23 28 7.7.1-.1z"/><path fill="#FFF" d="M64 52v13h16.8l-1.6 18-15.2 4.1V100l28-7.7 2-33.3.4-4.5L97 25H64z"/></svg>
                                        @elseif($kind === 'css')
                                            <svg class="cg-tab-icon" viewBox="0 0 128 128" aria-hidden="true"><path fill="#1572B6" d="M19 0l9 103 36 10 36-10 9-103z"/><path fill="#33A9DC" d="M64 117l29-8 8-88H64z"/><path fill="#EBEBEB" d="M64 52H45.5l-1.2-14H64V25H31.2l.5 6 3.2 36H64zm-.1 40.1l-.1.1-15.3-4.1-.9-11H34l1.7 22.5 28.1 7.8.1-.1z"/><path fill="#FFF" d="M64 52v13h16.9l-1.6 17.9-15.3 4.1v13.1l28.1-7.8 2.1-33.8.3-4.5L97 25H64v13h32.3z"/></svg>
                                        @elseif($kind === 'js')
                                            <svg class="cg-tab-icon" viewBox="0 0 128 128" aria-hidden="true"><path fill="#F7DF1E" d="M2 2h124v124H2z"/><path d="M67.3 97.4c2 3.4 4.6 5.9 9.3 5.9 3.9 0 6.4-2 6.4-4.7 0-3.3-2.6-4.4-6.9-6.3l-2.4-1c-6.9-2.9-11.5-6.6-11.5-14.3 0-7.1 5.4-12.6 13.9-12.6 6 0 10.4 2.1 13.5 7.6l-7.4 4.7c-1.6-2.9-3.4-4-6.1-4-2.8 0-4.5 1.8-4.5 4 0 2.8 1.7 3.9 5.7 5.6l2.4 1c8.1 3.5 12.7 7 12.7 15 0 8.6-6.7 13.3-15.7 13.3-8.8 0-14.5-4.2-17.3-9.7zm-29 1.5c1.5 2.7 2.9 4.9 6.2 4.9 3.2 0 5.2-1.2 5.2-6.1V61.3h9.3v36.6c0 9.6-5.6 14-13.8 14-7.4 0-11.7-3.8-13.9-8.5z"/></svg>
                                        @else
                                            <span class="badge bg-secondary">file</span>
                                        @endif
                                        <span>{{ $file->filename }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content cg-code-wrap">
                            @foreach($files as $i => $file)
                                <div class="tab-pane fade @if($i === 0) show active @endif" id="file-{{ $i }}">
                                    <pre class="cg-code" dir="ltr"><code>{{ $file->content }}</code></pre>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-muted">لا توجد ملفات في هذا التسليم</div>
                    @endif

                    @if($submission?->student_notes)
                        <div class="p-3 border-top">
                            <strong>ملاحظات الطالب:</strong>
                            <p class="mb-0 mt-1">{{ $submission->student_notes }}</p>
                        </div>
                    @endif
                </div>

                @if($isWeb && $files->count())
                    <div class="cg-panel">
                        <div class="cg-preview-toolbar">
                            <span class="fw-semibold">معاينة حية</span>
                            <div class="cg-preview-actions">
                                <button type="button" class="cg-preview-btn" id="cg-refresh-preview" title="تحديث المعاينة">
                                    <i class="fe fe-refresh-cw"></i>
                                    <span>تحديث</span>
                                </button>
                                <button type="button" class="cg-preview-btn" id="cg-open-preview" title="فتح في تاب جديد">
                                    <i class="fe fe-external-link"></i>
                                    <span>فتح في تاب جديد</span>
                                </button>
                            </div>
                        </div>
                        <iframe id="cg-preview-frame" class="cg-preview-frame" sandbox="allow-scripts" title="معاينة كود الطالب"></iframe>
                    </div>
                @else
                    <div class="cg-panel">
                        <div class="cg-panel__header">
                            <h6 class="cg-panel__title">المعاينة</h6>
                        </div>
                        <div class="p-4 text-muted">
                            @if(! $isWeb)
                                المعاينة الحية متاحة لتحديات الويب (HTML/CSS/JS) فقط.
                            @else
                                لا توجد ملفات لعرض المعاينة.
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-xl-6 col-lg-7">
                    <form id="challengeGradingForm" action="{{ route('admin.challenge-grading.grade', $attempt->id) }}" method="POST">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">الدرجة</div></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="challenge_grading_score">الدرجة (من {{ $attempt->max_score ?? $attempt->challenge->max_score }})</label>
                                    <input type="number" id="challenge_grading_score" name="score" class="form-control" required min="0"
                                           max="{{ $attempt->max_score ?? $attempt->challenge->max_score }}" step="0.01"
                                           value="{{ old('score', $attempt->score) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="challenge_grading_feedback">التعليقات</label>
                                    <textarea id="challenge_grading_feedback" name="feedback" class="form-control" rows="8">{{ old('feedback', $attempt->feedback) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">حفظ التقييم</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    @include('admin.blog.partials.tinymce-config', [
        'formSelector' => '#challengeGradingForm',
        'editors' => [
            ['selector' => '#challenge_grading_feedback', 'height' => 320],
        ],
    ])
@endsection

@if($isWeb && $files->count())
@php
    $previewFiles = $files->map(function ($f) {
        return [
            'file_role' => $f->file_role,
            'filename' => $f->filename,
            'content' => $f->content ?? '',
        ];
    })->values();
@endphp
@push('scripts')
<script>
(function () {
    const files = @json($previewFiles);
    const storeUrl = @json(route('admin.challenge-grading.live-preview.store'));
    const csrf = @json(csrf_token());

    function extractParts(list) {
        let html = '', css = '', js = '';
        list.forEach(function (f) {
            const role = String(f.file_role || '').toLowerCase();
            const name = String(f.filename || '').toLowerCase();
            if (role === 'html' || name.endsWith('.html')) html = f.content || '';
            else if (role === 'css' || name.endsWith('.css')) css = f.content || '';
            else if (role === 'js' || role === 'javascript' || name.endsWith('.js')) js = f.content || '';
        });
        return { html: html, css: css, js: js };
    }

    function buildSrcdoc(parts) {
        const html = (parts.html || '').trim();
        const css = parts.css || '';
        const js = parts.js || '';

        if (/^\s*<!DOCTYPE/i.test(html) || /^\s*<html/i.test(html)) {
            let doc = html;
            if (css) {
                if (/<\/head>/i.test(doc)) doc = doc.replace(/<\/head>/i, '<style>' + css + '</style></head>');
                else doc = doc.replace(/<html[^>]*>/i, function (m) { return m + '<head><style>' + css + '</style></head>'; });
            }
            if (js) {
                if (/<\/body>/i.test(doc)) doc = doc.replace(/<\/body>/i, '<script>' + js + '<\/script></body>');
                else doc += '<script>' + js + '<\/script>';
            }
            return doc;
        }

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>' +
            css + '</style></head><body>' + html + '<script>' + js + '<\/script></body></html>';
    }

    function renderPreview() {
        const frame = document.getElementById('cg-preview-frame');
        if (!frame) return;
        frame.srcdoc = buildSrcdoc(extractParts(files));
    }

    function openPreview() {
        const html = buildSrcdoc(extractParts(files));
        const win = window.open('about:blank', '_blank');
        if (!win) {
            alert('تعذر فتح التاب الجديد. تحقق من مانع النوافذ المنبثقة.');
            return;
        }
        win.document.write('<p style="font-family:system-ui;padding:2rem">جاري تجهيز المعاينة…</p>');
        win.document.close();

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ html: html }),
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.url) throw new Error(res.data.message || 'فشل حفظ المعاينة');
                win.location = res.data.url;
            })
            .catch(function (err) {
                try { win.close(); } catch (e) {}
                alert(err.message || 'تعذر إنشاء رابط المعاينة');
            });
    }

    document.getElementById('cg-refresh-preview')?.addEventListener('click', renderPreview);
    document.getElementById('cg-open-preview')?.addEventListener('click', openPreview);
    renderPreview();
})();
</script>
@endpush
@endif
