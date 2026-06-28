@forelse($categories as $category)
<tr class="doc-cat-row">
    <td class="text-muted fw-semibold">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
    <td>
        <div class="d-flex align-items-center gap-3">
            <div class="doc-cat-icon @if($category->kind === 'technology') doc-cat-icon--tech @else doc-cat-icon--section @endif">
                @if($category->icon)
                    <i class="{{ $category->icon }}"></i>
                @elseif($category->kind === 'technology')
                    <i class="fe fe-code"></i>
                @else
                    <i class="fe fe-book-open"></i>
                @endif
            </div>
            <div class="min-w-0">
                <a href="{{ route('admin.docs.categories.show', $category) }}" class="doc-cat-name-link fw-semibold text-truncate d-block">
                    {{ $category->name }}
                </a>
                @if($category->parent)
                    <small class="text-muted d-block text-truncate">
                        <i class="fe fe-corner-down-left me-1"></i>{{ $category->parent->name }}
                    </small>
                @elseif($category->description)
                    <small class="text-muted d-block text-truncate">{{ Str::limit($category->description, 60) }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        @if($category->kind === 'technology')
            <span class="doc-cat-chip doc-cat-chip--tech">
                <i class="fe fe-cpu"></i>تقنية
            </span>
        @else
            <span class="doc-cat-chip doc-cat-chip--section">
                <i class="fe fe-layers"></i>قسم
            </span>
        @endif
    </td>
    <td>
        <code class="doc-cat-slug">{{ $category->slug }}</code>
    </td>
    <td>
        <a href="{{ route('admin.docs.categories.show', $category) }}"
           class="doc-cat-pages-link"
           title="فتح صفحة القسم في لوحة التحكم">
            <span class="doc-cat-pages-count">{{ $category->pages_count }}</span>
            <span class="text-muted small">صفحة</span>
        </a>
    </td>
    <td>
        <span class="doc-cat-order">{{ $category->sort_order }}</span>
    </td>
    <td>
        @if($category->is_active)
            <span class="doc-cat-status doc-cat-status--active">
                <span class="doc-cat-status__dot"></span>مفعّل
            </span>
        @else
            <span class="doc-cat-status doc-cat-status--inactive">
                <span class="doc-cat-status__dot"></span>معطّل
            </span>
        @endif
    </td>
    <td>
        <div class="doc-cat-actions">
            <a href="{{ route('admin.docs.categories.show', $category) }}"
               class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary"
               title="فتح صفحة القسم">
                <i class="fe fe-folder"></i>
            </a>
            <form action="{{ route('admin.docs.categories.toggle-active', $category) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm doc-cat-action-btn"
                        title="{{ $category->is_active ? 'تعطيل' : 'تفعيل' }}">
                    <i class="fe {{ $category->is_active ? 'fe-eye-off' : 'fe-eye' }}"></i>
                </button>
            </form>
            <a href="{{ route('admin.docs.categories.edit', $category) }}"
               class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary"
               title="تعديل">
                <i class="fe fe-edit-2"></i>
            </a>
            <form action="{{ route('admin.docs.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف القسم «{{ $category->name }}»؟');">
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
            <div class="doc-cat-empty__icon"><i class="fe fe-folder"></i></div>
            <h6 class="mb-1">لا توجد أقسام بعد</h6>
            <p class="text-muted small mb-3">ابدأ بإنشاء أول قسم توثيق لتنظيم المقالات.</p>
            <a href="{{ route('admin.docs.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fe fe-plus me-1"></i>إضافة قسم
            </a>
        </div>
    </td>
</tr>
@endforelse
