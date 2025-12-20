@extends('admin.layouts.master')

@section('page-title')
    تعديل الإنجاز
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
                            <h5 class="mb-0 fw-bold">تعديل الإنجاز: {{ $achievement->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.achievements.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.achievements.update', $achievement->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $achievement->name) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">الأيقونة</label>
                                        <input type="text" class="form-control" name="icon" value="{{ old('icon', $achievement->icon) }}" placeholder="🏆">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">المستوى</label>
                                        <select class="form-select" name="tier">
                                            <option value="bronze" {{ $achievement->tier == 'bronze' ? 'selected' : '' }}>برونزي</option>
                                            <option value="silver" {{ $achievement->tier == 'silver' ? 'selected' : '' }}>فضي</option>
                                            <option value="gold" {{ $achievement->tier == 'gold' ? 'selected' : '' }}>ذهبي</option>
                                            <option value="platinum" {{ $achievement->tier == 'platinum' ? 'selected' : '' }}>بلاتيني</option>
                                            <option value="diamond" {{ $achievement->tier == 'diamond' ? 'selected' : '' }}>ماسي</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description', $achievement->description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">نوع المتطلب</label>
                                        <select class="form-select" name="requirement_type">
                                            <option value="lessons_completed" {{ $achievement->requirement_type == 'lessons_completed' ? 'selected' : '' }}>دروس مكتملة</option>
                                            <option value="quizzes_passed" {{ $achievement->requirement_type == 'quizzes_passed' ? 'selected' : '' }}>اختبارات ناجحة</option>
                                            <option value="points_earned" {{ $achievement->requirement_type == 'points_earned' ? 'selected' : '' }}>نقاط مكتسبة</option>
                                            <option value="badges_earned" {{ $achievement->requirement_type == 'badges_earned' ? 'selected' : '' }}>شارات مكتسبة</option>
                                            <option value="streak_days" {{ $achievement->requirement_type == 'streak_days' ? 'selected' : '' }}>أيام متتالية</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">قيمة المتطلب</label>
                                        <input type="number" class="form-control" name="requirement_value" value="{{ old('requirement_value', $achievement->requirement_value) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" name="points_reward" value="{{ old('points_reward', $achievement->points_reward) }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $achievement->is_active ? 'checked' : '' }}>
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
