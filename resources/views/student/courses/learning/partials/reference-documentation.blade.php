@if(($courseReferenceDocs ?? collect())->isNotEmpty() || ($lessonReferenceDocs ?? collect())->isNotEmpty())
    <div class="card custom-card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0"><i class="fe fe-book me-2"></i>توثيقات مرتبطة</h6>
        </div>
        <div class="card-body py-2">
            @foreach($courseReferenceDocs ?? [] as $link)
                @php $doc = $link->documentationPage; @endphp
                @if($doc)
                    <a href="{{ $doc->publicUrl() }}" target="_blank" rel="noopener"
                       class="d-flex align-items-center gap-2 text-decoration-none mb-2">
                        <i class="fe fe-file-text text-primary"></i>
                        <span>{{ $doc->title }}</span>
                    </a>
                @endif
            @endforeach
            @foreach($lessonReferenceDocs ?? [] as $link)
                @php $doc = $link->documentationPage; @endphp
                @if($doc)
                    <a href="{{ $doc->publicUrl() }}" target="_blank" rel="noopener"
                       class="d-flex align-items-center gap-2 text-decoration-none mb-2">
                        <i class="fe fe-book-open text-info"></i>
                        <span>{{ $doc->title }} <small class="text-muted">(مرتبط بهذا الدرس)</small></span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
