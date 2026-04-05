@extends('admin.layouts.master')

@section('page-title')
    موديلات Laravel AI SDK
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">موديلات Laravel AI SDK</h5>
                <p class="text-muted mb-0 small">مسار موازٍ لـ SDK الرسمي؛ لا يستبدل موديلات AI الحالية.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.ai-sdk.models.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة موديل
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2">
                <form method="get" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 small">الحالة</label>
                        <select name="active" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" {{ $activeFilter === null || $activeFilter === '' ? 'selected' : '' }}>الكل</option>
                            <option value="1" {{ $activeFilter === '1' ? 'selected' : '' }}>نشط فقط</option>
                            <option value="0" {{ $activeFilter === '0' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="lai_index_test_feedback" class="mb-3" aria-live="polite"></div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>المزود</th>
                                        <th>الموديل</th>
                                        <th>الأولوية</th>
                                        <th>القدرات</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($models as $m)
                                        <tr>
                                            <td>{{ $m->id }}</td>
                                            <td>{{ $m->name }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $providers[$m->provider] ?? $m->provider }}</span>
                                            </td>
                                            <td><code class="small">{{ $m->model }}</code></td>
                                            <td>{{ $m->priority }}</td>
                                            <td class="text-start">
                                                @if($m->capabilities)
                                                    @foreach($m->capabilities as $cap)
                                                        <span class="badge bg-secondary me-1">{{ $capabilities[$cap] ?? $cap }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($m->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                    <a href="{{ route('admin.ai-sdk.models.edit', $m) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.ai-sdk.models.test', $m) }}" method="POST" class="d-inline" id="test-form-{{ $m->id }}">
                                                        @csrf
                                                        <button type="button" class="btn btn-sm btn-warning test-laravel-ai-model" data-model-id="{{ $m->id }}">
                                                            <i class="fas fa-vial"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.ai-sdk.models.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا الموديل؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">لا توجد موديلات.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $models->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feedback = document.getElementById('lai_index_test_feedback');
    function showFeedback(html) {
        if (feedback) feedback.innerHTML = html;
    }
    document.querySelectorAll('.test-laravel-ai-model').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.modelId;
            const form = document.getElementById('test-form-' + id);
            const b = this;
            b.disabled = true;
            b.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            showFeedback('');
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data: data })))
            .then(({ ok, data }) => {
                const ms = data.response_time_ms != null ? data.response_time_ms : data.latency_ms;
                const msText = ms != null ? ' (' + ms + ' ms)' : '';
                if (ok && data.success) {
                    showFeedback('<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>نجح الاختبار</strong> للموديل #' + id + '<br>' + (data.message || '') + msText + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                } else {
                    showFeedback('<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>فشل الاختبار</strong> للموديل #' + id + '<br>' + (data.message || 'خطأ غير معروف') + msText + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                }
            })
            .catch(e => {
                showFeedback('<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>خطأ شبكة:</strong> ' + (e.message || 'تعذر الاتصال') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            })
            .finally(() => { b.disabled = false; b.innerHTML = '<i class="fas fa-vial"></i>'; });
        });
    });
});
</script>
@endpush
