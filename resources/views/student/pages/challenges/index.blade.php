@extends('student.layouts.master')

@section('page-title')
    مكتبة التحديات البرمجية
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/programming-challenge-student.css') }}?v={{ @filemtime(public_path('assets/css/programming-challenge-student.css')) ?: '1' }}">
@endpush

@section('content')
    <div class="main-content app-content pch-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="pch-hero">
                <div>
                    <span class="pch-hero__badge mb-2">
                        <i class="fe fe-code"></i>
                        تدريب تفاعلي
                    </span>
                    <h1 class="pch-hero__title">التحديات البرمجية</h1>
                    <p class="pch-hero__desc">
                        تدرب على HTML وCSS وJavaScript أو تنفيذ الكود عبر تحديات عملية مع معاينة حية وتسليم مباشر.
                    </p>
                </div>
            </div>

            <div class="card custom-card pch-filters">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">المستوى</label>
                            <select name="difficulty" class="form-select">
                                <option value="">كل المستويات</option>
                                @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $v => $l)
                                    <option value="{{ $v }}" @selected(request('difficulty') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">النوع</label>
                            <select name="type" class="form-select">
                                <option value="">كل الأنواع</option>
                                <option value="web_sandbox" @selected(request('type') === 'web_sandbox')>ويب (HTML/CSS/JS)</option>
                                <option value="code_runner" @selected(request('type') === 'code_runner')>تنفيذ كود</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-filter me-1"></i>تصفية
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
            @endphp

            <div class="pch-grid">
                @forelse($challenges as $challenge)
                    @php
                        $isWeb = $challenge->challenge_type === 'web_sandbox';
                    @endphp
                    <article class="pch-card">
                        <div class="pch-card__cover {{ $isWeb ? '' : 'pch-card__cover--code' }}">
                            <i class="fe {{ $isWeb ? 'fe-layout' : 'fe-terminal' }}"></i>
                        </div>
                        <div class="pch-card__body">
                            <div class="pch-card__meta">
                                <span class="pch-tag {{ $isWeb ? 'pch-tag--web' : 'pch-tag--code' }}">
                                    {{ $isWeb ? 'ويب HTML/CSS/JS' : 'تنفيذ كود' }}
                                </span>
                                <span class="pch-tag pch-tag--{{ $challenge->difficulty }}">
                                    {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                </span>
                            </div>
                            <h2 class="pch-card__title">{{ $challenge->title }}</h2>
                            <p class="pch-card__summary">{{ Str::limit(strip_tags($challenge->description ?? ''), 120) }}</p>
                            @if($challenge->languages->isNotEmpty())
                                <div class="pch-card__langs">
                                    @foreach($challenge->languages->take(4) as $lang)
                                        <span class="badge bg-light text-dark">{{ $lang->display_name }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <a href="{{ route('student.challenges.show', $challenge->id) }}" class="btn btn-primary btn-sm w-100 mt-auto">
                                <i class="fe fe-arrow-left me-1"></i>عرض التحدي
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="pch-empty">
                        <div class="pch-empty__icon"><i class="fe fe-code"></i></div>
                        <h3 class="pch-empty__title">لا توجد تحديات منشورة حالياً</h3>
                        <p class="pch-empty__text">
                            تظهر هنا التحديات المنشورة والمفعّلة في المكتبة المستقلة فقط. تأكد من تفعيل «منشور» و«مكتبة مستقلة» من لوحة الإدارة.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $challenges->links() }}</div>
        </div>
    </div>
@stop
