@extends('admin.layouts.master')

@section('page-title')
    تعديل المستوى
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb"></div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تعديل المستوى {{ $level->level }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.levels.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.levels.update', $level->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">رقم المستوى</label>
                                        <input type="number" class="form-control" value="{{ $level->level }}" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم المستوى <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $level->name) }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">XP المطلوب <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="xp_required" value="{{ old('xp_required', $level->xp_required) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" name="points_reward" value="{{ old('points_reward', $level->points_reward) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة الجواهر</label>
                                        <input type="number" class="form-control" name="gems_reward" value="{{ old('gems_reward', $level->gems_reward) }}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> تحديث</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
