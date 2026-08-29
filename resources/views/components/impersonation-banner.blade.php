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
        <div class="impersonation-bar__actions">
            <form action="{{ route('admin.stop-impersonate') }}" method="POST" class="impersonation-bar__form">
                @csrf
                <button type="submit" class="impersonation-bar__btn">
                    <i class="fe fe-log-out"></i>
                    العودة لحساب الأدمن
                </button>
            </form>
            <button type="button"
                    class="impersonation-bar__close"
                    data-impersonation-hide
                    title="إخفاء الشريط"
                    aria-label="إخفاء شريط وضع المشاهدة">
                <i class="fe fe-x" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<button type="button"
        class="impersonation-bar__restore"
        data-impersonation-show
        title="إظهار شريط وضع المشاهدة"
        aria-label="إظهار شريط وضع المشاهدة"
        hidden>
    <i class="fe fe-eye" aria-hidden="true"></i>
</button>

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
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin: 0;
}

.impersonation-bar__form {
    margin: 0;
}

.impersonation-bar__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.65rem;
    height: 1.65rem;
    padding: 0;
    border: 1px solid rgba(226, 232, 240, 0.22);
    background: transparent;
    color: #cbd5e1;
    border-radius: 0.4rem;
    font-size: 0.85rem;
    line-height: 1;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.impersonation-bar__close:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(226, 232, 240, 0.45);
    color: #fff;
}

/* زر إعادة إظهار الشريط بعد إغلاقه */
.impersonation-bar__restore {
    position: fixed;
    inset-block-start: 0.6rem;
    inset-inline-start: 0.6rem;
    z-index: 1056;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 50%;
    font-size: 0.85rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.55;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.25);
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.impersonation-bar__restore:hover {
    opacity: 1;
    transform: scale(1.05);
}

.impersonation-bar__restore[hidden] {
    display: none !important;
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

.impersonation-bar[hidden] {
    display: none !important;
}

[data-theme-mode="dark"] .impersonation-bar {
    background: #020617;
}

[data-theme-mode="dark"] .impersonation-bar__restore {
    background: #020617;
}
</style>

<script>
(function () {
    var STORAGE_KEY = 'impersonationBarHidden';
    var bar = document.querySelector('.impersonation-bar');
    var restoreBtn = document.querySelector('[data-impersonation-show]');
    var hideBtn = document.querySelector('[data-impersonation-hide]');

    if (!bar || !restoreBtn || !hideBtn) {
        return;
    }

    function readState() {
        try {
            return sessionStorage.getItem(STORAGE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function writeState(hidden) {
        try {
            sessionStorage.setItem(STORAGE_KEY, hidden ? '1' : '0');
        } catch (e) {
            /* الوضع الخاص قد يمنع التخزين — نتجاهل */
        }
    }

    function apply(hidden) {
        bar.hidden = hidden;
        restoreBtn.hidden = !hidden;
    }

    hideBtn.addEventListener('click', function () {
        apply(true);
        writeState(true);
    });

    restoreBtn.addEventListener('click', function () {
        apply(false);
        writeState(false);
    });

    // يبقى مخفياً أثناء التنقل بين الصفحات ضمن نفس الجلسة
    apply(readState());
})();
</script>
@endif
