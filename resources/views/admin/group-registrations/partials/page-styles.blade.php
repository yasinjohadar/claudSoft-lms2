@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .gr-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .gr-page-animate {
        animation-play-state: running !important;
    }

    .gr-reg-icon {
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

    .gr-table-row {
        transition: background-color 0.2s ease;
    }

    .gr-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        background: rgba(108, 117, 125, 0.12);
        color: #495057;
    }

    .gr-status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .gr-status-chip--pending { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .gr-status-chip--processing { background: rgba(13, 202, 240, 0.12); color: #0aa2c0; }
    .gr-status-chip--completed { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .gr-status-chip--failed { background: rgba(220, 53, 69, 0.12); color: #dc3545; }

    .gr-bool-chip--yes { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .gr-bool-chip--no { background: rgba(108, 117, 125, 0.14); color: #6c757d; }

    .gr-bool-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }
</style>
