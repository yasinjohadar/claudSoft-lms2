<style>
    .weekly-report-selected-lessons__section-title {
        color: #0b5ed7;
        background: rgba(13, 110, 253, 0.1);
        border-radius: 0.35rem;
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        padding: 0.5rem 0.75rem;
    }

    .weekly-report-selected-lessons__item--completed {
        background: rgba(25, 135, 84, 0.06);
    }

    .weekly-report-selected-lessons__completed-badge {
        background: rgba(25, 135, 84, 0.14) !important;
        border: 1px solid rgba(25, 135, 84, 0.35);
        color: #198754 !important;
        font-weight: 700;
    }
</style>

@if(($selectedLessonGroups ?? collect())->isNotEmpty())
    <div class="weekly-report-selected-lessons">
        @foreach($selectedLessonGroups as $group)
            <div class="weekly-report-selected-lessons__section mb-3">
                <div class="weekly-report-selected-lessons__section-title">
                    {{ $group['section_title'] }}
                </div>
                <ul class="list-group list-group-flush border rounded weekly-report-selected-lessons__list">
                    @foreach($group['items'] as $item)
                        <li class="list-group-item d-flex align-items-center gap-2 {{ $item['is_completed'] ? 'weekly-report-selected-lessons__item--completed' : '' }}">
                            <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0">
                                <i class="fe fe-book-open text-primary"></i>
                            </span>
                            <span class="flex-grow-1 min-w-0">
                                @if(!empty($item['type_label']))
                                    <span class="text-muted">{{ $item['type_label'] }}:</span>
                                @endif
                                {{ $item['title'] }}
                            </span>
                            @if($item['is_completed'])
                                <span class="badge weekly-report-selected-lessons__completed-badge">مكتمل</span>
                            @else
                                <span class="badge bg-light text-muted">غير مكتمل</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-light border mb-0">لا توجد دروس محددة.</div>
@endif
