@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .qb-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .qb-page-animate {
        animation-play-state: running !important;
    }

    .qb-type-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
    }

    .qb-option-item {
        background: rgba(var(--primary-rgb), 0.03);
        border: 1px solid rgba(var(--primary-rgb), 0.1) !important;
        border-radius: 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .qb-option-item:hover {
        border-color: rgba(var(--primary-rgb), 0.2) !important;
    }

    [data-theme-mode="dark"] .qb-option-item {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .admin-show-layout {
        max-width: 1140px;
        margin-inline: auto;
        width: 100%;
        padding-inline: 0.75rem;
    }

    .qb-show-meta-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .qb-show-meta-list__item {
        padding-bottom: 0.85rem;
        border-bottom: 1px solid var(--default-border, #eef1f6);
    }

    .qb-show-meta-list__item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .qb-show-meta-list__label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .qb-show-meta-list__value {
        font-weight: 600;
        color: var(--default-text-color);
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .qb-difficulty-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .qb-difficulty-chip--easy { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .qb-difficulty-chip--medium { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .qb-difficulty-chip--hard { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .qb-difficulty-chip--expert { background: rgba(33, 37, 41, 0.12); color: #212529; }

    .qb-show-question-text {
        padding: 1rem 1.15rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        line-height: 1.6;
    }

    .qb-show-option {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--default-border, #eef1f6);
        background: var(--custom-white, #fff);
    }

    .qb-show-option--correct {
        border-color: rgba(25, 135, 84, 0.35);
        background: rgba(25, 135, 84, 0.06);
    }

    .qb-stat-tile {
        text-align: center;
        padding: 1rem 0.75rem;
        border-radius: 12px;
        border: 1px solid var(--default-border, #eef1f6);
        background: rgba(var(--primary-rgb), 0.03);
        height: 100%;
    }

    .qb-stat-tile__value {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .qb-stat-tile__label {
        font-size: 0.78rem;
        color: var(--text-muted);
    }
</style>
