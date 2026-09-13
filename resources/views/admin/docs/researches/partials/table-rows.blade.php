@forelse($researches as $research)
<tr class="doc-cat-row">
    <td class="text-muted fw-semibold">{{ $loop->iteration + ($researches->currentPage() - 1) * $researches->perPage() }}</td>
    <td>
        <a href="{{ route('admin.docs.researches.show', $research) }}" class="doc-cat-name-link fw-semibold d-block">
            {{ $research->name }}
        </a>
        @if($research->description)
            <small class="text-muted d-block">{{ Str::limit($research->description, 60) }}</small>
        @endif
    </td>
    <td>
        @if($research->category)
            <a href="{{ route('admin.docs.categories.show', $research->category) }}" class="doc-cat-chip doc-cat-chip--section text-decoration-none">
                <i class="fe fe-folder"></i>{{ $research->category->name }}
            </a>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td><code class="doc-cat-slug">{{ $research->slug }}</code></td>
    <td>
        <a href="{{ route('admin.docs.researches.show', $research) }}" class="doc-cat-pages-link text-decoration-none">
            <span class="doc-cat-pages-link__count">{{ $research->pages_count }}</span> صفحة
        </a>
    </td>
    <td><span class="doc-cat-order">{{ $research->sort_order }}</span></td>
    <td>
        <span class="doc-cat-status doc-cat-status--{{ $research->is_active ? 'active' : 'inactive' }}">
            <span class="doc-cat-status__dot"></span>{{ $research->is_active ? 'مفعّل' : 'معطّل' }}
        </span>
    </td>
    <td>
        <div class="doc-cat-actions">
            <form action="{{ route('admin.docs.researches.toggle-active', $research) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm doc-cat-action-btn"
                        title="{{ $research->is_active ? 'تعطيل' : 'تفعيل' }}">
                    <i class="fe {{ $research->is_active ? 'fe-eye-off' : 'fe-eye' }}"></i>
                </button>
            </form>
            <a href="{{ route('admin.docs.researches.show', $research) }}" class="btn btn-sm doc-cat-action-btn" title="عرض التوثيقات">
                <i class="fe fe-list"></i>
            </a>
            <a href="{{ route('admin.docs.researches.edit', $research) }}" class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary" title="تعديل">
                <i class="fe fe-edit-2"></i>
            </a>
            <form action="{{ route('admin.docs.researches.destroy', $research) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('حذف البحث «{{ $research->name }}»؟ {{ $research->pages_count }} صفحة ستُصبح بلا بحث (لن تُحذف).');">
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
            <div class="doc-cat-empty__icon"><i class="fe fe-search"></i></div>
            <p class="mb-1 fw-semibold">لا توجد أبحاث</p>
            <p class="text-muted mb-0">
                البحث يجمع توثيقات موضوع واحد داخل قسم — مثل «الدوال» داخل php.
                <a href="{{ route('admin.docs.researches.create') }}">أضف أول بحث</a>.
            </p>
        </div>
    </td>
</tr>
@endforelse
