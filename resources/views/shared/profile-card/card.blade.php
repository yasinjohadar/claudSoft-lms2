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
        min-height: 100dvh;
        color: #0f172a;
        font-family: 'Cairo', sans-serif;
        -webkit-font-smoothing: antialiased;
        background: #ffffff;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }

    .profile-card-shell {
        --card-accent: {{ $accent }};
        --site-primary: #0066B3;
        --site-primary-light: #3399E0;
        --site-primary-dark: #004C8A;
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: #ffffff;
    }

    .profile-card {
        position: relative;
        width: 100%;
        max-width: 440px;
        background: #ffffff;
        border: 1px solid rgba(0, 102, 179, 0.1);
        border-radius: 12px;
        box-shadow: 0 8px 28px rgba(0, 76, 138, 0.12);
        overflow: hidden;
        animation: profileCardIn 0.35s ease both;
        contain: layout paint;
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
    }

    .profile-card__grid-layer--h {
        background-image: repeating-linear-gradient(
            180deg,
            transparent 0,
            transparent 31px,
            rgba(0, 102, 179, 0.04) 31px,
            rgba(0, 102, 179, 0.04) 32px
        );
        background-size: 100% 32px;
    }

    .profile-card__grid-layer--v {
        background-image: repeating-linear-gradient(
            90deg,
            transparent 0,
            transparent 31px,
            rgba(51, 153, 224, 0.035) 31px,
            rgba(51, 153, 224, 0.035) 32px
        );
        background-size: 32px 100%;
    }

    .profile-card__grid-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 30% 20%, rgba(51, 153, 224, 0.05), transparent 42%),
                    radial-gradient(circle at 75% 75%, rgba(0, 102, 179, 0.04), transparent 38%);
        opacity: 0.6;
    }

    @keyframes profileCardIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-card__accent-bar {
        height: 4px;
        background: linear-gradient(90deg, var(--site-primary), var(--site-primary-light));
    }

    @media (prefers-reduced-motion: reduce) {
        .profile-card {
            animation: none;
        }
    }

    @media (hover: hover) and (pointer: fine) {
        .profile-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .profile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 76, 138, 0.16);
        }

        .profile-card:hover .profile-card__avatar-wrap {
            transform: scale(1.03);
        }

        .profile-card__social-link:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
        }

        .profile-card__share:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .profile-card__qr:hover {
            transform: scale(1.02);
        }

        .profile-card__brand:hover {
            color: var(--site-primary);
        }
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
        box-shadow: 0 6px 18px color-mix(in srgb, var(--card-accent) 22%, transparent);
        overflow: hidden;
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
        border-radius: 8px;
        background: rgba(0, 102, 179, 0.06);
        border: 1px solid rgba(0, 102, 179, 0.14);
        color: var(--site-primary);
        font-size: 0.875rem;
        font-weight: 600;
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
        border-radius: 10px;
        border: none;
        color: #ffffff;
        text-decoration: none;
        font-size: 1.2rem;
        position: relative;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.12);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .profile-card__social-link:active {
        transform: scale(0.92);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1);
    }

    .profile-card__footer {
        padding: 1.15rem 1.75rem 1.5rem;
        border-top: 1px solid rgba(0, 102, 179, 0.08);
        background: rgba(0, 102, 179, 0.03);
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
    }

    .profile-card__brand img {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-card__qr {
        padding: 0.65rem;
        background: #ffffff;
        border: 1px solid rgba(0, 102, 179, 0.1);
        border-radius: 10px;
        line-height: 0;
        box-shadow: 0 2px 10px rgba(0, 102, 179, 0.06);
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
        border-radius: 10px;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        color: #fff;
        background: linear-gradient(135deg, var(--site-primary), var(--site-primary-dark));
        box-shadow: 0 6px 16px rgba(0, 102, 179, 0.22);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        -webkit-tap-highlight-color: transparent;
    }

    .profile-card__share:active {
        transform: scale(0.98);
        box-shadow: 0 3px 10px rgba(0, 102, 179, 0.18);
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
        .profile-card-shell { padding: 0.75rem 0.65rem; }
        .profile-card {
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 76, 138, 0.1);
        }
        .profile-card__hero { padding: 1.35rem 1.15rem 0.85rem; }
        .profile-card__name-ar { font-size: 1.45rem; }
        .profile-card__bio { padding: 0 1.15rem 0.85rem; font-size: 0.9rem; }
        .profile-card__social-section { padding: 0 1.15rem 0.85rem; }
        .profile-card__footer { padding: 1rem 1.15rem 1.25rem; }
        .profile-card__social-link { width: 44px; height: 44px; }
        .profile-card__qr svg { width: 100px; height: 100px; }
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
        </div>
        <div class="profile-card__accent-bar" aria-hidden="true"></div>

        <div class="profile-card__hero">
            <div class="profile-card__avatar-wrap">
                @if($hasCustomPhoto && $photoUrl)
                    <img src="{{ $photoUrl }}"
                         alt="{{ $displayName }}"
                         class="profile-card__avatar"
                         id="profileCardAvatar"
                         width="112"
                         height="112"
                         decoding="async"
                         fetchpriority="high"
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
        shareBtn.classList.add('is-pressed');
        setTimeout(function () { shareBtn.classList.remove('is-pressed'); }, 150);

        if (navigator.share) {
            navigator.share({ title: title, url: publicUrl }).catch(function () {});
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(publicUrl).then(function () {
                shareBtn.classList.add('is-success');
                if (shareLabel) shareLabel.textContent = 'تم نسخ الرابط';
                setTimeout(function () {
                    shareBtn.classList.remove('is-success');
                    if (shareLabel) shareLabel.textContent = 'مشاركة البطاقة';
                }, 1800);
            });
        }
    });
})();
</script>
@endif
