@extends('frontend.docs.layout')

@section('title', 'التوثيق')

@section('content')
    <div class="container">
        <header>
            <div class="header-tag">مركز المساعدة</div>
            <h1>التوثيق</h1>
            <p class="header-desc">تصفّح الأدلة والشروحات حسب اللغة أو التقنية أو الموضوع.</p>
        </header>

        <section class="content-section">
            @if ($categories->isEmpty())
                <div class="text-block">لا توجد أقسام توثيق منشورة حالياً.</div>
            @else
                @if ($technologies->isNotEmpty())
                    <div class="section-title">لغات وتقنيات</div>
                    <p class="text-block">صفحة مخصصة لكل تقنية تعرض جميع مقالاتها ودروسها.</p>
                    <div class="course-grid docs-tech-grid">
                        @foreach ($technologies as $cat)
                            <a href="{{ route('frontend.docs.category', ['categorySlug' => $cat->slug]) }}" class="lesson-card docs-tech-card">
                                <div class="docs-tech-card__icon">
                                    @if ($cat->icon)
                                        <i class="{{ $cat->icon }}"></i>
                                    @else
                                        <i class="bi bi-code-slash"></i>
                                    @endif
                                </div>
                                <h2 class="lesson-title">{{ $cat->name }}</h2>
                                @if ($cat->description)
                                    <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($cat->description), 120) }}</p>
                                @endif
                                <div class="lesson-status">
                                    <span class="docs-tech-card__count">{{ $cat->published_pages_count }} مقال</span>
                                    <div class="start-btn">
                                        <span>فتح التوثيق</span>
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

                @if ($sections->isNotEmpty())
                    <div class="section-title @if($technologies->isNotEmpty()) mt-5 @endif">أقسام موضوعية</div>
                    <div class="course-grid">
                        @foreach ($sections as $cat)
                            <a href="{{ route('frontend.docs.category', ['categorySlug' => $cat->slug]) }}" class="lesson-card">
                                <div class="lesson-number">{{ $loop->iteration }}</div>
                                <h2 class="lesson-title">{{ $cat->name }}</h2>
                                @if ($cat->description)
                                    <p class="lesson-desc">{{ \Illuminate\Support\Str::limit(strip_tags($cat->description), 140) }}</p>
                                @endif
                                <div class="lesson-status">
                                    <span class="docs-tech-card__count">{{ $cat->published_pages_count }} مقال</span>
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
            @endif
        </section>

        <footer>
            <strong>التوثيق</strong> — مركز المساعدة
        </footer>
    </div>
@endsection
