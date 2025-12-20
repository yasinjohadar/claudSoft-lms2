@extends('admin.layouts.master')

@section('page-title')
    تعديل الشارة
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            </div>
            <!-- Page Header Close -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تعديل الشارة: {{ $badge->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.badges.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.gamification.badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">اسم الشارة <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $badge->name) }}" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="slug" class="form-label">الاسم المختصر (Slug)</label>
                                        <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $badge->slug) }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $badge->description) }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="icon" class="form-label">الأيقونة (Emoji)</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $badge->icon) }}" placeholder="🏅">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="image" class="form-label">صورة الشارة</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        @if($badge->image)
                                            <small class="text-muted">الصورة الحالية: {{ $badge->image }}</small>
                                        @endif
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="rarity" class="form-label">الندرة <span class="text-danger">*</span></label>
                                        <select class="form-select" id="rarity" name="rarity" required>
                                            <option value="common" {{ old('rarity', $badge->rarity) == 'common' ? 'selected' : '' }}>عادي</option>
                                            <option value="uncommon" {{ old('rarity', $badge->rarity) == 'uncommon' ? 'selected' : '' }}>غير شائع</option>
                                            <option value="rare" {{ old('rarity', $badge->rarity) == 'rare' ? 'selected' : '' }}>نادر</option>
                                            <option value="epic" {{ old('rarity', $badge->rarity) == 'epic' ? 'selected' : '' }}>ملحمي</option>
                                            <option value="legendary" {{ old('rarity', $badge->rarity) == 'legendary' ? 'selected' : '' }}>أسطوري</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="category" class="form-label">الفئة</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">اختر الفئة</option>
                                            <option value="learning" {{ old('category', $badge->category) == 'learning' ? 'selected' : '' }}>التعلم</option>
                                            <option value="engagement" {{ old('category', $badge->category) == 'engagement' ? 'selected' : '' }}>التفاعل</option>
                                            <option value="achievement" {{ old('category', $badge->category) == 'achievement' ? 'selected' : '' }}>الإنجاز</option>
                                            <option value="social" {{ old('category', $badge->category) == 'social' ? 'selected' : '' }}>اجتماعي</option>
                                            <option value="special" {{ old('category', $badge->category) == 'special' ? 'selected' : '' }}>خاص</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="points_reward" class="form-label">مكافأة النقاط</label>
                                        <input type="number" class="form-control" id="points_reward" name="points_reward" value="{{ old('points_reward', $badge->points_reward) }}" min="0">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="xp_reward" class="form-label">مكافأة XP</label>
                                        <input type="number" class="form-control" id="xp_reward" name="xp_reward" value="{{ old('xp_reward', $badge->xp_reward) }}" min="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="requirement_type" class="form-label">نوع المتطلب</label>
                                        <select class="form-select" id="requirement_type" name="requirement_type">
                                            <option value="">اختر نوع المتطلب</option>
                                            <option value="lessons_completed" {{ old('requirement_type', $badge->requirement_type) == 'lessons_completed' ? 'selected' : '' }}>دروس مكتملة</option>
                                            <option value="quizzes_passed" {{ old('requirement_type', $badge->requirement_type) == 'quizzes_passed' ? 'selected' : '' }}>اختبارات ناجحة</option>
                                            <option value="points_earned" {{ old('requirement_type', $badge->requirement_type) == 'points_earned' ? 'selected' : '' }}>نقاط مكتسبة</option>
                                            <option value="streak_days" {{ old('requirement_type', $badge->requirement_type) == 'streak_days' ? 'selected' : '' }}>أيام متتالية</option>
                                            <option value="courses_completed" {{ old('requirement_type', $badge->requirement_type) == 'courses_completed' ? 'selected' : '' }}>كورسات مكتملة</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="requirement_value" class="form-label">قيمة المتطلب</label>
                                        <input type="number" class="form-control" id="requirement_value" name="requirement_value" value="{{ old('requirement_value', $badge->requirement_value) }}" min="0">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $badge->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            نشط
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> تحديث
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop
