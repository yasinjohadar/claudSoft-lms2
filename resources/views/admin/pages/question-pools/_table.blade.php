<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>اسم المجموعة</th>
                <th>الكورس</th>
                <th>الوصف</th>
                <th>الأسئلة</th>
                <th>الحالة</th>
                <th>تاريخ الإنشاء</th>
                <th style="width: 150px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pools as $pool)
                @php
                    $questionCount = $pool->pool_items_count ?? $pool->questions_count ?? 0;
                    $isReady = $questionCount > 0;
                @endphp
                <tr class="qp-table-row">
                    <td>{{ $loop->iteration + ($pools->currentPage() - 1) * $pools->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="qp-pool-icon"><i class="fe fe-layers"></i></span>
                            <div class="min-w-0">
                                <a href="{{ route('question-pools.show', $pool->id) }}" class="fw-semibold text-truncate d-block" style="max-width: 260px;" title="{{ $pool->name }}">
                                    {{ $pool->name }}
                                </a>
                                <small class="text-muted">
                                    <i class="fe fe-user me-1"></i>{{ $pool->creator->name ?? 'غير محدد' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($pool->course)
                            <span class="assignments-course-chip" title="{{ $pool->course->title }}">{{ $pool->course->title }}</span>
                        @else
                            <span class="qp-scope-chip qp-scope-chip--global">
                                <i class="fe fe-globe me-1"></i>عامة
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="text-muted d-inline-block text-truncate" style="max-width: 220px;" title="{{ $pool->description }}">
                            {{ $pool->description ? Str::limit($pool->description, 60) : '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="qp-questions-chip">
                            <i class="fe fe-help-circle"></i>{{ $questionCount }}
                        </span>
                    </td>
                    <td>
                        @if($isReady)
                            <span class="assignments-status-chip assignments-status-chip--published">جاهزة</span>
                        @else
                            <span class="assignments-status-chip assignments-status-chip--draft">فارغة</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $pool->created_at?->format('Y-m-d') }}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('question-pools.show', $pool->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('question-pools.edit', $pool->id) }}" class="btn btn-primary-light btn-sm assignments-actions__btn" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <form action="{{ route('question-pools.destroy', $pool->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-light btn-sm assignments-actions__btn" title="حذف">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="group-show-empty py-2">
                            <i class="fe fe-layers group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد مجموعات أسئلة</h5>
                            <p class="group-show-empty__desc mb-3">ابدأ بإنشاء أول مجموعة أسئلة لتنظيم بنك الأسئلة.</p>
                            <a href="{{ route('question-pools.create') }}" class="btn btn-primary btn-sm">
                                <i class="fe fe-plus me-1"></i>إضافة مجموعة جديدة
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pools->hasPages())
    <div class="mt-3">{{ $pools->withQueryString()->links() }}</div>
@endif
