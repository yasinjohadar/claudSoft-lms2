<style>
    html:not(.loaded) .doc-cat-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .doc-cat-animate {
        animation-play-state: running !important;
    }

    .doc-cat-filter-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    [data-theme-mode="dark"] .doc-cat-filter-card {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
    }

    .doc-cat-filter-card .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .doc-cat-table-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    [data-theme-mode="dark"] .doc-cat-table-card {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
    }

    .doc-cat-table-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--default-border);
        padding: 1rem 1.25rem;
    }

    .doc-cat-table {
        margin-bottom: 0;
    }

    .doc-cat-table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        background: rgba(var(--primary-rgb), 0.04);
        border-bottom: 1px solid var(--default-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .doc-cat-table tbody td {
        padding: 0.95rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(var(--primary-rgb), 0.06);
    }

    .doc-cat-row {
        transition: background-color 0.18s ease;
    }

    .doc-cat-row:hover {
        background: rgba(var(--primary-rgb), 0.03);
    }

    .doc-cat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .doc-cat-icon--lg {
        width: 64px;
        height: 64px;
        font-size: 1.65rem;
        border-radius: 16px;
    }

    .doc-cat-icon--tech {
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
    }

    .doc-cat-icon--section {
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .doc-cat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .doc-cat-chip--tech {
        background: rgba(6, 182, 212, 0.12);
        color: #0e7490;
    }

    .doc-cat-chip--section {
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .doc-cat-slug {
        font-size: 0.78rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        background: rgba(var(--primary-rgb), 0.06);
        color: rgb(var(--primary-rgb));
        border: none;
        display: inline-block;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        direction: ltr;
        unicode-bidi: plaintext;
        word-break: break-all;
    }

    /* Prevent docs.css inline-code rules from affecting admin meta */
    .doc-enhance-page-meta .doc-cat-slug {
        background: rgba(var(--primary-rgb), 0.06) !important;
        border: none !important;
        color: rgb(var(--primary-rgb)) !important;
        box-shadow: none !important;
        padding: 0.2rem 0.5rem !important;
        font-size: 0.78rem !important;
    }

    .doc-cat-name-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .doc-cat-name-link:hover {
        color: rgb(var(--primary-rgb));
    }

    .doc-cat-pages-link {
        display: inline-flex;
        align-items: baseline;
        gap: 0.25rem;
        text-decoration: none;
        color: inherit;
        padding: 0.2rem 0.45rem;
        border-radius: 8px;
        transition: background-color 0.15s ease;
    }

    .doc-cat-pages-link:hover {
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
    }

    .doc-cat-pages-count {
        font-weight: 700;
        font-size: 0.95rem;
    }

    .doc-cat-order {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.82rem;
        background: var(--default-background, #f8f9fa);
        color: var(--text-muted);
    }

    .doc-cat-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
    }

    .doc-cat-status__dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .doc-cat-status--active {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .doc-cat-status--active .doc-cat-status__dot {
        background: #198754;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
    }

    .doc-cat-status--inactive {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
    }

    .doc-cat-status--inactive .doc-cat-status__dot {
        background: #adb5bd;
    }

    .doc-cat-status--published {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .doc-cat-status--draft {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
    }

    .doc-cat-status--published .doc-cat-status__dot {
        background: #198754;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
    }

    .doc-cat-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .doc-cat-action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid var(--default-border);
        background: var(--custom-white, #fff);
        color: var(--text-muted);
        transition: all 0.18s ease;
    }

    .doc-cat-action-btn:hover {
        border-color: rgb(var(--primary-rgb));
        color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.06);
    }

    .doc-cat-action-btn--primary:hover {
        border-color: rgb(var(--primary-rgb));
        background: rgb(var(--primary-rgb));
        color: #fff;
    }

    .doc-cat-action-btn--danger:hover {
        border-color: #dc3545;
        background: #dc3545;
        color: #fff;
    }

    .doc-cat-empty__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
    }

    .doc-cat-results-meta {
        font-size: 0.82rem;
        color: var(--text-muted);
    }

    .doc-cat-show-hero {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .doc-cat-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.85rem;
    }

    .doc-cat-page-title {
        font-weight: 600;
        color: var(--default-text-color);
        line-height: 1.4;
    }

    .doc-cat-page-title--depth-1 { padding-right: 1.25rem; }
    .doc-cat-page-title--depth-2 { padding-right: 2.5rem; }
    .doc-cat-page-title--depth-3 { padding-right: 3.75rem; }

    .doc-cat-page-indent {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--text-muted);
        font-size: 0.72rem;
        margin-left: 0.35rem;
    }

    .doc-cat-page-excerpt {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.15rem;
        max-width: 420px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .doc-cat-page-actions .btn {
        border-radius: 10px;
        font-size: 0.78rem;
        padding: 0.25rem 0.55rem;
    }

    @media (max-width: 767.98px) {
        .doc-cat-show-hero {
            flex-direction: column;
        }

        .doc-cat-page-actions {
            flex-direction: column;
            align-items: stretch !important;
        }
    }
</style>
