<script>
(function () {
    const form = document.getElementById('badgeReportFiltersForm');
    if (!form) return;

    const indexUrl = @json($indexUrl);
    const courseGroupsUrl = @json(route('admin.gamification.badges.course-groups'));
    const reportMode = @json($reportMode ?? 'distribution');
    const studentDetailUrlTemplate = @json($studentDetailUrlTemplate ?? '');
    const allGroups = @json(collect($allGroups ?? [])->map(fn ($group) => ['id' => $group->id, 'name' => $group->name])->values());

    let filterTimeout;
    let isLoading = false;

    function formatCountupNumber(value, withDecimals) {
        if (withDecimals) {
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }).format(value);
        }

        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function initBadgeReportCountup(container) {
        const root = container || document;
        root.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const suffix = el.dataset.countupSuffix || '';
            const decimals = el.dataset.countupDecimals === '1';
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = formatCountupNumber(target * eased, decimals);
                el.textContent = value + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    }

    function buildFilterParams(page) {
        const params = new URLSearchParams(new FormData(form));
        params.set('page', String(page || 1));

        ['q', 'course_id', 'group_id', 'rarity'].forEach(function (key) {
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
        const groupSelect = document.getElementById('badgeReportGroup');
        if (!groupSelect) return;

        const selected = selectedGroupId || groupSelect.value || '';
        let html = '<option value="">كل المجموعات</option>';

        groups.forEach(function (group) {
            const isSelected = String(group.id) === String(selected) ? ' selected' : '';
            html += '<option value="' + group.id + '"' + isSelected + '>' + group.name + '</option>';
        });

        groupSelect.innerHTML = html;
    }

    function loadReport(page) {
        if (isLoading) return;

        isLoading = true;
        const params = buildFilterParams(page);
        const tableBody = document.getElementById('badge-report-table-body');
        const pagination = document.getElementById('badge-report-pagination');
        const statsContainer = document.getElementById('badge-report-stats-container');
        const totalBadge = document.getElementById('badge-report-total');

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
                    initBadgeReportCountup(statsContainer);
                }

                if (data.table) {
                    tableBody.innerHTML = data.table;
                }

                if (pagination) {
                    pagination.innerHTML = data.pagination || '';
                }

                if (totalBadge && typeof data.total !== 'undefined') {
                    totalBadge.textContent = data.total;
                }

                if (data.group_options) {
                    const groupSelect = document.getElementById('badgeReportGroup');
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
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-5"><div class="alert alert-danger mb-0">حدث خطأ أثناء تحميل التقرير</div></td></tr>';
            })
            .finally(function () {
                isLoading = false;
            });
    }

    function loadStudentDetail(studentId, studentName) {
        const modalEl = document.getElementById('badgeStudentDetailModal');
        const modalBody = document.getElementById('badgeStudentDetailModalBody');
        const modalTitle = document.getElementById('badgeStudentDetailModalTitle');

        if (!modalEl || !modalBody || !studentDetailUrlTemplate) return;

        const params = buildFilterParams(1);
        const detailUrl = studentDetailUrlTemplate.replace('__ID__', String(studentId)) + '?' + params.toString();

        modalTitle.textContent = 'شارات: ' + (studentName || '');
        modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        fetch(detailUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network error');
                return response.text();
            })
            .then(function (html) {
                modalBody.innerHTML = html;
            })
            .catch(function () {
                modalBody.innerHTML = '<div class="alert alert-danger mb-0">تعذر تحميل تفاصيل الطالب.</div>';
            });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        loadReport(1);
    });

    document.getElementById('badgeReportFilterReset')?.addEventListener('click', function () {
        form.reset();
        renderGroupOptions(allGroups, '');
        loadReport(1);
    });

    const searchInput = document.getElementById('badgeReportSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                loadReport(1);
            }, 500);
        });
    }

    ['badgeReportRarity', 'badgeReportGroup'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                loadReport(1);
            });
        }
    });

    const courseSelect = document.getElementById('badgeReportCourse');
    if (courseSelect) {
        courseSelect.addEventListener('change', function () {
            const courseId = courseSelect.value;

            if (!courseId) {
                renderGroupOptions(allGroups, '');
                const groupSelect = document.getElementById('badgeReportGroup');
                if (groupSelect) groupSelect.value = '';
                loadReport(1);
                return;
            }

            fetch(courseGroupsUrl + '?course_id=' + encodeURIComponent(courseId), {
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
                    renderGroupOptions(groups, '');
                    const groupSelect = document.getElementById('badgeReportGroup');
                    if (groupSelect) groupSelect.value = '';
                    loadReport(1);
                })
                .catch(function () {
                    renderGroupOptions([], '');
                    loadReport(1);
                });
        });
    }

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#badge-report-pagination .pagination a');
        if (paginationLink) {
            event.preventDefault();
            const url = new URL(paginationLink.href);
            const page = url.searchParams.get('page') || '1';
            loadReport(page);
            return;
        }

        const detailBtn = event.target.closest('.badge-student-detail-btn');
        if (detailBtn && reportMode === 'students') {
            loadStudentDetail(detailBtn.dataset.studentId, detailBtn.dataset.studentName);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        initBadgeReportCountup(document);
    });
})();
</script>
