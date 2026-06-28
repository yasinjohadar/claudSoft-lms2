@extends('admin.layouts.master')

@section('page-title')
    إنشاء محاكاة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-0">إنشاء محاكاة — لصق HTML / CSS / JS</h5>
                <p class="text-muted small mb-0">الصق كود ملفاتك الكاملة واعرضها كصفحة تفاعلية</p>
            </div>
            <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            <a href="{{ route('admin.lesson-simulators.ai.create') }}" class="btn btn-outline-danger btn-sm">توليد بالذكاء الاصطناعي</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
            \App\Support\SimulatorAiWizard::viewData(),
            ['panelId' => 'sim-ai-manual', 'collapsed' => true]
        ))

        @include('admin.lesson-simulators.partials.ai-refine-panel', array_merge(
            \App\Support\SimulatorAiWizard::viewData(),
            ['panelId' => 'sim-ai-refine']
        ))

        @include('admin.lesson-simulators.partials.bundle-form', [
            'action' => route('admin.lesson-simulators.store'),
            'method' => 'POST',
            'simulator' => null,
            'bundle' => $bundle,
            'courses' => $courses,
            'statuses' => $statuses,
        ])
    </div>
</div>
@endsection
