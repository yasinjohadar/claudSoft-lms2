<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageFileCatalogService;
use App\Services\Storage\StorageInventoryService;
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
}
