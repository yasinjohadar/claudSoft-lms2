@extends('student.layouts.master')

@include('shared.platform-review.assets')

@section('page-title')
    إضافة تقييم للمنصة
@stop

@section('content')
    <div class="main-content app-content platform-review-page">
        <div class="container-fluid">

            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة تقييم للمنصة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.platform-review.index') }}">تقييمي للمنصة</a></li>
                            <li class="breadcrumb-item active">إضافة تقييم</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9">
                    <div class="platform-review-form-card">
                        <div class="platform-review-form-card__hero">
                            <h2 class="platform-review-form-card__title">
                                <i class="fas fa-star text-warning"></i>
                                شاركنا تجربتك
                            </h2>
                            <p class="platform-review-form-card__lead">
                                رأيك يهمنا — ساعدنا على تحسين المنصة والكورسات من خلال تقييم صادق ومفصّل.
                            </p>
                        </div>
                        <div class="platform-review-form-card__body">
                            @include('shared.platform-review.form-fields', [
                                'formAction' => route('student.platform-review.store'),
                                'isEdit' => false,
                            ])
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
