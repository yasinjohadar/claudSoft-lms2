@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .platform-reviews-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .platform-reviews-page-animate {
        animation-play-state: running !important;
    }

    .platform-reviews-text-preview {
        max-width: 360px;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
    }

    .platform-reviews-position-chip {
        display: inline-block;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
    }

    .platform-reviews-stars {
        display: inline-flex;
        align-items: center;
        gap: 0.1rem;
        color: #f59e0b;
        font-size: 0.85rem;
    }

    .platform-reviews-actions .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .platform-reviews-table-row {
        transition: background-color 0.2s ease;
    }

    .platform-reviews-show-text {
        padding: 1rem 1.15rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        line-height: 1.65;
        word-break: break-word;
    }
</style>
