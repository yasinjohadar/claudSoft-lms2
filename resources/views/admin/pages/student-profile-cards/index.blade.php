@extends('admin.layouts.master')

@section('page-title')
    البطاقات التعريفية للطلاب
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-1">البطاقات التعريفية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">البطاقات التعريفية</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.student-profile-cards.settings') }}" class="btn btn-outline-primary btn-sm mt-3 mt-md-0">
                <i class="fe fe-settings me-1"></i>إعدادات الفضي/الذهبي
            </a>
        </div>

        <div class="row g-3 mb-4">
            @foreach([
                ['label' => 'إجمالي البطاقات', 'value' => $stats['total'], 'class' => 'primary'],
                ['label' => 'ظاهرة للعامة', 'value' => $stats['public'], 'class' => 'success'],
                ['label' => 'مفعّلة', 'value' => $stats['active'], 'class' => 'info'],
                ['label' => 'موقوفة', 'value' => $stats['inactive'], 'class' => 'danger'],
            ] as $stat)
                <div class="col-md-3 col-sm-6">
                    <div class="card custom-card mb-0">
                        <div class="card-body py-3">
                            <small class="text-muted">{{ $stat['label'] }}</small>
                            <h4 class="mb-0 text-{{ $stat['class'] }}">{{ $stat['value'] }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title mb-0">قائمة البطاقات</div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو slug..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="tier" class="form-select">
                            <option value="">كل الفئات</option>
                            <option value="gold" @selected(request('tier') === 'gold')>ذهبي</option>
                            <option value="silver" @selected(request('tier') === 'silver')>فضي</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="is_public" class="form-select">
                            <option value="">الظهور</option>
                            <option value="1" @selected(request('is_public') === '1')>عامة</option>
                            <option value="0" @selected(request('is_public') === '0')>مخفية</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="admin_enabled" class="form-select">
                            <option value="">الحالة</option>
                            <option value="1" @selected(request('admin_enabled') === '1')>مفعّلة</option>
                            <option value="0" @selected(request('admin_enabled') === '0')>موقوفة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">تصفية</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>Slug</th>
                                <th>الفئة</th>
                                <th>عامة</th>
                                <th>مفعّلة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cards as $card)
                                @php $tier = $tierByUserId[$card->user_id] ?? 'silver'; @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($cards->currentPage() - 1) * $cards->perPage() }}</td>
                                    <td>
                                        <strong>{{ $card->user?->name_ar ?: $card->user?->name }}</strong>
                                        <br><small class="text-muted">{{ $card->user?->email }}</small>
                                    </td>
                                    <td><code dir="ltr">{{ $card->slug }}</code></td>
                                    <td>
                                        <span class="badge bg-{{ $tier === 'gold' ? 'warning' : 'secondary' }}-transparent">
                                            {{ $tier === 'gold' ? 'ذهبي' : 'فضي' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($card->is_public)
                                            <span class="badge bg-success-transparent text-success">نعم</span>
                                        @else
                                            <span class="badge bg-secondary-transparent text-secondary">لا</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $card->admin_enabled ? 'success' : 'danger' }}-transparent text-{{ $card->admin_enabled ? 'success' : 'danger' }}" id="status-badge-{{ $card->id }}">
                                            {{ $card->admin_enabled ? 'مفعّلة' : 'موقوفة' }}
                                        </span>
                                    </td>
                                    <td>{{ $card->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-list">
                                            @if($card->is_public && $card->admin_enabled)
                                                <a href="{{ route('frontend.profile-card.show', $card->slug) }}" target="_blank" class="btn btn-sm btn-info-light" title="عرض">
                                                    <i class="fe fe-external-link"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-{{ $card->admin_enabled ? 'warning' : 'success' }}-light toggle-admin-enabled"
                                                    data-url="{{ route('admin.student-profile-cards.toggle-admin-enabled', $card) }}"
                                                    data-enabled="{{ $card->admin_enabled ? '1' : '0' }}">
                                                <i class="fe fe-{{ $card->admin_enabled ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">لا توجد بطاقات بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $cards->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.querySelectorAll('.toggle-admin-enabled').forEach(function (btn) {
    btn.addEventListener('click', function () {
        fetch(this.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'تعذر التحديث');
                }
            });
    });
});
</script>
@endsection
