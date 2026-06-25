@extends('admin.layouts.master')

@section('page-title')
    تعديل تحدي برمجي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل: {{ $challenge->title }}</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="btn btn-outline-primary btn-sm">اللغات</a>
                    <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="btn btn-outline-info btn-sm">الكود الابتدائي</a>
                    <a href="{{ route('programming-challenges.manage-test-cases', $challenge->id) }}" class="btn btn-outline-warning btn-sm">اختبارات</a>
                </div>
            </div>

            <form action="{{ route('programming-challenges.update', $challenge->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">العنوان</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $challenge->title) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $challenge->description) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">التعليمات</label>
                                    <textarea name="instructions" class="form-control" rows="5">{{ old('instructions', $challenge->instructions) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نوع التحدي</label>
                                        <select name="challenge_type" class="form-select">
                                            <option value="web_sandbox" @selected($challenge->challenge_type === 'web_sandbox')>Sandbox ويب</option>
                                            <option value="code_runner" @selected($challenge->challenge_type === 'code_runner')>تنفيذ كود</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نمط التقييم</label>
                                        <select name="grading_mode" class="form-select">
                                            <option value="manual" @selected($challenge->grading_mode === 'manual')>يدوي</option>
                                            <option value="auto" @selected($challenge->grading_mode === 'auto')>آلي</option>
                                            <option value="hybrid" @selected($challenge->grading_mode === 'hybrid')>هجين</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">الصعوبة</label>
                                    <select name="difficulty" class="form-select">
                                        @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $val => $label)
                                            <option value="{{ $val }}" @selected($challenge->difficulty === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الدرجة القصوى</label>
                                    <input type="number" name="max_score" class="form-control" value="{{ old('max_score', $challenge->max_score) }}" min="0" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المحاولات</label>
                                    <input type="number" name="attempts_allowed" class="form-control" value="{{ old('attempts_allowed', $challenge->attempts_allowed) }}" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">حد الوقت (ثوانٍ)</label>
                                    <input type="number" name="time_limit_seconds" class="form-control" value="{{ old('time_limit_seconds', $challenge->time_limit_seconds) }}" min="0">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_resubmit" id="allow_resubmit" @checked($challenge->allow_resubmit)>
                                    <label class="form-check-label" for="allow_resubmit">إعادة التسليم</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" @checked($challenge->is_published)>
                                    <label class="form-check-label" for="is_published">منشور</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_standalone" id="is_standalone" @checked($challenge->is_standalone)>
                                    <label class="form-check-label" for="is_standalone">مكتبة مستقلة</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">حفظ التغييرات</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
