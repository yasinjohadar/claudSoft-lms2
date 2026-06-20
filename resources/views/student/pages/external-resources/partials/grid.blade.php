@php
    $typeLabels = \App\Models\Resource::resourceTypeOptions();
    $classLabels = \App\Models\Resource::classificationOptions();
    $typeVariants = [
        'external_sites' => ['color' => 'primary', 'icon' => 'fa-globe'],
        'pdf' => ['color' => 'danger', 'icon' => 'fa-file-pdf'],
        'doc' => ['color' => 'info', 'icon' => 'fa-file-word'],
        'ppt' => ['color' => 'warning', 'icon' => 'fa-file-powerpoint'],
        'excel' => ['color' => 'success', 'icon' => 'fa-file-excel'],
        'image' => ['color' => 'teal', 'icon' => 'fa-file-image'],
        'audio' => ['color' => 'purple', 'icon' => 'fa-file-audio'],
        'archive' => ['color' => 'secondary', 'icon' => 'fa-file-archive'],
        'extenstion' => ['color' => 'orange', 'icon' => 'fa-puzzle-piece'],
        'other' => ['color' => 'secondary', 'icon' => 'fa-file'],
    ];
    $classColors = [
        'programming' => 'primary',
        'design' => 'pink',
        'animation' => 'warning',
        'video' => 'danger',
        'marketing' => 'success',
        'general' => 'secondary',
        'other' => 'secondary',
    ];
@endphp

<div class="row g-4" id="external-resources-cards">
    @forelse ($resources as $index => $resource)
        @php
            $variant = $typeVariants[$resource->resource_type] ?? $typeVariants['other'];
            $classColor = $classColors[$resource->classification] ?? 'secondary';
            $domain = null;
            if ($resource->resource_source === 'url' && $resource->resource_url) {
                $domain = parse_url($resource->resource_url, PHP_URL_HOST);
                $domain = is_string($domain) ? preg_replace('/^www\./i', '', $domain) : null;
            }
        @endphp
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-external-resources-stagger" style="--stagger-delay: {{ ($index % 12) * 45 }}ms">
            <article class="student-external-resource-card h-100">
                <div class="student-external-resource-card__cover student-external-resource-card__cover--{{ $variant['color'] }}">
                    <span class="student-external-resource-card__cover-icon">
                        <i class="fas {{ $resource->getIconClass() }}"></i>
                    </span>
                    @if($domain)
                        <span class="student-external-resource-card__domain" title="{{ $resource->resource_url }}">
                            <i class="fe fe-link-2 me-1"></i>{{ Str::limit($domain, 28) }}
                        </span>
                    @endif
                    <span class="student-external-resource-card__type-badge badge bg-white student-external-resource-card__type-badge--{{ $variant['color'] }}">
                        {{ $typeLabels[$resource->resource_type] ?? $resource->resource_type }}
                    </span>
                </div>

                <div class="student-external-resource-card__body">
                    <div class="student-external-resource-card__meta">
                        @if($resource->classification && isset($classLabels[$resource->classification]))
                            <span class="badge bg-{{ $classColor }}-transparent text-{{ $classColor }} student-external-resource-card__class-badge">
                                {{ $classLabels[$resource->classification] }}
                            </span>
                        @endif
                        @if($resource->file_name)
                            <span class="student-external-resource-card__file-chip" title="{{ $resource->file_name }}">
                                <i class="fe fe-paperclip me-1"></i>{{ Str::limit($resource->file_name, 22) }}
                            </span>
                        @endif
                    </div>

                    <h6 class="student-external-resource-card__title" title="{{ $resource->title }}">{{ $resource->title }}</h6>

                    @if($resource->description)
                        <p class="student-external-resource-card__desc">{{ $resource->description }}</p>
                    @else
                        <p class="student-external-resource-card__desc student-external-resource-card__desc--muted">لا يوجد وصف لهذا المورد.</p>
                    @endif

                    <div class="student-external-resource-card__actions">
                        @if($resource->resource_source === 'url' && $resource->resource_url)
                            <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm w-100 rounded-pill">
                                <i class="fe fe-external-link me-1"></i>فتح الرابط
                            </a>
                        @elseif($resource->file_path)
                            @if($resource->allow_download)
                                <a href="{{ route('student.external-resources.access', $resource) }}" class="btn btn-primary btn-sm w-100 rounded-pill">
                                    <i class="fe fe-download me-1"></i>تحميل الملف
                                </a>
                            @elseif(in_array($resource->resource_type, ['pdf', 'image'], true))
                                <a href="{{ route('student.external-resources.access', $resource) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                    <i class="fe fe-eye me-1"></i>معاينة الملف
                                </a>
                            @else
                                <span class="student-external-resource-card__locked">
                                    <i class="fe fe-lock me-1"></i>التحميل غير مفعّل
                                </span>
                            @endif
                        @else
                            <span class="student-external-resource-card__locked">لا يوجد ملف أو رابط</span>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="student-external-resources-empty text-center py-5">
                <div class="student-external-resources-empty__icon mb-4">
                    <i class="fe fe-folder"></i>
                </div>
                <h4 class="mb-2">لا توجد موارد مطابقة</h4>
                <p class="text-muted mb-0">جرّب تغيير الفلاتر أو البحث بكلمات أخرى.</p>
            </div>
        </div>
    @endforelse
</div>

@if($resources->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-3 border-top">
        {{ $resources->withQueryString()->links() }}
    </div>
@endif
