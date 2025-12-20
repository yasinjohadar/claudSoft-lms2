@extends('admin.layouts.master')

@section('page-title')
    تعديل المنتج
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb"></div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تعديل المنتج: {{ $item->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.shop.items.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.shop.items.update', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $item->name) }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الفئة <span class="text-danger">*</span></label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">اختر الفئة</option>
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}" {{ $item->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">السعر (نقاط) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="price_points" value="{{ old('price_points', $item->price_points) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">السعر (جواهر)</label>
                                        <input type="number" class="form-control" name="price_gems" value="{{ old('price_gems', $item->price_gems) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الكمية المتاحة</label>
                                        <input type="number" class="form-control" name="stock" value="{{ old('stock', $item->stock) }}" placeholder="اتركه فارغاً للكمية غير المحدودة">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الأيقونة</label>
                                        <input type="text" class="form-control" name="icon" value="{{ old('icon', $item->icon) }}" placeholder="🎁">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">المستوى المطلوب</label>
                                        <input type="number" class="form-control" name="required_level" value="{{ old('required_level', $item->required_level) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">نشط</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> تحديث</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
