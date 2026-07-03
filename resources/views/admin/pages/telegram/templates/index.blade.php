@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'قوالب Telegram';
    $tgTitle = 'قوالب الرسائل';
    $tgSubtitle = 'قوالب جاهزة للبث ودعوات الانضمام.';
    $breadcrumb = 'القوالب';
@endphp

@section('tg-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">{{ $templates->total() }} قالب</span>
    <a href="{{ route('admin.telegram.templates.create') }}" class="btn text-white" style="background: linear-gradient(135deg, #229ED9, #0088cc);">
        <i class="ri-add-line me-1"></i>قالب جديد
    </a>
</div>

<div class="tg-form-section p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>الاسم</th><th>نشط</th><th class="text-end">إجراءات</th></tr></thead>
            <tbody>
            @foreach($templates as $t)
                <tr>
                    <td class="fw-semibold">{{ $t->name }}</td>
                    <td>@if($t->is_active)<span class="badge bg-success-transparent text-success">نعم</span>@else<span class="badge bg-secondary-transparent">لا</span>@endif</td>
                    <td class="text-end">
                        <a href="{{ route('admin.telegram.templates.edit', $t) }}" class="btn btn-sm btn-outline-info">تعديل</a>
                        <form action="{{ route('admin.telegram.templates.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">حذف</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $templates->links() }}</div>
@endsection
