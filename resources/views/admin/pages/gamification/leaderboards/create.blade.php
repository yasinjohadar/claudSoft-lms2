@extends('admin.layouts.master')

@section('page-title')
    إضافة لوحة متصدرين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.leaderboards.index') }}">لوحات المتصدرين</a></li>
                <li class="breadcrumb-item active">إضافة</li>
            </ol></nav>
        </div>

        <div class="card custom-card group-show-members-card">
            <div class="card-header border-0"><h5 class="mb-0">إنشاء لوحة جديدة</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.gamification.leaderboards.store') }}">
                    @csrf
                    @include('admin.pages.gamification.leaderboards.partials.form')
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i>حفظ</button>
                        <a href="{{ route('admin.gamification.leaderboards.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
