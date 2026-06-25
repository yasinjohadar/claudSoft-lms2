@extends('admin.layouts.master')

@section('page-title')
    إنشاء تحدي برمجي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء تحدي برمجي جديد</h5>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('programming-challenges.store') }}" method="POST">
                @csrf
                @if($section)<input type="hidden" name="section_id" value="{{ $section->id }}">@endif

                <div class="row">
                    <div class="col-xl-8">
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">معلومات التحدي</div></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">التعليمات</label>
                                    <textarea name="instructions" class="form-control" rows="5">{{ old('instructions') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نوع التحدي</label>
                                        <select name="challenge_type" class="form-select">
                                            <option value="web_sandbox" @selected(old('challenge_type') === 'web_sandbox')>Sandbox ويب (HTML/CSS/JS)</option>
                                            <option value="code_runner" @selected(old('challenge_type') === 'code_runner')>تنفيذ كود (سيرفر)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نمط التقييم</label>
                                        <select name="grading_mode" class="form-select">
                                            <option value="manual" @selected(old('grading_mode', 'manual') === 'manual')>يدوي</option>
                                            <option value="auto" @selected(old('grading_mode') === 'auto')>آلي</option>
                                            <option value="hybrid" @selected(old('grading_mode') === 'hybrid')>هجين</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">الإعدادات</div></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">الصعوبة</label>
                                    <select name="difficulty" class="form-select">
                                        <option value="easy">سهل</option>
                                        <option value="medium">متوسط</option>
                                        <option value="hard">صعب</option>
                                        <option value="expert">خبير</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الدرجة القصوى</label>
                                    <input type="number" name="max_score" class="form-control" value="{{ old('max_score', 100) }}" min="0" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">المحاولات المسموحة</label>
                                    <input type="number" name="attempts_allowed" class="form-control" value="{{ old('attempts_allowed', 3) }}" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">حد الوقت (ثوانٍ)</label>
                                    <input type="number" name="time_limit_seconds" class="form-control" value="{{ old('time_limit_seconds') }}" min="0">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_resubmit" id="allow_resubmit" checked>
                                    <label class="form-check-label" for="allow_resubmit">السماح بإعادة التسليم</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published">
                                    <label class="form-check-label" for="is_published">نشر التحدي</label>
                                </div>
                                @unless($section)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_standalone" id="is_standalone" checked>
                                    <label class="form-check-label" for="is_standalone">إظهار في المكتبة المستقلة</label>
                                </div>
                                @endunless
                                <button type="submit" class="btn btn-primary w-100 mt-3">إنشاء والمتابعة</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
