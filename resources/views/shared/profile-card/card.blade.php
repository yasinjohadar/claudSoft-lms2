@php
    $theme = $card->resolvedTheme();
    $accent = $theme['accent_color'] ?? '#2563eb';
    $displayNameAr = trim((string) ($user->name_ar ?? ''));
    $displayNameEn = trim((string) ($user->name ?? ''));
    $displayName = $displayNameAr ?: $displayNameEn ?: 'طالب';
    $photoPath = trim((string) ($user->photo ?: $user->avatar ?: ''));
    $hasCustomPhoto = $photoPath !== '';
    $photoUrl = $hasCustomPhoto ? student_profile_photo_url($user, $photoPath) : null;
    $genderAvatarView = student_gender_avatar_view($user);
    $siteName = config('app.name', 'أكاديمية كلاودسوفت');
    $socialLinks = $card->enabledSocialLinks();
    $socialPlatforms = config('profile-card.social_platforms', []);
    $resolveSocialPlatform = function (array $link) use ($socialPlatforms): string {
        $platform = $link['platform'] ?? null;
        if ($platform && isset($socialPlatforms[$platform])) {
            return $platform;
        }
        $icon = strtolower((string) ($link['icon'] ?? ''));
        foreach ($socialPlatforms as $key => $preset) {
            if ($icon === strtolower((string) ($preset['default_icon'] ?? ''))) {
                return $key;
            }
        }
        $iconMap = [
            'facebook' => 'facebook',
            'instagram' => 'instagram',
            'linkedin' => 'linkedin',
            'twitter' => 'twitter',
            'x-twitter' => 'twitter',
            'github' => 'github',
            'youtube' => 'youtube',
            'tiktok' => 'tiktok',
            'whatsapp' => 'whatsapp',
            'telegram' => 'telegram',
            'globe' => 'website',
            'envelope' => 'email',
        ];
        foreach ($iconMap as $needle => $key) {
            if (str_contains($icon, $needle) && isset($socialPlatforms[$key])) {
                return $key;
            }
        }

        return 'custom';
    };
    $qrSvg = null;
    if ($card->qr_enabled && $card->qr_code_path) {
        $qrFile = storage_path('app/public/'.$card->qr_code_path);
        if (is_file($qrFile)) {
            $qrSvg = file_get_contents($qrFile);
        }
    }
@endphp

