@extends('admin.layouts.master')

@section('page-title')
    تقييم تسليم — {{ $attempt->challenge->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-4">
                <h5 class="page-title fs-21 mb-1">تقييم: {{ $attempt->challenge->title }}</h5>
                <p class="text-muted">الطالب: {{ $attempt->student->name }} — محاولة #{{ $attempt->attempt_number }}</p>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header"><div class="card-title">كود الطالب</div></div>
                        <div class="card-body">
                            @if($submission && $submission->files->count())
                                <ul class="nav nav-tabs mb-3" role="tablist">
                                    @foreach($submission->files as $i => $file)
                                        <li class="nav-item">
                                            <button class="nav-link @if($i === 0) active @endif" data-bs-toggle="tab"
                                                    data-bs-target="#file-{{ $i }}" type="button">{{ $file->filename }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content">
                                    @foreach($submission->files as $i => $file)
                                        <div class="tab-pane fade @if($i === 0) show active @endif" id="file-{{ $i }}">
                                            <pre class="bg-dark text-light p-3 rounded" dir="ltr" style="max-height:500px;overflow:auto"><code>{{ $file->content }}</code></pre>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">لا توجد ملفات في هذا التسليم</p>
                            @endif

                            @if($submission?->student_notes)
                                <div class="mt-3">
                                    <strong>ملاحظات الطالب:</strong>
                                    <p>{{ $submission->student_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <form action="{{ route('admin.challenge-grading.grade', $attempt->id) }}" method="POST">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">الدرجة</div></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">الدرجة (من {{ $attempt->max_score ?? $attempt->challenge->max_score }})</label>
                                    <input type="number" name="score" class="form-control" required min="0"
                                           max="{{ $attempt->max_score ?? $attempt->challenge->max_score }}" step="0.01"
                                           value="{{ old('score', $attempt->score) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">التعليقات</label>
                                    <textarea name="feedback" class="form-control" rows="5">{{ old('feedback', $attempt->feedback) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">حفظ التقييم</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
