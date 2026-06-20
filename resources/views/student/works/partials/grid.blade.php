<div class="row g-4" id="student-works-cards">
    @forelse($works as $index => $work)
        @include('student.works.partials.work-card', [
            'work' => $work,
            'index' => $index,
            'categories' => $categories,
            'statuses' => $statuses,
        ])
    @empty
        <div class="col-12">
            <div class="student-my-courses-empty text-center py-5">
                <div class="student-my-courses-empty__icon mb-4">
                    <i class="fe fe-search"></i>
                </div>
                @if(request()->hasAny(['search', 'status', 'category']))
                    <h4 class="mb-2">لا توجد أعمال مطابقة</h4>
                    <p class="text-muted mb-4">جرّب تعديل الفلاتر أو البحث بكلمات أخرى.</p>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" id="sw-empty-reset">
                        <i class="fe fe-rotate-ccw me-1"></i>إعادة تعيين
                    </button>
                @else
                    @include('student.works.partials.empty')
                @endif
            </div>
        </div>
    @endforelse
</div>

@if($works->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-2 student-works-pagination">
        {{ $works->withQueryString()->links() }}
    </div>
@endif
