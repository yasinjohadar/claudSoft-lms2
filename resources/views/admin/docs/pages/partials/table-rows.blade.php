@forelse($pages as $page)
<tr class="doc-pages-row">
    <td class="text-muted fw-semibold">{{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}</td>
    <td>
        <a href="{{ route('admin.docs.pages.edit', $page) }}" class="doc-cat-name-link fw-semibold d-block text-truncate" style="max-width: 280px;">
            {{ $page->title }}
        </a>
    </td>
    <td>
        @if($page->category)
            <a href="{{ route('admin.docs.categories.show', $page->category) }}" class="doc-cat-chip doc-cat-chip--section text-decoration-none">
                <i class="fe fe-folder"></i>{{ $page->category->name }}
            </a>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td>
        @if($page->parent)
            <small class="text-muted text-truncate d-block" style="max-width: 140px;">{{ $page->parent->title }}</small>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td><code class="doc-cat-slug">{{ $page->slug }}</code></td>
    <td>
        @if($page->status === 'published')
            <span class="doc-cat-status doc-cat-status--published">
                <span class="doc-cat-status__dot"></span>منشور
            </span>
        @else
            <span class="doc-cat-status doc-cat-status--draft">مسودة</span>
        @endif
    </td>
    <td><small class="text-muted">{{ $page->updated_at?->diffForHumans() }}</small></td>
    <td>
        <div class="doc-cat-actions">
            @if($page->isPublished())
                <button type="button"
                    class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--success"
                    data-bs-toggle="modal"
                    data-bs-target="#attachDocumentationModal"
                    data-page-id="{{ $page->id }}"
                    data-page-title="{{ $page->title }}"
                    data-page-category="{{ $page->category->name ?? '' }}"
                    data-page-slug="{{ $page->slug }}"
                    title="ربط بكورس">
                    <i class="fe fe-link"></i>
                </button>
            @endif
            <a href="{{ route('admin.docs.ai-pages.enhance', ['documentation_page_id' => $page->id]) }}"
               class="btn btn-sm doc-cat-action-btn"
               title="إضافة أفكار بالذكاء">
                <i class="fe fe-zap"></i>
            </a>
            <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $page->id]) }}"
               class="btn btn-sm doc-cat-action-btn"
               title="تحسين المحتوى">
                <i class="fe fe-tool"></i>
            </a>
            @if($page->category)
                <a href="{{ route('admin.docs.pages.pdf', $page) }}"
                   class="btn btn-sm doc-cat-action-btn"
                   target="_blank"
                   rel="noopener"
                   title="تصدير PDF">
                    <i class="fe fe-download"></i>
                </a>
            @endif
            @if($page->isPublished() && $page->category && $page->category->is_active)
                <a href="{{ route('frontend.docs.show', ['categorySlug' => $page->category->slug, 'pagePath' => $page->slugPathUnderCategory()]) }}"
                   class="btn btn-sm doc-cat-action-btn"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="عرض على الموقع">
                    <i class="fe fe-external-link"></i>
                </a>
            @else
                <button type="button" class="btn btn-sm doc-cat-action-btn" disabled title="يتطلب نشر الصفحة وقسمًا نشطًا">
                    <i class="fe fe-external-link"></i>
                </button>
            @endif
            <form action="{{ route('admin.docs.pages.toggle-publish', $page) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm doc-cat-action-btn"
                        title="{{ $page->status === 'published' ? 'إلغاء النشر' : 'نشر' }}">
                    <i class="fe {{ $page->status === 'published' ? 'fe-eye-off' : 'fe-eye' }}"></i>
                </button>
            </form>
            <a href="{{ route('admin.docs.pages.edit', $page) }}"
               class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary"
               title="تعديل">
                <i class="fe fe-edit-2"></i>
            </a>
            <form action="{{ route('admin.docs.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف «{{ $page->title }}»؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--danger" title="حذف">
                    <i class="fe fe-trash-2"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <div class="doc-cat-empty">
            <div class="doc-cat-empty__icon"><i class="fe fe-file-text"></i></div>
            <h6 class="mb-1">لا توجد صفحات بعد</h6>
            <p class="text-muted small mb-3">ابدأ بإنشاء أول مقال توثيق أو توليده بالذكاء الاصطناعي.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-primary btn-sm">
                    <i class="fe fe-plus me-1"></i>إضافة صفحة
                </a>
                <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fe fe-zap me-1"></i>توليد بالذكاء
                </a>
            </div>
        </div>
    </td>
</tr>
@endforelse
