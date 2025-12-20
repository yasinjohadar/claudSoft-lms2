@extends('admin.layouts.master')

@section('page-title')
    تعديل سؤال شائع
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل سؤال شائع</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.faqs.index') }}">الأسئلة الشائعة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات السؤال الشائع</div>
                        </div>

                        <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <div class="row g-3">

                                    <!-- السؤال -->
                                    <div class="col-12">
                                        <label for="question" class="form-label">
                                            السؤال <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('question') is-invalid @enderror" 
                                               id="question" 
                                               name="question" 
                                               value="{{ old('question', $faq->question) }}" 
                                               placeholder="أدخل السؤال"
                                               required>
                                        @error('question')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الإجابة -->
                                    <div class="col-12">
                                        <label for="answer" class="form-label">
                                            الإجابة <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('answer') is-invalid @enderror" 
                                                  id="answer" 
                                                  name="answer" 
                                                  rows="6" 
                                                  placeholder="أدخل الإجابة"
                                                  required>{{ old('answer', $faq->answer) }}</textarea>
                                        @error('answer')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الترتيب -->
                                    <div class="col-md-6">
                                        <label for="order" class="form-label">الترتيب</label>
                                        <input type="number" 
                                               class="form-control @error('order') is-invalid @enderror" 
                                               id="order" 
                                               name="order" 
                                               value="{{ old('order', $faq->order) }}" 
                                               min="0"
                                               placeholder="0">
                                        <small class="text-muted">يتم ترتيب الأسئلة حسب هذا الرقم (الأصغر أولاً)</small>
                                        @error('order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الحالة -->
                                    <div class="col-md-6">
                                        <label class="form-label">الحالة</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="is_active" 
                                                   name="is_active" 
                                                   value="1"
                                                   {{ old('is_active', $faq->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                نشط
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="fas fa-save me-2"></i>حفظ التغييرات
                                </button>
                                <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary btn-wave">
                                    <i class="fas fa-times me-2"></i>إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


