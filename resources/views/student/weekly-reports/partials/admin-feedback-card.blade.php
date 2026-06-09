@php
    $studentName = auth()->user()->name_ar ?? auth()->user()->name ?? 'طالب';
    $shareText = "🎉 تقريري الأسبوعي حصل على تعليق من الإدارة!\n\n"
        . "📋 {$report->report_title}\n"
        . "👤 {$studentName}\n"
        . "💬 \"{$report->admin_feedback}\"\n\n"
        . "#كلاودسوفت #تعلم_مستمر";
    $shareUrl = url()->current();
@endphp

<style>
    .weekly-report-feedback-card {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #14532d 0%, #16a34a 45%, #4ade80 100%);
        color: #fff;
        padding: 2rem 1.75rem 1.5rem;
        box-shadow: 0 18px 40px rgba(22, 163, 74, 0.28);
        margin-bottom: 1.5rem;
    }

    .weekly-report-feedback-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 35%),
            radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.12), transparent 30%);
        pointer-events: none;
    }

    .weekly-report-feedback-card > * {
        position: relative;
        z-index: 1;
    }

    .weekly-report-feedback-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .weekly-report-feedback-card__brand {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.2px;
    }

    .weekly-report-feedback-card__brand-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(6px);
        font-size: 1.2rem;
    }

    .weekly-report-feedback-card__status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .weekly-report-feedback-card__title {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        line-height: 1.5;
    }

    .weekly-report-feedback-card__student {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin-bottom: 1.25rem;
    }

    .weekly-report-feedback-card__quote {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .weekly-report-feedback-card__quote::before {
        content: "“";
        position: absolute;
        top: -0.35rem;
        right: 1rem;
        font-size: 3rem;
        line-height: 1;
        color: rgba(255, 255, 255, 0.35);
        font-family: Georgia, serif;
    }

    .weekly-report-feedback-card__quote-text {
        margin: 0;
        font-size: 1.15rem;
        line-height: 1.9;
        font-weight: 600;
        white-space: pre-line;
    }

    .weekly-report-feedback-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        color: rgba(255, 255, 255, 0.82);
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
    }

    .weekly-report-feedback-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .weekly-report-feedback-card__actions .btn {
        border-radius: 999px;
        font-weight: 600;
        padding-inline: 1rem;
    }

    .weekly-report-feedback-card__actions .btn-light {
        color: #14532d;
    }

    .weekly-report-feedback-card__actions .btn-outline-light:hover {
        color: #14532d;
        background: #fff;
    }

    @media (max-width: 575.98px) {
        .weekly-report-feedback-card {
            padding: 1.5rem 1.15rem 1.15rem;
        }

        .weekly-report-feedback-card__title {
            font-size: 1.15rem;
        }

        .weekly-report-feedback-card__quote-text {
            font-size: 1rem;
        }
    }
</style>

<div class="weekly-report-feedback-card" id="admin-feedback-share-card">
    <div class="weekly-report-feedback-card__top">
        <div class="weekly-report-feedback-card__brand">
            <span class="weekly-report-feedback-card__brand-icon">
                <i class="ri-award-line"></i>
            </span>
            <span>كلاودسوفت</span>
        </div>
        <span class="weekly-report-feedback-card__status">
            <i class="ri-checkbox-circle-line"></i>
            تمت مراجعة تقريرك
        </span>
    </div>

    <h3 class="weekly-report-feedback-card__title">{{ $report->report_title }}</h3>
    <p class="weekly-report-feedback-card__student">
        <i class="ri-user-smile-line me-1"></i>
        {{ $studentName }}
    </p>

    <div class="weekly-report-feedback-card__quote">
        <p class="weekly-report-feedback-card__quote-text">{{ $report->admin_feedback }}</p>
    </div>

    <div class="weekly-report-feedback-card__meta">
        @if($report->reviewed_at)
            <span>
                <i class="ri-time-line me-1"></i>
                {{ $report->reviewed_at->format('Y/m/d h:i A') }}
            </span>
        @endif
        @if($report->submitted_at)
            <span>
                <i class="ri-send-plane-line me-1"></i>
                أُرسل {{ $report->submitted_at->format('Y/m/d') }}
            </span>
        @endif
    </div>

    <div class="weekly-report-feedback-card__actions">
        <button type="button" class="btn btn-light btn-sm" id="copy-feedback-share-btn">
            <i class="ri-file-copy-line me-1"></i>نسخ النص
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" id="native-share-feedback-btn">
            <i class="ri-share-forward-line me-1"></i>مشاركة
        </button>
        <a href="https://wa.me/?text={{ urlencode($shareText) }}"
           target="_blank" rel="noopener"
           class="btn btn-outline-light btn-sm">
            <i class="ri-whatsapp-line me-1"></i>واتساب
        </a>
        <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}"
           target="_blank" rel="noopener"
           class="btn btn-outline-light btn-sm">
            <i class="ri-twitter-x-line me-1"></i>منصة X
        </a>
    </div>
</div>

<script>
(() => {
    const shareText = @json($shareText);
    const shareUrl = @json($shareUrl);

    const copyBtn = document.getElementById('copy-feedback-share-btn');
    const nativeShareBtn = document.getElementById('native-share-feedback-btn');

    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(shareText);
                copyBtn.innerHTML = '<i class="ri-check-line me-1"></i>تم النسخ';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="ri-file-copy-line me-1"></i>نسخ النص';
                }, 2000);
            } catch (e) {
                window.prompt('انسخ النص التالي:', shareText);
            }
        });
    }

    if (nativeShareBtn) {
        nativeShareBtn.addEventListener('click', async () => {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: @json($report->report_title),
                        text: shareText,
                        url: shareUrl,
                    });
                    return;
                } catch (e) {
                    if (e && e.name === 'AbortError') {
                        return;
                    }
                }
            }
            window.prompt('انسخ النص للمشاركة:', shareText);
        });
    }
})();
</script>
