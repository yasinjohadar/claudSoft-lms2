<script>
(function () {
    let detailModal = null;

    function getDetailModal() {
        const el = document.getElementById('membershipRequestDetailModal');
        if (!el || typeof bootstrap === 'undefined') return null;
        if (!detailModal) {
            detailModal = bootstrap.Modal.getOrCreateInstance(el);
        }
        return detailModal;
    }

    function openMembershipRequestDetail(url, studentName) {
        const modal = getDetailModal();
        const body = document.getElementById('membershipRequestDetailBody');
        const title = document.getElementById('membershipRequestDetailTitle');
        if (!modal || !body) {
            window.open(url, '_blank');
            return;
        }

        if (title) {
            title.textContent = studentName ? ('بيانات — ' + studentName) : 'بيانات طلب الانضمام';
        }

        body.innerHTML = '<div class="text-center py-5 text-muted">'
            + '<div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>'
            + '<p class="mb-0 small">جاري تحميل البيانات...</p></div>';

        modal.show();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('fetch failed');
                return r.json();
            })
            .then(function (data) {
                if (data.success && data.html) {
                    body.innerHTML = data.html;
                } else {
                    throw new Error(data.message || 'invalid');
                }
            })
            .catch(function () {
                body.innerHTML = '<div class="alert alert-danger mb-0">'
                    + '<i class="fe fe-alert-circle me-1"></i>'
                    + 'تعذّر تحميل البيانات. '
                    + '<a href="' + url + '" target="_blank" rel="noopener" class="alert-link">فتح في صفحة جديدة</a>'
                    + '</div>';
            });
    }

    document.addEventListener('click', function (e) {
        const rejectBtn = e.target.closest('.js-membership-detail-reject');
        if (rejectBtn) {
            const modal = getDetailModal();
            if (modal) modal.hide();
        }
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-membership-request-detail');
        if (!btn) return;
        e.preventDefault();
        const url = btn.getAttribute('data-url');
        const name = btn.getAttribute('data-student-name') || '';
        if (url) openMembershipRequestDetail(url, name);
    });

    window.openMembershipRequestDetail = openMembershipRequestDetail;
})();
</script>
