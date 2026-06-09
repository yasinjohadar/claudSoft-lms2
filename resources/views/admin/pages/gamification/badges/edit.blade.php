@extends('admin.layouts.master')

@section('page-title')
    تعديل الشارة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تعديل الشارة: {{ $badge->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.badges.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.gamification.badges.update', $badge->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('admin.pages.gamification.badges._form', [
                                    'badge' => $badge,
                                    'requirementType' => $requirementType,
                                    'requirementValue' => $requirementValue,
                                ])

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> تحديث
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
