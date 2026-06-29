@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .ud-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .ud-page-animate {
        animation-play-state: running !important;
    }

    .ud-user-avatar {
        width: 2.25rem;
        height: 2.25rem;
        min-width: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(var(--primary-rgb), 0.12);
        color: rgb(var(--primary-rgb));
        font-weight: 700;
        font-size: 0.85rem;
        overflow: hidden;
    }

    .ud-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ud-table-row {
        transition: background-color 0.2s ease;
    }

    .ud-device-icon {
        width: 2rem;
        height: 2rem;
        min-width: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
        font-size: 0.9rem;
    }

    .ud-logins-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(13, 202, 240, 0.12);
        color: #0aa2c0;
    }

    .ud-status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .ud-status-chip--trusted { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .ud-status-chip--blocked { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .ud-status-chip--normal { background: rgba(108, 117, 125, 0.14); color: #6c757d; }
    .ud-status-chip--pending { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }

    .ud-bulk-bar {
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.04) 0%, var(--custom-white, #fff) 100%);
    }

    .ud-quick-filter.active {
        font-weight: 600;
    }

    .ud-ip-text {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    .ud-show-user-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px solid rgba(var(--primary-rgb), 0.08);
    }

    .ud-show-user-card__meta {
        min-width: 0;
        flex: 1;
    }

    .ud-show-fingerprint {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.72rem;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        background: rgba(var(--primary-rgb), 0.06);
        color: rgb(var(--primary-rgb));
        word-break: break-all;
        display: block;
        line-height: 1.5;
    }

    .ud-show-ip-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 8px;
        background: rgba(220, 53, 69, 0.08);
        color: #dc3545;
    }

    .ud-action-card .btn {
        border-radius: 10px;
    }

    .ud-meta-pre {
        font-size: 0.75rem;
        max-height: 220px;
        overflow: auto;
        border-radius: 10px;
        margin-bottom: 0;
    }
</style>
