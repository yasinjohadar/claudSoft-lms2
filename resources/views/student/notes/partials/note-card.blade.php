@php
    $categories = \App\Models\Note::getCategories();
    $categoryInfo = $categories[$note->category] ?? $categories['personal'];
@endphp

<div class="card note-card {{ $pinned ? 'pinned' : '' }}" style="border-left-color: {{ $note->color }}; cursor: pointer;" onclick="viewNote({
    id: {{ $note->id }},
    title: {{ json_encode($note->title) }},
    content: {{ json_encode($note->content) }},
    category: {{ json_encode($note->category) }},
    color: {{ json_encode($note->color) }},
    is_important: {{ $note->is_important ? 'true' : 'false' }},
    is_pinned: {{ $note->is_pinned ? 'true' : 'false' }},
    reminder_at: {{ $note->reminder_at ? json_encode($note->reminder_at->format('Y-m-d H:i:s')) : 'null' }},
    created_at: {{ json_encode($note->created_at->format('Y-m-d H:i:s')) }}
})">
    <div class="card-body">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
                <h5 class="card-title mb-1">{{ $note->title }}</h5>
                <span class="category-badge" style="background-color: {{ $categoryInfo['color'] }}20; color: {{ $categoryInfo['color'] }};">
                    {{ $categoryInfo['icon'] }} {{ $categoryInfo['name'] }}
                </span>
            </div>
            <div class="dropdown" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" onclick="event.stopPropagation();">
                    <i class="ri-more-2-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick='event.stopPropagation(); editNote(@json($note));'>
                            <i class="ri-edit-line me-2"></i>تعديل
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick="event.stopPropagation(); togglePin({{ $note->id }});">
                            <i class="ri-pushpin-line me-2"></i>{{ $note->is_pinned ? 'إلغاء التثبيت' : 'تثبيت' }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick="event.stopPropagation(); toggleFavorite({{ $note->id }});">
                            <i class="ri-star-line me-2"></i>{{ $note->is_favorite ? 'إزالة من المفضلة' : 'إضافة للمفضلة' }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                           onclick="event.stopPropagation(); archiveNote({{ $note->id }});">
                            <i class="ri-archive-line me-2"></i>أرشفة
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                           onclick="event.stopPropagation(); deleteNote({{ $note->id }}, event);">
                            <i class="ri-delete-bin-line me-2"></i>حذف
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <p class="card-text text-muted" style="max-height: 100px; overflow: hidden; text-overflow: ellipsis;">
            {{ Str::limit($note->content, 150) }}
        </p>

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex gap-2">
                @if($note->is_pinned)
                    <span class="badge bg-warning">📌 مثبتة</span>
                @endif
                @if($note->is_favorite)
                    <span class="badge bg-danger">⭐ مفضلة</span>
                @endif
                @if($note->reminder_at)
                    <span class="badge bg-info">
                        <i class="ri-alarm-line"></i> {{ $note->reminder_at->format('Y/m/d H:i') }}
                    </span>
                @endif
            </div>
            <small class="text-muted">
                <i class="ri-time-line"></i> {{ $note->created_at->diffForHumans() }}
            </small>
        </div>
    </div>
</div>
