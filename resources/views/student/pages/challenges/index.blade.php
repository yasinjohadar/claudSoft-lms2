@extends('student.layouts.master')

@section('page-title')
    مكتبة التحديات البرمجية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="mb-1">التحديات البرمجية</h4>
                    <p class="text-muted mb-0">تدرب على البرمجة بتحديات تفاعلية</p>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <select name="difficulty" class="form-select">
                                <option value="">كل المستويات</option>
                                @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $v => $l)
                                    <option value="{{ $v }}" @selected(request('difficulty') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="type" class="form-select">
                                <option value="">كل الأنواع</option>
                                <option value="web_sandbox" @selected(request('type') === 'web_sandbox')>ويب (HTML/CSS/JS)</option>
                                <option value="code_runner" @selected(request('type') === 'code_runner')>تنفيذ كود</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">تصفية</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @forelse($challenges as $challenge)
                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-primary-transparent">{{ $challenge->challenge_type === 'web_sandbox' ? 'ويب' : 'كود' }}</span>
                                    <span class="badge bg-secondary-transparent">{{ $challenge->difficulty }}</span>
                                </div>
                                <h5 class="mb-2">{{ $challenge->title }}</h5>
                                <p class="text-muted small">{{ Str::limit($challenge->description, 120) }}</p>
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    @foreach($challenge->languages->take(4) as $lang)
                                        <span class="badge bg-light text-dark">{{ $lang->display_name }}</span>
                                    @endforeach
                                </div>
                                <a href="{{ route('student.challenges.show', $challenge->id) }}" class="btn btn-primary btn-sm w-100">عرض التحدي</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">لا توجد تحديات منشورة حالياً</div>
                @endforelse
            </div>

            {{ $challenges->links() }}
        </div>
    </div>
@stop
