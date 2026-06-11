@extends('admin.layouts.master')

@section('page-title')
    تعديل {{ $achievement->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.achievements.index') }}">الإنجازات</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gamification.achievements.show', $achievement) }}">{{ $achievement->name }}</a></li>
                <li class="breadcrumb-item active">تعديل</li>
            </ol></nav>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0">
                <h5 class="mb-1">تعديل الإنجاز</h5>
                <p class="fs-12 text-muted mb-0">تغيير المعايير يؤثر على الإنجازات الجديدة فقط؛ استخدم «إعادة تحقق الإنجازات» لتحديث الطلاب الحاليين.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.gamification.achievements.update', $achievement) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.pages.gamification.achievements.partials._form')
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i>تحديث</button>
                        <a href="{{ route('admin.gamification.achievements.show', $achievement) }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
