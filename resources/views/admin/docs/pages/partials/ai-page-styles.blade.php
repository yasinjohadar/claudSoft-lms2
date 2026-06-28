<style>
    html:not(.loaded) .doc-ai-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .doc-ai-animate {
        animation-play-state: running !important;
    }

    .doc-ai-panel {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    [data-theme-mode="dark"] .doc-ai-panel {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
    }

    .doc-ai-panel__header {
        background: transparent;
        border-bottom: 1px solid var(--default-border);
        padding: 1rem 1.25rem;
    }

    .doc-ai-panel__title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
        color: var(--default-text-color);
    }

    .doc-ai-panel__title-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .doc-ai-panel__title-icon--ai {
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .doc-ai-panel__title-icon--content {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .doc-ai-panel__title-icon--seo {
        background: rgba(234, 88, 12, 0.12);
        color: #ea580c;
    }

    .doc-ai-panel__title-icon--meta {
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
    }

    .doc-ai-panel .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .doc-ai-engine-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .doc-ai-engine-pill {
        position: relative;
        flex: 1 1 160px;
    }

    .doc-ai-engine-pill input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .doc-ai-engine-pill label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--default-border);
        border-radius: 12px;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--custom-white, #fff);
        transition: all 0.18s ease;
        margin: 0;
    }

    .doc-ai-engine-pill input:checked + label {
        border-color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.06);
        color: rgb(var(--primary-rgb));
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.08);
    }

    .doc-ai-hint {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
    }

    .doc-ai-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .doc-ai-generate-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px dashed var(--default-border);
    }

    .doc-ai-generate-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .doc-ai-generate-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.32);
        color: #fff;
    }

    .doc-ai-generate-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .doc-ai-sidebar-sticky {
        position: sticky;
        top: 5.5rem;
    }

    .doc-ai-save-card .btn-primary {
        border-radius: 12px;
        padding: 0.65rem 1rem;
        font-weight: 600;
    }

    .doc-ai-save-card .btn-secondary,
    .doc-ai-save-card .btn-light {
        border-radius: 12px;
    }

    .doc-ai-topic-input {
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }

    .loading-spinner { display: none; }
    .loading-spinner.active { display: inline-block; }

    @media (max-width: 991.98px) {
        .doc-ai-sidebar-sticky {
            position: static;
        }
    }

    .doc-ai-prefill-banner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1.15rem;
        border-radius: 12px;
        border: 1px solid rgba(var(--primary-rgb), 0.18);
        background: rgba(var(--primary-rgb), 0.06);
        margin-bottom: 1.25rem;
    }

    .doc-ai-prefill-banner__text {
        font-size: 0.9rem;
        color: var(--default-text-color);
        margin: 0;
    }

    .doc-ai-editor-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .doc-ai-editor-actions .btn {
        border-radius: 10px;
        font-size: 0.78rem;
    }

    .doc-ai-notes-area {
        min-height: 220px;
        border-radius: 12px;
        resize: vertical;
    }

    .doc-ai-refine-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .doc-ai-refine-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.32);
        color: #fff;
    }

    .doc-ai-refine-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .doc-ai-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        background: rgba(var(--primary-rgb), 0.12);
        color: rgb(var(--primary-rgb));
        margin-left: 0.35rem;
    }
</style>
