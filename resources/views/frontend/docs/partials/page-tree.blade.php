@foreach ($nodes as $node)
    @php
        /** @var \App\Models\DocumentationPage $page */
        $page = $node['page'];
        $children = $node['children'] ?? [];
        $depth = $depth ?? 0;
    @endphp

    <div class="docs-page-node" style="--node-depth: {{ $depth }};">
        <a href="{{ route('frontend.docs.show', ['categorySlug' => $category->slug, 'pagePath' => $page->slugPathUnderCategory()]) }}"
           class="docs-page-card @if($depth > 0) docs-page-card--nested @endif">
            <div class="docs-page-card__index">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</div>
            <div class="docs-page-card__body">
                <h3 class="docs-page-card__title">{{ $page->title }}</h3>
                @if ($page->excerpt)
                    <p class="docs-page-card__excerpt">{{ $page->excerpt }}</p>
                @endif
            </div>
            <div class="docs-page-card__arrow" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        @if (! empty($children))
            <div class="docs-page-children">
                @include('frontend.docs.partials.page-tree', [
                    'nodes' => $children,
                    'category' => $category,
                    'depth' => $depth + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
