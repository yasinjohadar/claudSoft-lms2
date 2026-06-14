<style>
    html:not(.loaded) .qb-import-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }
    html.loaded .qb-import-animate {
        animation-play-state: running !important;
    }

    .qb-import-steps {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .qb-import-step {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
        border: 1px solid rgba(var(--primary-rgb), 0.15);
    }

    .qb-import-step--muted {
        background: rgba(var(--secondary-rgb, 108, 117, 125), 0.08);
        color: var(--text-muted);
        border-color: var(--default-border, #eef1f6);
    }

    .qb-import-step__num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        background: rgba(var(--primary-rgb), 0.15);
    }

    .qb-import-tips {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .qb-import-tip {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--default-border, #eef1f6);
        background: var(--custom-white, #fff);
        font-size: 0.82rem;
        line-height: 1.55;
    }

    .qb-import-tip strong {
        display: block;
        margin-bottom: 0.25rem;
        color: var(--default-text-color);
    }

    .qb-import-dropzone {
        display: block;
        border: 2px dashed rgba(var(--primary-rgb), 0.35);
        border-radius: 16px;
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        background: rgba(var(--primary-rgb), 0.03);
        margin-bottom: 1rem;
    }

    .qb-import-dropzone:hover,
    .qb-import-dropzone.is-dragover {
        border-color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.07);
        transform: translateY(-1px);
    }

    .qb-import-dropzone__icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 0.75rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.12);
    }

    .qb-import-dropzone__filename {
        display: block;
        margin-top: 0.5rem;
        font-weight: 600;
        color: rgb(var(--primary-rgb));
        word-break: break-all;
    }

    .qb-import-preview-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .qb-import-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .qb-import-stat-chip--success {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .qb-import-stat-chip--danger {
        background: rgba(220, 53, 69, 0.12);
        color: #dc3545;
    }

    .preview-row-error { background-color: rgba(220, 53, 69, 0.06) !important; }
    .preview-row-valid { background-color: rgba(25, 135, 84, 0.06) !important; }

    .qb-type-pick-card {
        display: block;
        height: 100%;
        border: 1px solid var(--default-border, #eef1f6);
        border-radius: 16px;
        background: var(--custom-white, #fff);
        padding: 1.25rem 1rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .qb-type-pick-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        border-color: rgba(var(--primary-rgb), 0.35);
        color: inherit;
    }

    .qb-type-pick-card__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 0.85rem;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: rgb(var(--primary-rgb));
        background: rgba(var(--primary-rgb), 0.1);
    }
</style>
