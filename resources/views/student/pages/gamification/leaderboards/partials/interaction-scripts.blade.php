@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('leaderboardPlayerModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var rankEmoji = { 1: '🥇', 2: '🥈', 3: '🥉' };

    function openEntryModal(payload) {
        if (!payload) {
            return;
        }

        modalEl.querySelector('.js-lb-modal-rank').textContent = rankEmoji[payload.rank] || ('#' + payload.rank);
        modalEl.querySelector('.js-lb-modal-rank-num').textContent = '#' + payload.rank;
        modalEl.querySelector('.js-lb-modal-name').textContent = payload.name || '';
        var nameArEl = modalEl.querySelector('.js-lb-modal-name-ar');
        if (nameArEl) {
            if (payload.nameAr) {
                nameArEl.textContent = payload.nameAr;
                nameArEl.hidden = false;
            } else {
                nameArEl.textContent = '';
                nameArEl.hidden = true;
            }
        }
        modalEl.querySelector('.js-lb-modal-score').textContent = Number(payload.score || 0).toLocaleString('ar-EG');

        var metricLabel = payload.metricLabel || 'النتيجة';
        modalEl.querySelector('.js-lb-modal-metric-label').textContent = metricLabel;

        var meBadge = modalEl.querySelector('.js-lb-modal-me-badge');
        meBadge.hidden = !payload.isMe;

        var divisionBadge = modalEl.querySelector('.js-lb-modal-division');
        if (divisionBadge) {
            var division = payload.division || 'bronze';
            divisionBadge.className = 'student-leaderboard-division-badge student-leaderboard-division-badge--' + division + ' student-leaderboard-division-badge--md js-lb-modal-division';
            divisionBadge.innerHTML =
                '<span class="student-leaderboard-division-badge__icon" aria-hidden="true"><i class="ri ' + (payload.divisionIcon || 'ri-award-line') + '"></i></span>' +
                '<span class="student-leaderboard-division-badge__label">' + (payload.divisionLabel || '') + '</span>';
        }

        var photo = modalEl.querySelector('.js-lb-modal-photo');
        var initials = modalEl.querySelector('.js-lb-modal-initials');
        photo.src = payload.photoUrl || '';
        photo.alt = payload.name || '';
        photo.hidden = false;
        initials.textContent = payload.initials || '?';
        initials.hidden = true;
        photo.onerror = function () {
            photo.hidden = true;
            initials.hidden = false;
        };

        var changeWrap = modalEl.querySelector('.js-lb-modal-change-wrap');
        var changeEl = modalEl.querySelector('.js-lb-modal-change');
        if (payload.rankChange) {
            changeWrap.hidden = false;
            changeEl.textContent = (payload.rankChange > 0 ? '↑' : '↓') + Math.abs(payload.rankChange);
            changeEl.className = payload.rankChange > 0 ? 'text-success' : 'text-danger';
        } else {
            changeWrap.hidden = true;
        }

        var actions = modalEl.querySelector('.js-lb-modal-actions');
        actions.innerHTML = '';

        if (payload.isMe) {
            actions.innerHTML += '<a href="{{ route('student.profile.index') }}" class="btn btn-primary btn-sm"><i class="ri ri-user-line me-1"></i>ملفي الشخصي</a>';
        } else if (payload.profilePublic) {
            actions.innerHTML += '<a href="{{ url('/students') }}/' + payload.userId + '" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="ri ri-external-link-line me-1"></i>الملف العام</a>';
        }

        var listRow = document.querySelector('.js-leaderboard-entry[data-user-id="' + payload.userId + '"]');
        if (listRow) {
            var scrollBtn = document.createElement('button');
            scrollBtn.type = 'button';
            scrollBtn.className = 'btn btn-light btn-sm js-lb-scroll-to-row';
            scrollBtn.innerHTML = '<i class="ri ri-list-check me-1"></i>الانتقال في القائمة';
            scrollBtn.addEventListener('click', function () {
                modal.hide();
                listRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                listRow.classList.add('is-highlight');
                setTimeout(function () { listRow.classList.remove('is-highlight'); }, 1600);
            });
            actions.appendChild(scrollBtn);
        }

        modal.show();
    }

    document.querySelectorAll('.js-leaderboard-entry').forEach(function (el) {
        el.addEventListener('click', function () {
            try {
                openEntryModal(JSON.parse(el.getAttribute('data-entry')));
            } catch (e) {
                /* ignore */
            }
        });

        el.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                el.click();
            }
        });
    });

    document.querySelectorAll('[data-division-filter]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var filter = tab.getAttribute('data-division-filter');
            document.querySelectorAll('[data-division-filter]').forEach(function (t) {
                t.classList.toggle('is-active', t === tab);
            });

            document.querySelectorAll('.js-leaderboard-filterable').forEach(function (row) {
                var match = filter === 'all' || row.getAttribute('data-division') === filter;
                row.hidden = !match;
            });

            var empty = document.querySelector('.js-leaderboard-filter-empty');
            if (empty) {
                var visible = document.querySelectorAll('.js-leaderboard-filterable:not([hidden])').length;
                empty.hidden = visible > 0;
            }
        });
    });
});
</script>
@endpush
