@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = ($template->exists ? 'تعديل' : 'إنشاء').' قالب Telegram';
    $tgTitle = $template->exists ? 'تعديل القالب' : 'قالب جديد';
    $tgSubtitle = 'استخدم {student_name} {course_name} {group_name} {group_link}';
    $breadcrumb = $template->exists ? 'تعديل' : 'إنشاء';
@endphp

@section('tg-content')
<div class="col-lg-8 mx-auto">
    <div class="tg-form-section">
        <form method="POST" action="{{ $template->exists ? route('admin.telegram.templates.update', $template) : route('admin.telegram.templates.store') }}">
            @csrf
            @if($template->exists) @method('PUT') @endif
            <div class="tg-form-section__title">
                <span class="tg-form-section__icon"><i class="ri-file-edit-line"></i></span>
                {{ $template->exists ? 'تعديل القالب' : 'قالب جديد' }}
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">الاسم</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">النص</label>
                <textarea name="body" class="form-control" rows="10" required>{{ old('body', $template->body) }}</textarea>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $template->is_active ?? true))>
                <label for="is_active" class="form-check-label">نشط</label>
            </div>
            <button type="submit" class="btn text-white px-4" style="background: linear-gradient(135deg, #229ED9, #0088cc);">حفظ</button>
            <a href="{{ route('admin.telegram.templates.index') }}" class="btn btn-light ms-2">إلغاء</a>
        </form>
    </div>
</div>
@endsection
