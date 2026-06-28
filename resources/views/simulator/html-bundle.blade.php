@extends('simulator.bundle-shell')

@section('title', ($simulator->title ?? 'محاكاة').' — ClaudSoft')

@section('content')
@if(empty($hasContent))
    <div class="simulator-empty-state">
        <div class="simulator-empty-box">
            <h2>لا يوجد محتوى للعرض</h2>
            <p>لم يُرفَع HTML بعد — عدّل المحاكاة والصق الكود.</p>
            @if(!empty($isPreview))
                <a href="{{ route('admin.lesson-simulators.edit', $simulator) }}" class="btn">تعديل المحاكاة</a>
            @endif
        </div>
    </div>
@else
<div class="simulator-html-bundle-wrap">
    <iframe
        id="simulator-bundle-frame"
        src="{{ $playUrl }}"
        title="{{ $simulator->title }}"
        class="simulator-bundle-iframe"
        allow="clipboard-write"
    ></iframe>
</div>
@endif
@endsection
