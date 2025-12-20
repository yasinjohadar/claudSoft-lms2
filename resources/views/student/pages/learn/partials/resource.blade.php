@php
    $resource = $module->content; // Assuming polymorphic relationship
@endphp

@if($resource)
    <!-- Resource Header -->
    <div class="text-center mb-5">
        <div class="resource-icon bg-primary-transparent text-primary mx-auto mb-4"
             style="width: 100px; height: 100px; font-size: 3rem; border-radius: 20px;">
            @if($resource->resource_type == 'pdf')
                <i class="fas fa-file-pdf"></i>
            @elseif($resource->resource_type == 'document')
                <i class="fas fa-file-word"></i>
            @elseif($resource->resource_type == 'spreadsheet')
                <i class="fas fa-file-excel"></i>
            @elseif($resource->resource_type == 'presentation')
                <i class="fas fa-file-powerpoint"></i>
            @elseif($resource->resource_type == 'image')
                <i class="fas fa-file-image"></i>
            @elseif($resource->resource_type == 'archive')
                <i class="fas fa-file-archive"></i>
            @elseif($resource->resource_type == 'code')
                <i class="fas fa-file-code"></i>
            @else
                <i class="fas fa-file"></i>
            @endif
        </div>

        <h3 class="mb-2">{{ $resource->title ?? $module->title }}</h3>

        @if($resource->description)
            <p class="text-muted lead">{{ $resource->description }}</p>
        @endif
    </div>

    <!-- Resource Information -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-file-alt text-primary fa-2x mb-3"></i>
                <div class="fw-bold text-uppercase">
                    {{ strtoupper($resource->file_extension ?? $resource->resource_type ?? 'FILE') }}
                </div>
                <small class="text-muted">نوع الملف</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-hdd text-success fa-2x mb-3"></i>
                <div class="fw-bold">{{ $resource->file_size_formatted ?? 'غير معروف' }}</div>
                <small class="text-muted">حجم الملف</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-download text-info fa-2x mb-3"></i>
                <div class="fw-bold">{{ $resource->downloads_count ?? 0 }}</div>
                <small class="text-muted">مرات التحميل</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="text-center p-4 bg-light rounded">
                <i class="fas fa-calendar text-warning fa-2x mb-3"></i>
                <div class="fw-bold">{{ $resource->created_at ? $resource->created_at->format('Y-m-d') : '-' }}</div>
                <small class="text-muted">تاريخ الإضافة</small>
            </div>
        </div>
    </div>

    <!-- Download/Preview Actions -->
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <h5 class="mb-4">
                <i class="fas fa-info-circle text-primary me-2"></i>
                الملف جاهز للتحميل
            </h5>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <!-- Download Button -->
                <a href="{{ route('student.resources.download', $resource->id) }}"
                   class="btn btn-primary btn-lg"
                   onclick="trackDownload()">
                    <i class="fas fa-download me-2"></i>
                    تحميل الملف
                </a>

                <!-- Preview Button (if applicable) -->
                @if(in_array($resource->resource_type, ['pdf', 'image', 'document']))
                    <a href="{{ route('student.resources.preview', $resource->id) }}"
                       class="btn btn-outline-secondary btn-lg"
                       target="_blank">
                        <i class="fas fa-eye me-2"></i>
                        معاينة
                    </a>
                @endif

                <!-- External Link (if available) -->
                @if($resource->external_url)
                    <a href="{{ $resource->external_url }}"
                       class="btn btn-outline-info btn-lg"
                       target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>
                        فتح الرابط الخارجي
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- PDF Preview (if PDF) -->
    @if($resource->resource_type == 'pdf' && $resource->file_path)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-eye me-2"></i>معاينة الملف
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="togglePdfPreview()">
                    <i class="fas fa-chevron-down" id="pdfToggle"></i>
                </button>
            </div>
            <div class="card-body d-none" id="pdfPreviewContainer">
                <div style="height: 600px; overflow: auto;">
                    <iframe src="{{ asset('storage/' . $resource->file_path) }}"
                            width="100%"
                            height="100%"
                            style="border: none;">
                    </iframe>
                </div>
            </div>
        </div>
    @endif

    <!-- Image Preview (if Image) -->
    @if($resource->resource_type == 'image' && $resource->file_path)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-image me-2"></i>معاينة الصورة
                </h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $resource->file_path) }}"
                     alt="{{ $resource->title }}"
                     class="img-fluid rounded shadow"
                     style="max-height: 600px;">
            </div>
        </div>
    @endif

    <!-- Resource Description/Content -->
    @if($resource->content)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-align-right me-2"></i>وصف الملف
                </h6>
            </div>
            <div class="card-body">
                <div class="lesson-content">
                    {!! $resource->content !!}
                </div>
            </div>
        </div>
    @endif

    <!-- Additional Notes -->
    @if($resource->notes)
        <div class="alert alert-info">
            <h6 class="alert-heading">
                <i class="fas fa-sticky-note me-2"></i>ملاحظات هامة
            </h6>
            <p class="mb-0">{{ $resource->notes }}</p>
        </div>
    @endif

    <!-- Related Resources -->
    @if($resource->related_resources && count($resource->related_resources) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-link me-2"></i>ملفات ذات صلة
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($resource->related_resources as $relatedResource)
                        <div class="col-md-6">
                            <div class="resource-item">
                                <div class="d-flex align-items-center">
                                    <div class="resource-icon bg-secondary-transparent text-secondary">
                                        <i class="fas fa-file"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $relatedResource['title'] }}</div>
                                        <small class="text-muted">{{ $relatedResource['type'] ?? '' }}</small>
                                    </div>
                                </div>
                                <a href="{{ $relatedResource['url'] }}"
                                   class="btn btn-sm btn-outline-primary"
                                   download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Instructions -->
    @if($resource->instructions)
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>تعليمات الاستخدام
                </h6>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    @foreach($resource->instructions as $instruction)
                        <li class="mb-2">{{ $instruction }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    @endif

@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-folder-open fa-4x mb-3 opacity-25"></i>
        <h5>الملف غير متوفر</h5>
        <p>يرجى المحاولة لاحقاً أو التواصل مع الدعم الفني</p>
    </div>
@endif

@push('scripts')
<script>
    function togglePdfPreview() {
        const container = document.getElementById('pdfPreviewContainer');
        const toggle = document.getElementById('pdfToggle');

        container.classList.toggle('d-none');

        if (container.classList.contains('d-none')) {
            toggle.classList.remove('fa-chevron-up');
            toggle.classList.add('fa-chevron-down');
        } else {
            toggle.classList.remove('fa-chevron-down');
            toggle.classList.add('fa-chevron-up');
        }
    }

    function trackDownload() {
        // Track download event
        fetch('{{ route('student.resources.track-download', $resource->id ?? 0) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        }).catch(error => console.error('Error tracking download:', error));
    }
</script>
@endpush
