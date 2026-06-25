@extends('student.layouts.master')

@section('page-title')
    {{ $showcase->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="mb-3">
                <a href="{{ route('student.community-projects.index') }}" class="text-muted">
                    <i class="fe fe-arrow-right me-1"></i>العودة للمعرض
                </a>
            </div>

            <div class="pc-showcase-hero" @if($showcase->cover_image) style="background-image:url('{{ $showcase->cover_image }}')" @endif>
                <div class="pc-showcase-hero__overlay">
                    <h1 class="pc-showcase-hero__title">{{ $showcase->title }}</h1>
                    @if($showcase->summary)
                        <p class="mb-0 opacity-75">{{ $showcase->summary }}</p>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="pc-showcase-links">
                        @if($showcase->github_url)
                            <a href="{{ $showcase->github_url }}" target="_blank" rel="noopener" class="pc-showcase-link-btn">
                                <i class="fe fe-github"></i> GitHub
                            </a>
                        @endif
                        @if($showcase->demo_url)
                            <a href="{{ $showcase->demo_url }}" target="_blank" rel="noopener" class="pc-showcase-link-btn">
                                <i class="fe fe-globe"></i> عرض تجريبي
                            </a>
                        @endif
                        @if($showcase->video_url)
                            <a href="{{ $showcase->video_url }}" target="_blank" rel="noopener" class="pc-showcase-link-btn">
                                <i class="fe fe-play-circle"></i> فيديو
                            </a>
                        @endif
                    </div>

                    @if($showcase->screenshots && count($showcase->screenshots))
                        <div class="row g-3 mb-4">
                            @foreach($showcase->screenshots as $screenshot)
                                <div class="col-md-6">
                                    <img src="{{ $screenshot }}" alt="" class="img-fluid rounded" style="width:100%;object-fit:cover">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="pc-comments-section">
                        <h5 class="mb-3"><i class="fe fe-message-square me-1"></i>التعليقات ({{ $showcase->comments->count() }})</h5>

                        <form action="{{ route('student.community-projects.comments.store', $showcase->slug) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-2">
                                <textarea name="body" class="form-control" rows="3" placeholder="أضف تعليقك..." required maxlength="5000">{{ old('body') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fe fe-send me-1"></i>نشر التعليق
                            </button>
                        </form>

                        @forelse($showcase->comments as $comment)
                            @include('student.pages.community-projects._comment', ['comment' => $comment, 'showcase' => $showcase, 'depth' => 0])
                        @empty
                            <p class="text-muted">كن أول من يعلق على هذا المشروع!</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">الفريق</div></div>
                        <div class="card-body">
                            <h6 class="mb-3">{{ $showcase->team->name }}</h6>
                            <div class="pc-members">
                                @foreach($showcase->team->activeMembers as $member)
                                    <span class="pc-member-chip @if($member->user_id === $showcase->team->leader_id) pc-member-chip--leader @endif">
                                        <i class="fe fe-user"></i>
                                        {{ $member->user->name ?? $member->user->email }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($showcase->challenge)
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">التحدي</div></div>
                            <div class="card-body">
                                <p class="mb-2">{{ $showcase->challenge->title }}</p>
                                <a href="{{ route('student.project-challenges.show', $showcase->challenge->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                    عرض التحدي
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($showcase->published_at)
                        <p class="text-muted small mt-3 text-center">
                            <i class="fe fe-calendar me-1"></i>نُشر {{ $showcase->published_at->format('Y-m-d') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
