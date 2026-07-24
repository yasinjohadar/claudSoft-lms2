@php
    use App\Services\Documentation\DocumentationPdfBranding;

    $phones = DocumentationPdfBranding::phoneNumbers();
    $emails = DocumentationPdfBranding::emails();
    $website = DocumentationPdfBranding::website();
    $address = DocumentationPdfBranding::address();
@endphp
<footer class="docs-pdf-doc-footer" aria-label="تذييل الأكاديمية">
    <div class="docs-pdf-doc-footer__brand">
        <strong>{{ DocumentationPdfBranding::organizationName() }}</strong>
        <span>{{ DocumentationPdfBranding::tagline() }}</span>
    </div>
    <div class="docs-pdf-doc-footer__contacts">
        @if($phones !== [])
            <div class="docs-pdf-doc-footer__row">
                <span class="docs-pdf-doc-footer__label">الهاتف:</span>
                <span>{{ implode(' · ', $phones) }}</span>
            </div>
        @endif
        @if($emails !== [])
            <div class="docs-pdf-doc-footer__row">
                <span class="docs-pdf-doc-footer__label">البريد:</span>
                <span>{{ implode(' · ', $emails) }}</span>
            </div>
        @endif
        @if($website)
            <div class="docs-pdf-doc-footer__row">
                <span class="docs-pdf-doc-footer__label">الموقع:</span>
                <span>{{ $website }}</span>
            </div>
        @endif
        @if($address)
            <div class="docs-pdf-doc-footer__row">
                <span class="docs-pdf-doc-footer__label">العنوان:</span>
                <span>{{ $address }}</span>
            </div>
        @endif
    </div>
</footer>
