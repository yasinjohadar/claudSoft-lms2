@php
    use App\Services\Documentation\DocumentationPdfBranding;

    $logoSrc = DocumentationPdfBranding::logoDataUri() ?? DocumentationPdfBranding::logoUrl();
@endphp
<header class="docs-pdf-doc-header" aria-label="ترويسة الأكاديمية">
    <div class="docs-pdf-doc-header__inner">
        <div class="docs-pdf-doc-header__brand">
            @if($logoSrc)
                <img
                    src="{{ $logoSrc }}"
                    alt="{{ DocumentationPdfBranding::organizationName() }}"
                    class="docs-pdf-doc-header__logo"
                    width="56"
                    height="56"
                >
            @endif
            <div class="docs-pdf-doc-header__text">
                <div class="docs-pdf-doc-header__name">{{ DocumentationPdfBranding::organizationName() }}</div>
                <div class="docs-pdf-doc-header__tagline">{{ DocumentationPdfBranding::tagline() }}</div>
                <div class="docs-pdf-doc-header__trainer">المدرب: ياسين محمد جوخدار</div>
            </div>
        </div>
        <div class="docs-pdf-doc-header__meta">
            <div class="docs-pdf-doc-header__site">{{ DocumentationPdfBranding::website() }}</div>
        </div>
    </div>
</header>
