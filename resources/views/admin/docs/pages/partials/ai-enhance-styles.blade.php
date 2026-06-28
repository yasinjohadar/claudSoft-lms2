<style>
    .doc-enhance-page-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px dashed rgba(var(--primary-rgb), 0.15);
    }

    .doc-enhance-page-meta:empty,
    .doc-enhance-page-meta[style*="display: none"] {
        display: none !important;
    }

    .doc-enhance-review {
        display: none;
    }

    .doc-enhance-review.is-visible {
        display: block;
    }

    .doc-enhance-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .doc-enhance-stat {
        padding: 0.75rem;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        text-align: center;
    }

    .doc-enhance-stat__value {
        font-size: 1.15rem;
        font-weight: 700;
        color: rgb(var(--primary-rgb));
        display: block;
    }

    .doc-enhance-stat__label {
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    .doc-enhance-tabs .nav-link {
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.45rem 0.85rem;
        color: var(--text-muted);
    }

    .doc-enhance-tabs .nav-link.active {
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .doc-enhance-preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 767.98px) {
        .doc-enhance-preview-grid {
            grid-template-columns: 1fr;
        }
    }

    .doc-enhance-preview-pane {
        border: 1px solid var(--default-border);
        border-radius: 10px;
        overflow: hidden;
        background: var(--custom-white, #fff);
    }

    .doc-enhance-preview-pane__header {
        padding: 0.55rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 700;
        border-bottom: 1px solid var(--default-border);
        background: rgba(var(--primary-rgb), 0.04);
    }

    .doc-enhance-preview-pane__header--old { color: #6c757d; }
    .doc-enhance-preview-pane__header--new { color: #198754; }

    .doc-enhance-preview-pane__body {
        padding: 1rem;
        max-height: 420px;
        overflow: auto;
        direction: rtl;
        font-size: 0.9rem;
        line-height: 1.7;
    }

    .doc-enhance-diff {
        border: 1px solid var(--default-border);
        border-radius: 10px;
        padding: 1rem;
        max-height: 480px;
        overflow: auto;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 0.78rem;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
        direction: rtl;
        background: var(--custom-white, #fff);
    }

    .doc-enhance-diff ins {
        background: rgba(25, 135, 84, 0.18);
        color: #146c43;
        text-decoration: none;
    }

    .doc-enhance-diff del {
        background: rgba(220, 53, 69, 0.15);
        color: #b02a37;
        text-decoration: line-through;
    }

    .doc-enhance-diff .diff-unchanged {
        color: var(--text-muted);
    }

    .doc-enhance-review-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px solid var(--default-border);
    }

    .doc-enhance-preview-pane__body.docs-content {
        background: #f8fafc;
        color: #1e293b;
    }

    [data-theme-mode="dark"] .doc-enhance-preview-pane__body.docs-content {
        background: #0f1419;
        color: #e8eaf0;
    }

    .doc-enhance-page-meta .doc-cat-slug {
        background: rgba(var(--primary-rgb), 0.08) !important;
        border: none !important;
        color: rgb(var(--primary-rgb)) !important;
        box-shadow: none !important;
    }

    #doc_original { display: none; }

    .doc-ai-save-card .loading-spinner { display: none; }
    .doc-ai-save-card .loading-spinner.active { display: inline-block; }
</style>
