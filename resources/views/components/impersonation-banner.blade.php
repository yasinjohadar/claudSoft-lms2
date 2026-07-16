@if(isset($isImpersonating) && $isImpersonating && isset($originalUser))
<div class="impersonation-bar" role="status">
    <div class="impersonation-bar__inner">
        <div class="impersonation-bar__info">
            <span class="impersonation-bar__dot" aria-hidden="true"></span>
            <span class="impersonation-bar__label">وضع المشاهدة كطالب</span>
            <span class="impersonation-bar__divider" aria-hidden="true"></span>
            <strong class="impersonation-bar__user">{{ auth()->user()->name }}</strong>
            <span class="impersonation-bar__meta">الأدمن: {{ $originalUser->name }}</span>
        </div>
        <form action="{{ route('admin.stop-impersonate') }}" method="POST" class="impersonation-bar__actions">
            @csrf
            <button type="submit" class="impersonation-bar__btn">
                <i class="fe fe-log-out"></i>
                العودة لحساب الأدمن
            </button>
        </form>
    </div>
</div>

<style>
.impersonation-bar {
    position: sticky;
    top: 0;
    z-index: 1055;
    background: #0f172a;
    color: #e2e8f0;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.18);
}

.impersonation-bar__inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem 1rem;
    padding: 0.45rem 1rem;
    min-height: 2.5rem;
}

.impersonation-bar__info {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem 0.65rem;
    font-size: 0.82rem;
}

.impersonation-bar__dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
}

.impersonation-bar__label {
    font-weight: 700;
    color: #f8fafc;
}

.impersonation-bar__divider {
    width: 1px;
    height: 0.9rem;
    background: rgba(148, 163, 184, 0.35);
}

.impersonation-bar__user {
    color: #fff;
    font-weight: 700;
}

.impersonation-bar__meta {
    color: #94a3b8;
}

.impersonation-bar__actions {
    margin: 0;
}

.impersonation-bar__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid rgba(226, 232, 240, 0.28);
    background: rgba(255, 255, 255, 0.06);
    color: #f8fafc;
    border-radius: 0.45rem;
    padding: 0.3rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.2;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.impersonation-bar__btn:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(226, 232, 240, 0.45);
    color: #fff;
}

[data-theme-mode="dark"] .impersonation-bar {
    background: #020617;
}
</style>
@endif
