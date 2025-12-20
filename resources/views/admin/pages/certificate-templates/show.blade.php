@extends('admin.layouts.master')

@section('page-title')
    تفاصيل القالب
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $template->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.certificate-templates.index') }}">القوالب</a></li>
                            <li class="breadcrumb-item active">التفاصيل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.certificate-templates.edit', $template->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Template Info -->
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات القالب</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold" width="200">الاسم:</td>
                                        <td>{{ $template->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">النوع:</td>
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
                                    </tr>
                                    @if($template->description)
                                        <tr>
                                            <td class="fw-semibold">الوصف:</td>
                                            <td>{{ $template->description }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="fw-semibold">الحالة:</td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">نشط</span>
                                            @else
                                                <span class="badge bg-secondary">غير نشط</span>
                                            @endif
                                            @if($template->is_default)
                                                <span class="badge bg-primary ms-2">
                                                    <i class="fas fa-star me-1"></i>افتراضي
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">الاتجاه:</td>
                                        <td>{{ $template->orientation == 'landscape' ? 'أفقي (Landscape)' : 'عمودي (Portrait)' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">حجم الصفحة:</td>
                                        <td>{{ $template->page_size }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">الترتيب:</td>
                                        <td>{{ $template->sort_order }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">تاريخ الإنشاء:</td>
                                        <td>{{ $template->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">المتطلبات</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">{{ $template->getRequirementsSummary() }}</p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle fs-20 {{ $template->requires_completion ? 'text-success' : 'text-muted' }} me-2"></i>
                                            <h6 class="mb-0">إكمال الكورس</h6>
                                        </div>
                                        @if($template->requires_completion)
                                            <p class="mb-0 text-success">مطلوب - {{ $template->min_completion_percentage }}%</p>
                                        @else
                                            <p class="mb-0 text-muted">غير مطلوب</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-user-check fs-20 {{ $template->requires_attendance ? 'text-success' : 'text-muted' }} me-2"></i>
                                            <h6 class="mb-0">الحضور</h6>
                                        </div>
                                        @if($template->requires_attendance)
                                            <p class="mb-0 text-success">مطلوب - {{ $template->min_attendance_percentage }}%</p>
                                        @else
                                            <p class="mb-0 text-muted">غير مطلوب</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-graduation-cap fs-20 {{ $template->requires_final_exam ? 'text-success' : 'text-muted' }} me-2"></i>
                                            <h6 class="mb-0">الاختبار النهائي</h6>
                                        </div>
                                        @if($template->requires_final_exam)
                                            <p class="mb-0 text-success">مطلوب - {{ $template->min_final_exam_score }} درجة</p>
                                        @else
                                            <p class="mb-0 text-muted">غير مطلوب</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar-times fs-20 {{ $template->has_expiry ? 'text-warning' : 'text-muted' }} me-2"></i>
                                            <h6 class="mb-0">تاريخ الانتهاء</h6>
                                        </div>
                                        @if($template->has_expiry)
                                            <p class="mb-0 text-warning">{{ $template->expiry_months }} شهر من تاريخ الإصدار</p>
                                        @else
                                            <p class="mb-0 text-muted">غير محدد</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HTML Content -->
                    @if($template->html_content)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">محتوى HTML</div>
                            </div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ $template->html_content }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4">
                    <!-- Quick Stats -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إحصائيات</div>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-certificate fs-60 text-primary mb-3"></i>
                                <h3 class="mb-1">{{ $template->certificates_count ?? 0 }}</h3>
                                <p class="text-muted">شهادة صادرة</p>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الإعدادات</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span><i class="fas fa-magic me-2"></i>إصدار تلقائي</span>
                                    @if($template->auto_issue)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span><i class="fas fa-star me-2"></i>قالب افتراضي</span>
                                    @if($template->is_default)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span><i class="fas fa-toggle-on me-2"></i>نشط</span>
                                    @if($template->is_active)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إجراءات</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.certificate-templates.preview', $template->id) }}"
                                   class="btn btn-info" target="_blank">
                                    <i class="fas fa-eye me-2"></i>معاينة القالب
                                </a>

                                <a href="{{ route('admin.certificate-templates.edit', $template->id) }}"
                                   class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>تعديل القالب
                                </a>

                                @if(!$template->is_default)
                                    <form action="{{ route('admin.certificate-templates.set-default', $template->id) }}"
                                          method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-star me-2"></i>تعيين كافتراضي
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.certificate-templates.destroy', $template->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف القالب
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
