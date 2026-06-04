@php
    $typeLabels = \App\Models\Resource::resourceTypeOptions();
    $classLabels = \App\Models\Resource::classificationOptions();
@endphp

<div class="row g-4" id="external-resources-cards">
    @forelse ($resources as $index => $resource)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index % 12) * 40 }}ms">
            <article class="student-external-resource-card h-100">
                <div class="student-external-resource-card__header">
                    <span class="student-external-resource-card__icon">
                        <i class="fas {{ $resource->getIconClass() }}"></i>
                    </span>
                    <div class="min-w-0 flex-fill">
                        <h6 class="student-external-resource-card__title mb-1" title="{{ $resource->title }}">{{ $resource->title }}</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-primary-transparent fs-11">{{ $typeLabels[$resource->resource_type] ?? $resource->resource_type }}</span>
                            @if($resource->classification && isset($classLabels[$resource->classification]))
                                <span class="badge bg-secondary-transparent fs-11">{{ $classLabels[$resource->classification] }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($resource->description)
                    <p class="student-external-resource-card__desc">{{ $resource->description }}</p>
                @endif

                <div class="student-external-resource-card__footer">
                    @if($resource->resource_source === 'url' && $resource->resource_url)
                        <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm rounded-pill">
                            <i class="fe fe-external-link me-1"></i>فتح الرابط
                        </a>
                    @elseif($resource->file_path)
                        @if($resource->allow_download)
                            <a href="{{ route('student.external-resources.access', $resource) }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fe fe-download me-1"></i>تحميل
                            </a>
                        @elseif(in_array($resource->resource_type, ['pdf', 'image'], true))
                            <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="fe fe-eye me-1"></i>معاينة
                            </a>
                        @else
                            <span class="text-muted small"><i class="fe fe-lock me-1"></i>التحميل غير مفعّل</span>
                        @endif
                        @if($resource->file_name)
                            <span class="student-external-resource-card__filename" title="{{ $resource->file_name }}">{{ $resource->file_name }}</span>
                        @endif
                    @else
                        <span class="text-muted small">لا يوجد ملف أو رابط</span>
                    @endif
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="student-my-courses-empty text-center py-5">
                <div class="student-my-courses-empty__icon mb-4">
                    <i class="fe fe-folder"></i>
                </div>
                <h4 class="mb-2">لا توجد موارد مطابقة</h4>
                <p class="text-muted mb-0">جرّب تغيير الفلاتر أو البحث بكلمات أخرى.</p>
            </div>
        </div>
    @endforelse
</div>

@if($resources->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-2">
        {{ $resources->withQueryString()->links() }}
    </div>
@endif
