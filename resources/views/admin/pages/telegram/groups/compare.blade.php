@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'مقارنة أعضاء Telegram';
    $tgTitle = 'مقارنة أعضاء Telegram';
    $tgSubtitle = 'مقارنة أعضاء المجموعة مع طلاب المنصة (يتطلب MTProto Bridge).';
    $breadcrumb = 'مقارنة';
@endphp

@section('tg-content')
@if(empty($bridgeAvailable))
    <div class="alert alert-warning border-0 shadow-sm"><i class="ri-alert-line me-2"></i>MTProto Bridge غير مُعد. أدخل Bridge URL من الإعدادات.</div>
@endif

<div class="tg-form-section">
    <div class="tg-form-section__title">
        <span class="tg-form-section__icon"><i class="ri-git-merge-line"></i></span>
        تشغيل المقارنة
    </div>
    <form method="POST" action="{{ route('admin.telegram.groups.compare.run') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" dir="ltr" value="{{ $selectedChatId ?? '' }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">course_id</label>
                <input type="number" name="course_id" class="form-control" value="{{ $selectedCourseId ?? '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">group_id</label>
                <input type="number" name="group_id" class="form-control" value="{{ $selectedGroupId ?? '' }}">
            </div>
        </div>
        <button type="submit" class="btn text-white mt-3" style="background: linear-gradient(135deg, #229ED9, #0088cc);">مقارنة</button>
    </form>
</div>

@if(!empty($compareResult))
<div class="row g-3 mt-1">
    @foreach([
        ['title' => 'في Telegram وليس المنصة', 'items' => $compareResult['in_telegram_not_platform'], 'key' => 'username'],
        ['title' => 'في المنصة وليس Telegram', 'items' => $compareResult['in_platform_not_telegram'], 'key' => 'name'],
        ['title' => 'متطابق', 'items' => $compareResult['matched'], 'key' => 'name'],
    ] as $col)
        <div class="col-md-4">
            <div class="tg-form-section h-100 mb-0">
                <h6 class="fw-bold mb-3">{{ $col['title'] }} <span class="badge bg-info">{{ count($col['items']) }}</span></h6>
                <ul class="small mb-0 ps-3">
                    @foreach($col['items'] as $m)
                        <li>{{ $m[$col['key']] ?? $m['id'] ?? '?' }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection
