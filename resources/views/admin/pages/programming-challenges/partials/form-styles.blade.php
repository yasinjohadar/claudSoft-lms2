{{-- Shared elegant styles for programming challenge create/edit --}}
<style>
.pch-form {
    --pf-ink: #0f172a;
    --pf-muted: #64748b;
    --pf-border: #e2e8f0;
    --pf-card: #fff;
    --pf-soft: #f8fafc;
    --pf-primary: #2563eb;
}

.pch-form__hero {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin: 1.25rem 0 1.35rem;
    padding: 1.2rem 1.35rem;
    border: 1px solid var(--pf-border);
    border-radius: 16px;
    background: var(--pf-soft);
}

.pch-form__hero h5 {
    margin: 0 0 0.3rem;
    font-weight: 800;
    color: var(--pf-ink);
}

.pch-form__hero p {
    margin: 0;
    color: var(--pf-muted);
    font-size: 0.9rem;
}

.pch-form__panel {
    border: 1px solid var(--pf-border);
    border-radius: 16px;
    background: var(--pf-card);
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    margin-bottom: 1rem;
}

.pch-form__panel-head {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.95rem 1.15rem;
    border-bottom: 1px solid var(--pf-border);
    background: var(--pf-soft);
}

.pch-form__panel-icon {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #1d4ed8;
    flex-shrink: 0;
}

.pch-form__panel-icon--amber { background: #ffedd5; color: #c2410c; }
.pch-form__panel-icon--slate { background: #e2e8f0; color: #334155; }
.pch-form__panel-icon--green { background: #dcfce7; color: #15803d; }

.pch-form__panel-title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--pf-ink);
}

.pch-form__panel-sub {
    margin: 0.15rem 0 0;
    font-size: 0.78rem;
    color: var(--pf-muted);
}

.pch-form__panel-body {
    padding: 1.15rem;
}

.pch-form__label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
}

.pch-form__hint {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.75rem;
    color: var(--pf-muted);
    line-height: 1.5;
}

.pch-form .form-control,
.pch-form .form-select {
    border-radius: 0.6rem;
    border-color: var(--pf-border);
    min-height: 2.55rem;
    box-shadow: none;
}

.pch-form .form-control:focus,
.pch-form .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.pch-form__grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

@media (max-width: 575.98px) {
    .pch-form__grid-2 { grid-template-columns: 1fr; }
}

.pch-form__switch {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 0.95rem;
    border: 1px solid var(--pf-border);
    border-radius: 12px;
    background: var(--pf-soft);
    margin-bottom: 0.65rem;
}

.pch-form__switch:last-child { margin-bottom: 0; }

.pch-form__switch .form-check-input {
    width: 2.4rem;
    height: 1.25rem;
    margin-top: 0.15rem;
    flex-shrink: 0;
    cursor: pointer;
}

.pch-form__switch-label {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--pf-ink);
    cursor: pointer;
}

.pch-form__switch-desc {
    margin: 0.2rem 0 0;
    font-size: 0.75rem;
    color: var(--pf-muted);
    line-height: 1.45;
}

.pch-form__access-note {
    padding: 0.75rem 0.9rem;
    border-radius: 12px;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1e3a8a;
    font-size: 0.8rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.pch-form__submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    width: 100%;
    padding: 0.7rem 1rem;
    border: 0;
    border-radius: 0.65rem;
    background: var(--pf-primary);
    color: #fff;
    font-weight: 800;
    font-size: 0.92rem;
}

.pch-form__submit:hover {
    background: #1d4ed8;
    color: #fff;
}

.pch-form__side-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.pch-form__side-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.7rem;
    border-radius: 0.5rem;
    border: 1px solid var(--pf-border);
    background: #fff;
    color: var(--pf-ink);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
}

.pch-form__side-link:hover {
    background: var(--pf-soft);
    color: var(--pf-ink);
}

[data-theme-mode="dark"] .pch-form {
    --pf-ink: #f1f5f9;
    --pf-muted: #94a3b8;
    --pf-border: rgba(148, 163, 184, 0.25);
    --pf-card: rgba(15, 23, 42, 0.55);
    --pf-soft: rgba(15, 23, 42, 0.4);
}

[data-theme-mode="dark"] .pch-form__access-note {
    background: rgba(37, 99, 235, 0.16);
    border-color: rgba(147, 197, 253, 0.35);
    color: #bfdbfe;
}

[data-theme-mode="dark"] .pch-form__switch,
[data-theme-mode="dark"] .pch-form__side-link {
    background: rgba(15, 23, 42, 0.45);
}
</style>
