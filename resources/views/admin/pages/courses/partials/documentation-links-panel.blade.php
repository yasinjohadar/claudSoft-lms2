<div class="card custom-card border mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="card-title mb-0">
            <i class="fe fe-book me-2"></i>توثيقات مرتبطة (مراجع)
        </div>
        <button type="button"
                class="btn btn-sm btn-primary-light rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#attachDocumentationModal">
            <i class="fe fe-plus me-1"></i>ربط توثيق
        </button>
    </div>
    <div class="card-body p-0">
        @if(($courseReferenceLinks ?? collect())->isEmpty())
            <div class="p-4 text-muted text-center fs-13">
                لا توجد صفحات توثيق مرتبطة كمراجع عامة لهذا الكورس.
                يمكنك أيضاً إضافتها للمنهج عبر زر «توثيق» داخل القسم.
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($courseReferenceLinks as $link)
                    @php $doc = $link->documentationPage; @endphp
                    @if($doc)
                        <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <strong>{{ $doc->title }}</strong>
                                @if($doc->category)
                                    <span class="group-show-chip group-show-chip--sm text-muted ms-1">{{ $doc->category->name }}</span>
                                @endif
                                @if($doc->excerpt)
                                    <div class="text-muted fs-12 mt-1">{{ Str::limit($doc->excerpt, 120) }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ $doc->publicUrl() }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-info-light rounded-pill">
                                    <i class="fe fe-external-link"></i> عرض
                                </a>
                                <a href="{{ route('admin.docs.pages.edit', $doc) }}"
                                   class="btn btn-sm btn-primary-light rounded-pill">
                                    <i class="fe fe-edit-2"></i>
                                </a>
                                <form action="{{ route('documentation-page-links.destroy', $link) }}" method="POST"
                                      onsubmit="return confirm('إزالة ربط هذا التوثيق؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger-light rounded-pill">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
