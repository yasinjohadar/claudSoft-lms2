{{-- Shared styling for the batch progress table, status badges and history rows. --}}
<style>
    .doc-ai-batch-table { width: 100%; font-size: .875rem; }
    .doc-ai-batch-table td, .doc-ai-batch-table th { padding: .5rem .6rem; vertical-align: top; }
    .doc-ai-batch-table tbody tr { border-top: 1px solid rgba(0,0,0,.05); }
    .doc-ai-batch-badge { display:inline-block; padding:.2rem .55rem; border-radius:999px; font-size:.75rem; font-weight:600; white-space:nowrap; }
    .doc-ai-batch-badge--pending { background:#eef0f2; color:#6b7280; }
    .doc-ai-batch-badge--running { background:#fff3cd; color:#8a6100; }
    .doc-ai-batch-badge--resume_queued { background:#e0ecff; color:#1e4bd8; }
    .doc-ai-batch-badge--completed { background:#e6f7ed; color:#0f7b42; }
    .doc-ai-batch-badge--incomplete { background:#fdf0e3; color:#a15c00; }
    .doc-ai-batch-badge--stuck { background:#f3e8ff; color:#6b21a8; }
    .doc-ai-batch-badge--failed { background:#fdecea; color:#b3261e; }
    .doc-ai-batch-badge--skipped { background:#eef0f2; color:#6b7280; }
    .doc-ai-batch-summary { display:flex; flex-wrap:wrap; gap:.4rem; }
    .doc-ai-batch-stat { font-size:.75rem; font-weight:600; padding:.2rem .6rem; border-radius:999px; background:#f3f4f6; color:#374151; }
    .doc-ai-batch-stat--done { background:#e6f7ed; color:#0f7b42; }
    .doc-ai-batch-stat--incomplete { background:#fdf0e3; color:#a15c00; }
    .doc-ai-batch-stat--queued { background:#e0ecff; color:#1e4bd8; }
    .doc-ai-sec-heads { display:flex; flex-wrap:wrap; gap:.25rem; margin-top:.35rem; }
    .doc-ai-sec-head { font-size:.7rem; padding:.1rem .45rem; border-radius:6px; background:#fdecea; color:#b3261e; }
    .doc-ai-item-done { margin-top:.5rem; padding:.4rem .6rem; border-radius:8px; background:#e6f7ed; color:#0f7b42; font-size:.78rem; font-weight:600; }
    .doc-ai-item-topic { display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
</style>
