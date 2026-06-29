@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .qp-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .qp-page-animate {
        animation-play-state: running !important;
    }

    .qp-pool-icon {
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

    .qp-questions-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(108, 117, 125, 0.12);
        color: #495057;
    }

    .qp-scope-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .qp-scope-chip--global {
        background: rgba(111, 66, 193, 0.12);
        color: #6f42c1;
    }

    .qp-table-row {
        transition: background-color 0.2s ease;
    }
</style>
