<div class="challenge-ide" id="challenge-ide-app">
    @if($challenge->isCodeRunner() && isset($pistonAvailable) && ! $pistonAvailable)
        <div class="alert alert-warning challenge-ide__piston-warning mb-0 rounded-0 border-0 border-bottom">
            <i class="fe fe-alert-triangle me-1"></i>
            خدمة تشغيل الكود غير متاحة حالياً. يمكنك الكتابة والتسليم، لكن التشغيل والتقييم الآلي قد لا يعملان.
        </div>
    @endif
    <header class="challenge-ide__toolbar">
        <div class="challenge-ide__toolbar-start">
            <a href="{{ $backUrl }}" class="btn btn-sm btn-light" title="رجوع">
                <i class="fe fe-arrow-right"></i>
            </a>
            <h1 class="challenge-ide__title">{{ $challenge->title }}</h1>
            <span class="challenge-ide__badge">{{ $challenge->challenge_type === 'web_sandbox' ? 'ويب' : 'كود' }}</span>
        </div>
        <div class="challenge-ide__toolbar-end">
            <span class="challenge-ide__save-status text-muted small" id="challenge-save-status">جاهز</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="challenge-btn-save">
                <i class="fe fe-save"></i> حفظ
            </button>
            @if($challenge->isCodeRunner())
                <button type="button" class="btn btn-sm btn-outline-info" id="challenge-btn-run">
                    <i class="fe fe-play"></i> تشغيل
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-primary" id="challenge-btn-submit">
                <i class="fe fe-send"></i> تسليم
            </button>
        </div>
    </header>

    <div class="challenge-ide__workspace">
        <div class="challenge-ide__editor-panel">
            <ul class="challenge-ide__tabs nav nav-tabs" id="challenge-editor-tabs" role="tablist"></ul>
            <div class="challenge-ide__editors" id="challenge-editors"></div>
        </div>
        <div class="challenge-ide__resizer" id="challenge-resizer" aria-hidden="true"></div>
        <div class="challenge-ide__preview-panel">
            @if($challenge->isWebSandbox())
                <div class="challenge-ide__panel-label">معاينة حية</div>
                <iframe id="challenge-preview-frame" class="challenge-ide__preview-frame" sandbox="allow-scripts" title="معاينة"></iframe>
            @else
                <div class="challenge-ide__panel-label">المخرجات</div>
                <pre id="challenge-console" class="challenge-ide__console" dir="ltr"></pre>
            @endif
        </div>
    </div>

    @if($challenge->instructions)
        <details class="challenge-ide__instructions">
            <summary><i class="fe fe-info me-1"></i>تعليمات التحدي</summary>
            <div class="challenge-ide__instructions-body">{!! nl2br(e($challenge->instructions)) !!}</div>
        </details>
    @endif
</div>