<style>
    .profile-card-page-body {
        margin: 0;
        min-height: 100vh;
        color: #0f172a;
        font-family: 'Cairo', sans-serif;
        -webkit-font-smoothing: antialiased;
        background: #ffffff;
    }

    .profile-card-shell {
        --card-accent: {{ $accent }};
        --site-primary: #0066B3;
        --site-primary-light: #3399E0;
        --site-primary-dark: #004C8A;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: #ffffff;
    }

    .profile-card {
        position: relative;
        isolation: isolate;
        width: 100%;
        max-width: 440px;
        background: linear-gradient(
            145deg,
            rgba(255, 255, 255, 0.82) 0%,
            rgba(255, 255, 255, 0.58) 42%,
            rgba(0, 102, 179, 0.1) 100%
        );
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: 5px;
        box-shadow:
            0 8px 32px rgba(0, 76, 138, 0.14),
            0 2px 10px rgba(0, 102, 179, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            inset 0 -1px 0 rgba(0, 102, 179, 0.06);
        overflow: hidden;
        transition: transform 0.35s cubic-bezier(.2,.8,.2,1), box-shadow 0.35s ease;
        animation: profileCardIn 0.55s ease both;
    }

    .profile-card > :not(.profile-card__bg-pattern) {
        position: relative;
        z-index: 1;
    }

    .profile-card__bg-pattern {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: inherit;
        pointer-events: none;
        z-index: 0;
    }

    .profile-card__bg-grid {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .profile-card__grid-layer {
        position: absolute;
        inset: 0;
        will-change: background-position;
    }

    .profile-card__grid-layer--h {
        background-image: repeating-linear-gradient(
            180deg,
            transparent 0,
            transparent 31px,
            rgba(0, 102, 179, 0.045) 31px,
            rgba(0, 102, 179, 0.045) 32px
        );
        background-size: 100% 32px;
        animation: profileCardGridWeaveH 24s linear infinite;
    }

    .profile-card__grid-layer--v {
        background-image: repeating-linear-gradient(
            90deg,
            transparent 0,
            transparent 31px,
            rgba(51, 153, 224, 0.04) 31px,
            rgba(51, 153, 224, 0.04) 32px
        );
        background-size: 32px 100%;
        animation: profileCardGridWeaveV 28s linear infinite;
    }

    .profile-card__grid-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 30% 20%, rgba(51, 153, 224, 0.04), transparent 42%),
                    radial-gradient(circle at 75% 75%, rgba(0, 102, 179, 0.035), transparent 38%);
        animation: profileCardGridGlow 12s ease-in-out infinite alternate;
    }

    @keyframes profileCardGridWeaveH {
        0% { background-position: 0 0; }
        100% { background-position: 0 32px; }
    }

    @keyframes profileCardGridWeaveV {
        0% { background-position: 0 0; }
        100% { background-position: 32px 0; }
    }

    @keyframes profileCardGridGlow {
        0% { opacity: 0.45; }
        100% { opacity: 0.7; }
    }

    .profile-card__bg-orbs {
        position: absolute;
        inset: 0;
    }

    .profile-card__orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(28px);
        opacity: 0.45;
        will-change: transform;
    }

    .profile-card__orb--1 {
        width: 160px;
        height: 160px;
        top: -40px;
        right: -30px;
        background: radial-gradient(circle, rgba(51, 153, 224, 0.55), rgba(51, 153, 224, 0));
        animation: profileCardOrbFloat1 14s ease-in-out infinite;
    }

    .profile-card__orb--2 {
        width: 130px;
        height: 130px;
        bottom: 60px;
        left: -35px;
        background: radial-gradient(circle, rgba(0, 102, 179, 0.4), rgba(0, 102, 179, 0));
        animation: profileCardOrbFloat2 18s ease-in-out infinite;
    }

    .profile-card__orb--3 {
        width: 100px;
        height: 100px;
        top: 42%;
        left: 58%;
        background: radial-gradient(circle, rgba(142, 197, 239, 0.5), rgba(142, 197, 239, 0));
        animation: profileCardOrbFloat3 16s ease-in-out infinite;
    }

    @keyframes profileCardOrbFloat1 {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(-12px, 18px) scale(1.06); }
    }

    @keyframes profileCardOrbFloat2 {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(16px, -14px) scale(1.08); }
    }

    @keyframes profileCardOrbFloat3 {
        0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.35; }
        50% { transform: translate(-10px, 12px) scale(1.1); opacity: 0.55; }
    }

    @media (prefers-reduced-motion: reduce) {
        .profile-card__orb,
        .profile-card__grid-layer,
        .profile-card__grid-glow,
        .profile-card__accent-bar::after {
            animation: none !important;
        }
    }

    .profile-card:hover {
        transform: translateY(-4px);
        box-shadow:
            0 14px 40px rgba(0, 76, 138, 0.18),
            0 4px 14px rgba(0, 102, 179, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            inset 0 -1px 0 rgba(0, 102, 179, 0.08);
    }

    @keyframes profileCardIn {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-card__accent-bar {
        height: 4px;
        background: linear-gradient(90deg, var(--site-primary), var(--site-primary-light));
        position: relative;
        overflow: hidden;
    }

    .profile-card__accent-bar::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.55), transparent);
        animation: profileCardBarShimmer 4s ease-in-out infinite;
    }

    @keyframes profileCardBarShimmer {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(120%); }
    }

    .profile-card__hero {
        padding: 2rem 1.75rem 1.25rem;
        text-align: center;
    }

    .profile-card__avatar-wrap {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 1.15rem;
        border-radius: 50%;
        padding: 4px;
        background: linear-gradient(145deg, var(--card-accent), color-mix(in srgb, var(--card-accent) 40%, #cbd5e1));
        box-shadow: 0 8px 24px color-mix(in srgb, var(--card-accent) 28%, transparent);
        transition: transform 0.3s ease;
        overflow: hidden;
    }

    .profile-card:hover .profile-card__avatar-wrap {
        transform: scale(1.03);
    }

    .profile-card__avatar,
    .profile-card__avatar-svg {
        position: absolute;
        top: 4px;
        left: 4px;
        width: calc(100% - 8px);
        height: calc(100% - 8px);
        border-radius: 50%;
        object-fit: cover;
        display: block;
        border: 3px solid #fff;
        background: #f1f5f9;
        overflow: hidden;
    }

    .profile-card__avatar.is-hidden,
    .profile-card__avatar-svg.is-hidden {
        display: none !important;
    }

    .profile-card__avatar-svg svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .profile-card__name-ar {
        font-family: 'Alexandria', sans-serif;
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.35rem;
        letter-spacing: -0.02em;
    }

    .profile-card__name-en {
        font-size: 0.98rem;
        color: #64748b;
        margin: 0 0 0.75rem;
        direction: ltr;
        font-weight: 500;
    }

    .profile-card__job {
        font-family: 'Cairo', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.9rem;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(0, 102, 179, 0.18);
        color: var(--site-primary);
        font-size: 0.875rem;
        font-weight: 600;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .profile-card__bio {
        padding: 0 1.75rem 1.25rem;
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.75;
        text-align: center;
        position: relative;
    }

    .profile-card__bio::before,
    .profile-card__bio::after {
        display: inline;
        font-family: 'Alexandria', sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: rgba(0, 102, 179, 0.32);
        line-height: inherit;
        vertical-align: baseline;
    }

    .profile-card__bio::before {
        content: '«';
        margin-left: 0.25rem;
    }

    .profile-card__bio::after {
        content: '»';
        margin-right: 0.25rem;
    }

    .profile-card__social-section {
        padding: 0 1.75rem 1.25rem;
        text-align: center;
    }

    .profile-card__social-title {
        font-family: 'Alexandria', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.85rem;
        letter-spacing: -0.01em;
    }

    .profile-card__social {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.55rem;
    }

    .profile-card__social-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 5px;
        border: none;
        color: #ffffff;
        text-decoration: none;
        font-size: 1.2rem;
        transition: transform 0.22s ease, filter 0.22s ease, box-shadow 0.22s ease;
        position: relative;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.14);
    }

    .profile-card__social-link:hover {
        transform: translateY(-3px) scale(1.04);
        filter: brightness(1.08);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.2);
    }

    .profile-card__social-link .social-tooltip {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) scale(0.9);
        opacity: 0;
        pointer-events: none;
        background: #0f172a;
        color: #fff;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .profile-card__social-link:hover .social-tooltip {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }

    .profile-card__footer {
        padding: 1.15rem 1.75rem 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.5);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(0, 102, 179, 0.06));
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .profile-card__qr-label {
        font-family: 'Cairo', sans-serif;
        font-size: 0.78rem;
        color: #64748b;
        margin: 0 0 0.35rem;
        text-align: center;
    }

    .profile-card__brand {
        font-family: 'Cairo', sans-serif;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        margin-top: 0.15rem;
        font-size: 0.72rem;
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .profile-card__brand:hover {
        color: var(--site-primary);
    }

    .profile-card__brand img {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-card__qr {
        padding: 0.65rem;
        background: rgba(255, 255, 255, 0.62);
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: 5px;
        line-height: 0;
        transition: transform 0.25s ease;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: 0 4px 16px rgba(0, 102, 179, 0.08);
    }

    .profile-card__qr:hover {
        transform: scale(1.02);
    }

    .profile-card__qr svg {
        width: 112px;
        height: 112px;
        display: block;
    }

    .profile-card__share {
        font-family: 'Cairo', sans-serif;
        width: 100%;
        border: none;
        border-radius: 5px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, var(--site-primary), var(--site-primary-dark));
        box-shadow: 0 8px 20px rgba(0, 102, 179, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .profile-card__share:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
        box-shadow: 0 10px 24px rgba(0, 102, 179, 0.34);
    }

    .profile-card__share:active {
        transform: translateY(0);
    }

    .profile-card__share.is-success {
        background: linear-gradient(135deg, #059669, #047857);
    }

    .profile-card-preview-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: #f59e0b;
        color: #111;
        text-align: center;
        padding: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    @media (max-width: 480px) {
        .profile-card-shell { padding: 1rem 0.75rem; }
        .profile-card__hero { padding: 1.5rem 1.25rem 1rem; }
        .profile-card__bio { padding: 0 1.25rem 1rem; }
        .profile-card__social-section { padding: 0 1.25rem 1rem; }
    }
</style>

@if(!empty($isPreview))
    <div class="profile-card-preview-banner">معاينة — هذه الصفحة غير عامة</div>
@endif

<div class="profile-card-shell">
    <article class="profile-card" id="profileCard">
        <div class="profile-card__bg-pattern" aria-hidden="true">
            <div class="profile-card__bg-grid">
                <div class="profile-card__grid-layer profile-card__grid-layer--h"></div>
                <div class="profile-card__grid-layer profile-card__grid-layer--v"></div>
                <div class="profile-card__grid-glow"></div>
            </div>
            <div class="profile-card__bg-orbs">
                <span class="profile-card__orb profile-card__orb--1"></span>
                <span class="profile-card__orb profile-card__orb--2"></span>
                <span class="profile-card__orb profile-card__orb--3"></span>
            </div>
        </div>
        <div class="profile-card__accent-bar" aria-hidden="true"></div>

        <div class="profile-card__hero">
            <div class="profile-card__avatar-wrap">
                @if($hasCustomPhoto && $photoUrl)
                    <img src="{{ $photoUrl }}"
                         alt="{{ $displayName }}"
                         class="profile-card__avatar"
                         id="profileCardAvatar"
                         onerror="this.classList.add('is-hidden');var el=document.getElementById('profileCardAvatarDefault');if(el){el.classList.remove('is-hidden');}">
                    <div class="profile-card__avatar-svg is-hidden"
                         id="profileCardAvatarDefault"
                         aria-hidden="true">
                        @include($genderAvatarView, ['uid' => 'profile-card'])
                    </div>
                @else
                    <div class="profile-card__avatar-svg" id="profileCardAvatarDefault" aria-hidden="false">
                        @include($genderAvatarView, ['uid' => 'profile-card'])
                    </div>
                @endif
            </div>

            @if($displayNameAr)
                <h1 class="profile-card__name-ar">{{ $displayNameAr }}</h1>
            @endif
            @if($displayNameEn)
                <p class="profile-card__name-en">{{ $displayNameEn }}</p>
            @endif
            @if($card->job_title)
                <span class="profile-card__job">
                    <i class="fas fa-briefcase" style="font-size:0.8rem;"></i>
                    {{ $card->job_title }}
                </span>
            @endif
        </div>

        @if($card->bio)
            <div class="profile-card__bio">{{ $card->bio }}</div>
        @endif

        @if(count($socialLinks) > 0)
            <section class="profile-card__social-section" aria-labelledby="profileCardSocialTitle">
                <h2 class="profile-card__social-title" id="profileCardSocialTitle">تواصل معي</h2>
                <div class="profile-card__social">
                @foreach($socialLinks as $link)
                    @php
                        $platform = $resolveSocialPlatform($link);
                        $preset = $socialPlatforms[$platform] ?? $socialPlatforms['custom'];
                        if (! empty($preset['brand_gradient'])) {
                            $socialStyle = 'background:'.$preset['brand_gradient'].';';
                        } elseif (! empty($preset['brand_color'])) {
                            $socialStyle = 'background:'.$preset['brand_color'].';';
                        } else {
                            $socialStyle = 'background:var(--card-accent);';
                        }
                    @endphp
                    <a href="{{ $link['url'] }}"
                       class="profile-card__social-link profile-card__social-link--{{ $platform }}"
                       style="{{ $socialStyle }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="{{ $link['label'] ?? 'رابط' }}">
                        <span class="social-tooltip">{{ $link['label'] ?? 'رابط' }}</span>
                        <i class="{{ $link['icon'] ?? ($preset['default_icon'] ?? 'fas fa-link') }}" aria-hidden="true"></i>
                    </a>
                @endforeach
                </div>
            </section>
        @endif

        <div class="profile-card__footer">
            @if($qrSvg)
                <p class="profile-card__qr-label">امسح للوصول السريع</p>
                <div class="profile-card__qr" aria-label="رمز QR">{!! $qrSvg !!}</div>
            @endif

            @if(empty($isPreview))
                <button type="button" class="profile-card__share" id="shareProfileCard">
                    <i class="fas fa-share-nodes"></i>
                    <span id="shareProfileCardLabel">مشاركة البطاقة</span>
                </button>
            @endif

            @if(empty($isPreview))
                <a href="{{ url('/') }}" class="profile-card__brand" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('frontend2/assets/images/logo.png') }}" alt="">
                    <span>{{ $siteName }}</span>
                </a>
            @endif
        </div>
    </article>
</div>

@if(empty($isPreview))
<script>
(function () {
    var publicUrl = @json($card->public_url);
    var shareBtn = document.getElementById('shareProfileCard');
    var shareLabel = document.getElementById('shareProfileCardLabel');

    if (!shareBtn) return;

    shareBtn.addEventListener('click', function () {
        var title = document.title;
        if (navigator.share) {
            navigator.share({ title: title, url: publicUrl }).catch(function () {});
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(publicUrl).then(function () {
                shareBtn.classList.add('is-success');
                if (shareLabel) shareLabel.textContent = 'تم نسخ الرابط للمشاركة';
                setTimeout(function () {
                    shareBtn.classList.remove('is-success');
                    if (shareLabel) shareLabel.textContent = 'مشاركة البطاقة';
                }, 1800);
            });
        }
    });

    var avatar = document.getElementById('profileCardAvatar');
    if (avatar) {
        avatar.addEventListener('error', function () {
            avatar.style.display = 'none';
            var fallback = document.getElementById('profileCardAvatarDefault');
            if (fallback) {
                fallback.hidden = false;
            }
        });
    }
})();
</script>
@endif
