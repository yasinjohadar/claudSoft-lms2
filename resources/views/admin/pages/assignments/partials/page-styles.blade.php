<style>
    html:not(.loaded) .assignments-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .assignments-page-animate {
        animation-play-state: running !important;
    }

    .assignments-section-icon {
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

    .assignments-lesson-icon {
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

    .assignments-grade-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .assignments-status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .assignments-status-chip--published { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .assignments-status-chip--draft { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .assignments-status-chip--expired { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .assignments-status-chip--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .assignments-status-chip--graded { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .assignments-status-chip--pending { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .assignments-status-chip--submission-draft { background: rgba(108, 117, 125, 0.14); color: #6c757d; }

    .assignments-course-chip,
    .assignments-lesson-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .assignments-course-chip { background: rgba(var(--primary-rgb), 0.1); color: rgb(var(--primary-rgb)); }
    .assignments-lesson-chip { background: rgba(6, 182, 212, 0.12); color: #0891b2; }

    .assignments-submissions-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(108, 117, 125, 0.12);
        color: #495057;
        text-decoration: none;
    }

    .assignments-submissions-chip:hover {
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .assignments-table-row { transition: background-color 0.2s ease; }

    .assignments-actions__btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .assignments-empty-state__icon {
        width: 3.5rem;
        height: 3.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .assignments-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
    }

    .assignments-info-grid--single {
        grid-template-columns: 1fr;
    }

    .assignments-info-item {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px solid rgba(var(--primary-rgb), 0.08);
        min-width: 0;
    }

    .assignments-info-item__label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .assignments-info-item__value {
        font-weight: 700;
        color: var(--default-text-color);
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .assignments-info-item .assignments-course-chip,
    .assignments-info-item .assignments-lesson-chip {
        max-width: 100%;
        white-space: normal;
        word-break: break-word;
        display: block;
        line-height: 1.45;
    }

    .assignments-attachment-card {
        border: 1px solid var(--default-border, #eef1f6);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        height: 100%;
    }

    .assignments-form-actions {
        position: sticky;
        bottom: 1rem;
        z-index: 5;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    [data-theme-mode="dark"] .assignments-form-actions {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    }

    .assignments-stat-progress {
        height: 6px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(var(--primary-rgb), 0.08);
    }

    .assignments-stat-progress .progress-bar {
        height: 100%;
    }

    .admin-form-layout {
        max-width: 880px;
        margin-inline: auto;
        width: 100%;
    }
</style>
