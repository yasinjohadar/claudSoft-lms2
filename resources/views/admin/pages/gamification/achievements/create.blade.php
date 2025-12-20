@extends('admin.layouts.master')

@section('page-title')
    إضافة إنجاز جديد
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
                            <h5 class="mb-0 fw-bold">إضافة إنجاز جديد</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.achievements.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.achievements.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">الأيقونة</label>
                                        <input type="text" class="form-control" name="icon" value="{{ old('icon') }}" placeholder="🏆">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">المستوى</label>
                                        <select class="form-select" name="tier">
                                            <option value="bronze">برونزي</option>
                                            <option value="silver">فضي</option>
                                            <option value="gold">ذهبي</option>
                                            <option value="platinum">بلاتيني</option>
                                            <option value="diamond">ماسي</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">نوع المتطلب</label>
                                        <select class="form-select" name="requirement_type">
                                            <option value="lessons_completed">دروس مكتملة</option>
                                            <option value="quizzes_passed">اختبارات ناجحة</option>
                                            <option value="points_earned">نقاط مكتسبة</option>
                                            <option value="badges_earned">شارات مكتسبة</option>
                                            <option value="streak_days">أيام متتالية</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">قيمة المتطلب</label>
                                        <input type="number" class="form-control" name="requirement_value" value="{{ old('requirement_value', 1) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" name="points_reward" value="{{ old('points_reward', 0) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <label class="form-check-label">نشط</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
