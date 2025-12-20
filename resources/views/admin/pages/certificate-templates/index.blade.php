@extends('admin.layouts.master')

@section('page-title')
    قوالب الشهادات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">قوالب الشهادات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">الشهادات</a></li>
                            <li class="breadcrumb-item active">القوالب</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>قالب جديد
                    </a>
                </div>
            </div>

            <!-- Templates List -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">قائمة القوالب</div>
                            <div class="text-muted">
                                <i class="fas fa-file-alt me-1"></i>{{ $templates->total() }} قالب
                            </div>
                        </div>
                        <div class="card-body">
                            @if($templates->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>الاسم</th>
                                                <th>النوع</th>
                                                <th>المتطلبات</th>
                                                <th>الحالة</th>
                                                <th>افتراضي</th>
                                                <th>الترتيب</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($templates as $template)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm bg-primary-transparent me-2">
                                                                <i class="fas fa-file-alt"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $template->name }}</h6>
                                                                @if($template->description)
                                                                    <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($template->type == 'html')
                                                            <span class="badge bg-info-transparent">
                                                                <i class="fas fa-code me-1"></i>HTML
                                                            </span>
                                                        @else
                                                            <span class="badge bg-purple-transparent">
                                                                <i class="fas fa-image me-1"></i>صورة
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $template->getRequirementsSummary() }}</small>
                                                    </td>
                                                    <td>
                                                        @if($template->is_active)
                                                            <span class="badge bg-success">نشط</span>
                                                        @else
                                                            <span class="badge bg-secondary">غير نشط</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($template->is_default)
                                                            <span class="badge bg-primary">
                                                                <i class="fas fa-star me-1"></i>افتراضي
                                                            </span>
                                                        @else
                                                            <form action="{{ route('admin.certificate-templates.set-default', $template->id) }}"
                                                                  method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                                        title="تعيين كافتراضي">
                                                                    <i class="fas fa-star"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $template->sort_order }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.certificate-templates.preview', $template->id) }}"
                                                               class="btn btn-sm btn-primary" title="معاينة" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.certificate-templates.show', $template->id) }}"
                                                               class="btn btn-sm btn-info" title="تفاصيل">
                                                                <i class="fas fa-file-alt"></i>
                                                            </a>
                                                            <a href="{{ route('admin.certificate-templates.edit', $template->id) }}"
                                                               class="btn btn-sm btn-warning" title="تعديل">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.certificate-templates.destroy', $template->id) }}"
                                                                  method="POST" style="display: inline;"
                                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $templates->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fs-60 text-muted mb-3"></i>
                                    <h5 class="text-muted">لا توجد قوالب</h5>
                                    <p class="text-muted">ابدأ بإنشاء أول قالب للشهادات</p>
                                    <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>إنشاء قالب جديد
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
