@forelse($pages as $page)
<tr>
    <td>{{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}</td>
    <td><strong>{{ $page->title }}</strong></td>
    <td>{{ $page->category->name ?? '—' }}</td>
    <td>{{ $page->parent->title ?? '—' }}</td>
    <td><code>{{ $page->slug }}</code></td>
    <td>
        @if($page->status === 'published')
            <span class="badge bg-success">منشور</span>
        @else
            <span class="badge bg-secondary">مسودة</span>
        @endif
    </td>
    <td><small class="text-muted">{{ $page->updated_at?->diffForHumans() }}</small></td>
    <td class="d-flex flex-wrap gap-1 align-items-center">
        <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $page->id]) }}"
            class="btn btn-sm btn-outline-primary"
            title="فتح هذه الصفحة في أداة الفحص والتعديل بالتعليمات">فحص بالذكاء</a>
        @if($page->isPublished() && $page->category && $page->category->is_active)
            <a href="{{ route('frontend.docs.show', ['categorySlug' => $page->category->slug, 'pagePath' => $page->slugPathUnderCategory()]) }}"
                class="btn btn-sm btn-outline-info" target="_blank" rel="noopener noreferrer" title="فتح الصفحة على الموقع">عرض المقال</a>
        @else
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="يتطلب نشر الصفحة وقسمًا نشطًا">عرض المقال</button>
        @endif
        <form action="{{ route('admin.docs.pages.toggle-publish', $page) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary" title="نشر / إلغاء نشر">نشر</button>
        </form>
        <a href="{{ route('admin.docs.pages.edit', $page) }}" class="btn btn-sm btn-primary">تعديل</a>
        <form action="{{ route('admin.docs.pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الصفحة؟');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="8" class="text-center py-4 text-muted">لا توجد صفحات بعد</td></tr>
@endforelse
