@extends('admin.layouts.master')

@section('page-title', 'سجل دفعات التوليد بالذكاء الاصطناعي')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@include('admin.docs.pages.partials.ai-batch-styles')
<style>
    .doc-ai-hist-table td, .doc-ai-hist-table th { vertical-align: middle; }
    .doc-ai-hist-row--incomplete { background: rgba(253, 240, 227, .45); }
    .doc-ai-hist-bar { height: 6px; min-width: 120px; }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-ai-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.pages.index') }}">التوثيق</a></li>
                    <li class="breadcrumb-item active">سجل الدفعات</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-clock me-1"></i>
                        مساعد التوثيق
                    </span>
                    <h2 class="group-show-hero__title mb-2">سجل دفعات التوليد</h2>
                    <p class="group-show-hero__desc mb-2">
                        كل دفعة بدأتها محفوظة هنا. الدفعات التي تحتوي مواضيع غير مكتملة مميّزة —
                        افتح الدفعة لترى الأقسام الناقصة لكل موضوع وتتابع توليدها من حيث توقّفت.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.ai-pages.batch.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus-circle me-1"></i>دفعة جديدة
                        </a>
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة الصفحات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card custom-card doc-ai-animate mb-0">
                    <div class="card-body">
                        <span class="d-block text-muted fs-12 mb-1">إجمالي الدفعات</span>
                        <h4 class="mb-0">{{ $totals['batches'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card custom-card doc-ai-animate mb-0">
                    <div class="card-body">
                        <span class="d-block text-muted fs-12 mb-1">مواضيع غير مكتملة</span>
                        <h4 class="mb-0 {{ $totals['incomplete_items'] ? 'text-warning' : 'text-success' }}">
                            {{ $totals['incomplete_items'] }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate">
            <div class="card-header doc-ai-panel__header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="doc-ai-panel__title mb-0">
                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-layers"></i></span>
                    الدفعات
                </h6>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.docs.ai-pages.batch.index') }}"
                       class="btn {{ $onlyIncomplete ? 'btn-light border' : 'btn-primary' }}">الكل</a>
                    <a href="{{ route('admin.docs.ai-pages.batch.index', ['incomplete' => 1]) }}"
                       class="btn {{ $onlyIncomplete ? 'btn-primary' : 'btn-light border' }}">غير المكتملة فقط</a>
                </div>
            </div>
            <div class="card-body pt-2">
                @if($batches->isEmpty())
                    <div class="doc-ai-hint mb-0 text-center py-4">
                        <i class="fe fe-inbox d-block fs-3 mb-2"></i>
                        @if($onlyIncomplete)
                            لا توجد دفعات تحتوي مواضيع غير مكتملة — كل ما ولّدته اكتمل بنجاح.
                        @else
                            لم تبدأ أي دفعة بعد.
                            <a href="{{ route('admin.docs.ai-pages.batch.create') }}">ابدأ دفعتك الأولى</a>.
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table doc-ai-hist-table align-middle">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المواضيع</th>
                                    <th>التقدّم</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    @php
                                        // Green is only what actually got saved; stopped topics get their own amber slice.
                                        $pct = $batch->total ? (int) round($batch->completed_count / $batch->total * 100) : 0;
                                        $failedPct = $batch->total ? (int) round($batch->failed_count / $batch->total * 100) : 0;
                                        $noProgressCount = max(0, $batch->failed_count - $batch->resumable_count);
                                        $statusMap = [
                                            'queued' => ['في الطابور', 'pending'],
                                            'running' => ['جارية', 'running'],
                                            'completed' => ['مكتملة', 'completed'],
                                            'completed_with_errors' => ['مكتملة مع نواقص', 'incomplete'],
                                            'cancelled' => ['ملغاة', 'skipped'],
                                        ];
                                        [$label, $badgeKey] = $statusMap[$batch->status] ?? [$batch->status, 'pending'];
                                    @endphp
                                    <tr class="{{ $batch->failed_count ? 'doc-ai-hist-row--incomplete' : '' }}">
                                        <td>
                                            <span class="d-block">{{ $batch->created_at?->format('Y-m-d H:i') }}</span>
                                            <small class="text-muted">{{ $batch->created_at?->diffForHumans() }}</small>
                                        </td>
                                        <td><strong>{{ $batch->total }}</strong></td>
                                        <td style="min-width:180px;">
                                            <div class="progress doc-ai-hist-bar mb-1">
                                                <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                                <div class="progress-bar bg-warning" style="width: {{ $failedPct }}%"></div>
                                            </div>
                                            <small class="text-muted">
                                                مكتملة {{ $batch->completed_count }}
                                                @if($batch->failed_count)
                                                    · <span class="text-warning fw-semibold">غير مكتملة {{ $batch->failed_count }}</span>
                                                @endif
                                                @if($batch->open_count)
                                                    · قيد الانتظار {{ $batch->open_count }}
                                                @endif
                                            </small>
                                            @if($batch->failed_count)
                                                <small class="d-block text-muted">
                                                    @if($batch->resumable_count)
                                                        <i class="fe fe-check-circle text-success"></i> تم التحقق: {{ $batch->resumable_count }} قابل للمتابعة (أقسامه المكتملة محفوظة)
                                                    @endif
                                                    @if($noProgressCount)
                                                        <span class="d-block"><i class="fe fe-alert-circle text-danger"></i> {{ $noProgressCount }} بلا تقدم محفوظ — يحتاج إعادة توليد من دفعة جديدة</span>
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="doc-ai-batch-badge doc-ai-batch-badge--{{ $badgeKey }}">{{ $label }}</span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if($batch->resumable_count && ! $batch->open_count)
                                                <button type="button" class="btn btn-sm btn-warning doc-ai-hist-resume"
                                                        data-url="{{ route('admin.docs.ai-pages.batch.resume-all', $batch->uuid) }}"
                                                        data-show-url="{{ route('admin.docs.ai-pages.batch.show', $batch->uuid) }}"
                                                        data-count="{{ $batch->resumable_count }}">
                                                    <span class="spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                                                    <i class="fe fe-play-circle me-1"></i>متابعة ({{ $batch->resumable_count }})
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.docs.ai-pages.batch.show', $batch->uuid) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fe fe-eye me-1"></i>عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $batches->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
(function () {
    const csrfToken = @json(csrf_token());

    function toast(opts) {
        if (typeof Swal === 'undefined') {
            if (opts.icon === 'error') alert(opts.title + (opts.text ? '\n' + opts.text : ''));
            return;
        }
        Swal.fire(Object.assign({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
        }, opts));
    }

    function setBusy(btn, busy) {
        btn.disabled = busy;
        btn.querySelector('.spinner-border').style.display = busy ? '' : 'none';
    }

    // The server re-checks every topic before queueing it (resumeAll →
    // queueResume), so a stale count here can never resume a finished topic.
    document.querySelectorAll('.doc-ai-hist-resume').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setBusy(btn, true);
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                credentials: 'same-origin',
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    if (res.body.success) {
                        // The detail page polls the batch, so progress is visible there.
                        window.location.href = btn.dataset.showUrl;
                        return;
                    }
                    setBusy(btn, false);
                    toast({ icon: 'warning', title: 'تعذرت المتابعة', text: res.body.message || '' });
                })
                .catch(function () {
                    setBusy(btn, false);
                    toast({ icon: 'error', title: 'تعذرت المتابعة', text: 'تحقق من الاتصال ثم أعد المحاولة.' });
                });
        });
    });
})();
</script>
@endsection
