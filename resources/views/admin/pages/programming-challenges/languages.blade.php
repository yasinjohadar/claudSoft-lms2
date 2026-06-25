@extends('admin.layouts.master')

@section('page-title')
    لغات التحدي — {{ $challenge->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-4">
                <h5 class="page-title fs-21 mb-1">اختيار اللغات — {{ $challenge->title }}</h5>
                <p class="text-muted">الخطوة 1 من 3: حدد اللغات المتاحة للطلاب في هذا التحدي</p>
            </div>

            <form action="{{ route('programming-challenges.update-languages', $challenge->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="card custom-card">
                    <div class="card-body">
                        @if($languages->isEmpty())
                            <div class="alert alert-warning">
                                لا توجد لغات قابلة للتشغيل. شغّل <code>ProgrammingLanguageExecutionSeeder</code> أولاً.
                            </div>
                        @else
                            <div class="row">
                                @foreach($languages as $lang)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check border rounded p-3">
                                            <input class="form-check-input" type="checkbox" name="languages[]"
                                                   value="{{ $lang->id }}" id="lang_{{ $lang->id }}"
                                                   @checked(in_array($lang->id, $selectedIds))>
                                            <label class="form-check-label w-100" for="lang_{{ $lang->id }}">
                                                <strong>{{ $lang->display_name }}</strong>
                                                <br><small class="text-muted">{{ $lang->execution_mode === 'client_web' ? 'متصفح' : 'سيرفر' }}</small>
                                            </label>
                                            <div class="mt-2">
                                                <input type="radio" name="default_language" value="{{ $lang->id }}"
                                                       @checked($challenge->languages->where('pivot.is_default', true)->first()?->id === $lang->id)>
                                                <small>افتراضي</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('programming-challenges.index') }}" class="btn btn-light">رجوع</a>
                        <button type="submit" class="btn btn-primary">حفظ والمتابعة للكود الابتدائي</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
