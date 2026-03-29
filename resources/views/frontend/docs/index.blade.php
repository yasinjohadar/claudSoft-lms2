@extends('frontend.docs.layout')

@section('title', 'التوثيق')

@section('content')
    <div class="container">
        <header>
            <div class="header-tag">مركز المساعدة</div>
            <h1>التوثيق</h1>
            <p class="header-desc">تصفح الأدلة والشروحات حسب القسم.</p>
        </header>

        <section class="content-section" style="animation-delay: 0.1s;">
            @if ($categories->isEmpty())
                <div class="text-block">لا توجد أقسام توثيق منشورة حالياً.</div>
            @else
                <div class="course-grid">
                    @foreach ($categories as $cat)
                        <a href="{{ route('frontend.docs.show', ['categorySlug' => $cat->slug]) }}" class="lesson-card">
                            <div class="lesson-number">{{ $loop->iteration }}</div>
                            <h2 class="lesson-title">{{ $cat->name }}</h2>
                            @if ($cat->description)
                                <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($cat->description), 140) }}</p>
                            @endif
                            <div class="lesson-status">
                                <span></span>
                                <div class="start-btn">
                                    <span>فتح القسم</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <footer>
            <strong>التوثيق</strong> — مركز المساعدة
        </footer>
    </div>
@endsection
