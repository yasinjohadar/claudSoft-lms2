@php
    $depth = $depth ?? 0;
@endphp

@foreach ($nodes as $node)
    @php
        /** @var \App\Models\DocumentationPage $page */
        $page = $node['page'];
        $children = $node['children'] ?? [];
        $depthClass = $depth > 0 ? 'doc-cat-page-title--depth-' . min($depth, 3) : '';
    @endphp

    <tr class="doc-cat-row">
        <td>
            <div class="doc-cat-page-title {{ $depthClass }}">
                @if ($depth > 0)
                    <span class="doc-cat-page-indent">
                        <i class="fe fe-corner-down-left"></i>فرعي
                    </span>
                @endif
                <a href="{{ route('admin.docs.pages.edit', $page) }}" class="doc-cat-name-link">
                    {{ $page->title }}
                </a>
                @if ($page->excerpt)
                    <div class="doc-cat-page-excerpt">{{ $page->excerpt }}</div>
                @endif
            </div>
        </td>
        <td><code class="doc-cat-slug">{{ $page->slug }}</code></td>
        <td>
            @if ($page->status === 'published')
                <span class="doc-cat-status doc-cat-status--published">
                    <span class="doc-cat-status__dot"></span>منشور
                </span>
            @else
                <span class="doc-cat-status doc-cat-status--draft">مسودة</span>
            @endif
        </td>
        <td><span class="doc-cat-order">{{ $page->sort_order }}</span></td>
        <td><small class="text-muted">{{ $page->updated_at?->diffForHumans() }}</small></td>
        <td>
            <div class="doc-cat-actions doc-cat-page-actions">
                @if($page->isPublished())
                    <button type="button"
                        class="btn btn-sm btn-outline-success"
                        data-bs-toggle="modal"
                        data-bs-target="#attachDocumentationModal"
                        data-page-id="{{ $page->id }}"
                        data-page-title="{{ $page->title }}"
                        data-page-category="{{ $category->name }}"
                        data-page-slug="{{ $page->slug }}"
                        title="ربط بكورس">
                        <i class="fe fe-link me-1"></i>ربط
                    </button>
                @endif
                <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $page->id]) }}"
                   class="btn btn-sm btn-outline-primary" title="فحص بالذكاء">
                    <i class="fe fe-zap"></i>
                </a>
                <a href="{{ route('admin.docs.pages.pdf', $page) }}"
                   class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener" title="PDF">
                    <i class="fe fe-download"></i>
                </a>
                @if($page->isPublished() && $category->is_active)
                    <a href="{{ route('frontend.docs.show', ['categorySlug' => $category->slug, 'pagePath' => $page->slugPathUnderCategory()]) }}"
                       class="btn btn-sm btn-outline-info" target="_blank" rel="noopener" title="عرض على الموقع">
                        <i class="fe fe-external-link"></i>
                    </a>
                @endif
                <form action="{{ route('admin.docs.pages.toggle-publish', $page) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="نشر / إلغاء">
                        <i class="fe fe-eye"></i>
                    </button>
                </form>
                <a href="{{ route('admin.docs.pages.edit', $page) }}" class="btn btn-sm btn-primary" title="تعديل">
                    <i class="fe fe-edit-2"></i>
                </a>
                <form action="{{ route('admin.docs.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف «{{ $page->title }}»؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                        <i class="fe fe-trash-2"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>

    @if (! empty($children))
        @include('admin.docs.categories.partials.page-tree-rows', [
            'nodes' => $children,
            'category' => $category,
            'depth' => $depth + 1,
        ])
    @endif
@endforeach
