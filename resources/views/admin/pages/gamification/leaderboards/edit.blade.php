@extends('admin.layouts.master')

@section('page-title')
    تعديل لوحة المتصدرين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb"></div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تعديل لوحة المتصدرين: {{ $leaderboard->name }}</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.leaderboards.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.gamification.leaderboards.update', $leaderboard->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $leaderboard->name) }}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">النوع <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                            <option value="">اختر النوع</option>
                                            <option value="global" {{ old('type', $leaderboard->type) == 'global' ? 'selected' : '' }}>عام</option>
                                            <option value="course" {{ old('type', $leaderboard->type) == 'course' ? 'selected' : '' }}>كورس</option>
                                            <option value="weekly" {{ old('type', $leaderboard->type) == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                            <option value="monthly" {{ old('type', $leaderboard->type) == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            <option value="speed" {{ old('type', $leaderboard->type) == 'speed' ? 'selected' : '' }}>السرعة</option>
                                            <option value="accuracy" {{ old('type', $leaderboard->type) == 'accuracy' ? 'selected' : '' }}>الدقة</option>
                                            <option value="streak" {{ old('type', $leaderboard->type) == 'streak' ? 'selected' : '' }}>السلسلة</option>
                                            <option value="social" {{ old('type', $leaderboard->type) == 'social' ? 'selected' : '' }}>اجتماعي</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">الفترة <span class="text-danger">*</span></label>
                                        <select class="form-select @error('period') is-invalid @enderror" name="period" required>
                                            <option value="">اختر الفترة</option>
                                            <option value="all_time" {{ old('period', $leaderboard->period) == 'all_time' ? 'selected' : '' }}>كل الأوقات</option>
                                            <option value="daily" {{ old('period', $leaderboard->period) == 'daily' ? 'selected' : '' }}>يومي</option>
                                            <option value="weekly" {{ old('period', $leaderboard->period) == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                            <option value="monthly" {{ old('period', $leaderboard->period) == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            <option value="yearly" {{ old('period', $leaderboard->period) == 'yearly' ? 'selected' : '' }}>سنوي</option>
                                            <option value="season" {{ old('period', $leaderboard->period) == 'season' ? 'selected' : '' }}>موسم</option>
                                        </select>
                                        @error('period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description', $leaderboard->description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $leaderboard->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">نشط</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_visible" id="is_visible" value="1" {{ old('is_visible', $leaderboard->is_visible) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_visible">مرئي للطلاب</label>
                                        </div>
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
