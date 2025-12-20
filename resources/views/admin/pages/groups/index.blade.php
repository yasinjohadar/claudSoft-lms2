@extends('admin.layouts.master')

@section('page-title')
    المجموعات - {{ $course->title }}
@stop

@section('css')
<style>
    .group-card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .group-card:hover {
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
    }
    .group-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .member-avatar-small {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        margin-left: -10px;
    }
</style>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مجموعات الكورس</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">المجموعات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card custom-card">
                <!-- Statistics -->
                <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="stat-item text-center">
                                <div class="stat-number" style="font-size: 2.5rem; font-weight: 700;">{{ $totalGroups }}</div>
                                <div class="stat-label" style="opacity: 0.9;">إجمالي المجموعات</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item text-center">
                                <div class="stat-number" style="font-size: 2.5rem; font-weight: 700;">{{ $activeGroups }}</div>
                                <div class="stat-label" style="opacity: 0.9;">نشطة</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item text-center">
                                <div class="stat-number" style="font-size: 2.5rem; font-weight: 700;">{{ $totalMembers }}</div>
                                <div class="stat-label" style="opacity: 0.9;">إجمالي الأعضاء</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item text-center">
                                <a href="{{ route('courses.groups.create', $course->id) }}" class="btn btn-light btn-lg w-100">
                                    <i class="fas fa-plus me-2"></i>إنشاء مجموعة جديدة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-header">
                    <h6 class="card-title mb-0">فلترة وبحث</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">البحث</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="ابحث عن مجموعة..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحالة</label>
                            <select name="is_active" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشطة</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشطة</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>بحث
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Groups List -->
                <div class="card-body">
                    @if($groups->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
                            <h4 class="text-muted mb-3">لا توجد مجموعات</h4>
                            <p class="text-muted">لا توجد مجموعات في هذا الكورس بعد</p>
                            <a href="{{ route('courses.groups.create', $course->id) }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>إنشاء مجموعة جديدة
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($groups as $group)
                                <div class="col-md-6 col-lg-4">
                                    <div class="group-card">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="group-icon me-3">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $group->name }}</h6>
                                                @if($group->description)
                                                    <small class="text-muted">{{ Str::limit($group->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $group->is_active ? 'نشطة' : 'غير نشطة' }}
                                            </span>
                                            <span class="badge bg-primary ms-2">
                                                <i class="fas fa-users me-1"></i>{{ $group->members_count ?? 0 }} عضو
                                            </span>
                                        </div>

                                        @if($group->created_by_user)
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>{{ $group->created_by_user->name }}
                                                </small>
                                            </div>
                                        @endif

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-eye me-1"></i>عرض
                                            </a>
                                            <a href="{{ route('courses.groups.edit', [$course->id, $group->id]) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                                <i class="fas fa-edit me-1"></i>تعديل
                                            </a>
                                            <form action="{{ route('courses.groups.destroy', [$course->id, $group->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Pagination -->
                @if($groups->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $groups->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
