@include('admin.pages.assignments.partials.page-styles')
<style>
    html:not(.loaded) .quizzes-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .quizzes-page-animate {
        animation-play-state: running !important;
    }

    .quizzes-type-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .quizzes-type-chip--practice { background: rgba(6, 182, 212, 0.12); color: #0891b2; }
    .quizzes-type-chip--graded { background: rgba(255, 193, 7, 0.15); color: #cc9a00; }
    .quizzes-type-chip--final { background: rgba(220, 53, 69, 0.12); color: #dc3545; }
    .quizzes-type-chip--survey { background: rgba(108, 117, 125, 0.14); color: #6c757d; }

    .quizzes-questions-chip,
    .quizzes-attempts-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        text-decoration: none;
    }

    .quizzes-questions-chip { background: rgba(108, 117, 125, 0.12); color: #495057; }
    .quizzes-attempts-chip { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
    .quizzes-attempts-chip:hover { background: rgba(139, 92, 246, 0.18); color: #6d28d9; }

    .quizzes-table-row { transition: background-color 0.2s ease; }
    .quizzes-quiz-icon {
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
</style>
