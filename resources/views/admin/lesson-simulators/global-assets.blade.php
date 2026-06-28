@extends('admin.layouts.master')

@section('page-title')
    الملفات المركزية CSS / JS
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-0">الملفات المركزية — CSS & JavaScript</h5>
                <p class="text-muted small mb-0">تُحمَّل تلقائياً مع كل محاكاة HTML — عدّلها مرة واحدة هنا</p>
            </div>
            <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="alert alert-info">
            <strong>كيف يعمل؟</strong>
            <ul class="mb-0 mt-2">
                <li>كل محاكاة تحفظ <strong>HTML فقط</strong> — CSS/JS يأتيان من هنا تلقائياً</li>
                <li>مسارات الملفات: <a href="{{ $cssUrl }}" target="_blank"><code>{{ $cssUrl }}</code></a> و <a href="{{ $jsUrl }}" target="_blank"><code>{{ $jsUrl }}</code></a></li>
                <li>في HTML يمكنك أيضاً كتابة: <code>__GLOBAL_ASSETS__/page.css</code> و <code>__GLOBAL_ASSETS__/simulator.js</code></li>
            </ul>
        </div>

        <form action="{{ route('admin.lesson-simulators.global-assets.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header"><strong>page.css</strong> — مركزي</div>
                        <div class="card-body">
                            <textarea name="global_css" class="form-control font-monospace" rows="28" dir="ltr" style="text-align:left;font-size:12px;">{{ old('global_css', $globalCss) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header"><strong>simulator.js</strong> — مركزي</div>
                        <div class="card-body">
                            <textarea name="global_js" class="form-control font-monospace" rows="28" dir="ltr" style="text-align:left;font-size:12px;">{{ old('global_js', $globalJs) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">حفظ الملفات المركزية</button>
        </form>
    </div>
</div>
@endsection
