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
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
                <a href="{{ route('admin.lesson-simulators.ai.create') }}" class="btn btn-outline-danger btn-sm">توليد بالذكاء الاصطناعي</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-light border mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <strong>توليد بالذكاء الاصطناعي؟</strong>
                <span class="text-muted small d-block">أنشئ المحاكاة تلقائياً ثم عدّلها هنا أو احفظها مباشرة.</span>
            </div>
            <a href="{{ route('admin.lesson-simulators.ai.create') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-bolt me-1"></i> صفحة التوليد بالـ AI
            </a>
        </div>

        @include('admin.lesson-simulators.partials.bundle-form', [
            'action' => route('admin.lesson-simulators.store'),
            'method' => 'POST',
            'simulator' => null,
            'bundle' => $bundle,
            'courses' => $courses,
            'statuses' => $statuses,
            'categoryOptions' => $categoryOptions,
        ])
    </div>
</div>
@endsection
