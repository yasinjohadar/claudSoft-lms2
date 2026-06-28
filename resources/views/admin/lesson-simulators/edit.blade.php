@extends('admin.layouts.master')

@section('page-title')
    تعديل محاكاة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-0">تعديل: {{ $lessonSimulator->title }}</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="#sim-ai-refine-panel" class="btn btn-success btn-sm">
                    <i class="fe fe-edit-2 me-1"></i>تعديل بالذكاء الاصطناعي
                </a>
                <a href="{{ route('admin.lesson-simulators.preview', $lessonSimulator) }}" class="btn btn-outline-info btn-sm" target="_blank">معاينة كاملة</a>
                <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
            \App\Support\SimulatorAiWizard::viewData(),
            [
                'panelId' => 'sim-ai-edit',
                'collapsed' => true,
                'regenerateUrl' => route('admin.lesson-simulators.ai.regenerate', $lessonSimulator),
                'showRegenerateAsync' => true,
                'defaultTopic' => $lessonSimulator->description ?? $lessonSimulator->title,
            ]
        ))

        @include('admin.lesson-simulators.partials.ai-refine-panel', array_merge(
            \App\Support\SimulatorAiWizard::viewData(),
            ['panelId' => 'sim-ai-refine']
        ))

        @include('admin.lesson-simulators.partials.bundle-form', [
            'action' => route('admin.lesson-simulators.update', $lessonSimulator),
            'method' => 'PUT',
            'simulator' => $lessonSimulator,
            'bundle' => $bundle,
            'courses' => $courses,
            'statuses' => $statuses,
            'categoryOptions' => $categoryOptions,
        ])
    </div>
</div>
@endsection
