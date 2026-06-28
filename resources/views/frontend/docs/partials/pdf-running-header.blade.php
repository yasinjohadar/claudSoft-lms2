@php
    use App\Services\Documentation\DocumentationPdfBranding;
@endphp
<header class="docs-pdf-running-header" aria-hidden="true">
    <div class="docs-pdf-running-header__brand">
        <div class="docs-pdf-running-header__name">{{ DocumentationPdfBranding::organizationName() }}</div>
        <div class="docs-pdf-running-header__tagline">{{ DocumentationPdfBranding::tagline() }}</div>
    </div>
    <div class="docs-pdf-running-header__page">ص <span class="docs-pdf-page-num"></span></div>
</header>
