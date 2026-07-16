<div class="challenge-ide challenge-ide--pro {{ $challenge->isWebSandbox() ? 'challenge-ide--web' : 'challenge-ide--runner' }}" id="challenge-ide-app" data-layout="side">
    @if($challenge->isCodeRunner() && isset($pistonAvailable) && ! $pistonAvailable)
        <div class="alert alert-warning challenge-ide__piston-warning mb-0 rounded-0 border-0 border-bottom">
            <i class="fe fe-alert-triangle me-1"></i>
            خدمة تشغيل الكود غير متاحة حالياً. يمكنك الكتابة والتسليم، لكن التشغيل والتقييم الآلي قد لا يعملان.
        </div>
    @endif

    <header class="challenge-ide__toolbar">
        <div class="challenge-ide__toolbar-start">
            <a href="{{ $backUrl }}" class="challenge-ide__icon-btn" title="رجوع">
                <i class="fe fe-arrow-right"></i>
            </a>
            <div class="challenge-ide__heading">
                <div class="challenge-ide__heading-row">
                    <h1 class="challenge-ide__title">{{ $challenge->title }}</h1>
                    <span class="challenge-ide__badge {{ $challenge->isWebSandbox() ? 'challenge-ide__badge--web' : 'challenge-ide__badge--code' }}">
                        {{ $challenge->isWebSandbox() ? 'ويب · HTML/CSS/JS' : 'تنفيذ كود' }}
                    </span>
                </div>
                <p class="challenge-ide__subtitle">
                    @if($challenge->isWebSandbox())
                        مشروع واحد في محرر موحّد — بدّل التبويبات والمعاينة تتحدّث مباشرة
                    @else
                        اكتب الكود، شغّله، ثم سلّم المحاولة
                    @endif
                </p>
            </div>
        </div>
        <div class="challenge-ide__toolbar-end">
            <span class="challenge-ide__save-status" id="challenge-save-status">جاهز</span>
            @if($challenge->isWebSandbox())
                <div class="challenge-ide__layout-toggle" role="group" aria-label="موضع المعاينة">
                    <button type="button" class="challenge-ide__layout-btn is-active" data-layout-set="side" title="معاينة بالجانب">
                        <i class="fe fe-columns"></i>
                    </button>
                    <button type="button" class="challenge-ide__layout-btn" data-layout-set="bottom" title="معاينة بالأسفل">
                        <i class="fe fe-credit-card"></i>
                    </button>
                </div>
            @endif
            <button type="button" class="challenge-ide__btn challenge-ide__btn--ghost" id="challenge-btn-save">
                <i class="fe fe-save"></i> حفظ
            </button>
            @if($challenge->isCodeRunner())
                <button type="button" class="challenge-ide__btn challenge-ide__btn--ghost" id="challenge-btn-run">
                    <i class="fe fe-play"></i> تشغيل
                </button>
            @endif
            <button type="button" class="challenge-ide__btn challenge-ide__btn--primary" id="challenge-btn-submit">
                <i class="fe fe-send"></i> تسليم
            </button>
        </div>
    </header>

    <div class="challenge-ide__workspace">
        <div class="challenge-ide__editor-panel" dir="ltr">
            <div class="challenge-ide__editor-chrome">
                <ul class="challenge-ide__tabs nav nav-tabs" id="challenge-editor-tabs" role="tablist"></ul>
                <div class="challenge-ide__filechip" id="challenge-active-file" title="الملف الحالي">
                    <i class="fe fe-file-text"></i>
                    <span id="challenge-active-filename">—</span>
                </div>
            </div>
            <div class="challenge-ide__editors" id="challenge-editors"></div>
        </div>

        <div class="challenge-ide__resizer" id="challenge-resizer" aria-hidden="true"></div>

        <div class="challenge-ide__preview-panel {{ $challenge->isWebSandbox() ? 'challenge-ide__preview-panel--web' : '' }}">
            @if($challenge->isWebSandbox())
                <div class="challenge-ide__preview-toolbar">
                    <div class="challenge-ide__preview-title">
                        <span class="challenge-ide__live-dot" aria-hidden="true"></span>
                        <span class="challenge-ide__panel-label challenge-ide__panel-label--inline">معاينة حية</span>
                    </div>
                    <div class="challenge-ide__preview-actions">
                        <button type="button" class="challenge-ide__preview-btn" id="challenge-btn-refresh-preview" title="تحديث المعاينة">
                            <i class="fe fe-refresh-cw"></i>
                            <span class="d-none d-md-inline">تحديث</span>
                        </button>
                        <button type="button" class="challenge-ide__preview-btn" id="challenge-btn-open-preview" title="فتح في تاب جديد">
                            <i class="fe fe-external-link"></i>
                            <span class="d-none d-md-inline">تاب جديد</span>
                        </button>
                    </div>
                </div>
                <div class="challenge-ide__preview-stage">
                    <iframe id="challenge-preview-frame" class="challenge-ide__preview-frame" sandbox="allow-scripts" title="معاينة"></iframe>
                </div>
            @else
                <div class="challenge-ide__preview-toolbar">
                    <span class="challenge-ide__panel-label challenge-ide__panel-label--inline">المخرجات</span>
                </div>
                <pre id="challenge-console" class="challenge-ide__console" dir="ltr"></pre>
            @endif
        </div>
    </div>

    @if($challenge->description || $challenge->instructions)
        <details class="challenge-ide__instructions">
            <summary><i class="fe fe-book-open me-1"></i>تعليمات التحدي</summary>
            <div class="challenge-ide__instructions-body rich-content">
                @if($challenge->description)
                    <div class="challenge-ide__description mb-2">{!! $challenge->description !!}</div>
                @endif
                @if($challenge->instructions)
                    {!! $challenge->instructions !!}
                @endif
            </div>
        </details>
    @endif
</div>
