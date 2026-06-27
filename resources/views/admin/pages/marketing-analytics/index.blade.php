@extends('admin.layouts.master')

@section('page-title')
    إحصائيات التسويق
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/marketing-analytics.css') }}?v={{ filemtime(public_path('assets/css/marketing-analytics.css')) }}">
@endpush

@section('content')
<div class="main-content app-content ma-page">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">إحصائيات التسويق</li>
                </ol>
            </nav>
        </div>

        <div class="ma-hero dashboard-fade-in">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <i class="fab fa-google fa-lg"></i>
                        <span class="ma-hero__badge">Google Marketing</span>
                        @if($stats['api_active'])
                            <span class="ma-hero__badge ma-hero__badge--ok"><i class="fe fe-wifi me-1"></i>API متصل</span>
                        @endif
                    </div>
                    <h1 class="ma-hero__title">إحصائيات التسويق</h1>
                    <p class="ma-hero__desc">
                        Analytics 4 + Search Console — بيانات مُخزّنة مؤقتاً، تحميل تفاعلي، وصفر تأثير على أداء الموقع العام.
                    </p>
                </div>
                <div class="ma-toolbar">
                    <div class="d-flex gap-1" id="maPeriodGroup">
                        <button type="button" class="ma-period-btn" data-period="7d">7 أيام</button>
                        <button type="button" class="ma-period-btn active" data-period="30d">30 يوم</button>
                        <button type="button" class="ma-period-btn" data-period="90d">90 يوم</button>
                    </div>
                    <button type="button" id="maRefreshBtn" class="ma-btn-ghost">
                        <i class="fe fe-refresh-cw me-1"></i>تحديث
                    </button>
                    <a href="{{ route('admin.google-settings.edit') }}" class="ma-btn-ghost">
                        <i class="fe fe-settings me-1"></i>الإعدادات
                    </a>
                </div>
            </div>
        </div>

        <div id="maStatusBar" class="ma-toast ma-toast--info d-none"></div>
        <div id="maErrorBar" class="ma-toast ma-toast--warn d-none"></div>

        <div class="ma-tabs" role="tablist">
            <button class="ma-tab active" data-bs-toggle="tab" data-bs-target="#maOverview" type="button">
                <span class="ma-tab__icon"><i class="fe fe-grid"></i></span>
                نظرة عامة
            </button>
            <button class="ma-tab" data-bs-toggle="tab" data-bs-target="#maGa4" type="button">
                <span class="ma-tab__icon"><i class="fe fe-bar-chart-2"></i></span>
                Analytics
            </button>
            <button class="ma-tab" data-bs-toggle="tab" data-bs-target="#maGsc" type="button">
                <span class="ma-tab__icon"><i class="fe fe-search"></i></span>
                Search Console
            </button>
        </div>

        <div class="tab-content">
            {{-- Overview --}}
            <div class="tab-pane fade show active" id="maOverview">
                <div class="row g-3 mb-4 ma-kpi-grid" id="maOverviewKpis">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $i * 80 }}ms">
                            <div class="ma-skeleton" style="height:96px;"></div>
                        </div>
                    @endfor
                </div>

                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="ma-panel">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-link-2"></i></span>
                                    حالة الربط
                                </h2>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="maConnectionStatus">
                                <span class="ma-status-pill {{ $stats['gtm_active'] ? 'on' : 'off' }}">
                                    <span class="ma-status-dot"></span> Tag Manager
                                </span>
                                <span class="ma-status-pill {{ $stats['gsc_active'] ? 'on' : 'off' }}">
                                    <span class="ma-status-dot"></span> Search Console
                                </span>
                                <span class="ma-status-pill {{ $stats['api_active'] ? 'on' : 'off' }}">
                                    <span class="ma-status-dot"></span> Analytics API
                                </span>
                                <span class="ma-status-pill {{ $stats['gsc_api_active'] ? 'on' : 'off' }}">
                                    <span class="ma-status-dot"></span> GSC API
                                </span>
                            </div>
                            @if(!$stats['api_active'])
                                <div class="alert alert-light border mb-0 py-2 px-3 small">
                                    <i class="fe fe-info me-1 text-primary"></i>
                                    لعرض الإحصائيات هنا، فعّل Analytics API من
                                    <a href="{{ route('admin.google-settings.edit') }}">إعدادات Google</a>.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="ma-panel h-100">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-external-link"></i></span>
                                    فتح في Google
                                </h2>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="https://analytics.google.com/" target="_blank" class="ma-link-card">
                                    <span class="ma-link-card__icon ma-link-card__icon--ga"><i class="fe fe-bar-chart"></i></span>
                                    <span>Google Analytics 4</span>
                                    <i class="fe fe-arrow-up-left ms-auto opacity-50"></i>
                                </a>
                                <a href="https://search.google.com/search-console" target="_blank" class="ma-link-card">
                                    <span class="ma-link-card__icon ma-link-card__icon--gsc"><i class="fe fe-search"></i></span>
                                    <span>Search Console</span>
                                    <i class="fe fe-arrow-up-left ms-auto opacity-50"></i>
                                </a>
                                <a href="https://tagmanager.google.com/" target="_blank" class="ma-link-card">
                                    <span class="ma-link-card__icon ma-link-card__icon--gtm"><i class="fe fe-tag"></i></span>
                                    <span>Tag Manager</span>
                                    <i class="fe fe-arrow-up-left ms-auto opacity-50"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GA4 --}}
            <div class="tab-pane fade" id="maGa4">
                <div class="row g-3 mb-4 ma-kpi-grid" id="maGa4Kpis">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $i * 80 }}ms">
                            <div class="ma-skeleton" style="height:96px;"></div>
                        </div>
                    @endfor
                </div>
                <div class="ma-panel">
                    <div class="ma-panel__head">
                        <h2 class="ma-panel__title">
                            <span class="ma-panel__icon"><i class="fe fe-trending-up"></i></span>
                            الجلسات اليومية
                        </h2>
                    </div>
                    <div id="maGa4Chart" class="ma-chart ma-skeleton"></div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="ma-panel">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-file-text"></i></span>
                                    أفضل الصفحات
                                </h2>
                            </div>
                            <div id="maGa4Pages"><div class="ma-skeleton" style="height:200px;"></div></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ma-panel">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-share-2"></i></span>
                                    مصادر الزيارات
                                </h2>
                            </div>
                            <div id="maGa4Sources"><div class="ma-skeleton" style="height:200px;"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GSC --}}
            <div class="tab-pane fade" id="maGsc">
                <div class="row g-3 mb-4 ma-kpi-grid" id="maGscKpis">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $i * 80 }}ms">
                            <div class="ma-skeleton" style="height:96px;"></div>
                        </div>
                    @endfor
                </div>
                <div class="ma-panel">
                    <div class="ma-panel__head">
                        <h2 class="ma-panel__title">
                            <span class="ma-panel__icon"><i class="fe fe-activity"></i></span>
                            النقرات vs الظهور
                        </h2>
                    </div>
                    <div id="maGscChart" class="ma-chart ma-skeleton"></div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="ma-panel">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-hash"></i></span>
                                    أفضل استعلامات البحث
                                </h2>
                            </div>
                            <div id="maGscQueries"><div class="ma-skeleton" style="height:200px;"></div></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ma-panel">
                            <div class="ma-panel__head">
                                <h2 class="ma-panel__title">
                                    <span class="ma-panel__icon"><i class="fe fe-globe"></i></span>
                                    أفضل صفحات البحث
                                </h2>
                            </div>
                            <div id="maGscPages"><div class="ma-skeleton" style="height:200px;"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var currentPeriod = '30d';
    var refreshBtn = document.getElementById('maRefreshBtn');
    var ga4Chart = null;
    var gscChart = null;
    var dataUrl = @json(route('admin.marketing-analytics.data'));

    document.querySelectorAll('.ma-period-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.ma-period-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentPeriod = btn.dataset.period;
            loadData(false);
        });
    });

    document.querySelectorAll('.ma-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.ma-tab').forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
        });
    });

    function kpiCard(variant, icon, label, value, sub, countup) {
        var valHtml = countup
            ? '<h3 class="ma-kpi__value mb-1" data-countup="' + value + '">0</h3>'
            : '<h3 class="ma-kpi__value ma-kpi__value--text mb-1">' + value + '</h3>';
        return '<div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay:' + (Math.random() * 200) + 'ms">' +
            '<div class="card ma-kpi ma-kpi--' + variant + '">' +
            '<div class="card-body d-flex align-items-center gap-3">' +
            '<div class="ma-kpi__icon-wrap"><i class="fe ' + icon + ' ma-kpi__icon"></i></div>' +
            '<div class="flex-fill min-w-0"><p class="ma-kpi__label mb-1">' + label + '</p>' +
            valHtml + '<p class="ma-kpi__sub mb-0">' + sub + '</p></div></div></div></div>';
    }

    function triggerCountup(container) {
        if (!container) return;
        container.querySelectorAll('[data-countup]').forEach(function (el) {
            if (el.dataset.countupAnimated === 'true') return;
            el.dataset.countupAnimated = 'true';
            var target = parseFloat(el.dataset.countup || '0');
            var duration = 900;
            var start = performance.now();
            var step = function (now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    }

    function emptyState(msg) {
        return '<div class="ma-empty"><i class="fe fe-inbox"></i><p class="mb-0">' + msg + '</p></div>';
    }

    function renderRankTable(containerId, headers, rows, barIndex) {
        var el = document.getElementById(containerId);
        if (!rows || !rows.length) {
            el.innerHTML = emptyState('لا توجد بيانات لهذه الفترة');
            return;
        }
        var maxVal = 1;
        if (barIndex !== null && rows.length) {
            maxVal = Math.max.apply(null, rows.map(function (r) { return parseFloat(String(r[barIndex]).replace(/[^\d.]/g, '')) || 0; }));
        }
        var html = '<div class="table-responsive"><table class="table table-sm ma-rank-table mb-0"><thead><tr><th style="width:40px">#</th>';
        headers.forEach(function (h) { html += '<th>' + h + '</th>'; });
        html += '</tr></thead><tbody>';
        rows.forEach(function (row, i) {
            html += '<tr><td><span class="ma-rank-badge ' + (i < 3 ? 'top' : '') + '">' + (i + 1) + '</span></td>';
            row.forEach(function (cell, ci) {
                if (barIndex !== null && ci === barIndex) {
                    var num = parseFloat(String(cell).replace(/[^\d.]/g, '')) || 0;
                    var pct = Math.round((num / maxVal) * 100);
                    html += '<td><div class="d-flex align-items-center gap-2"><span class="text-nowrap">' + cell + '</span><div class="ma-bar-wrap flex-grow-1"><div class="ma-bar" style="width:' + pct + '%"></div></div></div></td>';
                } else {
                    html += '<td class="' + (ci === 0 ? 'text-truncate' : '') + '" style="max-width:200px">' + cell + '</td>';
                }
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        el.innerHTML = html;
    }

    function renderGa4(data) {
        var ga4 = data.ga4;
        if (!ga4) {
            document.getElementById('maGa4Kpis').innerHTML = '<div class="col-12">' + emptyState('فعّل Analytics API من الإعدادات') + '</div>';
            return;
        }
        var o = ga4.overview;
        document.getElementById('maGa4Kpis').innerHTML =
            kpiCard('blue', 'fe-activity', 'الجلسات', o.sessions, 'GA4', true) +
            kpiCard('green', 'fe-users', 'المستخدمون', o.users, 'Unique users', true) +
            kpiCard('cyan', 'fe-eye', 'مشاهدات الصفحة', o.page_views, 'Page views', true) +
            kpiCard('orange', 'fe-trending-up', 'Engagement', o.engagement_rate + '%', 'معدل التفاعل', false);
        triggerCountup(document.getElementById('maGa4Kpis'));

        document.getElementById('maOverviewKpis').innerHTML =
            kpiCard('blue', 'fe-activity', 'جلسات GA4', o.sessions, 'آخر ' + data.period, true) +
            kpiCard('green', 'fe-users', 'مستخدمون', o.users, 'Analytics', true) +
            (data.gsc
                ? kpiCard('cyan', 'fe-mouse-pointer', 'نقرات البحث', data.gsc.overview.clicks, 'Search Console', true)
                : kpiCard('cyan', 'fe-search', 'Search Console', '—', 'غير مربوط', false)) +
            kpiCard('orange', 'fe-eye', 'مشاهدات', o.page_views, 'GA4', true);
        triggerCountup(document.getElementById('maOverviewKpis'));

        var chartEl = document.getElementById('maGa4Chart');
        chartEl.classList.remove('ma-skeleton');
        chartEl.innerHTML = '';
        if (typeof ApexCharts !== 'undefined' && ga4.daily_sessions.labels.length) {
            if (ga4Chart) ga4Chart.destroy();
            ga4Chart = new ApexCharts(chartEl, {
                chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, speed: 800 } },
                series: [{ name: 'جلسات', data: ga4.daily_sessions.values }],
                xaxis: { categories: ga4.daily_sessions.labels, labels: { style: { colors: '#64748b' } } },
                yaxis: { labels: { style: { colors: '#64748b' } } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                colors: ['#4285f4'],
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                fill: { type: 'solid', opacity: 0.15 },
                tooltip: { theme: 'light', y: { formatter: function (v) { return v.toLocaleString('ar'); } } }
            });
            ga4Chart.render();
        } else {
            chartEl.innerHTML = emptyState('لا توجد بيانات جلسات');
        }

        renderRankTable('maGa4Pages', ['الصفحة', 'المشاهدات'],
            ga4.top_pages.map(function (r) { return [r.path, r.views.toLocaleString('ar')]; }), 1);
        renderRankTable('maGa4Sources', ['المصدر', 'الجلسات'],
            ga4.traffic_sources.map(function (r) { return [r.source, r.sessions.toLocaleString('ar')]; }), 1);
    }

    function renderGsc(data) {
        var gsc = data.gsc;
        if (!gsc) {
            document.getElementById('maGscKpis').innerHTML = '<div class="col-12">' + emptyState('فعّل Search Console API') + '</div>';
            return;
        }
        var o = gsc.overview;
        document.getElementById('maGscKpis').innerHTML =
            kpiCard('blue', 'fe-mouse-pointer', 'النقرات', o.clicks, 'Search', true) +
            kpiCard('green', 'fe-eye', 'الظهور', o.impressions, 'Impressions', true) +
            kpiCard('cyan', 'fe-percent', 'CTR', o.ctr + '%', 'Click-through rate', false) +
            kpiCard('orange', 'fe-hash', 'الموضع', o.position, 'متوسط الترتيب', false);
        triggerCountup(document.getElementById('maGscKpis'));

        var chartEl = document.getElementById('maGscChart');
        chartEl.classList.remove('ma-skeleton');
        chartEl.innerHTML = '';
        if (typeof ApexCharts !== 'undefined' && gsc.daily.labels.length) {
            if (gscChart) gscChart.destroy();
            gscChart = new ApexCharts(chartEl, {
                chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, speed: 800 } },
                series: [
                    { name: 'نقرات', data: gsc.daily.clicks },
                    { name: 'ظهور', data: gsc.daily.impressions }
                ],
                xaxis: { categories: gsc.daily.labels, labels: { style: { colors: '#64748b' } } },
                yaxis: { labels: { style: { colors: '#64748b' } } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                colors: ['#4285f4', '#34a853'],
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                legend: { position: 'top', horizontalAlign: 'left' },
                tooltip: { theme: 'light' }
            });
            gscChart.render();
        } else {
            chartEl.innerHTML = emptyState('لا توجد بيانات بحث');
        }

        renderRankTable('maGscQueries', ['الاستعلام', 'نقرات', 'ظهور', 'CTR'],
            gsc.top_queries.map(function (r) { return [r.query, r.clicks, r.impressions, r.ctr + '%']; }), 1);
        renderRankTable('maGscPages', ['الصفحة', 'نقرات', 'ظهور'],
            gsc.top_pages.map(function (r) { return [r.page, r.clicks, r.impressions]; }), 1);
    }

    function loadData(refresh) {
        refreshBtn.classList.add('spinning');
        refreshBtn.disabled = true;

        var url = dataUrl + '?period=' + encodeURIComponent(currentPeriod) + (refresh ? '&refresh=1' : '');

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json().then(function (json) { return { ok: res.ok, json: json }; }); })
            .then(function (result) {
                refreshBtn.classList.remove('spinning');
                refreshBtn.disabled = false;

                if (!result.ok || !result.json.success) {
                    var errBar = document.getElementById('maErrorBar');
                    errBar.classList.remove('d-none');
                    errBar.innerHTML = '<i class="fe fe-alert-triangle"></i> ' + (result.json.message || 'تعذّر تحميل البيانات');
                    return;
                }
                document.getElementById('maErrorBar').classList.add('d-none');
                var data = result.json.data;
                var statusBar = document.getElementById('maStatusBar');
                statusBar.classList.remove('d-none');
                var syncText = data.generated_at ? new Date(data.generated_at).toLocaleString('ar') : '—';
                statusBar.innerHTML = '<i class="fe fe-clock"></i> آخر تحديث: ' + syncText;
                if (data.errors && Object.keys(data.errors).length) {
                    statusBar.innerHTML += ' <span class="opacity-75">| ' + Object.values(data.errors).join(' — ') + '</span>';
                }
                renderGa4(data);
                renderGsc(data);
            })
            .catch(function () {
                refreshBtn.classList.remove('spinning');
                refreshBtn.disabled = false;
                var errBar = document.getElementById('maErrorBar');
                errBar.classList.remove('d-none');
                errBar.innerHTML = '<i class="fe fe-wifi-off"></i> خطأ في الاتصال بالخادم';
            });
    }

    refreshBtn.addEventListener('click', function () { loadData(true); });
    loadData(false);
});
</script>
@stop
