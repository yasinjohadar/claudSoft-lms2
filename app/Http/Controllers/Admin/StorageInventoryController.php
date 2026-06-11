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
use Illuminate\View\View;

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
            'statuses' => [
                StorageLocationResolver::STATUS_CLOUD_ONLY,
                StorageLocationResolver::STATUS_LOCAL_ONLY,
                StorageLocationResolver::STATUS_BOTH,
                StorageLocationResolver::STATUS_MISSING,
            ],
            'filters' => [
                'disk' => $disk,
                'source' => $sourceKey,
                'status' => $status,
            ],
            'disks' => collect($this->catalogService->sources())->pluck('disk', 'disk')->keys(),
            'progress' => $progress,
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
            ->with('success', 'تم مسح الملفات وتحديث الجرد.');
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

        $scan = $this->inventoryService->getCachedScan();

        if ($scan['scanned_at'] === null && empty($validated['phase'])) {
            return back()->with('error', 'قم بمسح الجرد أولاً قبل الترحيل.');
        }

        if (! empty($validated['phase'])) {
            $scan = $this->inventoryService->scan(phase: $validated['phase']);
        }

        $items = $scan['items'];

        if (! empty($validated['paths'])) {
            $selectedPaths = $validated['paths'];
            $items = array_values(array_filter($items, fn (array $item) => in_array($item['path'], $selectedPaths, true)));
        } else {
            $items = $this->inventoryService->filterItems(
                $items,
                $validated['disk'] ?? null,
                $validated['source'] ?? null,
                $validated['status'] ?? StorageLocationResolver::STATUS_LOCAL_ONLY,
            );
        }

        if ($items === []) {
            return back()->with('warning', 'لا توجد ملفات مطابقة للترحيل.');
        }

        if ($request->boolean('dry_run')) {
            $preview = $this->migrationService->dryRun($items);

            return back()->with('info', sprintf(
                'معاينة: %d ملف للترحيل، %d متخطى، %d مفقود، الحجم ~%s بايت.',
                $preview['eligible_count'],
                $preview['skipped_count'],
                $preview['missing_count'],
                number_format((float) $preview['total_bytes'])
            ));
        }

        $deleteLocal = $request->boolean('delete_local');

        if ($request->boolean('use_queue')) {
            $migrationId = $this->migrationService->startQueuedMigration($items, $deleteLocal);

            return back()->with('success', "تم إرسال {$migrationId} إلى قائمة الانتظار لـ ".count($items).' ملف.');
        }

        $results = $this->migrationService->migrateSync($items, $deleteLocal);

        return back()->with('success', sprintf(
            'اكتمل الترحيل: %d نُقل، %d تُخطى، %d فشل.',
            $results['migrated'],
            $results['skipped'],
            $results['failed']
        ));
    }

    public function progress(): JsonResponse
    {
        return response()->json([
            'progress' => $this->migrationService->getProgress(),
        ]);
    }
}
