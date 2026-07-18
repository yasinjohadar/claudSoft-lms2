@extends('admin.layouts.master')

@section('page-title')
    استعراض الملفات المحلية
@stop

@section('styles')
<style>
    .bl-page { --bl-ink:#1e293b; --bl-muted:#64748b; --bl-line:#e2e8f0; --bl-soft:#f8fafc; --bl-accent:#0f766e; }
    .bl-page .bl-hero {
        background: linear-gradient(135deg, #0f766e 0%, #134e4a 55%, #1e293b 100%);
        color: #fff; border-radius: 1rem; padding: 1.35rem 1.5rem; margin-bottom: 1.15rem;
    }
    .bl-page .bl-hero h5 { color:#fff; margin:0 0 .35rem; font-weight:700; }
    .bl-page .bl-hero p { margin:0; color:rgba(255,255,255,.85); max-width:48rem; }
    .bl-page .bl-hero .btn { border-color:rgba(255,255,255,.4); color:#fff; }
    .bl-page .bl-hero .btn:hover { background:rgba(255,255,255,.12); color:#fff; }
    .bl-page .bl-stat {
        border:1px solid var(--bl-line); border-radius:.9rem; background:#fff;
        padding:.9rem; text-align:center; height:100%;
    }
    .bl-page .bl-stat .label { font-size:.78rem; color:var(--bl-muted); }
    .bl-page .bl-stat .value { font-size:1.35rem; font-weight:700; color:var(--bl-ink); }
    .bl-page .bl-card {
        border:1px solid var(--bl-line); border-radius:1rem; background:#fff; margin-bottom:1.15rem; overflow:hidden;
    }
    .bl-page .bl-card-head {
        padding:.9rem 1.1rem; border-bottom:1px solid var(--bl-line); background:var(--bl-soft);
        display:flex; justify-content:space-between; gap:.75rem; flex-wrap:wrap; align-items:center;
    }
    .bl-page .bl-card-head h6 { margin:0; font-weight:700; }
    .bl-page .bl-card-body { padding:1.1rem; }
    .bl-page .bl-note {
        border:1px solid #99f6e4; background:#f0fdfa; color:#115e59;
        border-radius:.85rem; padding:.85rem 1rem; margin-bottom:1.15rem; font-size:.88rem;
    }
    .bl-page .bl-breadcrumb {
        display:flex; flex-wrap:wrap; gap:.35rem; align-items:center;
        padding:.75rem 1rem; background:var(--bl-soft); border:1px solid var(--bl-line);
        border-radius:.85rem; margin-bottom:1rem;
    }
    .bl-page .bl-breadcrumb a {
        color:var(--bl-accent); text-decoration:none; font-size:.88rem;
    }
    .bl-page .bl-breadcrumb a:hover { text-decoration:underline; }
    .bl-page .bl-breadcrumb .sep { color:var(--bl-muted); font-size:.75rem; }
    .bl-page .bl-shortcut {
        display:inline-block; margin:.2rem .35rem .2rem 0; padding:.25rem .65rem;
        border:1px solid #99f6e4; background:#fff; border-radius:999px; font-size:.78rem;
        color:#0f766e; text-decoration:none;
    }
    .bl-page .bl-shortcut:hover { background:#ccfbf1; color:#134e4a; }
    .bl-page .table thead th { white-space:nowrap; font-size:.82rem; }
    .bl-page .row-folder { cursor:pointer; }
    .bl-page .row-folder:hover { background:#f0fdfa; }
    .bl-page .icon-folder { color:#ca8a04; }
    .bl-page .icon-file { color:var(--bl-muted); }
</style>
@stop

@section('content')
@php
    $formatBytes = fn ($b) => $inventoryService->formatBytes((int) $b);
    $routeParams = fn (?string $path = null) => array_filter([
        'path' => $path ?? ($filters['path'] ?? ''),
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<div class="main-content app-content bl-page">
    <div class="container-fluid">

        <div class="bl-hero">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                <div>
                    <h5 class="fs-21">استعراض الملفات المحلية</h5>
                    <p>
                        تصفّح المجلدات والملفات الفعلية على السيرفر في
                        <code class="text-white">{{ $rootLabel }}</code>.
                        هذه الصفحة <strong>للقراءة فقط</strong> — لحذف النسخ المحلية استخدم صفحة «إدارة النسخ المحلية».
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('app-storage.inventory.index') }}" class="btn btn-sm btn-outline-light">
                        ← العودة للجرد
                    </a>
                    <a href="{{ route('app-storage.inventory.cloud-files') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-cloud me-1"></i> استعراض السحابة
                    </a>
                    <a href="{{ route('app-storage.inventory.local-files') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-trash-alt me-1"></i> إدارة النسخ المحلية
                    </a>
                </div>
            </div>
        </div>

        <div class="bl-note">
            <strong>ملاحظة:</strong>
            هذا الاستعراض يقرأ المسار الفيزيائي على السيرفر وليس ما يظهر عبر <code>Storage::disk('public')</code>
            (قد يكون موجهاً للسحابة). إذا كان المجلد فارغاً هنا بينما السحابة فيها ملفات، فالنسخة الفعّالة على السحابة فقط.
        </div>

        @if($browseError)
            <div class="alert alert-danger">{{ $browseError }}</div>
        @endif

        @include('admin.pages.app-storage.partials.capacity-summary', [
            'capacitySummary' => $capacitySummary,
            'inventoryService' => $inventoryService,
        ])

        @if(!empty($shortcuts))
            <div class="bl-card">
                <div class="bl-card-head">
                    <h6><i class="fas fa-bolt me-1"></i> اختصارات سريعة</h6>
                </div>
                <div class="bl-card-body">
                    @foreach($shortcuts as $shortcut)
                        <a href="{{ route('app-storage.inventory.browse-local', $routeParams($shortcut['path'])) }}"
                           class="bl-shortcut"
                           title="{{ $shortcut['disk'] }}">
                            {{ $shortcut['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($listing)
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="bl-stat">
                        <div class="label">مجلدات</div>
                        <div class="value">{{ $listing['summary']['directory_count'] }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="bl-stat">
                        <div class="label">ملفات في هذا المستوى</div>
                        <div class="value">{{ $listing['summary']['file_count'] }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bl-stat">
                        <div class="label">حجم الملفات هنا</div>
                        <div class="value">{{ $formatBytes($listing['summary']['total_bytes']) }}</div>
                    </div>
                </div>
            </div>

            <div class="bl-breadcrumb">
                @foreach($listing['breadcrumbs'] as $index => $crumb)
                    @if($index > 0)
                        <span class="sep">/</span>
                    @endif
                    @if($loop->last)
                        <span class="fw-semibold text-dark">{{ $crumb['label'] }}</span>
                    @else
                        <a href="{{ route('app-storage.inventory.browse-local', $routeParams($crumb['path'])) }}">
                            {{ $crumb['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="bl-card">
                <div class="bl-card-head">
                    <h6>
                        <i class="fas fa-folder-open me-1"></i>
                        {{ $listing['path'] === '' ? $rootLabel : $rootLabel.'/'.$listing['path'] }}
                    </h6>
                    @if($listing['path'] !== '')
                        @php
                            $parentPath = dirname($listing['path']);
                            $parentPath = $parentPath === '.' ? '' : $parentPath;
                        @endphp
                        <a href="{{ route('app-storage.inventory.browse-local', $routeParams($parentPath)) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-level-up-alt me-1"></i> مجلد أعلى
                        </a>
                    @endif
                </div>
                <div class="bl-card-body p-0">
                    @if(empty($listing['directories']) && empty($listing['files']))
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                            <div>هذا المجلد فارغ على السيرفر المحلي.</div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاسم</th>
                                        <th>النوع</th>
                                        <th>الحجم</th>
                                        <th>آخر تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($listing['directories'] as $dir)
                                        <tr class="row-folder"
                                            onclick="window.location='{{ route('app-storage.inventory.browse-local', $routeParams($dir['path'])) }}'">
                                            <td>
                                                <i class="fas fa-folder icon-folder me-2"></i>
                                                <span class="fw-semibold">{{ $dir['name'] }}</span>
                                            </td>
                                            <td><span class="badge bg-warning-subtle text-warning-emphasis">مجلد</span></td>
                                            <td class="text-muted">—</td>
                                            <td class="text-muted">—</td>
                                        </tr>
                                    @endforeach
                                    @foreach($listing['files'] as $file)
                                        <tr>
                                            <td>
                                                <i class="fas fa-file icon-file me-2"></i>
                                                {{ $file['name'] }}
                                                <div class="small text-muted font-monospace">{{ $file['path'] }}</div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">ملف</span></td>
                                            <td>{{ $formatBytes($file['size']) }}</td>
                                            <td>
                                                @if($file['last_modified'])
                                                    {{ \Carbon\Carbon::createFromTimestamp($file['last_modified'])->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>
@stop
