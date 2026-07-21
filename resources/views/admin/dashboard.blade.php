@extends('admin.layouts.master')

@section('page-title')
لوحة التحكم الرئيسية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.dashboard.partials.header-stats')

            @include('admin.dashboard.partials.kpi-cards')

            @include('admin.dashboard.partials.quick-access')
        </div>
    </div>
@stop

@push('dashboard-scripts')
    <script src="{{ asset('assets/js/admin-dashboard.js') }}?v={{ @filemtime(public_path('assets/js/admin-dashboard.js')) ?: '1' }}" defer></script>
@endpush
