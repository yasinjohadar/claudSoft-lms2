<script>
(function () {
    function formatNumber(value, decimals, suffix) {
        var formatted = decimals
            ? new Intl.NumberFormat('ar-EG', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value)
            : new Intl.NumberFormat('ar-EG').format(Math.round(value));
        return formatted + (suffix || '');
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var suffix = el.dataset.countupSuffix || '';
        var decimals = el.dataset.countupDecimals === '1';
        var start = performance.now();
        var duration = 900;

        function tick(now) {
            var p = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = formatNumber(target * eased, decimals, suffix);
            if (p < 1) requestAnimationFrame(tick);
        }

        requestAnimationFrame(tick);
    });

    var grid = document.getElementById('achievementGrid');
    var emptyState = document.getElementById('achievementEmptyFiltered');
    var countEl = document.getElementById('achievementVisibleCount');
    var statusTabs = document.querySelectorAll('[data-status-filter]');
    var activeStatus = 'all';

    function getGridItems() {
        return grid ? Array.from(grid.querySelectorAll('.achievement-grid-item')) : [];
    }

    function applyStatusFilter(status) {
        activeStatus = status;
        var visible = 0;

        getGridItems().forEach(function (item) {
            var match = status === 'all' || item.dataset.achievementStatus === status;
            item.classList.toggle('d-none', !match);
            if (match) visible++;
        });

        if (countEl) countEl.textContent = '(' + visible + ')';
        if (emptyState) emptyState.classList.toggle('d-none', visible > 0);
        if (grid) grid.classList.toggle('d-none', visible === 0);

        statusTabs.forEach(function (tab) {
            tab.classList.toggle('is-active', tab.dataset.statusFilter === status);
        });
    }

    statusTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            applyStatusFilter(tab.dataset.statusFilter || 'all');
        });
    });

    var resetBtn = document.getElementById('achievementResetFilters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            applyStatusFilter('all');
        });
    }

    var modalEl = document.getElementById('achievementDetailModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var iconEl = document.getElementById('achievementModalIcon');
    var tierEl = document.getElementById('achievementModalTier');
    var titleEl = document.getElementById('achievementModalTitle');
    var descEl = document.getElementById('achievementModalDesc');
    var reqEl = document.getElementById('achievementModalRequirement');
    var pointsEl = document.getElementById('achievementModalPoints');
    var progressEl = document.getElementById('achievementModalProgress');
    var progressWrap = document.getElementById('achievementModalProgressWrap');
    var barEl = document.getElementById('achievementModalBar');
    var completedWrap = document.getElementById('achievementModalCompletedWrap');
    var completedAtEl = document.getElementById('achievementModalCompletedAt');
    var showLink = document.getElementById('achievementModalShowLink');
    var claimForm = document.getElementById('achievementModalClaimForm');

    function openFromCard(card) {
        var tierKey = card.dataset.tierKey || 'bronze';
        iconEl.textContent = card.dataset.icon || '🏆';
        tierEl.textContent = card.dataset.tier || '—';
        tierEl.className = 'student-achievement-modal__tier badge student-achievement-modal__tier--' + tierKey;
        titleEl.textContent = card.dataset.name || '—';
        descEl.textContent = card.dataset.description || '';
        descEl.classList.toggle('d-none', !card.dataset.description);
        reqEl.textContent = card.dataset.requirement || '—';
        pointsEl.textContent = (card.dataset.points || '0') + ' نقطة';
        showLink.href = card.dataset.showUrl || '#';

        var isCompleted = card.dataset.status === 'completed';
        completedWrap.classList.toggle('d-none', !isCompleted);
        progressWrap.classList.toggle('d-none', isCompleted);

        if (isCompleted) {
            progressEl.textContent = '100%';
            completedAtEl.textContent = card.dataset.completedAt
                ? 'اكتمل في ' + card.dataset.completedAt
                : '';
        } else {
            var pct = parseFloat(card.dataset.progress || '0');
            progressEl.textContent = Math.round(pct) + '% (' + (card.dataset.current || '0') + ' / ' + (card.dataset.target || '0') + ')';
            barEl.style.width = Math.max(0, Math.min(100, pct)) + '%';
        }

        if (claimForm) {
            var claimUrl = card.dataset.claimUrl;
            claimForm.classList.toggle('d-none', !claimUrl);
            if (claimUrl) claimForm.action = claimUrl;
        }

        modal.show();
    }

    document.querySelectorAll('[data-achievement-open]').forEach(function (card) {
        card.addEventListener('click', function () { openFromCard(card); });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openFromCard(card);
            }
        });
    });

    document.querySelectorAll('.student-achievement-card__bar').forEach(function (bar) {
        requestAnimationFrame(function () {
            bar.style.width = bar.style.getPropertyValue('--progress');
        });
    });
})();
</script>
