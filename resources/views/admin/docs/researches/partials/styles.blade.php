{{-- Styling specific to researches; the shared card/table/badge vocabulary comes
     from admin.docs.categories.partials.styles, which every research view includes. --}}
<style>
    .doc-research-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
        background: rgba(var(--primary-rgb), .08);
        color: rgb(var(--primary-rgb));
    }

    /* Page list on the research detail screen. A stack of cards rather than a
       table: dragula's drag mirror collapses when the dragged node is a <tr>. */
    .doc-research-list {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .doc-research-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .7rem .9rem;
        border: 1px solid var(--default-border);
        border-radius: 12px;
        background: var(--custom-white);
    }

    .doc-research-item__main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .doc-research-item__title {
        font-weight: 600;
        color: inherit;
        text-decoration: none;
        display: block;
        white-space: normal;
        overflow-wrap: anywhere;
        line-height: 1.55;
    }

    .doc-research-item__title:hover {
        color: rgb(var(--primary-rgb));
    }

    .doc-research-item__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        margin-top: .3rem;
    }

    .doc-research-order {
        flex: 0 0 auto;
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(var(--primary-rgb), .06);
        font-size: .8rem;
        font-weight: 700;
        color: rgb(var(--primary-rgb));
    }

    .doc-research-handle {
        flex: 0 0 auto;
        cursor: grab;
        color: var(--text-muted);
        padding: .25rem;
        border-radius: 6px;
    }

    .doc-research-handle:active { cursor: grabbing; }

    .doc-research-list--locked .doc-research-handle {
        opacity: .35;
        cursor: not-allowed;
    }

    .gu-mirror {
        list-style: none;
        opacity: .95;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .16);
    }

    .gu-transit { opacity: .35; }

    .doc-research-save-state {
        font-size: .78rem;
        font-weight: 600;
        transition: opacity .2s ease;
    }

    @media (max-width: 575.98px) {
        .doc-research-item { flex-wrap: wrap; }
    }
</style>
