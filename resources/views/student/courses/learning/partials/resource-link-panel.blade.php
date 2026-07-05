@php
    /** @var \App\Models\Resource $resource */
    $resourceTitle = $resource->title ?? $module->title;
    $resourceUrl = $resource->resource_url ?? null;

    $typeLabels = \App\Models\Resource::resourceTypeOptions();
    $classLabels = \App\Models\Resource::classificationOptions();

    $typeVariants = [
        'external_sites' => ['color' => 'primary', 'icon' => 'ri-global-line', 'label' => 'موقع خارجي'],
        'pdf' => ['color' => 'danger', 'icon' => 'ri-file-pdf-2-line', 'label' => 'PDF'],
        'doc' => ['color' => 'info', 'icon' => 'ri-file-word-2-line', 'label' => 'مستند'],
        'ppt' => ['color' => 'warning', 'icon' => 'ri-file-ppt-2-line', 'label' => 'عرض تقديمي'],
        'excel' => ['color' => 'success', 'icon' => 'ri-file-excel-2-line', 'label' => 'جدول بيانات'],
        'image' => ['color' => 'teal', 'icon' => 'ri-image-line', 'label' => 'صورة'],
        'audio' => ['color' => 'purple', 'icon' => 'ri-music-2-line', 'label' => 'صوت'],
        'archive' => ['color' => 'secondary', 'icon' => 'ri-folder-zip-line', 'label' => 'أرشيف'],
        'extenstion' => ['color' => 'orange', 'icon' => 'ri-puzzle-line', 'label' => 'إضافة'],
        'other' => ['color' => 'secondary', 'icon' => 'ri-link-m', 'label' => 'رابط'],
    ];

    $variant = $typeVariants[$resource->resource_type] ?? $typeVariants['other'];
    if ($resourceUrl && ($resource->resource_type === 'other' || $resource->resource_type === 'external_sites' || empty($resource->resource_type))) {
        $variant = $typeVariants['external_sites'];
    }

    $domain = null;
    if ($resourceUrl) {
        $parsedHost = parse_url($resourceUrl, PHP_URL_HOST);
        $domain = is_string($parsedHost) ? preg_replace('/^www\./i', '', $parsedHost) : null;
    }

    $classColor = match ($resource->classification) {
        'programming' => 'primary',
        'design' => 'pink',
        'animation' => 'warning',
        'video' => 'danger',
        'marketing' => 'success',
        default => 'secondary',
    };
@endphp

<article class="student-learn-resource-panel dashboard-fade-in">
    <div class="student-learn-resource-panel__hero student-learn-resource-panel__hero--{{ $variant['color'] }}">
        <div class="student-learn-resource-panel__hero-content">
            <span class="student-learn-resource-panel__icon" aria-hidden="true">
                <i class="{{ $variant['icon'] }}"></i>
            </span>
            <div class="student-learn-resource-panel__hero-text min-w-0">
                <div class="student-learn-resource-panel__badges">
                    <span class="badge bg-white text-{{ $variant['color'] }} student-learn-resource-panel__type-badge">
                        {{ $typeLabels[$resource->resource_type] ?? $variant['label'] }}
                    </span>
                    @if($resource->classification && isset($classLabels[$resource->classification]))
                        <span class="badge bg-{{ $classColor }}-transparent text-{{ $classColor }}">
                            {{ $classLabels[$resource->classification] }}
                        </span>
                    @endif
                </div>
                <h4 class="student-learn-resource-panel__title mb-0">{{ $resourceTitle }}</h4>
                @if($domain)
                    <p class="student-learn-resource-panel__domain mb-0" title="{{ $resourceUrl }}">
                        <i class="ri-links-line"></i>
                        <span dir="ltr">{{ $domain }}</span>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="student-learn-resource-panel__body">
        @if(!empty($resource->description))
            <p class="student-learn-resource-panel__desc">{{ $resource->description }}</p>
        @else
            <p class="student-learn-resource-panel__desc student-learn-resource-panel__desc--muted">
                هذا الدرس يحتوي على مورد خارجي — افتح الرابط للاطلاع على المحتوى كاملاً.
            </p>
        @endif

        @if($resource->isEmbedded() && $resourceUrl)
            <div class="student-learn-resource-panel__embed-wrap">
                <iframe
                    src="{{ htmlspecialchars($resourceUrl, ENT_QUOTES, 'UTF-8') }}"
                    class="student-learn-resource-panel__embed"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                    title="{{ $resourceTitle }}">
                </iframe>
            </div>
            <div class="student-learn-resource-panel__embed-actions">
                <a href="{{ $resourceUrl }}" target="_blank" rel="noopener noreferrer"
                   class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fe fe-external-link me-1"></i>فتح في نافذة جديدة
                </a>
            </div>
        @elseif($resourceUrl)
            <div class="student-learn-resource-panel__tip">
                <span class="student-learn-resource-panel__tip-icon" aria-hidden="true">
                    <i class="ri-information-line"></i>
                </span>
                <div>
                    <strong>كيف تستخدم هذا المورد؟</strong>
                    <p class="mb-0">اضغط «فتح الرابط» لفتح المحتوى في تبويب جديد. بعد الاطلاع، عد إلى هذه الصفحة وحدّد الدرس كمكتمل.</p>
                </div>
            </div>

            @if($resourceUrl)
                <div class="student-learn-resource-panel__url-preview" dir="ltr" title="{{ $resourceUrl }}">
                    <i class="ri-link"></i>
                    <span>{{ Str::limit($resourceUrl, 88) }}</span>
                </div>
            @endif

            <div class="student-learn-resource-panel__actions">
                <a href="{{ $resourceUrl }}" target="_blank" rel="noopener noreferrer"
                   class="btn btn-primary btn-lg rounded-pill student-learn-resource-panel__cta">
                    <i class="ri-external-link-line me-2"></i>
                    فتح الرابط
                </a>
                <span class="student-learn-resource-panel__cta-hint">
                    <i class="ri-window-line me-1"></i>يفتح في تبويب جديد
                </span>
            </div>
        @elseif($resource->file_path)
            <div class="student-learn-resource-panel__tip">
                <span class="student-learn-resource-panel__tip-icon" aria-hidden="true">
                    <i class="ri-download-cloud-2-line"></i>
                </span>
                <div>
                    <strong>ملف للتحميل</strong>
                    <p class="mb-0">يمكنك تحميل الملف مباشرة والاطلاع عليه على جهازك.</p>
                </div>
            </div>

            <div class="student-learn-resource-panel__actions">
                <a href="{{ route('student.resources.download', $resource->id) }}"
                   class="btn btn-primary btn-lg rounded-pill student-learn-resource-panel__cta"
                   data-turbo="false">
                    <i class="ri-download-2-line me-2"></i>
                    تحميل الملف
                </a>
                @if($resource->file_name)
                    <span class="student-learn-resource-panel__cta-hint">
                        <i class="ri-file-line me-1"></i>{{ Str::limit($resource->file_name, 36) }}
                    </span>
                @endif
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="fe fe-alert-triangle me-2"></i>
                لا توجد بيانات صالحة لهذا المورد حالياً.
            </div>
        @endif
    </div>
</article>
