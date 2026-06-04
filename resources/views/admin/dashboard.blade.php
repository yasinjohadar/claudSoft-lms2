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

            <div class="row mt-2">
                @include('admin.dashboard.partials.chart-enrollments')
                @include('admin.dashboard.partials.today-summary')
            </div>

            @include('admin.dashboard.partials.activity-row')
        </div>
    </div>
@stop

@push('dashboard-scripts')
    <script src="{{ asset('assets/js/admin-dashboard.js') }}" defer></script>
@endpush
