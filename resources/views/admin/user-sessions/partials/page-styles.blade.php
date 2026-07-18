@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .us-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .us-page-animate {
        animation-play-state: running !important;
    }

    .us-session-icon {
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

    .us-user-avatar {
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

    .us-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .us-table-row {
        transition: background-color 0.2s ease;
    }

    .us-duration-chip {
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

    .us-activities-chip {
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

    .us-activities-chip:hover {
        background: rgba(var(--primary-rgb), 0.12);
        color: rgb(var(--primary-rgb));
    }

    .us-status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .us-status-chip--active { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .us-status-chip--completed { background: rgba(13, 202, 240, 0.12); color: #0aa2c0; }
    .us-status-chip--disconnected { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .us-status-chip--timeout { background: rgba(108, 117, 125, 0.14); color: #6c757d; }

    .us-bulk-bar {
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.04) 0%, var(--custom-white, #fff) 100%);
    }

    .us-quick-filter.active {
        font-weight: 600;
    }

    .us-show-user-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: rgba(var(--primary-rgb), 0.04);
        border: 1px solid rgba(var(--primary-rgb), 0.08);
    }

    .us-show-user-card__meta {
        min-width: 0;
    }

    .us-show-uuid {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        padding: 0.35rem 0.55rem;
        border-radius: 8px;
        background: rgba(var(--primary-rgb), 0.06);
        color: rgb(var(--primary-rgb));
        word-break: break-all;
    }

    .us-show-live-duration {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .us-show-live-duration .fe {
        animation: us-pulse 1.5s ease-in-out infinite;
    }

    @keyframes us-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .us-activity-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .us-activity-chip--session_start { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .us-activity-chip--session_end { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .us-activity-chip--page_view { background: rgba(13, 202, 240, 0.12); color: #0aa2c0; }
    .us-activity-chip--action { background: rgba(var(--primary-rgb), 0.12); color: rgb(var(--primary-rgb)); }
    .us-activity-chip--disconnect { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .us-activity-chip--reconnect { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .us-activity-chip--idle_start,
    .us-activity-chip--idle_end { background: rgba(108, 117, 125, 0.14); color: #6c757d; }
    .us-activity-chip--focus_lost { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .us-activity-chip--focus_gained { background: rgba(25, 135, 84, 0.12); color: #198754; }
    .us-activity-chip--lesson_open,
    .us-activity-chip--lesson_complete,
    .us-activity-chip--video_start,
    .us-activity-chip--video_complete,
    .us-activity-chip--quiz_start,
    .us-activity-chip--quiz_submit,
    .us-activity-chip--file_download { background: rgba(var(--primary-rgb), 0.12); color: rgb(var(--primary-rgb)); }
    .us-activity-chip--default { background: rgba(108, 117, 125, 0.14); color: #6c757d; }

    .us-distribution-row {
        margin-bottom: 0.65rem;
    }

    .us-distribution-row:last-child {
        margin-bottom: 0;
    }

    .us-distribution-row__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .us-distribution-row__bar {
        height: 6px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(var(--primary-rgb), 0.08);
    }

    .us-distribution-row__fill {
        height: 100%;
        border-radius: 999px;
        background: rgb(var(--primary-rgb));
        transition: width 0.6s ease;
    }

    .us-activities-scroll {
        max-height: 520px;
        overflow: auto;
    }

    .us-activities-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--custom-white, #fff);
        box-shadow: 0 1px 0 var(--default-border, #eef1f6);
    }

    .us-ip-chip {
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
</style>
