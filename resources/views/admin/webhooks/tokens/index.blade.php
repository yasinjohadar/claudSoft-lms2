@extends('admin.layouts.master')

@section('page-title')
    إدارة توكنات Webhooks
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة توكنات Webhooks</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.webhooks.index') }}">Webhooks</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التوكنات</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">قائمة التوكنات</div>
                            <a href="{{ route('admin.webhooks.tokens.create') }}" class="btn btn-primary btn-wave">
                                <i class="fas fa-plus me-2"></i>إضافة توكن جديد
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">الاسم</th>
                                            <th scope="col">المصدر</th>
                                            <th scope="col">الحالة</th>
                                            <th scope="col">IPs المسموحة</th>
                                            <th scope="col">تاريخ الإنشاء</th>
                                            <th scope="col" style="min-width: 200px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tokens as $token)
                                            <tr>
                                                <td>{{ $loop->iteration + ($tokens->currentPage() - 1) * $tokens->perPage() }}</td>
                                                <td>
                                                    <strong>{{ $token->name }}</strong>
                                                    @if($token->description)
                                                        <br><small class="text-muted">{{ Str::limit($token->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $sources = [
                                                            'wpforms' => ['label' => 'WPForms', 'color' => 'primary'],
                                                            'n8n' => ['label' => 'n8n', 'color' => 'info'],
                                                            'other' => ['label' => 'أخرى', 'color' => 'secondary'],
                                                        ];
                                                        $source = $sources[$token->source] ?? ['label' => $token->source, 'color' => 'secondary'];
                                                    @endphp
                                                    <span class="badge bg-{{ $source['color'] }}">{{ $source['label'] }}</span>
                                                </td>
                                                <td>
                                                    @if($token->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($token->allowed_ips && count($token->allowed_ips) > 0)
                                                        <span class="badge bg-info">{{ count($token->allowed_ips) }} IP</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $token->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <a href="{{ route('admin.webhooks.tokens.show', $token) }}" 
                                                           class="btn btn-sm btn-info btn-wave">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.webhooks.tokens.edit', $token) }}" 
                                                           class="btn btn-sm btn-warning btn-wave">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-{{ $token->is_active ? 'secondary' : 'success' }} btn-wave toggle-active"
                                                                data-id="{{ $token->id }}"
                                                                data-active="{{ $token->is_active }}">
                                                            <i class="fas fa-{{ $token->is_active ? 'ban' : 'check' }}"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger btn-wave delete-token"
                                                                data-id="{{ $token->id }}"
                                                                data-name="{{ $token->name }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">لا توجد توكنات حالياً</p>
                                                        <a href="{{ route('admin.webhooks.tokens.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus me-2"></i>إضافة توكن جديد
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($tokens->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $tokens->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteTokenModal" tabindex="-1" aria-labelledby="deleteTokenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTokenModalLabel">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف التوكن <strong id="tokenNameToDelete"></strong>؟</p>
                    <p class="text-danger"><small>هذه العملية لا يمكن التراجع عنها!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form id="deleteTokenForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    // Toggle Active Status
    document.querySelectorAll('.toggle-active').forEach(button => {
        button.addEventListener('click', function() {
            const tokenId = this.dataset.id;
            const isActive = this.dataset.active === '1';

            fetch(`/admin/webhooks/tokens/${tokenId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء تحديث الحالة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
            });
        });
    });

    // Delete Token
    document.querySelectorAll('.delete-token').forEach(button => {
        button.addEventListener('click', function() {
            const tokenId = this.dataset.id;
            const tokenName = this.dataset.name;
            
            document.getElementById('tokenNameToDelete').textContent = tokenName;
            document.getElementById('deleteTokenForm').action = `/admin/webhooks/tokens/${tokenId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteTokenModal'));
            modal.show();
        });
    });
</script>
@stop


