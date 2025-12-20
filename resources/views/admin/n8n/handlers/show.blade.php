@extends('admin.layouts.master')

@section('page-title')
    {{ $handler->handler_type }} - المعالج
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-cogs me-2"></i>{{ $handler->handler_type }}
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.handlers.index') }}">المعالجات</a></li>
                        <li class="breadcrumb-item active">{{ $handler->handler_type }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.n8n.handlers.index') }}" class="btn btn-outline-secondary me-1">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('admin.n8n.handlers.edit', $handler) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>تعديل
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">تفاصيل المعالج</h5>
                        @if($handler->is_active)
                            <span class="badge bg-success">فعال</span>
                        @else
                            <span class="badge bg-secondary">معطل</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">نوع المعالج:</th>
                                <td><code>{{ $handler->handler_type }}</code></td>
                            </tr>
                            <tr>
                                <th>فئة المعالج:</th>
                                <td><code>{{ $handler->handler_class }}</code></td>
                            </tr>
                            <tr>
                                <th>الوصف:</th>
                                <td>{{ $handler->description }}</td>
                            </tr>
                            <tr>
                                <th>الحقول المطلوبة:</th>
                                <td>
                                    @if($handler->required_fields)
                                        <ul class="mb-0">
                                            @foreach($handler->required_fields as $field)
                                            <li><code>{{ $field }}</code></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">لا يوجد</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>الحقول الاختيارية:</th>
                                <td>
                                    @if($handler->optional_fields)
                                        <ul class="mb-0">
                                            @foreach($handler->optional_fields as $field)
                                            <li><code>{{ $field }}</code></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">لا يوجد</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Example Payload -->
                @if($handler->example_payload)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">مثال على الطلب</h5>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0">{{ json_encode($handler->example_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">الإجراءات</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.n8n.handlers.toggle', $handler) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-{{ $handler->is_active ? 'secondary' : 'success' }} w-100">
                                <i class="fas fa-power-off me-1"></i>
                                {{ $handler->is_active ? 'تعطيل' : 'تفعيل' }} المعالج
                            </button>
                        </form>

                        <form action="{{ route('admin.n8n.handlers.destroy', $handler) }}" method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المعالج؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>حذف المعالج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
