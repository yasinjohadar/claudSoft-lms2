@extends('admin.layouts.master')

@section('page-title')
    استعراض ملفات السحابة
@stop

@section('styles')
<style>
    .cf-page { --cf-ink:#1e293b; --cf-muted:#64748b; --cf-line:#e2e8f0; --cf-soft:#f8fafc; --cf-accent:#0369a1; }
    .cf-page .cf-hero {
        background: linear-gradient(135deg, #0369a1 0%, #0c4a6e 55%, #1e293b 100%);
        color: #fff; border-radius: 1rem; padding: 1.35rem 1.5rem; margin-bottom: 1.15rem;
    }
    .cf-page .cf-hero h5 { color:#fff; margin:0 0 .35rem; font-weight:700; }
    .cf-page .cf-hero p { margin:0; color:rgba(255,255,255,.85); max-width:48rem; }
    .cf-page .cf-hero .btn { border-color:rgba(255,255,255,.4); color:#fff; }
    .cf-page .cf-hero .btn:hover { background:rgba(255,255,255,.12); color:#fff; }
    .cf-page .cf-stat {
        border:1px solid var(--cf-line); border-radius:.9rem; background:#fff;
        padding:.9rem; text-align:center; height:100%;
    }
    .cf-page .cf-stat .label { font-size:.78rem; color:var(--cf-muted); }
    .cf-page .cf-stat .value { font-size:1.35rem; font-weight:700; color:var(--cf-ink); }
    .cf-page .cf-card {
        border:1px solid var(--cf-line); border-radius:1rem; background:#fff; margin-bottom:1.15rem; overflow:hidden;
    }
    .cf-page .cf-card-head {
        padding:.9rem 1.1rem; border-bottom:1px solid var(--cf-line); background:var(--cf-soft);
        display:flex; justify-content:space-between; gap:.75rem; flex-wrap:wrap; align-items:center;
    }
    .cf-page .cf-card-head h6 { margin:0; font-weight:700; }
    .cf-page .cf-card-body { padding:1.1rem; }
    .cf-page .cf-note {
        border:1px solid #bae6fd; background:#f0f9ff; color:#0c4a6e;
        border-radius:.85rem; padding:.85rem 1rem; margin-bottom:1.15rem; font-size:.88rem;
    }
    .cf-page .cf-breadcrumb {
        display:flex; flex-wrap:wrap; gap:.35rem; align-items:center;
        padding:.75rem 1rem; background:var(--cf-soft); border:1px solid var(--cf-line);
        border-radius:.85rem; margin-bottom:1rem;
    }
    .cf-page .cf-breadcrumb a {
        color:var(--cf-accent); text-decoration:none; font-size:.88rem;
    }
    .cf-page .cf-breadcrumb a:hover { text-decoration:underline; }
    .cf-page .cf-breadcrumb .sep { color:var(--cf-muted); font-size:.75rem; }
    .cf-page .cf-shortcut {
        display:inline-block; margin:.2rem .35rem .2rem 0; padding:.25rem .65rem;
        border:1px solid #bae6fd; background:#fff; border-radius:999px; font-size:.78rem;
        color:#0369a1; text-decoration:none;
    }
    .cf-page .cf-shortcut:hover { background:#e0f2fe; color:#0c4a6e; }
    .cf-page .table thead th { white-space:nowrap; font-size:.82rem; }
    .cf-page .row-folder { cursor:pointer; }
    .cf-page .row-folder:hover { background:#f0f9ff; }
    .cf-page .icon-folder { color:#ca8a04; }
    .cf-page .icon-file { color:var(--cf-muted); }
</style>
@stop

@section('content')
@php
    $formatBytes = fn ($b) => $inventoryService->formatBytes((int) $b);
    $routeParams = fn (?string $path = null) => array_filter([
        'config' => $selectedConfig?->id,
        'path' => $path ?? ($filters['path'] ?? ''),
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<div class="main-content app-content cf-page">
    <div class="container-fluid">

        <div class="cf-hero">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                <div>
                    <h5 class="fs-21">استعراض ملفات السحابة</h5>
                    <p>
                        تصفّح المجلدات والملفات كما هي مخزّنة على السحابة (S3 وغيرها).
                        هذه الصفحة <strong>للقراءة فقط</strong> — لا يُحذف أي ملف من السحابة من هنا.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('app-storage.inventory.index') }}" class="btn btn-sm btn-outline-light">
                        ← العودة للجرد
                    </a>
                    <a href="{{ route('app-storage.inventory.local-files') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-hdd me-1"></i> النسخ المحلية
                    </a>
                </div>
            </div>
        </div>

        <div class="cf-note">
            <strong>ملاحظة:</strong>
            اختر إعداد التخزين السحابي النشط، ثم تنقّل بين المجلدات.
            يمكنك استخدام الاختصارات السريعة للانتقال إلى مجلدات التطبيق الشائعة (مدونة، كورسات، إلخ).
        </div>

        @if($browseError)
            <div class="alert alert-danger">{{ $browseError }}</div>
        @endif

        @if($configs->isEmpty())
            <div class="alert alert-warning">
                لا يوجد إعداد تخزين سحابي نشط. راجع
                <a href="{{ route('app-storage.configs.index') }}">إعدادات التخزين</a>.
            </div>
        @else
            <div class="cf-card">
                <div class="cf-card-head">
                    <h6><i class="fas fa-database me-1"></i> إعداد التخزين</h6>
                </div>
                <div class="cf-card-body">
                    <form method="GET" action="{{ route('app-storage.inventory.cloud-files') }}" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label small text-muted">السحابة</label>
                            <select name="config" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($configs as $config)
                                    <option value="{{ $config->id }}" @selected($selectedConfig?->id === $config->id)>
                                        {{ $config->name }} ({{ $config->driver }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-sync-alt me-1"></i> تحديث
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(!empty($shortcuts))
                <div class="cf-card">
                    <div class="cf-card-head">
                        <h6><i class="fas fa-bolt me-1"></i> اختصارات سريعة</h6>
                    </div>
                    <div class="cf-card-body">
                        @foreach($shortcuts as $shortcut)
                            <a href="{{ route('app-storage.inventory.cloud-files', $routeParams($shortcut['path'])) }}"
                               class="cf-shortcut"
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
                        <div class="cf-stat">
                            <div class="label">مجلدات</div>
                            <div class="value">{{ $listing['summary']['directory_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="cf-stat">
                            <div class="label">ملفات في هذا المستوى</div>
                            <div class="value">{{ $listing['summary']['file_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="cf-stat">
                            <div class="label">حجم الملفات هنا</div>
                            <div class="value">{{ $formatBytes($listing['summary']['total_bytes']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="cf-breadcrumb">
                    @foreach($listing['breadcrumbs'] as $index => $crumb)
                        @if($index > 0)
                            <span class="sep">/</span>
                        @endif
                        @if($loop->last)
                            <span class="fw-semibold text-dark">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ route('app-storage.inventory.cloud-files', $routeParams($crumb['path'])) }}">
                                {{ $crumb['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>

                <div class="cf-card">
                    <div class="cf-card-head">
                        <h6>
                            <i class="fas fa-folder-open me-1"></i>
                            {{ $listing['path'] === '' ? 'جذر السحابة' : $listing['path'] }}
                        </h6>
                        @if($listing['path'] !== '')
                            @php
                                $parentPath = dirname($listing['path']);
                                $parentPath = $parentPath === '.' ? '' : $parentPath;
                            @endphp
                            <a href="{{ route('app-storage.inventory.cloud-files', $routeParams($parentPath)) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-level-up-alt me-1"></i> مجلد أعلى
                            </a>
                        @endif
                    </div>
                    <div class="cf-card-body p-0">
                        @if(empty($listing['directories']) && empty($listing['files']))
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 opacity-50"></i>
                                <div>هذا المجلد فارغ.</div>
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
                                                onclick="window.location='{{ route('app-storage.inventory.cloud-files', $routeParams($dir['path'])) }}'">
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
        @endif

    </div>
</div>
@stop
