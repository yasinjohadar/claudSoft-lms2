@extends('student.layouts.master')

@section('page-title')
    بطاقتي التعريفية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('student.components.alerts')

        <div class="card custom-card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="avatar avatar-xxl bg-warning-transparent mx-auto mb-3">
                    <i class="fe fe-lock text-warning fs-24"></i>
                </div>
                <h4 class="mb-2">البطاقة التعريفية غير متاحة لحسابك</h4>
                <p class="text-muted mb-3">
                    حسابك حالياً <strong>{{ $accountTier === 'gold' ? 'ذهبي' : 'فضي' }}</strong>.
                    هذه الميزة غير مفعّلة لفئتك من قبل الإدارة، أو تحتاج للترقية.
                </p>
                @if($accountTier === 'silver')
                    <a href="{{ route('student.training-camps.index') }}" class="btn btn-primary">
                        <i class="fe fe-award me-1"></i>استكشف المعسكرات للترقية
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
