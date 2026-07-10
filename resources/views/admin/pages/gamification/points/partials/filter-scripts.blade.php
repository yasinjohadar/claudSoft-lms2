<script>
(function () {
    const form = document.getElementById('pointsFiltersForm');
    if (!form) return;

    const indexUrl = @json(route('admin.gamification.points.index'));
    const courseGroupsUrl = @json(route('admin.gamification.points.course-groups'));
    const allGroups = @json(($allGroups ?? collect())->map(fn ($group) => ['id' => $group->id, 'name' => $group->name])->values());

    let filterTimeout;
    let isLoading = false;

    function formatCountupNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function initPointsCountup(container) {
        const root = container || document;
        root.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatCountupNumber(target * eased);
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    }

    function buildFilterParams(page) {
        const params = new URLSearchParams(new FormData(form));
        params.set('page', String(page || 1));

        ['q', 'course_id', 'group_id', 'source', 'type', 'from_date', 'to_date'].forEach(function (key) {
            if (!params.get(key)) {
                params.delete(key);
            }
        });

        return params;
    }

    function updateBrowserUrl(params) {
        const url = new URL(indexUrl, window.location.origin);
        url.search = params.toString();
        window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
    }

    function renderGroupOptions(groups, selectedGroupId) {
        const groupSelect = document.getElementById('pointsGroup');
        if (!groupSelect) return;

        const selected = selectedGroupId || groupSelect.value || '';
        let html = '<option value="">كل المجموعات</option>';

        groups.forEach(function (group) {
            const isSelected = String(group.id) === String(selected) ? ' selected' : '';
            html += '<option value="' + group.id + '"' + isSelected + '>' + group.name + '</option>';
        });

        groupSelect.innerHTML = html;
    }

    function loadGroupsForCourse(courseId, selectedGroupId) {
        if (!courseId) {
            renderGroupOptions(allGroups, selectedGroupId);
            return Promise.resolve();
        }

        return fetch(courseGroupsUrl + '?course_id=' + encodeURIComponent(courseId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function (groups) {
                renderGroupOptions(groups, selectedGroupId);
            })
            .catch(function () {
                renderGroupOptions([], '');
            });
    }

    function loadTransactions(page) {
        if (isLoading) return;

        isLoading = true;
        const params = buildFilterParams(page);
        const tableBody = document.getElementById('points-transactions-table-body');
        const pagination = document.getElementById('points-transactions-pagination');
        const statsContainer = document.getElementById('points-stats-container');
        const modalsContainer = document.getElementById('points-transactions-modals');
        const countBadge = document.getElementById('points-transactions-count');

        tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></td></tr>';

        fetch(indexUrl + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(function (data) {
                if (data.stats && statsContainer) {
                    statsContainer.innerHTML = data.stats;
                    initPointsCountup(statsContainer);
                }

                if (data.table) {
                    tableBody.innerHTML = data.table;
                }

                if (pagination) {
                    pagination.innerHTML = data.pagination || '';
                }

                if (modalsContainer && data.modals) {
                    modalsContainer.innerHTML = data.modals;
                }

                if (countBadge && typeof data.total !== 'undefined') {
                    countBadge.textContent = data.total;
                }

                if (data.group_options) {
                    const groupSelect = document.getElementById('pointsGroup');
                    const currentGroup = groupSelect ? groupSelect.value : '';
                    if (groupSelect) {
                        groupSelect.innerHTML = data.group_options;
                        if (currentGroup) {
                            groupSelect.value = currentGroup;
                        }
                    }
                }

                updateBrowserUrl(params);
            })
            .catch(function () {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><div class="alert alert-danger mb-0">حدث خطأ أثناء تحميل المعاملات</div></td></tr>';
            })
            .finally(function () {
                isLoading = false;
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        loadTransactions(1);
    });

    document.getElementById('pointsFilterReset')?.addEventListener('click', function () {
        form.reset();
        renderGroupOptions(allGroups, '');
        loadTransactions(1);
    });

    const searchInput = document.getElementById('pointsSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                loadTransactions(1);
            }, 500);
        });
    }

    ['pointsSource', 'pointsType', 'pointsFromDate', 'pointsToDate', 'pointsGroup'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                loadTransactions(1);
            });
        }
    });

    const courseSelect = document.getElementById('pointsCourse');
    if (courseSelect) {
        courseSelect.addEventListener('change', function () {
            const courseId = courseSelect.value;
            const groupSelect = document.getElementById('pointsGroup');

            loadGroupsForCourse(courseId, '').then(function () {
                if (groupSelect) {
                    groupSelect.value = '';
                }
                loadTransactions(1);
            });
        });
    }

    document.addEventListener('click', function (event) {
        const link = event.target.closest('#points-transactions-pagination .pagination a');
        if (!link) return;

        event.preventDefault();
        const url = new URL(link.href);
        const page = url.searchParams.get('page') || '1';
        loadTransactions(page);
    });

    document.addEventListener('DOMContentLoaded', function () {
        initPointsCountup(document);
    });
})();
</script>
