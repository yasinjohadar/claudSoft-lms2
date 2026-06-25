@extends('student.layouts.master')

@section('page-title')
    معرض المشاريع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="mb-1">معرض المشاريع</h4>
                    <p class="text-muted mb-0">استكشف مشاريع الطلاب المنشورة</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.project-challenges.index') }}" class="btn btn-outline-primary">
                        <i class="fe fe-layers me-1"></i>تحديات المشاريع
                    </a>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-8">
                            <input type="text" name="q" class="form-control" placeholder="ابحث في المشاريع..." value="{{ request('q') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-search me-1"></i>بحث
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pc-showcase-grid">
                @forelse($showcases as $showcase)
                    <a href="{{ route('student.community-projects.show', $showcase->slug) }}" class="pc-showcase-card">
                        <div class="pc-showcase-card__image" @if($showcase->cover_image) style="background-image:url('{{ $showcase->cover_image }}')" @endif></div>
                        <div class="pc-showcase-card__body">
                            <h5 class="pc-showcase-card__title">{{ $showcase->title }}</h5>
                            <p class="text-muted small mb-2">{{ Str::limit($showcase->summary, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ $showcase->team->name ?? '' }}</small>
                                @if($showcase->challenge)
                                    <span class="badge bg-primary-transparent">{{ Str::limit($showcase->challenge->title, 20) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-12 text-center text-muted py-5">لا توجد مشاريع منشورة بعد</div>
                @endforelse
            </div>

            <div class="mt-4">{{ $showcases->links() }}</div>
        </div>
    </div>
@stop
