@extends('admin.layouts.master')

@section('page-title')
    منح نقاط
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
                            <h5 class="mb-0 fw-bold">منح نقاط للطالب</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.points.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.points.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                        <select class="form-select" name="user_id" required>
                                            <option value="">اختر الطالب</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">النقاط <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="points" value="{{ old('points') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">النوع</label>
                                        <select class="form-select" name="type">
                                            <option value="bonus">مكافأة</option>
                                            <option value="penalty">عقوبة</option>
                                            <option value="adjustment">تعديل</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">السبب <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-coins me-1"></i> منح النقاط</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
