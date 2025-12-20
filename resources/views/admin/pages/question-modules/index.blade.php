@extends('admin.layouts.master')

@section('page-title')
    وحدات الأسئلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">وحدات الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">وحدات الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-modules.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة وحدة أسئلة جديدة
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي الوحدات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionModules->total() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-clipboard-list fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الوحدات المنشورة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionModules->where('is_published', true)->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الوحدات المرئية</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionModules->where('is_visible', true)->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-eye fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionModules->sum(function($module) { return $module->questions->count(); }) }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-question-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Modules Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">قائمة وحدات الأسئلة</div>
                        </div>
                        <div class="card-body">
                            @if($questionModules->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>العنوان</th>
                                                <th>الوصف</th>
                                                <th>عدد الأسئلة</th>
                                                <th>المنشئ</th>
                                                <th>الحالة</th>
                                                <th>تاريخ الإنشاء</th>
                                                <th width="200">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($questionModules as $module)
                                                <tr>
                                                    <td>{{ $loop->iteration + ($questionModules->currentPage() - 1) * $questionModules->perPage() }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h6 class="mb-0 fw-semibold">{{ $module->title }}</h6>
                                                                @if($module->description)
                                                                    <small class="text-muted">{{ Str::limit($module->description, 50) }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ Str::limit($module->description ?? 'لا يوجد وصف', 80) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-transparent">
                                                            <i class="fas fa-question-circle me-1"></i>
                                                            {{ $module->questions->count() }} سؤال
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($module->creator)
                                                            <div class="d-flex align-items-center">
                                                                <span class="avatar avatar-sm bg-primary-transparent text-primary me-2">
                                                                    {{ mb_substr($module->creator->name, 0, 1) }}
                                                                </span>
                                                                <span>{{ $module->creator->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">غير محدد</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @if($module->is_published)
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check-circle me-1"></i>منشور
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">
                                                                    <i class="fas fa-times-circle me-1"></i>غير منشور
                                                                </span>
                                                            @endif
                                                            @if($module->is_visible)
                                                                <span class="badge bg-info">
                                                                    <i class="fas fa-eye me-1"></i>مرئي
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-eye-slash me-1"></i>مخفي
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $module->created_at->format('Y-m-d') }}
                                                            <br>
                                                            <span class="text-muted">{{ $module->created_at->diffForHumans() }}</span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <a href="{{ route('question-modules.show', $module->id) }}" 
                                                               class="btn btn-sm btn-info" 
                                                               title="عرض">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('question-modules.manage-questions', $module->id) }}" 
                                                               class="btn btn-sm btn-primary" 
                                                               title="إدارة الأسئلة">
                                                                <i class="fas fa-cog"></i>
                                                            </a>
                                                            <a href="{{ route('question-modules.edit', $module->id) }}" 
                                                               class="btn btn-sm btn-warning" 
                                                               title="تعديل">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('question-modules.destroy', $module->id) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه الوحدة؟')">
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
                                <div class="mt-3">
                                    {{ $questionModules->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد وحدات أسئلة بعد</p>
                                    <a href="{{ route('question-modules.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>إضافة وحدة أسئلة جديدة
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

