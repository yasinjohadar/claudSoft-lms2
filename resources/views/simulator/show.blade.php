@extends('simulator.layout')

@section('title', ($simulator->title ?? 'محاكاة').' — ClaudSoft')

@section('content')
@if(!empty($isPreview))
    <div class="simulator-preview-banner">وضع المعاينة — Admin</div>
@endif

@php
    $genStatus = $generationMeta['status'] ?? null;
    $genError = $generationMeta['error'] ?? null;
    $showEmpty = empty($hasSections);
@endphp

@if($showEmpty)
    <div class="simulator-empty-state">
        <div class="simulator-empty-box">
            @if($genStatus === 'failed')
                <i class="fas fa-exclamation-triangle text-danger fa-2x mb-3"></i>
                <h2>فشل توليد المحاكاة</h2>
                <p class="text-muted">{{ $genError ?? 'حدث خطأ أثناء التوليد بالذكاء الاصطناعي.' }}</p>
                @if(str_contains($genError ?? '', '403'))
                    <p class="small">تحقق من: مفتاح API في موديل Laravel AI، إعدادات الشبكة/البروxy، أو قيود المزود.</p>
                @endif
            @elseif(in_array($genStatus, ['pending', 'processing'], true))
                <div class="spinner-border text-primary mb-3"></div>
                <h2>جاري التوليد...</h2>
                <p class="text-muted">انتظر قليلاً ثم حدّث الصفحة.</p>
            @else
                <i class="fas fa-file-circle-xmark text-muted fa-2x mb-3"></i>
                <h2>لا يوجد محتوى للعرض</h2>
                <p class="text-muted">المحاكaة لا تحتوي أقساماً بعد. أعد التوليد من لوحة الأدمن.</p>
            @endif
            @if(!empty($isPreview))
                <a href="{{ route('admin.lesson-simulators.edit', $simulator) }}" class="btn btn-primary mt-3">تعديل المحاكاة</a>
            @endif
        </div>
    </div>
@else
<div class="simulator-app" id="simulator-app">
    <header class="simulator-header">
        <div class="simulator-header-inner">
            <div>
                <p class="simulator-breadcrumb">محاكاة تفاعلية</p>
                <h1 class="simulator-title">{{ $spec['meta']['title'] ?? $simulator->title }}</h1>
            </div>
            @if(!empty($spec['meta']['languages']))
                <div class="simulator-langs">
                    @foreach($spec['meta']['languages'] as $lang)
                        <span class="simulator-lang-badge">{{ strtoupper($lang) }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="simulator-progress-wrap">
            <div class="simulator-progress-bar" id="simulator-progress-bar" style="width:0%"></div>
        </div>
    </header>

    <div class="simulator-body">
        <aside class="simulator-sidebar" id="simulator-sidebar">
            <nav class="simulator-nav" id="simulator-nav"></nav>
        </aside>
        <main class="simulator-main">
            <div id="simulator-sections"></div>
        </main>
    </div>
</div>

<script>
    window.__SIMULATOR_SPEC__ = @json($spec);
</script>
@foreach($widgetScripts ?? [] as $script)
    <script src="{{ asset($script) }}"></script>
@endforeach
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.SimulatorEngine && window.__SIMULATOR_SPEC__) {
            window.SimulatorEngine.init(window.__SIMULATOR_SPEC__);
        }
    });
</script>
@endif
@endsection

@push('head')
@if($showEmpty ?? false)
<style>
.simulator-empty-state {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    font-family: 'Cairo', sans-serif;
}
.simulator-empty-box {
    max-width: 520px;
    text-align: center;
    background: #fff;
    border: 1px solid rgba(0,102,179,.15);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 24px rgba(0,102,179,.08);
}
.simulator-empty-box h2 {
    font-family: 'Alexandria', sans-serif;
    color: #0066B3;
    font-size: 1.25rem;
}
</style>
@endif
@endpush
