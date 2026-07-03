<div class="my-4 page-header-breadcrumb">
    <nav>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.telegram.settings.index') }}">Telegram</a></li>
            @if(!empty($breadcrumb))
                <li class="breadcrumb-item active">{{ $breadcrumb }}</li>
            @endif
        </ol>
    </nav>
</div>

<div class="tg-hero dashboard-fade-in">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ri-telegram-line fa-lg"></i>
                <span class="badge bg-light text-info">Bot API</span>
            </div>
            <h1 class="tg-hero__title">{{ $title ?? 'Telegram' }}</h1>
            <p class="tg-hero__desc">{{ $subtitle ?? '' }}</p>
        </div>
        @if(!empty($badge))
            <span class="tg-stat-pill">{!! $badge !!}</span>
        @endif
    </div>
</div>
