@extends('admin.layouts.master')

@section('page-title')
    إضافة مستوى جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb"></div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">إضافة مستوى جديد</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.levels.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.levels.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">رقم المستوى <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="level" value="{{ old('level', $nextLevel ?? 1) }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم المستوى <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">XP المطلوب <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="xp_required" value="{{ old('xp_required', $suggestedXP ?? 100) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" name="points_reward" value="{{ old('points_reward', 0) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة الجواهر</label>
                                        <input type="number" class="form-control" name="gems_reward" value="{{ old('gems_reward', 0) }}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
