@extends('student.layouts.master')

@section('page-title')
   المعسكرات التدريبية
@stop

@section('css')
<style>
    .camp-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }
    .camp-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .camp-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }
    .camp-placeholder {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    .price-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 15px;
        border-radius: 25px;
        font-weight: bold;
        color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .status-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="bi bi-patch-check me-2"></i>
                        المعسكرات التدريبية المتاحة
                    </h5>
                </div>
            </div>

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

            <!-- Search and Filter Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.training-camps.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="البحث بالاسم أو المدرب..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">جميع التخصصات</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>قادم</option>
                                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>جاري</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Camps Grid -->
            <div class="row">
                @forelse($camps as $camp)
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card camp-card shadow-sm">
                            <div class="position-relative">
                                @if($camp->image)
                                    <img src="{{ asset('storage/' . $camp->image) }}"
                                         alt="{{ $camp->name }}"
                                         class="camp-image">
                                @else
                                    <div class="camp-placeholder">
                                        <i class="bi bi-patch-check"></i>
                                    </div>
                                @endif

                                <div class="price-badge">
                                    ${{ number_format($camp->price, 2) }}
                                </div>

                                @php
                                    $statusColors = [
                                        'upcoming' => 'bg-primary text-white',
                                        'ongoing' => 'bg-success text-white',
                                        'completed' => 'bg-secondary text-white'
                                    ];
                                @endphp
                                <span class="status-badge {{ $statusColors[$camp->status] ?? 'bg-secondary text-white' }}">
                                    {{ $camp->status_label }}
                                </span>
                            </div>

                            <div class="card-body">
                                <div class="mb-2">
                                    @if($camp->category)
                                        <span class="badge bg-info">{{ $camp->category->name }}</span>
                                    @endif
                                    @if($camp->is_featured)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i> مميز
                                        </span>
                                    @endif
                                </div>

                                <h5 class="card-title fw-bold mb-2">{{ $camp->name }}</h5>

                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($camp->description, 100, '...') }}
                                </p>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-person-fill-gear"></i>
                                            {{ $camp->instructor_name ?? 'غير محدد' }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i>
                                            {{ $camp->location ?? 'غير محدد' }}
                                        </small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event"></i>
                                            {{ $camp->start_date->format('Y-m-d') }}
                                        </small>
                                        <small class="text-muted">
                                            <span class="badge bg-secondary">{{ $camp->duration_days }} يوم</span>
                                        </small>
                                    </div>

                                    @if($camp->max_participants)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-people"></i>
                                                {{ $camp->current_participants }} / {{ $camp->max_participants }}
                                            </small>
                                            <small class="text-success fw-semibold">
                                                {{ $camp->available_seats }} مقعد متبقي
                                            </small>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('student.training-camps.show', $camp->slug) }}"
                                   class="btn btn-primary w-100">
                                    <i class="bi bi-eye me-1"></i> عرض التفاصيل
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">لا توجد معسكرات تدريبية متاحة</h5>
                                <p class="text-muted">يرجى المحاولة لاحقاً أو تغيير معايير البحث</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $camps->links() }}
            </div>
        </div>
    </div>
@stop
