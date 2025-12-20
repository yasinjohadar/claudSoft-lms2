@extends('student.layouts.master')

@section('page-title')
   أعمالي
@stop

@section('css')
<style>
    .work-image {
        width: 60px !important;
        height: 60px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
        max-width: 60px !important;
        max-height: 60px !important;
        min-width: 60px !important;
        min-height: 60px !important;
        display: block !important;
    }
    .badge-featured {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .work-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .work-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">أعمالي</h4>
                    <p class="mb-0 text-muted">عرض جميع أعمالي المنشورة</p>
                </div>
            </div>
            <!-- Page Header Close -->

            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    {!! \Session::get('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {!! \Session::get('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-briefcase me-2"></i>
                                قائمة أعمالي
                            </h5>
                        </div>

                        <div class="card-body">
                            <!-- Search and Filter Form -->
                            <form method="GET" action="{{ route('student.my-works.index') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="text"
                                               name="search"
                                               class="form-control"
                                               placeholder="البحث بالعنوان..."
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="status" class="form-select">
                                            <option value="">جميع الحالات</option>
                                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>مفعل</option>
                                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="featured" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>مميز</option>
                                            <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>غير مميز</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-search me-1"></i> بحث
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>الصورة</th>
                                            <th>العنوان</th>
                                            <th>الروابط</th>
                                            <th>الحالة</th>
                                            <th>مميز</th>
                                            <th>الترتيب</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($works as $work)
                                            <tr>
                                                <td>{{ $work->id }}</td>
                                                <td>
                                                    @if($work->image)
                                                        <img src="{{ asset('storage/' . $work->image) }}"
                                                             alt="{{ $work->title }}"
                                                             class="work-image"
                                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                    @else
                                                        <div class="work-image bg-light d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $work->title }}</div>
                                                    @if($work->description)
                                                        <small class="text-muted">{{ Str::limit($work->description, 80) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @if($work->video_url)
                                                            <a href="{{ $work->video_url }}" target="_blank" class="btn btn-sm btn-outline-danger" title="الفيديو">
                                                                <i class="bi bi-play-circle"></i> فيديو
                                                            </a>
                                                        @endif
                                                        @if($work->website_url)
                                                            <a href="{{ $work->website_url }}" target="_blank" class="btn btn-sm btn-outline-primary" title="الموقع">
                                                                <i class="bi bi-globe"></i> موقع
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $work->is_active ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $work->is_active ? 'مفعل' : 'معطل' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $work->is_featured ? 'badge-featured' : 'bg-secondary' }}">
                                                        {{ $work->is_featured ? 'مميز' : 'عادي' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $work->order }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('student.my-works.show', $work->id) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="عرض التفاصيل">
                                                        <i class="bi bi-eye me-1"></i> عرض
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                                    <span class="text-muted fs-5">لا توجد أعمال منشورة لك حتى الآن</span>
                                                    <p class="text-muted mt-2">سيتم عرض أعمالك هنا عندما يقوم الأدمن بإضافتها</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($works->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $works->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@endsection

@section('js')
@endsection
