@php
    $typeLabels = \App\Models\Resource::resourceTypeOptions();
    $classLabels = \App\Models\Resource::classificationOptions();
@endphp
<div class="row g-4" id="external-resources-cards">
    @forelse ($resources as $resource)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <div class="card custom-card h-100 external-resource-card border shadow-none">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="avatar avatar-lg bg-primary-transparent text-primary rounded-3 flex-shrink-0">
                            <i class="fas {{ $resource->getIconClass() }} fs-3"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="mb-1 fw-semibold text-truncate" title="{{ $resource->title }}">{{ $resource->title }}</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-primary-transparent text-primary">{{ $typeLabels[$resource->resource_type] ?? $resource->resource_type }}</span>
                                @if($resource->classification && isset($classLabels[$resource->classification]))
                                    <span class="badge bg-secondary-transparent">{{ $classLabels[$resource->classification] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($resource->description)
                        <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $resource->description }}
                        </p>
                    @else
                        <div class="flex-grow-1"></div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 align-items-center pt-2 border-top border-block-dashed mt-auto">
                        @if($resource->resource_source === 'url' && $resource->resource_url)
                            <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                                <i class="ri-external-link-line me-1"></i>فتح الرابط
                            </a>
                        @elseif($resource->file_path)
                            @if($resource->allow_download)
                                <a href="{{ route('student.external-resources.access', $resource) }}" class="btn btn-primary btn-sm">
                                    <i class="ri-download-2-line me-1"></i>تحميل
                                </a>
                            @elseif(in_array($resource->resource_type, ['pdf', 'image'], true))
                                <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-eye-line me-1"></i>معاينة
                                </a>
                            @else
                                <span class="text-muted small"><i class="ri-lock-line me-1"></i>التحميل غير مفعّل</span>
                            @endif
                            @if($resource->file_name)
                                <span class="text-muted small ms-auto text-truncate" style="max-width: 140px;" title="{{ $resource->file_name }}">{{ $resource->file_name }}</span>
                            @endif
                        @else
                            <span class="text-muted small">لا يوجد ملف أو رابط</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <span class="avatar avatar-xl bg-secondary-transparent text-secondary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center">
                        <i class="ri-folder-open-line fs-1"></i>
                    </span>
                    <h6 class="mb-2">لا توجد موارد مطابقة</h6>
                    <p class="text-muted mb-0">جرّب تغيير الفلاتر أو البحث بكلمات أخرى.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($resources->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-2">
        {{ $resources->withQueryString()->links() }}
    </div>
@endif
