@extends('student.layouts.master')

@section('page-title')
    {{ $challenge->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            @php
                $typeLabels = [
                    'team_project' => 'مشروع فريق',
                    'open_challenge' => 'تحدي مفتوح',
                    'hackathon' => 'هاكاثون',
                    'capstone' => 'مشروع تخرج',
                ];
                $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
            @endphp

            <div class="mb-3">
                <a href="{{ route('student.project-challenges.index') }}" class="text-muted">
                    <i class="fe fe-arrow-right me-1"></i>العودة للتحديات
                </a>
            </div>

            <div class="pc-detail-hero">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="pc-tag">{{ $typeLabels[$challenge->project_type] ?? $challenge->project_type }}</span>
                    <span class="pc-tag pc-tag--{{ $challenge->difficulty }}">{{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}</span>
                    @if($challenge->expected_duration)
                        <span class="pc-tag"><i class="fe fe-clock me-1"></i>{{ $challenge->expected_duration }}</span>
                    @endif
                    @if($challenge->points_total)
                        <span class="pc-tag"><i class="fe fe-award me-1"></i>{{ $challenge->points_total }} نقطة</span>
                    @endif
                </div>
                <h2 class="mb-2">{{ $challenge->title }}</h2>
                @if($challenge->summary)
                    <div class="text-muted mb-3 pc-rich-content">{!! $challenge->summary !!}</div>
                @endif
                @if($challenge->description)
                    <div class="mb-3 pc-rich-content">{!! $challenge->description !!}</div>
                @endif
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($challenge->skills as $skill)
                        <span class="badge bg-light text-dark">{{ $skill->name }}</span>
                    @endforeach
                    @foreach($challenge->technologies as $tech)
                        <span class="badge bg-primary-transparent">{{ $tech->name }}</span>
                    @endforeach
                </div>
                <div class="row text-muted small">
                    <div class="col-md-4"><i class="fe fe-users me-1"></i>{{ $challenge->min_members }}–{{ $challenge->max_members }} أعضاء</div>
                    @if($challenge->starts_at)
                        <div class="col-md-4"><i class="fe fe-calendar me-1"></i>يبدأ: {{ $challenge->starts_at->format('Y-m-d') }}</div>
                    @endif
                    @if($challenge->ends_at)
                        <div class="col-md-4"><i class="fe fe-flag me-1"></i>ينتهي: {{ $challenge->ends_at->format('Y-m-d') }}</div>
                    @endif
                </div>
            </div>

            @if($userTeam)
                <div class="card custom-card mb-4 border-success">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1"><i class="fe fe-check-circle text-success me-1"></i>أنت عضو في فريق: {{ $userTeam->name }}</h5>
                            <p class="text-muted mb-0">التقدم: {{ number_format($userTeam->progress_percent, 0) }}%</p>
                        </div>
                        <a href="{{ route('student.project-teams.workspace', $userTeam->id) }}" class="btn btn-success">
                            <i class="fe fe-monitor me-1"></i>الذهاب لمساحة العمل
                        </a>
                    </div>
                </div>
            @else
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card custom-card h-100">
                            <div class="card-body text-center">
                                <i class="fe fe-plus-circle fs-1 text-primary mb-3 d-block"></i>
                                <h5>إنشاء فريق جديد</h5>
                                <p class="text-muted">كن قائد فريق وادعُ الآخرين للانضمام</p>
                                @if($challenge->hasReachedTeamLimit())
                                    <button class="btn btn-secondary" disabled>تم الوصول للحد الأقصى للفرق</button>
                                @else
                                    <a href="{{ route('student.project-challenges.teams.create', $challenge->id) }}" class="btn btn-primary">
                                        <i class="fe fe-plus me-1"></i>إنشاء فريق
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="mb-3"><i class="fe fe-users me-1"></i>فرق مفتوحة للانضمام</h5>
                                @if($openTeams->isNotEmpty())
                                    <div class="list-group pc-open-teams-list">
                                        @foreach($openTeams as $team)
                                            <div class="list-group-item">
                                                <div>
                                                    <strong>{{ $team->name }}</strong>
                                                    <br><small class="text-muted">القائد: {{ $team->leader->name ?? '—' }} — {{ $team->active_members_count }}/{{ $challenge->max_members }} أعضاء</small>
                                                </div>
                                                <form action="{{ route('student.project-teams.join', $team->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" @if(!$team->canAcceptMembers()) disabled @endif>
                                                        <i class="fe fe-user-plus me-1"></i>انضمام
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0">لا توجد فرق مفتوحة حالياً. أنشئ فريقك الخاص!</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($challenge->stages->isNotEmpty())
                <div class="card custom-card">
                    <div class="card-header"><div class="card-title">مراحل التحدي</div></div>
                    <div class="card-body">
                        <div class="pc-timeline">
                            @foreach($challenge->stages as $stage)
                                <div class="pc-timeline-item">
                                    <div class="pc-timeline-item__dot"></div>
                                    <div class="pc-timeline-item__card">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1">{{ $stage->title }}</h6>
                                            @if($stage->is_optional)
                                                <span class="badge bg-secondary-transparent">اختياري</span>
                                            @endif
                                        </div>
                                        @if($stage->description)
                                            <p class="text-muted small mb-0">{{ Str::limit($stage->description, 150) }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
