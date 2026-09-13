@extends('admin.layouts.master')

@section('page-title', 'تعديل بحث')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h4 class="mb-0">تعديل البحث: {{ $research->name }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.docs.researches.show', $research) }}" class="btn btn-light border">عرض التوثيقات</a>
                <a href="{{ route('admin.docs.researches.index') }}" class="btn btn-secondary">رجوع</a>
            </div>
        </div>

        @include('admin.components.alerts')

        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.docs.researches.update', $research) }}" method="POST" class="card custom-card">
            @csrf
            @method('PUT')
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $research->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">القسم <span class="text-danger">*</span></label>
                    <select name="documentation_category_id" class="form-select" required @if($hasPages) disabled @endif>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $research->documentation_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @if($hasPages)
                        {{-- Disabled inputs are not submitted; keep the value so the
                             required rule still passes on update. --}}
                        <input type="hidden" name="documentation_category_id" value="{{ $research->documentation_category_id }}">
                        <p class="text-muted small mb-0 mt-1">
                            <i class="fe fe-lock me-1"></i>
                            لا يمكن نقل بحث يحتوي على صفحات إلى قسم آخر. أزل الصفحات من البحث أولاً.
                        </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">الرابط (slug)</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $research->slug) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">أيقونة (فئة CSS)</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $research->icon) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $research->sort_order) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $research->description) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                               {{ old('is_active', $research->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">مفعل</label>
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

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
@endsection
