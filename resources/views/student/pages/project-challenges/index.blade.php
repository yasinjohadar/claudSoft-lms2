@extends('student.layouts.master')

@section('page-title')
    تحديات المشاريع
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="mb-1">تحديات المشاريع</h4>
                    <p class="text-muted mb-0">انضم لفريق وابنِ مشروعك خطوة بخطوة</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.community-projects.index') }}" class="btn btn-outline-primary">
                        <i class="fe fe-grid me-1"></i>معرض المشاريع
                    </a>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-3">
                            <select name="difficulty" class="form-select">
                                <option value="">كل المستويات</option>
                                @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $v => $l)
                                    <option value="{{ $v }}" @selected(request('difficulty') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">كل الأنواع</option>
                                @foreach(['team_project' => 'مشروع فريق', 'open_challenge' => 'تحدي مفتوح', 'hackathon' => 'هاكاثون', 'capstone' => 'مشروع تخرج'] as $v => $l)
                                    <option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" @checked(request()->boolean('featured'))>
                                <label class="form-check-label" for="featured">المميزة فقط</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">تصفية</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pc-challenge-grid">
                @php
                    $typeLabels = [
                        'team_project' => 'مشروع فريق',
                        'open_challenge' => 'تحدي مفتوح',
                        'hackathon' => 'هاكاثون',
                        'capstone' => 'مشروع تخرج',
                    ];
                    $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
                @endphp
                @forelse($challenges as $challenge)
                    @php $myTeam = $userTeams->get($challenge->id); @endphp
                    <div class="pc-challenge-card">
                        <div class="pc-challenge-card__cover">
                            @if($challenge->cover_image)
                                <img src="{{ $challenge->cover_image }}" alt="" style="width:100%;height:100%;object-fit:cover">
                            @else
                                <i class="fe fe-layers"></i>
                            @endif
                        </div>
                        <div class="pc-challenge-card__body">
                            <div class="pc-challenge-card__meta">
                                <span class="pc-tag">{{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}</span>
                                <span class="pc-tag pc-tag--{{ $challenge->difficulty }}">{{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}</span>
                                @if($challenge->is_featured)
                                    <span class="pc-tag" style="background:rgba(245,158,11,.15);color:#f59e0b">مميز</span>
                                @endif
                            </div>
                            <h5 class="pc-challenge-card__title">{{ $challenge->title }}</h5>
                            <p class="pc-challenge-card__summary">{{ Str::limit(strip_tags($challenge->summary ?? $challenge->description), 120) }}</p>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                @foreach($challenge->skills->take(3) as $skill)
                                    <span class="badge bg-light text-dark">{{ $skill->name }}</span>
                                @endforeach
                                @foreach($challenge->technologies->take(3) as $tech)
                                    <span class="badge bg-primary-transparent">{{ $tech->name }}</span>
                                @endforeach
                            </div>
                            <div class="pc-challenge-card__footer">
                                @if($myTeam)
                                    <a href="{{ route('student.project-teams.workspace', $myTeam->id) }}" class="btn btn-success btn-sm w-100">
                                        <i class="fe fe-monitor me-1"></i>مساحة العمل
                                    </a>
                                @else
                                    <a href="{{ route('student.project-challenges.show', $challenge->id) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="fe fe-arrow-left me-1"></i>عرض التفاصيل
                                    </a>
                                @endif
                                <small class="text-muted d-block text-center mt-2">{{ $challenge->teams_count }} فريق</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">لا توجد تحديات منشورة حالياً</div>
                @endforelse
            </div>

            <div class="mt-4">{{ $challenges->links() }}</div>
        </div>
    </div>
@stop
