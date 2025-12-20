@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التوكن
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
                    <h5 class="page-title fs-21 mb-1">تفاصيل التوكن</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.webhooks.index') }}">Webhooks</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.webhooks.tokens.index') }}">التوكنات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تفاصيل</li>
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
                            <div class="card-title">معلومات التوكن</div>
                            <div class="btn-list">
                                <a href="{{ route('admin.webhooks.tokens.edit', $token) }}" class="btn btn-warning btn-wave">
                                    <i class="fas fa-edit me-2"></i>تعديل
                                </a>
                                <a href="{{ route('admin.webhooks.tokens.index') }}" class="btn btn-secondary btn-wave">
                                    <i class="fas fa-arrow-right me-2"></i>رجوع
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">الاسم:</th>
                                            <td><strong>{{ $token->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>المصدر:</th>
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
                                        </tr>
                                        <tr>
                                            <th>الحالة:</th>
                                            <td>
                                                @if($token->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>التوكن:</th>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="tokenValue" 
                                                           value="{{ $token->token }}" 
                                                           readonly>
                                                    <button class="btn btn-outline-secondary" 
                                                            type="button" 
                                                            onclick="copyToken()">
                                                        <i class="fas fa-copy"></i> نسخ
                                                    </button>
                                                </div>
                                                <small class="text-muted">انقر على نسخ لنسخ التوكن</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">IPs المسموحة:</th>
                                            <td>
                                                @if($token->allowed_ips && count($token->allowed_ips) > 0)
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($token->allowed_ips as $ip)
                                                            <li><code>{{ $ip }}</code></li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">لا توجد قيود</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>ربط Form IDs:</th>
                                            <td>
                                                @if($token->form_types && count($token->form_types) > 0)
                                                    <pre class="bg-light p-2 rounded"><code>{{ json_encode($token->form_types, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                @else
                                                    <span class="text-muted">لا يوجد ربط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الوصف:</th>
                                            <td>{{ $token->description ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإنشاء:</th>
                                            <td>{{ $token->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>آخر تحديث:</th>
                                            <td>{{ $token->updated_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('script')
<script>
    function copyToken() {
        const tokenInput = document.getElementById('tokenValue');
        tokenInput.select();
        tokenInput.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(tokenInput.value).then(function() {
            alert('تم نسخ التوكن بنجاح!');
        }, function(err) {
            // Fallback for older browsers
            document.execCommand('copy');
            alert('تم نسخ التوكن بنجاح!');
        });
    }
</script>
@stop


