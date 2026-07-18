<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageCloudBrowserService;
use App\Services\Storage\StorageFileCatalogService;
use App\Services\Storage\StorageInventoryService;
use App\Services\Storage\StorageLocalBrowserService;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageInventoryController extends Controller
{
    public function __construct(
        protected StorageInventoryService $inventoryService,
        protected StorageBulkMigrationService $migrationService,
        protected StorageFileCatalogService $catalogService,
        protected StorageCloudBrowserService $cloudBrowser,
        protected StorageLocalBrowserService $localBrowser,
    ) {}

    public function index(Request $request): View
    {
        $scan = $this->inventoryService->getCachedScan();

        $disk = $request->get('disk');
        $sourceKey = $request->get('source');
        $status = $request->get('status');

        $items = $this->inventoryService->filterItems($scan['items'], $disk, $sourceKey, $status);
        $summary = $this->inventoryService->summarize($items);
        $progress = $this->migrationService->getProgress();

        return view('admin.pages.app-storage.inventory', [
            'items' => $items,
            'summary' => $summary,
            'scannedAt' => $scan['scanned_at'],
            'sources' => $this->catalogService->sources(),
            'phases' => $this->catalogService->phases(),
            'phaseLabels' => config('storage_inventory.phase_labels', []),
            'statusLabels' => $this->inventoryService->statusLabels(),
            'statuses' => array_keys($this->inventoryService->statusLabels()),
            'filters' => [
                'disk' => $disk,
                'source' => $sourceKey,
                'status' => $status,
            ],
            'disks' => collect($this->catalogService->sources())->pluck('disk', 'disk')->keys()
                ->merge(collect($scan['items'])->pluck('disk')->unique()->filter()->values())
                ->unique()
                ->values(),
            'progress' => $progress,
            'inventoryService' => $this->inventoryService,
            'dryRunPreview' => session('storage_dry_run'),
            'verifyReport' => session('storage_verify'),
            'cleanupReport' => session('storage_cleanup'),
            'migrateReport' => session('storage_migrate'),
        ]);
    }

    public function scan(Request $request): RedirectResponse
    {
        $this->inventoryService->scan(
            disk: $request->get('disk') ?: null,
            sourceKey: $request->get('source') ?: null,
            phase: $request->get('phase') ?: null,
        );

        return redirect()
            ->route('app-storage.inventory.index', $request->only(['disk', 'source', 'status']))
            ->with('success', 'تم تحليل مواقع الملفات وتحديث الجرد.');
    }

    public function migrate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:local_only,both,cloud_only,missing',
            'delete_local' => 'nullable|boolean',
            'dry_run' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
            'disk' => 'nullable|string',
            'source' => 'nullable|string',
            'phase' => 'nullable|string',
            'paths' => 'nullable|array',
            'paths.*' => 'string',
        ]);

        $items = $this->resolveTargetItems($request, $validated, defaultStatus: StorageLocationResolver::STATUS_LOCAL_ONLY);

        if ($items === null) {
            return back()->with('error', 'قم بتحليل الجرد أولاً قبل الترحيل.');
        }

        if ($items === []) {
            return back()->with('warning', 'لا توجد ملفات مطابقة للترحيل.');
        }

        if ($request->boolean('dry_run')) {
            $preview = $this->migrationService->dryRun($items);

            return back()
                ->with('info', sprintf(
                    'معاينة: %d ملف للترحيل، %d متخطى، %d مفقود، %d تحذير، الحجم ~%s.',
                    $preview['eligible_count'],
                    $preview['skipped_count'],
                    $preview['missing_count'],
                    $preview['warning_count'] ?? 0,
                    $this->inventoryService->formatBytes((int) $preview['total_bytes'])
                ))
                ->with('storage_dry_run', $preview);
        }

        $deleteLocal = $request->boolean('delete_local');

        if ($request->boolean('use_queue')) {
            $migrationId = $this->migrationService->startQueuedMigration($items, $deleteLocal);

            return back()->with('success', "تم إرسال {$migrationId} إلى قائمة الانتظار لـ ".count($items).' ملف.');
        }

        $results = $this->migrationService->migrateSync($items, $deleteLocal);

        return back()
            ->with('success', sprintf(
                'اكتمل الترحيل: %d نُقل، %d تُخطى، %d فشل.',
                $results['migrated'],
                $results['skipped'],
                $results['failed']
            ))
            ->with('storage_migrate', $results);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:local_only,both,cloud_only,missing',
            'disk' => 'nullable|string',
            'source' => 'nullable|string',
            'paths' => 'nullable|array',
            'paths.*' => 'string',
        ]);

        $items = $this->resolveTargetItems($request, $validated, defaultStatus: StorageLocationResolver::STATUS_BOTH);

        if ($items === null) {
            return back()->with('error', 'قم بتحليل الجرد أولاً قبل التحقق.');
        }

        if ($items === []) {
            return back()->with('warning', 'لا توجد ملفات مطابقة للتحقق.');
        }

        $report = $this->migrationService->verify($items);

        return back()
            ->with('success', sprintf(
                'تم التحقق من %d ملف: سحابة مؤكدة=%d، نسختان=%d، محلي فقط=%d، مفقود=%d.',
                $report['checked'],
                $report['cloud_confirmed'],
                $report['both'],
                $report['local_only'],
                $report['missing']
            ))
            ->with('storage_verify', $report);
    }

    public function cleanupLocal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'disk' => 'nullable|string',
            'source' => 'nullable|string',
            'paths' => 'nullable|array',
            'paths.*' => 'string',
        ]);

        $validated['status'] = StorageLocationResolver::STATUS_BOTH;

        $items = $this->resolveTargetItems($request, $validated, defaultStatus: StorageLocationResolver::STATUS_BOTH);

        if ($items === null) {
            return back()->with('error', 'قم بتحليل الجرد أولاً قبل التنظيف.');
        }

        if ($items === []) {
            return back()->with('warning', 'لا توجد ملفات بحالة «نسختان» للتنظيف الآمن.');
        }

        $report = $this->migrationService->cleanupLocalBatch($items);

        // Refresh inventory cache for cleaned paths
        $this->inventoryService->scan(
            disk: $validated['disk'] ?? null,
            sourceKey: $validated['source'] ?? null,
        );

        return back()
            ->with('success', sprintf(
                'تنظيف محلي آمن: %d حُذف، %d تُخطى، %d فشل.',
                $report['cleaned'],
                $report['skipped'],
                $report['failed']
            ))
            ->with('storage_cleanup', $report);
    }

    /**
     * Dedicated page: manage local copies only (never deletes cloud).
     */
    public function localFiles(Request $request): View|RedirectResponse
    {
        $scan = $this->inventoryService->getCachedScan();

        if ($scan['scanned_at'] === null) {
            return redirect()
                ->route('app-storage.inventory.index')
                ->with('warning', 'قم بتحليل الجرد أولاً ثم افتح إدارة النسخ المحلية.');
        }

        $disk = $request->get('disk');
        $sourceKey = $request->get('source');
        $status = $request->get('status'); // both | local_only | empty=both+local_only

        $items = collect($scan['items'])
            ->filter(function (array $item) use ($disk, $sourceKey, $status) {
                $itemStatus = $item['status'] ?? '';

                if (! in_array($itemStatus, [
                    StorageLocationResolver::STATUS_BOTH,
                    StorageLocationResolver::STATUS_LOCAL_ONLY,
                ], true)) {
                    return false;
                }

                if ($status !== null && $status !== '' && $itemStatus !== $status) {
                    return false;
                }

                if ($disk !== null && $disk !== '' && ($item['disk'] ?? null) !== $disk) {
                    return false;
                }

                if ($sourceKey !== null && $sourceKey !== '' && ($item['source_key'] ?? null) !== $sourceKey) {
                    return false;
                }

                return true;
            })
            ->map(function (array $item) {
                $item = $this->inventoryService->enrichItem($item);
                $localLocations = array_values(array_filter(
                    $item['locations'] ?? [],
                    fn (array $loc) => empty($loc['is_cloud'])
                ));
                $cloudLocations = array_values(array_filter(
                    $item['locations'] ?? [],
                    fn (array $loc) => ! empty($loc['is_cloud'])
                ));
                $item['local_locations'] = $localLocations;
                $item['cloud_locations'] = $cloudLocations;
                $item['cloud_confirmed'] = $cloudLocations !== [];
                $item['local_bytes'] = (int) collect($localLocations)->sum('size');
                $item['selection_key'] = ($item['disk'] ?? '').'::'.($item['path'] ?? '');
                $item['can_safe_delete'] = ($item['status'] ?? '') === StorageLocationResolver::STATUS_BOTH
                    && $item['cloud_confirmed'];

                return $item;
            })
            ->values()
            ->all();

        $summary = [
            'total' => count($items),
            'both' => count(array_filter($items, fn ($i) => ($i['status'] ?? '') === StorageLocationResolver::STATUS_BOTH)),
            'local_only' => count(array_filter($items, fn ($i) => ($i['status'] ?? '') === StorageLocationResolver::STATUS_LOCAL_ONLY)),
            'safe_deletable' => count(array_filter($items, fn ($i) => ! empty($i['can_safe_delete']))),
            'local_bytes' => (int) collect($items)->sum('local_bytes'),
        ];

        return view('admin.pages.app-storage.local-files', [
            'items' => $items,
            'summary' => $summary,
            'scannedAt' => $scan['scanned_at'],
            'sources' => $this->catalogService->sources(),
            'statusLabels' => $this->inventoryService->statusLabels(),
            'filters' => [
                'disk' => $disk,
                'source' => $sourceKey,
                'status' => $status,
            ],
            'disks' => collect($items)->pluck('disk')->unique()->filter()->values(),
            'inventoryService' => $this->inventoryService,
            'deleteReport' => session('storage_local_delete'),
        ]);
    }

    public function deleteLocalFiles(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:single,selected,all_safe,all_local',
            'disk' => 'nullable|string',
            'path' => 'nullable|string',
            'keys' => 'nullable|array',
            'keys.*' => 'string',
            'allow_orphan_local' => 'nullable|boolean',
            'confirm_orphan' => 'nullable|string',
            'filter_disk' => 'nullable|string',
            'filter_source' => 'nullable|string',
            'filter_status' => 'nullable|string',
        ]);

        $scan = $this->inventoryService->getCachedScan();

        if ($scan['scanned_at'] === null) {
            return redirect()
                ->route('app-storage.inventory.index')
                ->with('error', 'قم بتحليل الجرد أولاً.');
        }

        $allowOrphan = $request->boolean('allow_orphan_local');
        $mode = $validated['mode'];

        if ($mode === 'all_local') {
            if (! $allowOrphan || $request->input('confirm_orphan') !== 'DELETE_LOCAL') {
                return back()->with('error', 'لحذف كل المحلي بما فيه «محلي فقط» يجب التأكيد بالنص DELETE_LOCAL وتفعيل الخيار.');
            }
            $allowOrphan = true;
        }

        if ($mode === 'all_safe') {
            $allowOrphan = false;
        }

        $pool = collect($scan['items'])->filter(function (array $item) use ($validated) {
            $itemStatus = $item['status'] ?? '';

            if (! in_array($itemStatus, [
                StorageLocationResolver::STATUS_BOTH,
                StorageLocationResolver::STATUS_LOCAL_ONLY,
            ], true)) {
                return false;
            }

            if (! empty($validated['filter_disk']) && ($item['disk'] ?? null) !== $validated['filter_disk']) {
                return false;
            }

            if (! empty($validated['filter_source']) && ($item['source_key'] ?? null) !== $validated['filter_source']) {
                return false;
            }

            if (! empty($validated['filter_status']) && $itemStatus !== $validated['filter_status']) {
                return false;
            }

            return true;
        })->values();

        $targets = match ($mode) {
            'single' => $pool->filter(fn (array $item) => ($item['disk'] ?? '') === ($validated['disk'] ?? '')
                && ($item['path'] ?? '') === ($validated['path'] ?? ''))->values()->all(),
            'selected' => $pool->filter(function (array $item) use ($validated) {
                $key = ($item['disk'] ?? '').'::'.($item['path'] ?? '');

                return in_array($key, $validated['keys'] ?? [], true);
            })->values()->all(),
            'all_safe' => $pool->filter(fn (array $item) => ($item['status'] ?? '') === StorageLocationResolver::STATUS_BOTH)
                ->values()->all(),
            'all_local' => $pool->all(),
            default => [],
        };

        if ($targets === []) {
            return back()->with('warning', 'لا توجد ملفات مطابقة للحذف.');
        }

        // For selected/single without allow orphan: only delete safe both unless explicitly allowed
        if (in_array($mode, ['single', 'selected'], true) && ! $allowOrphan) {
            $unsafe = array_filter(
                $targets,
                fn (array $item) => ($item['status'] ?? '') === StorageLocationResolver::STATUS_LOCAL_ONLY
            );
            if ($unsafe !== []) {
                // Still process; service will skip unsafe ones unless allowOrphan
            }
        }

        $report = $this->migrationService->cleanupLocalBatch($targets, $allowOrphan);

        $this->inventoryService->scan(
            disk: $validated['filter_disk'] ?? null,
            sourceKey: $validated['filter_source'] ?? null,
        );

        $redirect = redirect()->route('app-storage.inventory.local-files', array_filter([
            'disk' => $validated['filter_disk'] ?? null,
            'source' => $validated['filter_source'] ?? null,
            'status' => $validated['filter_status'] ?? null,
        ]))->with('storage_local_delete', $report);

        $summaryMessage = sprintf(
            'نتيجة حذف المحلي: %d نجح، %d تُخطى، %d فشل. (السحابة لم تُمس)',
            $report['cleaned'],
            $report['skipped'],
            $report['failed']
        );

        $firstFailure = collect($report['details'] ?? [])
            ->first(fn (array $detail) => ($detail['action'] ?? '') === 'failed');

        if (($report['cleaned'] ?? 0) > 0 && ($report['failed'] ?? 0) === 0) {
            return $redirect->with('success', $summaryMessage);
        }

        if (($report['failed'] ?? 0) > 0) {
            $detailMessage = $firstFailure['message'] ?? '';

            return $redirect->with('error', $summaryMessage.($detailMessage ? ' — '.$detailMessage : ''));
        }

        if (($report['skipped'] ?? 0) > 0) {
            $detailMessage = collect($report['details'] ?? [])
                ->first(fn (array $detail) => ($detail['action'] ?? '') === 'skipped')['message'] ?? '';

            return $redirect->with('warning', $summaryMessage.($detailMessage ? ' — '.$detailMessage : ''));
        }

        return $redirect->with('warning', $summaryMessage);
    }

    public function export(Request $request): StreamedResponse|JsonResponse|Response
    {
        $scan = $this->inventoryService->getCachedScan();
        $items = $this->inventoryService->filterItems(
            $scan['items'],
            $request->get('disk'),
            $request->get('source'),
            $request->get('status'),
        );
        $rows = $this->inventoryService->exportRows($items);
        $format = $request->get('format', 'csv');

        if ($format === 'json') {
            return response()->json([
                'scanned_at' => $scan['scanned_at'],
                'filters' => $request->only(['disk', 'source', 'status']),
                'summary' => $this->inventoryService->summarize($items),
                'items' => $rows,
            ], 200, [
                'Content-Disposition' => 'attachment; filename="storage-inventory.json"',
            ]);
        }

        $filename = 'storage-inventory-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            if ($rows !== []) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            } else {
                fputcsv($out, ['source', 'disk', 'path', 'status', 'size']);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function progress(): JsonResponse
    {
        return response()->json([
            'progress' => $this->migrationService->getProgress(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array<string, mixed>>|null  null = no scan yet
     */
    protected function resolveTargetItems(Request $request, array $validated, string $defaultStatus): ?array
    {
        $scan = $this->inventoryService->getCachedScan();

        if ($scan['scanned_at'] === null && empty($validated['phase'])) {
            return null;
        }

        if (! empty($validated['phase'])) {
            $scan = $this->inventoryService->scan(phase: $validated['phase']);
        }

        $items = $scan['items'];

        if (! empty($validated['paths'])) {
            $selectedPaths = $validated['paths'];

            return array_values(array_filter(
                $items,
                fn (array $item) => in_array($item['path'], $selectedPaths, true)
            ));
        }

        return $this->inventoryService->filterItems(
            $items,
            $validated['disk'] ?? null,
            $validated['source'] ?? null,
            $validated['status'] ?? $defaultStatus,
        );
    }

    public function cloudFiles(Request $request): View
    {
        $configs = $this->cloudBrowser->availableConfigs();
        $requestedConfigId = $request->integer('config') ?: null;
        $selectedConfig = $requestedConfigId
            ? $configs->firstWhere('id', $requestedConfigId)
            : $configs->first();

        $path = (string) $request->get('path', '');
        $listing = null;
        $browseError = null;
        $safePath = '';

        try {
            $safePath = $this->cloudBrowser->normalizePath($path);
        } catch (\InvalidArgumentException $e) {
            $browseError = $e->getMessage();
        }

        if ($selectedConfig && $browseError === null) {
            try {
                $listing = $this->cloudBrowser->browse($selectedConfig, $safePath);
            } catch (\Throwable $e) {
                $browseError = 'تعذّر قراءة محتويات السحابة: '.$e->getMessage();
            }
        }

        return view('admin.pages.app-storage.cloud-files', [
            'configs' => $configs,
            'selectedConfig' => $selectedConfig,
            'listing' => $listing,
            'browseError' => $browseError,
            'shortcuts' => $this->cloudBrowser->folderShortcuts(),
            'filters' => [
                'config' => $selectedConfig?->id,
                'path' => $listing['path'] ?? $safePath,
            ],
            'inventoryService' => $this->inventoryService,
        ]);
    }

    public function browseLocal(Request $request): View
    {
        $path = (string) $request->get('path', '');
        $listing = null;
        $browseError = null;
        $safePath = '';

        try {
            $safePath = $this->cloudBrowser->normalizePath($path);
        } catch (\InvalidArgumentException $e) {
            $browseError = $e->getMessage();
        }

        if ($browseError === null) {
            try {
                $listing = $this->localBrowser->browse($safePath);
            } catch (\Throwable $e) {
                $browseError = 'تعذّر قراءة الملفات المحلية: '.$e->getMessage();
            }
        }

        return view('admin.pages.app-storage.browse-local', [
            'listing' => $listing,
            'browseError' => $browseError,
            'shortcuts' => $this->localBrowser->folderShortcuts(),
            'rootLabel' => $this->localBrowser->rootLabel(),
            'filters' => [
                'path' => $listing['path'] ?? $safePath,
            ],
            'inventoryService' => $this->inventoryService,
        ]);
    }
}
