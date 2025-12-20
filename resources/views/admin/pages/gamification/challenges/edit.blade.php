@extends('admin.layouts.master')

@section('page-title')
    تعديل التحدي
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
                            <h5 class="mb-0 fw-bold">تعديل التحدي: {{ $challenge->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.challenges.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.challenges.update', $challenge->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $challenge->name) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">الأيقونة</label>
                                        <input type="text" class="form-control" name="icon" value="{{ old('icon', $challenge->icon) }}" placeholder="🎯">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">النوع</label>
                                        <select class="form-select" name="type">
                                            <option value="daily" {{ $challenge->type == 'daily' ? 'selected' : '' }}>يومي</option>
                                            <option value="weekly" {{ $challenge->type == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                            <option value="monthly" {{ $challenge->type == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            <option value="special" {{ $challenge->type == 'special' ? 'selected' : '' }}>خاص</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description', $challenge->description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">نوع الهدف</label>
                                        <select class="form-select" name="target_type">
                                            <option value="complete_lessons" {{ $challenge->target_type == 'complete_lessons' ? 'selected' : '' }}>إكمال دروس</option>
                                            <option value="pass_quizzes" {{ $challenge->target_type == 'pass_quizzes' ? 'selected' : '' }}>اجتياز اختبارات</option>
                                            <option value="earn_points" {{ $challenge->target_type == 'earn_points' ? 'selected' : '' }}>كسب نقاط</option>
                                            <option value="login_streak" {{ $challenge->target_type == 'login_streak' ? 'selected' : '' }}>تسجيل دخول متتالي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">قيمة الهدف</label>
                                        <input type="number" class="form-control" name="target_value" value="{{ old('target_value', $challenge->target_value) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" name="points_reward" value="{{ old('points_reward', $challenge->points_reward) }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">تاريخ البداية</label>
                                        <input type="datetime-local" class="form-control" name="starts_at" value="{{ old('starts_at', $challenge->starts_at ? $challenge->starts_at->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">تاريخ النهاية</label>
                                        <input type="datetime-local" class="form-control" name="ends_at" value="{{ old('ends_at', $challenge->ends_at ? $challenge->ends_at->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $challenge->is_active ? 'checked' : '' }}>
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
