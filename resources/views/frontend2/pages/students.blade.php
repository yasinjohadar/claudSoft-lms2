@extends('frontend2.layouts.master')

@php
    $pageTitle = 'الطلاب المميزون | أكاديمية كلاودسوفت';
    $pageDescription = 'تعرّف على طلابنا المميزين في المنصة — ملفات عامة، إنجازات وتعلّم مستمر. ابحث بالاسم أو البريد أو الهاتف.';
    $canonicalUrl = $students->url($students->currentPage());
    $ogImage = asset('frontend2/assets/images/logo.png');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SY">
    <meta property="og:site_name" content="{{ config('app.name', 'أكاديمية كلاودسوفت') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "الرئيسية",
                "item": "{{ route('frontend.home') }}"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "الطلاب المميزون",
                "item": "{{ route('frontend.students.index') }}"
            }
        ]
    }
    </script>
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $pageTitle,
    'description' => Str::limit(strip_tags($pageDescription), 300),
    'url' => route('frontend.students.index'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
    @if($students->count() > 0)
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => $students->values()->map(function ($s, $idx) use ($students) {
        $position = $idx + 1 + ($students->currentPage() - 1) * $students->perPage();
        return [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => [
                '@type' => 'Person',
                'name' => $s->name_ar ?: $s->name,
                'url' => route('frontend.students.show', $s->id),
            ],
        ];
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
    @endif
@endpush

@section('content')

    <section class="page-banner page-banner-students">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-user-graduate"></i></div>
                <h1 class="page-banner-title">الطلاب <span>المميزون</span></h1>
                <p class="page-banner-desc">مجتمع المتعلمين في أكاديمية كلاودسوفت — ملفات عامة، إنجازات ومسيرة تعليمية يمكنك استكشافها</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>الطلاب</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding students-page-section">
        <div class="container">
            <div class="glass-panel students-search-panel animate-on-scroll mb-4 mb-lg-5">
                <form action="{{ route('frontend.students.index') }}" method="GET" class="students-search-form" role="search">
                    <label class="form-label students-search-label" for="students-search-input">البحث عن طالب</label>
                    <div class="input-group students-search-input-group">
                        <span class="input-group-text students-search-addon" id="search-addon"><i class="fas fa-magnifying-glass"></i></span>
                        <input
                            id="students-search-input"
                            type="search"
                            name="search"
                            class="form-control students-search-control"
                            placeholder="الاسم، البريد، أو رقم الهاتف…"
                            value="{{ request('search') }}"
                            autocomplete="off"
                            aria-describedby="search-addon"
                        >
                        @if(request('search'))
                            <a href="{{ route('frontend.students.index') }}" class="btn btn-outline-secondary students-search-clear" title="مسح البحث" aria-label="مسح البحث"><i class="fas fa-times"></i></a>
                        @endif
                        <button type="submit" class="btn btn-primary-custom students-search-submit">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </form>
            </div>

            @if(request('search'))
                <div class="alert alert-info students-search-alert animate-on-scroll d-flex align-items-center gap-2 flex-wrap" role="status">
                    <i class="fas fa-info-circle"></i>
                    <span>نتائج البحث عن «<strong>{{ request('search') }}</strong>» — <strong>{{ $students->total() }}</strong> نتيجة</span>
                </div>
            @endif

            <div class="scards-grid">
                @forelse($students as $student)
                    @php
                        $displayName = trim((string) ($student->name_ar ?: $student->name));
                        $initial = mb_substr($displayName !== '' ? $displayName : '؟', 0, 1);
                    @endphp
                    <a href="{{ route('frontend.students.show', $student->id) }}" class="scard animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                        <div class="scard__avatar">
                            @if($student->avatar)
                                <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $displayName }}" width="88" height="88" loading="lazy">
                            @else
                                <span class="scard__initial" aria-hidden="true">{{ $initial }}</span>
                            @endif
                        </div>
                        <div class="scard__body">
                            <h2 class="scard__name">{{ $displayName }}</h2>
                            <p class="scard__meta"><i class="fas fa-calendar-plus"></i> انضم {{ $student->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="scard__footer">
                            <span class="scard__cta">عرض الملف الشخصي <i class="fas fa-arrow-left"></i></span>
                        </div>
                    </a>
                @empty
                    <div class="scards-empty">
                        <div class="glass-panel students-empty-state text-center py-5 px-4 animate-on-scroll">
                            @if(request('search'))
                                <i class="fas fa-search fa-3x students-empty-icon mb-3" aria-hidden="true"></i>
                                <h2 class="h4 fw-bold mb-2" style="color: var(--clr-text);">لم يُعثر على نتائج</h2>
                                <p class="mb-4" style="color: var(--clr-text-secondary); max-width: 420px; margin-left: auto; margin-right: auto;">لا يوجد طلاب يطابقون «{{ request('search') }}». جرّب كلمات أخرى أو امسح البحث.</p>
                                <a href="{{ route('frontend.students.index') }}" class="btn-primary-custom" style="justify-content: center; display: inline-flex;"><i class="fas fa-arrow-right"></i> عرض جميع الطلاب</a>
                            @else
                                <i class="fas fa-users fa-3x students-empty-icon mb-3" aria-hidden="true"></i>
                                <h2 class="h4 fw-bold mb-2" style="color: var(--clr-text);">لا يوجد طلاب معروضون حالياً</h2>
                                <p class="mb-0" style="color: var(--clr-text-secondary);">عند تفعيل الملفات العامة سيظهر الطلاب هنا.</p>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            @if($students->hasPages())
                <div class="f2-pagination-wrap mt-5" aria-label="ترقيم الصفحات">
                    {{ $students->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

@endsection
