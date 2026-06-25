@extends('admin.layouts.master')

@section('page-title')
    الكود الابتدائي — {{ $challenge->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-4">
                <h5 class="page-title fs-21 mb-1">الكود الابتدائي — {{ $challenge->title }}</h5>
                <p class="text-muted">الخطوة 2 من 3</p>
            </div>

            @php
                $existingFiles = $challenge->files->keyBy('file_role');
                $webRoles = ['html', 'css', 'js'];
                $defaults = [
                    'html' => ['filename' => 'index.html', 'content' => "<!DOCTYPE html>\n<html>\n<head>\n  <meta charset=\"UTF-8\">\n  <title>التحدي</title>\n</head>\n<body>\n  <h1>مرحباً</h1>\n</body>\n</html>"],
                    'css' => ['filename' => 'style.css', 'content' => "body {\n  font-family: sans-serif;\n}"],
                    'js' => ['filename' => 'script.js', 'content' => "// اكتب كود JavaScript هنا\n"],
                ];
            @endphp

            <form action="{{ route('programming-challenges.update-starter', $challenge->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="card custom-card">
                    <div class="card-body">
                        @if($challenge->isWebSandbox())
                            @foreach($webRoles as $i => $role)
                                @php $file = $existingFiles->get($role); @endphp
                                <div class="mb-4">
                                    <input type="hidden" name="files[{{ $i }}][file_role]" value="{{ $role }}">
                                    <label class="form-label text-uppercase fw-bold">{{ $role }}</label>
                                    <input type="text" name="files[{{ $i }}][filename]" class="form-control form-control-sm mb-2"
                                           value="{{ $file->filename ?? $defaults[$role]['filename'] }}">
                                    <textarea name="files[{{ $i }}][content]" class="form-control font-monospace" rows="8"
                                              dir="ltr" style="text-align:left">{{ $file->content ?? $defaults[$role]['content'] }}</textarea>
                                </div>
                            @endforeach
                        @else
                            @forelse($challenge->languages as $i => $lang)
                                @php
                                    $file = $challenge->files->where('programming_language_id', $lang->id)->first();
                                @endphp
                                <div class="mb-4">
                                    <input type="hidden" name="files[{{ $i }}][file_role]" value="starter">
                                    <input type="hidden" name="files[{{ $i }}][programming_language_id]" value="{{ $lang->id }}">
                                    <label class="form-label fw-bold">{{ $lang->display_name }}</label>
                                    <input type="text" name="files[{{ $i }}][filename]" class="form-control form-control-sm mb-2"
                                           value="{{ $file->filename ?? ($lang->default_filename ?? 'main.txt') }}">
                                    <textarea name="files[{{ $i }}][content]" class="form-control font-monospace" rows="10"
                                              dir="ltr" style="text-align:left">{{ $file->content ?? '' }}</textarea>
                                </div>
                            @empty
                                <div class="alert alert-info">اختر اللغات أولاً من <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}">صفحة اللغات</a></div>
                            @endforelse
                        @endif
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="btn btn-light">رجوع</a>
                        <button type="submit" class="btn btn-primary">حفظ والمتابعة للاختبارات</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
