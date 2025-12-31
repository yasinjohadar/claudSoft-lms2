@extends('admin.layouts.master')

@section('page-title')
    مقدمي خدمات الذكاء الاصطناعي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مقدمي خدمات الذكاء الاصطناعي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الذكاء الاصطناعي</li>
                            <li class="breadcrumb-item active">مقدمي الخدمة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.ai.providers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة مقدم خدمة جديد
                    </a>
                </div>
            </div>

            <!-- Providers Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">جميع مقدمي الخدمة</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>الاسم</th>
                                            <th>النوع</th>
                                            <th>النموذج</th>
                                            <th>الحالة</th>
                                            <th>افتراضي</th>
                                            <th>الأولوية</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($providers as $provider)
                                            <tr>
                                                <td>{{ $provider->name }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ $provider->type }}</span>
                                                </td>
                                                <td>{{ $provider->model_name }}</td>
                                                <td>
                                                    @if($provider->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($provider->is_default)
                                                        <span class="badge bg-primary">افتراضي</span>
                                                    @else
                                                        <a href="{{ route('admin.ai.providers.set-default', $provider) }}" class="btn btn-sm btn-outline-primary">
                                                            تعيين كافتراضي
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>{{ $provider->priority }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <a href="{{ route('admin.ai.providers.show', $provider) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.ai.providers.edit', $provider) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-warning test-connection" data-id="{{ $provider->id }}">
                                                            <i class="fas fa-plug"></i> اختبار
                                                        </button>
                                                        <form action="{{ route('admin.ai.providers.destroy', $provider) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
                                                <td colspan="7" class="text-center">لا توجد مقدمي خدمة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $providers->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.test-connection').forEach(btn => {
            btn.addEventListener('click', function() {
                const providerId = this.dataset.id;
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';

                fetch(`/admin/ai/providers/${providerId}/test-connection`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ الاتصال ناجح: ' + data.message);
                    } else {
                        alert('✗ فشل الاتصال: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('حدث خطأ: ' + error.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug"></i> اختبار';
                });
            });
        });
    </script>
    @endpush
@stop

