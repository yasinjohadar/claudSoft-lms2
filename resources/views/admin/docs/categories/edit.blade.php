@extends('admin.layouts.master')

@section('page-title', 'تعديل قسم توثيق')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h4 class="mb-0">تعديل: {{ $category->name }}</h4>
            <a href="{{ route('admin.docs.categories.index') }}" class="btn btn-secondary">رجوع</a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.docs.categories.update', $category) }}" method="POST" class="card custom-card">
            @csrf
            @method('PUT')
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الرابط (slug)</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                    <select name="kind" class="form-select" required>
                        <option value="section" {{ old('kind', $category->kind) === 'section' ? 'selected' : '' }}>قسم موضوعي</option>
                        <option value="technology" {{ old('kind', $category->kind) === 'technology' ? 'selected' : '' }}>لغة / تقنية</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">القسم الأب</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— رئيسي —</option>
                        @foreach($parentCategories as $p)
                        <option value="{{ $p->id }}" {{ (string)old('parent_id', $category->parent_id) === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">أيقونة</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $category->icon) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">مفعّل</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
