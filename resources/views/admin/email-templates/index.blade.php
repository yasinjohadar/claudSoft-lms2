@extends('admin.layouts.master')

@section('page-title')
    قوالب البريد الإلكتروني
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-envelope me-2"></i>
                    قوالب البريد الإلكتروني
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    إضافة قالب جديد
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.email-templates.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">البحث</label>
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" 
                               placeholder="ابحث بالاسم أو الموضوع...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="type" class="form-select">
                            <option value="">جميع الأنواع</option>
                            <option value="registration_welcome" {{ request('type') == 'registration_welcome' ? 'selected' : '' }}>
                                ترحيب بالتسجيل
                            </option>
                            <option value="enrollment_confirmation" {{ request('type') == 'enrollment_confirmation' ? 'selected' : '' }}>
                                تأكيد التسجيل
                            </option>
                            <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>
                                مخصص
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="is_active" class="form-select">
                            <option value="">جميع الحالات</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>معطل</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            بحث
                        </button>
                        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i>
                            إعادة تعيين
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Templates Table -->
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الاسم بالعربية</th>
                                <th>الموضوع</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                                    <td>
                                        <strong>{{ $template->name }}</strong>
                                    </td>
                                    <td>{{ $template->name_ar ?? '-' }}</td>
                                    <td>
                                        <span class="text-muted">{{ Str::limit($template->subject, 50) }}</span>
                                    </td>
                                    <td>
                                        @if($template->type === 'registration_welcome')
                                            <span class="badge bg-info">ترحيب بالتسجيل</span>
                                        @elseif($template->type === 'enrollment_confirmation')
                                            <span class="badge bg-success">تأكيد التسجيل</span>
                                        @else
                                            <span class="badge bg-secondary">مخصص</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">معطل</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.email-templates.show', $template) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.email-templates.preview', $template) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="معاينة"
                                               target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.email-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.email-templates.duplicate', $template) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('هل أنت متأكد من نسخ هذا القالب؟');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary" title="نسخ">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.email-templates.destroy', $template) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-inbox display-4 text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">لا توجد قوالب</h5>
                                        <p class="text-muted">ابدأ بإنشاء قالب جديد</p>
                                        <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary mt-3">
                                            <i class="fas fa-plus me-2"></i>
                                            إضافة قالب جديد
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($templates->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
